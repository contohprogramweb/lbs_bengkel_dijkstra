<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Schedule Controller
 * 
 * RESTful API endpoints for workshop schedule management (SRS v4.0 Section 5.6)
 * Handles available slots, booking, and cancellation
 */
class Schedule extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('workshop_schedule_model');
        $this->load->model('booking_model');
        
        // Require authentication for all API calls
        if (!$this->is_logged_in()) {
            $this->json_error('Authentication required.', 401);
            return;
        }
    }

    /**
     * GET /api/schedule/available/{workshop_id}
     * Get available dates for a workshop (next 30 days)
     */
    public function available($workshop_id)
    {
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+30 days'));
        
        $available_dates = $this->workshop_schedule_model->get_available_dates(
            $workshop_id, 
            $start_date, 
            $end_date
        );

        $this->json_response([
            'success' => true,
            'data' => $available_dates
        ], 200, 'Available dates retrieved successfully.');
    }

    /**
     * GET /api/schedule/slots/{workshop_id}/{date}
     * Get available time slots for a specific date
     * BR-82: Interval 30-240 menit, BR-83: Kapasitas 1-20
     */
    public function slots($workshop_id, $date)
    {
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->json_error('Invalid date format. Use YYYY-MM-DD.', 400);
            return;
        }

        // H+1 rule: Cannot book same day (BR-77)
        if ($date <= date('Y-m-d')) {
            $this->json_error('Penjadwalan minimal H+1 (hari berikutnya).', 400);
            return;
        }

        $slots = $this->workshop_schedule_model->get_available_slots($workshop_id, $date);

        $this->json_response([
            'success' => true,
            'data' => [
                'workshop_id' => $workshop_id,
                'date' => $date,
                'slots' => $slots
            ]
        ], 200, 'Available slots retrieved successfully.');
    }

    /**
     * POST /api/schedule/book
     * Book a time slot (creates booking)
     */
    public function book()
    {
        $workshop_id = $this->input->post('workshop_id', TRUE);
        $scheduled_date = $this->input->post('scheduled_date', TRUE);
        $scheduled_time = $this->input->post('scheduled_time', TRUE);
        $vehicle_id = $this->input->post('vehicle_id', TRUE);
        $service_description = $this->input->post('service_description', TRUE);
        $service_type = $this->input->post('service_type', TRUE);

        // Validation
        $this->form_validation->set_rules('workshop_id', 'Workshop ID', 'required|numeric');
        $this->form_validation->set_rules('scheduled_date', 'Tanggal', 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]');
        $this->form_validation->set_rules('scheduled_time', 'Waktu', 'required|regex_match[/^\d{2}:\d{2}$/]');
        $this->form_validation->set_rules('vehicle_id', 'Kendaraan', 'required|numeric');
        $this->form_validation->set_rules('service_description', 'Deskripsi Layanan', 'required|trim|min_length[10]');

        if ($this->form_validation->run() === FALSE) {
            $this->json_error(validation_errors(), 400);
            return;
        }

        // H+1 rule validation
        if ($scheduled_date <= date('Y-m-d')) {
            $this->json_error('Penjadwalan minimal H+1 (hari berikutnya).', 400);
            return;
        }

        // Check slot availability
        $is_available = $this->workshop_schedule_model->check_slot_availability(
            $workshop_id, 
            $scheduled_date, 
            $scheduled_time
        );

        if (!$is_available) {
            $this->json_error('Slot waktu tidak tersedia. Silakan pilih slot lain.', 409);
            return;
        }

        // Generate booking number (BR-61)
        $booking_number = 'B-' . date('Ymd') . '-' . str_pad($this->booking_model->get_daily_count() + 1, 4, '0', STR_PAD_LEFT);

        $booking_data = [
            'booking_number' => $booking_number,
            'user_id' => $this->user_id,
            'workshop_id' => $workshop_id,
            'vehicle_id' => $vehicle_id,
            'service_type' => $service_type ?? 'regular',
            'service_description' => $service_description,
            'scheduled_date' => $scheduled_date,
            'scheduled_time' => $scheduled_time,
            'status' => 'pending'
        ];

        $booking_id = $this->booking_model->create_booking($booking_data);

        if ($booking_id) {
            $this->json_response([
                'success' => true,
                'data' => [
                    'booking_id' => $booking_id,
                    'booking_number' => $booking_number
                ]
            ], 201, 'Booking berhasil dibuat. Menunggu konfirmasi bengkel.');
        } else {
            $this->json_error('Gagal membuat booking. Silakan coba lagi.', 500);
        }
    }

    /**
     * POST /api/schedule/cancel/{booking_id}
     * Cancel a booking
     */
    public function cancel($booking_id)
    {
        $booking = $this->booking_model->get_booking_by_id($booking_id);

        if (!$booking) {
            $this->json_error('Booking tidak ditemukan.', 404);
            return;
        }

        // Only user can cancel their own booking
        if ($booking['user_id'] != $this->user_id) {
            $this->json_error('Anda tidak memiliki izin untuk membatalkan booking ini.', 403);
            return;
        }

        // Can only cancel pending or accepted bookings
        if (!in_array($booking['status'], ['pending', 'accepted'])) {
            $this->json_error('Booking dengan status "' . $booking['status'] . '" tidak dapat dibatalkan.', 400);
            return;
        }

        $reason = $this->input->post('cancellation_reason', TRUE);

        if ($this->booking_model->cancel_booking($booking_id, $this->user_id, $reason)) {
            $this->json_response([
                'success' => true
            ], 200, 'Booking berhasil dibatalkan.');
        } else {
            $this->json_error('Gagal membatalkan booking.', 500);
        }
    }
}
