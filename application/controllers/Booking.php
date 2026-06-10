<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Booking Controller (User Side)
 * 
 * Handles multi-step booking flow:
 * Step 1: Select Vehicle
 * Step 2: Select Date & Time Slot
 * Step 3: Select Services
 * Step 4: Confirmation
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Controllers
 * @version     4.0
 */
class Booking extends Customer_Controller {

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('booking_model');
        $this->load->model('vehicle_model');
        $this->load->model('workshop_model');
        $this->load->model('workshop_schedule_model');
        $this->load->library('form_validation');
    }

    // ================================================================
    // BOOKING FLOW - MAIN ENTRY
    // ================================================================

    /**
     * Start new booking - redirect to step 1
     * @param int|null $workshop_id Optional workshop ID if coming from map/search
     */
    public function index($workshop_id = NULL)
    {
        // If workshop specified, go directly to step 1 with preselected workshop
        if ($workshop_id) {
            redirect('booking/step1/' . $workshop_id);
        }

        // Otherwise, show workshop selection first
        $data['page_title'] = 'Pilih Bengkel';
        $data['workshops'] = $this->workshop_model->get_active_workshops();
        $data['step'] = 0; // Set step to 0 for workshop selection page
        
        $this->render('user/booking/workshop_select', $data, FALSE, 'layouts/booking_layout');
    }

    // ================================================================
    // STEP 1: SELECT VEHICLE
    // ================================================================

    /**
     * Step 1: Select vehicle for the booking
     * @param int $workshop_id
     */
    public function step1($workshop_id)
    {
        // Validate workshop exists and is active
        $workshop = $this->workshop_model->find_by_id($workshop_id);
        if (!$workshop || $workshop['status'] !== 'active') {
            $this->session->set_flashdata('error', 'Bengkel tidak ditemukan atau tidak aktif.');
            redirect('booking');
        }

        $data['page_title'] = 'Langkah 1: Pilih Kendaraan';
        $data['workshop'] = $workshop;
        $data['step'] = 1;

        // Get user's vehicles
        $data['vehicles'] = $this->vehicle_model->get_user_vehicles($this->user_id);

        // If no vehicles, redirect to add vehicle
        if (empty($data['vehicles'])) {
            $this->session->set_flashdata('info', 'Anda belum memiliki kendaraan. Silakan tambahkan kendaraan terlebih dahulu.');
            redirect('user/vehicles/add?redirect=' . urlencode('booking/step1/' . $workshop_id));
        }

        // Handle form submission
        if ($this->input->post()) {
            $vehicle_id = $this->input->post('vehicle_id');

            // Validate vehicle belongs to user
            $vehicle = $this->vehicle_model->find_by_id($vehicle_id);
            if (!$vehicle || $vehicle->user_id != $this->user_id) {
                $this->session->set_flashdata('error', 'Kendaraan tidak valid.');
                redirect('booking/step1/' . $workshop_id);
            }

            // Store in session and proceed to step 2
            $this->_init_booking_session([
                'workshop_id' => $workshop_id,
                'vehicle_id' => $vehicle_id
            ]);

            redirect('booking/step2/' . $workshop_id);
        }

        $this->render('user/booking/step1_vehicle', $data, FALSE, 'layouts/booking_layout');
    }

    // ================================================================
    // STEP 2: SELECT DATE & TIME SLOT
    // ================================================================

    /**
     * Step 2: Select date and time slot
     * @param int $workshop_id
     */
    public function step2($workshop_id)
    {
        // Validate booking session
        $booking_data = $this->_get_booking_session();
        if (!$booking_data || empty($booking_data['vehicle_id'])) {
            redirect('booking/step1/' . $workshop_id);
        }

        $workshop = $this->workshop_model->find_by_id($workshop_id);
        if (!$workshop) {
            redirect('booking');
        }

        $data['page_title'] = 'Langkah 2: Pilih Jadwal';
        $data['workshop'] = $workshop;
        $data['step'] = 2;
        $data['vehicle'] = $this->vehicle_model->find_by_id($booking_data['vehicle_id']);

        // Get current month and year for calendar
        $data['current_month'] = $this->input->get('month', TRUE) ?: date('m');
        $data['current_year'] = $this->input->get('year', TRUE) ?: date('Y');

        // Get selected date (if any)
        $data['selected_date'] = $this->input->get('date', TRUE);

        // Get available slots for selected date
        $data['available_slots'] = [];
        if ($data['selected_date']) {
            // Validate date
            $validation = $this->booking_model->validate_booking_date($data['selected_date']);
            if (!$validation['valid']) {
                $this->session->set_flashdata('error', $validation['message']);
                $data['selected_date'] = NULL;
            } else {
                $data['available_slots'] = $this->booking_model->get_available_slots(
                    $workshop_id,
                    $data['selected_date']
                );
            }
        }

        // Get date availability status for calendar
        $data['calendar_data'] = $this->_get_calendar_availability($workshop_id, $data['current_year'], $data['current_month']);

        // Handle slot selection
        if ($this->input->post()) {
            $selected_date = $this->input->post('slot_date');
            $selected_time = $this->input->post('slot_time');

            if (!$selected_date || !$selected_time) {
                $this->session->set_flashdata('error', 'Pilih tanggal dan waktu slot.');
                redirect('booking/step2/' . $workshop_id);
            }

            // Final availability check (race condition prevention)
            $availability = $this->booking_model->check_slot_availability($workshop_id, $selected_date, $selected_time);
            if (!$availability['available']) {
                $this->session->set_flashdata('error', 'Slot baru saja terisi. Silakan pilih slot lain.');
                redirect('booking/step2/' . $workshop_id . '?date=' . $selected_date);
            }

            // Store in session and proceed to step 3
            $booking_data['scheduled_date'] = $selected_date;
            $booking_data['scheduled_time'] = $selected_time;
            $this->_update_booking_session($booking_data);

            redirect('booking/step3/' . $workshop_id);
        }

        $this->render('user/booking/step2_schedule', $data, FALSE, 'layouts/booking_layout');
    }

    /**
     * AJAX: Get slots for a specific date
     */
    public function ajax_get_slots($workshop_id)
    {
        $date = $this->input->get('date');

        if (!$date) {
            $this->json_error('Tanggal tidak valid', 400);
            return;
        }

        // Validate date
        $validation = $this->booking_model->validate_booking_date($date);
        if (!$validation['valid']) {
            $this->json_error($validation['message'], 400);
            return;
        }

        // Check if workshop operates on this day
        $day_of_week = date('w', strtotime($date));
        $schedule = $this->workshop_schedule_model->get_schedule_by_day($workshop_id, $day_of_week);

        if (!$schedule || !$schedule['is_open']) {
            $this->json_response([
                'slots' => [],
                'is_closed' => TRUE,
                'message' => 'Bengkel tutup pada hari ini'
            ]);
            return;
        }

        // Check if date is blocked
        if ($this->workshop_schedule_model->is_date_blocked($workshop_id, $date)) {
            $this->json_response([
                'slots' => [],
                'is_blocked' => TRUE,
                'message' => 'Tanggal ini adalah hari libur'
            ]);
            return;
        }

        // Get available slots
        $slots = $this->booking_model->get_available_slots($workshop_id, $date);

        // Format slots for display
        $formatted_slots = [];
        foreach ($slots as $slot) {
            $formatted_slots[] = [
                'time' => date('H:i', strtotime($slot['slot_time'])),
                'time_full' => $slot['slot_time'],
                'remaining' => $slot['remaining_capacity'],
                'capacity' => $slot['slot_capacity'],
                'label' => date('H:i', strtotime($slot['slot_time'])) . ' (' . $slot['remaining_capacity'] . '/' . $slot['slot_capacity'] . ')'
            ];
        }

        $this->json_response([
            'slots' => $formatted_slots,
            'date' => $date,
            'count' => count($formatted_slots)
        ]);
    }

    /**
     * AJAX: Check single slot availability (real-time validation)
     */
    public function ajax_check_slot($workshop_id)
    {
        $date = $this->input->post('date');
        $time = $this->input->post('time');

        if (!$date || !$time) {
            $this->json_error('Parameter tidak lengkap', 400);
            return;
        }

        $availability = $this->booking_model->check_slot_availability($workshop_id, $date, $time);

        $this->json_response([
            'available' => $availability['available'],
            'remaining' => $availability['remaining']
        ]);
    }

    // ================================================================
    // STEP 3: SELECT SERVICES
    // ================================================================

    /**
     * Step 3: Select services and view estimated cost
     * @param int $workshop_id
     */
    public function step3($workshop_id)
    {
        // Validate booking session
        $booking_data = $this->_get_booking_session();
        if (!$booking_data || empty($booking_data['scheduled_date'])) {
            redirect('booking/step1/' . $workshop_id);
        }

        $workshop = $this->workshop_model->find_by_id($workshop_id);
        if (!$workshop) {
            redirect('booking');
        }

        $data['page_title'] = 'Langkah 3: Pilih Layanan';
        $data['workshop'] = $workshop;
        $data['step'] = 3;
        $data['vehicle'] = $this->vehicle_model->find_by_id($booking_data['vehicle_id']);

        // Get workshop services
        $data['services'] = $this->workshop_model->get_workshop_services($workshop_id);

        // Handle service selection
        if ($this->input->post()) {
            $service_ids = $this->input->post('service_ids', TRUE);
            $service_description = $this->input->post('service_description', TRUE);
            $service_type = $this->input->post('service_type', TRUE) ?: 'regular';

            if (empty($service_ids) && empty($service_description)) {
                $this->session->set_flashdata('error', 'Pilih minimal satu layanan atau isi deskripsi keluhan.');
                redirect('booking/step3/' . $workshop_id);
            }

            // Calculate estimated price
            $estimated_price = 0;
            $selected_services = [];

            if (!empty($service_ids)) {
                foreach ($service_ids as $sid) {
                    foreach ($data['services'] as $svc) {
                        if ($svc['id'] == $sid) {
                            $estimated_price += $svc['price_min'] ?? 0;
                            $selected_services[] = $svc;
                            break;
                        }
                    }
                }
            }

            // Store in session and proceed to step 4
            $booking_data['service_ids'] = $service_ids;
            $booking_data['selected_services'] = $selected_services;
            $booking_data['service_description'] = $service_description;
            $booking_data['service_type'] = $service_type;
            $booking_data['estimated_price'] = $estimated_price;
            $booking_data['estimated_duration'] = count($service_ids) * 60; // Default 1 hour per service

            $this->_update_booking_session($booking_data);

            redirect('booking/step4/' . $workshop_id);
        }

        $this->render('user/booking/step3_services', $data, FALSE, 'layouts/booking_layout');
    }

    // ================================================================
    // STEP 4: CONFIRMATION
    // ================================================================

    /**
     * Step 4: Review and confirm booking
     * @param int $workshop_id
     */
    public function step4($workshop_id)
    {
        // Validate booking session
        $booking_data = $this->_get_booking_session();
        if (!$booking_data || empty($booking_data['service_ids'])) {
            redirect('booking/step1/' . $workshop_id);
        }

        $workshop = $this->workshop_model->find_by_id($workshop_id);
        if (!$workshop) {
            redirect('booking');
        }

        $data['page_title'] = 'Konfirmasi Booking';
        $data['workshop'] = $workshop;
        $data['step'] = 4;
        $data['vehicle'] = $this->vehicle_model->find_by_id($booking_data['vehicle_id']);
        $data['booking_data'] = $booking_data;

        // Handle confirmation
        if ($this->input->post()) {
            // Final validation - recheck slot availability
            $availability = $this->booking_model->check_slot_availability(
                $workshop_id,
                $booking_data['scheduled_date'],
                $booking_data['scheduled_time']
            );

            if (!$availability['available']) {
                // Race condition: slot taken during checkout
                $this->_clear_booking_session();
                $this->session->set_flashdata('error', 'Maaf, slot baru saja terisi oleh pengguna lain. Silakan ulangi pemesanan.');
                redirect('booking/step2/' . $workshop_id);
            }

            // Create booking
            $result = $this->booking_model->create_booking([
                'user_id' => $this->user_id,
                'workshop_id' => $workshop_id,
                'vehicle_id' => $booking_data['vehicle_id'],
                'scheduled_date' => $booking_data['scheduled_date'],
                'scheduled_time' => $booking_data['scheduled_time'],
                'service_type' => $booking_data['service_type'],
                'service_description' => $booking_data['service_description'],
                'estimated_price' => $booking_data['estimated_price'],
                'estimated_duration' => $booking_data['estimated_duration']
            ]);

            if ($result['success']) {
                // Clear session
                $this->_clear_booking_session();

                // Set success message
                $this->session->set_flashdata('success', 
                    'Booking berhasil dibuat!<br>' .
                    'Kode booking: <strong>' . $result['booking_number'] . '</strong><br>' .
                    'Silakan cek email untuk konfirmasi detail booking.'
                );

                // Redirect to booking detail or success page
                redirect('booking/success/' . $result['booking_id']);
            } else {
                $this->session->set_flashdata('error', $result['message']);
                redirect('booking/step4/' . $workshop_id);
            }
        }

        $this->render('user/booking/step4_confirm', $data, FALSE, 'layouts/booking_layout');
    }

    // ================================================================
    // BOOKING SUCCESS & DETAIL
    // ================================================================

    /**
     * Booking success page
     * @param int $booking_id
     */
    public function success($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);

        if (!$booking || $booking['user_id'] != $this->user_id) {
            show_404();
        }

        $data['page_title'] = 'Booking Berhasil';
        $data['booking'] = $booking;
        $data['workshop'] = $this->workshop_model->find_by_id($booking['workshop_id']);
        $data['vehicle'] = $this->vehicle_model->find_by_id($booking['vehicle_id']);

        $this->render('user/booking/success', $data, FALSE, 'layouts/user_layout');
    }

    /**
     * View booking detail
     * @param int $booking_id
     */
    public function detail($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);

        if (!$booking || $booking['user_id'] != $this->user_id) {
            show_404();
        }

        $data['page_title'] = 'Detail Booking';
        $data['booking'] = $booking;
        $data['workshop'] = $this->workshop_model->find_by_id($booking['workshop_id']);
        $data['vehicle'] = $this->vehicle_model->find_by_id($booking['vehicle_id']);

        $this->render('user/booking/detail', $data, FALSE, 'layouts/user_layout');
    }

    /**
     * Cancel booking
     * @param int $booking_id
     */
    public function cancel($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);

        if (!$booking || $booking['user_id'] != $this->user_id) {
            show_404();
        }

        // Only allow cancellation for certain statuses
        $allowed_statuses = ['pending', 'accepted', 'approved'];
        if (!in_array($booking['status'], $allowed_statuses)) {
            $this->session->set_flashdata('error', 'Booking dengan status ' . ucfirst($booking['status']) . ' tidak dapat dibatalkan.');
            redirect('booking/detail/' . $booking_id);
        }

        if ($this->input->post()) {
            $reason = $this->input->post('cancellation_reason', TRUE);

            $result = $this->booking_model->cancel($booking_id, $this->user_id, $reason);

            if ($result) {
                $this->session->set_flashdata('success', 'Booking berhasil dibatalkan.');
                redirect('bookings');
            } else {
                $this->session->set_flashdata('error', 'Gagal membatalkan booking. Silakan coba lagi.');
                redirect('booking/detail/' . $booking_id);
            }
        }

        $data['page_title'] = 'Batalkan Booking';
        $data['booking'] = $booking;

        $this->render('user/booking/cancel', $data, FALSE, 'layouts/user_layout');
    }

    /**
     * Reschedule booking (BR-63)
     * @param int $booking_id
     */
    public function reschedule($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);

        if (!$booking || $booking['user_id'] != $this->user_id) {
            show_404();
        }

        // BR-63: Only allow reschedule if pending
        if ($booking['status'] !== 'pending') {
            $this->session->set_flashdata('error', 'Hanya booking dengan status Pending yang dapat diubah jadwalnya.');
            redirect('booking/detail/' . $booking_id);
        }

        if ($this->input->post()) {
            $new_date = $this->input->post('new_date');
            $new_time = $this->input->post('new_time');

            $result = $this->booking_model->reschedule($booking_id, $new_date, $new_time, $this->user_id);

            if ($result['success']) {
                $this->session->set_flashdata('success', 'Jadwal booking berhasil diubah.');
                redirect('booking/detail/' . $booking_id);
            } else {
                $this->session->set_flashdata('error', $result['message']);
                redirect('booking/reschedule/' . $booking_id);
            }
        }

        $data['page_title'] = 'Ubah Jadwal Booking';
        $data['booking'] = $booking;
        $data['workshop'] = $this->workshop_model->find_by_id($booking['workshop_id']);

        // Get available slots for next 7 days
        $data['available_dates'] = [];
        for ($i = 1; $i <= 7; $i++) {
            $date = date('Y-m-d', strtotime('+ ' . $i . ' days'));
            $avail = $this->booking_model->check_date_availability($booking['workshop_id'], $date);
            if ($avail['available']) {
                $data['available_dates'][] = [
                    'date' => $date,
                    'formatted' => date('d M Y', strtotime($date)),
                    'day_name' => date('l', strtotime($date))
                ];
            }
        }

        $this->render('user/booking/reschedule', $data, FALSE, 'layouts/user_layout');
    }

    // ================================================================
    // MY BOOKINGS LIST
    // ================================================================

    /**
     * List all user bookings
     */
    public function my_bookings()
    {
        $data['page_title'] = 'Riwayat Booking';
        
        $filters = [];
        $status_filter = $this->input->get('status', TRUE);
        if ($status_filter) {
            $filters['status'] = $status_filter;
        }

        $data['bookings'] = $this->booking_model->get_user_bookings($this->user_id, $filters);
        $data['stats'] = $this->booking_model->get_user_statistics($this->user_id);

        $this->render('user/booking/my_bookings', $data, FALSE, 'layouts/user_layout');
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Initialize booking session
     * @param array $data
     */
    private function _init_booking_session($data)
    {
        $this->session->set_userdata('booking_flow', $data);
    }

    /**
     * Get booking session data
     * @return array|NULL
     */
    private function _get_booking_session()
    {
        return $this->session->userdata('booking_flow');
    }

    /**
     * Update booking session
     * @param array $data
     */
    private function _update_booking_session($data)
    {
        $this->session->set_userdata('booking_flow', $data);
    }

    /**
     * Clear booking session
     */
    private function _clear_booking_session()
    {
        $this->session->unset_userdata('booking_flow');
    }

    /**
     * Get calendar availability data for a month
     * @param int $workshop_id
     * @param string $year
     * @param string $month
     * @return array
     */
    private function _get_calendar_availability($workshop_id, $year, $month)
    {
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
        $calendar = [];

        for ($day = 1; $day <= $days_in_month; $day++) {
            $date = sprintf('%s-%s-%02d', $year, $month, $day);
            $day_of_week = date('w', strtotime($date));
            
            // Check if weekend (optional: mark as closed)
            $is_weekend = ($day_of_week == 0 || $day_of_week == 6);

            // Check availability
            $availability = $this->booking_model->check_date_availability($workshop_id, $date);

            $calendar[$day] = [
                'date' => $date,
                'day_of_week' => $day_of_week,
                'is_weekend' => $is_weekend,
                'is_available' => $availability['available'],
                'is_blocked' => $availability['is_blocked'] ?? FALSE,
                'is_closed' => $availability['is_closed'] ?? FALSE,
                'slots_count' => $availability['available_slots_count'] ?? 0
            ];
        }

        return $calendar;
    }
}

/* End of file Booking.php */
/* Location: ./application/controllers/Booking.php */
