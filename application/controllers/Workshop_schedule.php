<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Workshop_schedule extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        
        // Cek autentikasi dan role workshop_owner
        if (!$this->session->userdata('logged_in') || 
            $this->session->userdata('role') !== 'workshop_owner') {
            redirect('auth/login');
        }

        $this->load->model('Workshop_schedule_model');
        $this->load->model('Workshop_model');
        $this->load->helper(['form', 'date']);
    }

    /**
     * Halaman utama: Konfigurasi Jadwal Harian
     */
    public function index()
    {
        $workshop_id = $this->session->userdata('workshop_id');
        
        // Get schedules untuk semua hari
        $schedules = $this->Workshop_schedule_model->get_schedules($workshop_id);
        
        // Jika belum ada konfigurasi, initialize default
        if (empty($schedules)) {
            $this->Workshop_schedule_model->initialize_default_schedules($workshop_id);
            $schedules = $this->Workshop_schedule_model->get_schedules($workshop_id);
        }

        // Format schedules by day
        $schedule_by_day = [];
        foreach ($schedules as $sch) {
            $schedule_by_day[$sch['day_of_week']] = $sch;
        }

        $data = [
            'title' => 'Konfigurasi Jadwal Harian',
            'schedules' => $schedule_by_day,
            'day_names' => $this->Workshop_schedule_model->get_day_names(),
            'interval_options' => $this->Workshop_schedule_model->get_interval_options(),
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        ];

        $this->load->view('workshop/templates/header');
        $this->load->view('workshop/schedule/config', $data);
        $this->load->view('workshop/templates/footer');
    }

    /**
     * Save konfigurasi jadwal harian
     */
    public function save_schedule()
    {
        $workshop_id = $this->session->userdata('workshop_id');
        
        // Get POST data per hari
        $days = $this->input->post('day');
        
        if (!is_array($days)) {
            $this->session->set_flashdata('error', 'Data tidak valid');
            redirect('workshop_schedule');
        }

        $errors = [];
        $success_count = 0;

        foreach ($days as $day_of_week => $is_open) {
            $open_time = $this->input->post('open_time_' . $day_of_week);
            $close_time = $this->input->post('close_time_' . $day_of_week);
            $slot_interval = $this->input->post('slot_interval_' . $day_of_week);
            $capacity = $this->input->post('capacity_' . $day_of_week);

            // Jika hari ini aktif, validasi input
            if ($is_open == 1) {
                // Validasi BR-82 & BR-83: Interval 30-240 menit, kelipatan 30
                if (!$this->Workshop_schedule_model->validate_interval($slot_interval)) {
                    $errors[] = "Hari " . $this->Workshop_schedule_model->get_day_names()[$day_of_week] . ": Interval harus antara 30-240 menit (kelipatan 30)";
                    continue;
                }

                // Validasi kapasitas: 1-20
                if (!$this->Workshop_schedule_model->validate_capacity($capacity)) {
                    $errors[] = "Hari " . $this->Workshop_schedule_model->get_day_names()[$day_of_week] . ": Kapasitas harus antara 1-20 kendaraan";
                    continue;
                }

                // Validasi jam buka < jam tutup
                if ($open_time >= $close_time) {
                    $errors[] = "Hari " . $this->Workshop_schedule_model->get_day_names()[$day_of_week] . ": Jam buka harus lebih awal dari jam tutup";
                    continue;
                }
            }

            $data = [
                'workshop_id' => $workshop_id,
                'day_of_week' => $day_of_week,
                'is_open' => $is_open,
                'open_time' => ($is_open == 1) ? $open_time : null,
                'close_time' => ($is_open == 1) ? $close_time : null,
                'slot_interval' => ($is_open == 1) ? $slot_interval : 60,
                'capacity_per_slot' => ($is_open == 1) ? $capacity : 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->Workshop_schedule_model->save_schedule($data);
            $success_count++;
        }

        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode('<br>', $errors));
        } else {
            $this->session->set_flashdata('success', 'Jadwal berhasil disimpan!');
        }

        redirect('workshop_schedule');
    }

    /**
     * Halaman: Kalender Hari Libur & Blokir Tanggal
     */
    public function blocked_dates()
    {
        $workshop_id = $this->session->userdata('workshop_id');
        
        // Get blocked dates untuk 3 bulan ke depan dan 3 bulan ke belakang
        $start_date = date('Y-m-d', strtotime('-3 months'));
        $end_date = date('Y-m-d', strtotime('+3 months'));
        
        $blocked_dates = $this->Workshop_schedule_model->get_blocked_dates($workshop_id, $start_date, $end_date);

        $data = [
            'title' => 'Kalender Hari Libur',
            'blocked_dates' => $blocked_dates,
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        ];

        $this->load->view('workshop/templates/header');
        $this->load->view('workshop/schedule/blocked_dates', $data);
        $this->load->view('workshop/templates/footer');
    }

    /**
     * AJAX: Blokir tanggal (hari libur)
     */
    public function ajax_block_date()
    {
        $this->output->set_content_type('application/json');
        
        $workshop_id = $this->session->userdata('workshop_id');
        $date = $this->input->post('date');
        $reason = $this->input->post('reason');
        $is_full_day = $this->input->post('is_full_day', 1);

        if (!$date) {
            echo json_encode(['success' => false, 'message' => 'Tanggal harus diisi']);
            return;
        }

        // Cek apakah sudah diblokir
        if ($this->Workshop_schedule_model->is_date_blocked($workshop_id, $date)) {
            echo json_encode(['success' => false, 'message' => 'Tanggal ini sudah diblokir sebelumnya']);
            return;
        }

        $result = $this->Workshop_schedule_model->block_date($workshop_id, $date, $reason, $is_full_day);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Tanggal berhasil diblokir']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memblokir tanggal']);
        }
    }

    /**
     * AJAX: Buka blokir tanggal
     */
    public function ajax_unblock_date()
    {
        $this->output->set_content_type('application/json');
        
        $workshop_id = $this->session->userdata('workshop_id');
        $date = $this->input->post('date');

        $result = $this->Workshop_schedule_model->unblock_date($workshop_id, $date);

        echo json_encode(['success' => $result, 'message' => $result ? 'Blokir tanggal dihapus' : 'Gagal menghapus blokir']);
    }

    /**
     * AJAX: Blokir slot spesifik
     */
    public function ajax_block_slot()
    {
        $this->output->set_content_type('application/json');
        
        $workshop_id = $this->session->userdata('workshop_id');
        $date = $this->input->post('date');
        $time = $this->input->post('time');
        $reason = $this->input->post('reason');

        if (!$date || !$time) {
            echo json_encode(['success' => false, 'message' => 'Tanggal dan waktu harus diisi']);
            return;
        }

        $result = $this->Workshop_schedule_model->block_slot($workshop_id, $date, $time, $reason);

        echo json_encode(['success' => $result > 0, 'message' => $result > 0 ? 'Slot berhasil diblokir' : 'Gagal memblokir slot']);
    }

    /**
     * AJAX: Buka blokir slot
     */
    public function ajax_unblock_slot()
    {
        $this->output->set_content_type('application/json');
        
        $workshop_id = $this->session->userdata('workshop_id');
        $date = $this->input->post('date');
        $time = $this->input->post('time');

        $result = $this->Workshop_schedule_model->unblock_slot($workshop_id, $date, $time);

        echo json_encode(['success' => $result, 'message' => $result ? 'Blokir slot dihapus' : 'Gagal menghapus blokir']);
    }

    /**
     * AJAX: Get slots tersedia untuk tanggal tertentu
     */
    public function ajax_get_slots($date = null)
    {
        $this->output->set_content_type('application/json');
        
        $workshop_id = $this->session->userdata('workshop_id');
        
        if (!$date) {
            $date = $this->input->post('date');
        }

        if (!$date) {
            echo json_encode(['success' => false, 'message' => 'Tanggal harus diisi']);
            return;
        }

        $slots = $this->Workshop_schedule_model->generate_available_slots($workshop_id, $date);

        echo json_encode([
            'success' => true,
            'date' => $date,
            'slots' => $slots,
            'day_name' => $this->Workshop_schedule_model->get_day_names()[date('N', strtotime($date)) % 7]
        ]);
    }

    /**
     * Halaman: Kalender Manajemen Mekanik (UC-WRK-09)
     * Tampilan kalender mingguan dengan booking yang terisi
     */
    public function calendar()
    {
        $workshop_id = $this->session->userdata('workshop_id');
        
        // Default: minggu ini
        $start_param = $this->input->get('start');
        $end_param = $this->input->get('end');
        
        if ($start_param) {
            $start_date = $start_param;
        } else {
            $start_date = date('Y-m-d', strtotime('monday this week'));
        }
        
        if ($end_param) {
            $end_date = $end_param;
        } else {
            $end_date = date('Y-m-d', strtotime('sunday this week', strtotime($start_date)));
        }

        // Get bookings untuk periode ini
        $bookings = $this->Workshop_schedule_model->get_calendar_bookings($workshop_id, $start_date, $end_date);
        
        // Get statistik
        $stats = $this->Workshop_schedule_model->get_slot_statistics($workshop_id, $start_date, $end_date);

        $data = [
            'title' => 'Kalender Manajemen Mekanik',
            'bookings' => $bookings,
            'statistics' => $stats,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'success' => $this->session->flashdata('success'),
            'error' => $this->session->flashdata('error')
        ];

        $this->load->view('workshop/templates/header');
        $this->load->view('workshop/schedule/calendar', $data);
        $this->load->view('workshop/templates/footer');
    }

    /**
     * AJAX: Get events untuk FullCalendar
     */
    public function ajax_get_events()
    {
        $this->output->set_content_type('application/json');
        
        $workshop_id = $this->session->userdata('workshop_id');
        $start = $this->input->get('start');
        $end = $this->input->get('end');

        // Convert timestamp to date
        $start_date = date('Y-m-d', intval($start) / 1000);
        $end_date = date('Y-m-d', intval($end) / 1000);

        $bookings = $this->Workshop_schedule_model->get_calendar_bookings($workshop_id, $start_date, $end_date);
        $blocked_dates = $this->Workshop_schedule_model->get_blocked_dates($workshop_id, $start_date, $end_date);

        $events = [];

        // Add bookings as events
        foreach ($bookings as $booking) {
            $color = '#3788d8'; // default blue
            if ($booking['status'] === 'pending') {
                $color = '#ffc107'; // yellow
            } elseif ($booking['status'] === 'accepted') {
                $color = '#28a745'; // green
            } elseif ($booking['status'] === 'in_progress') {
                $color = '#17a2b8'; // cyan
            } elseif ($booking['status'] === 'completed') {
                $color = '#6c757d'; // gray
            }

            $events[] = [
                'id' => $booking['id'],
                'title' => $booking['user_name'] . ' - ' . ($booking['vehicle_number'] ?? 'N/A'),
                'start' => $booking['appointment_date'] . 'T' . $booking['appointment_time'],
                'end' => $booking['appointment_date'] . 'T' . date('H:i:s', strtotime($booking['appointment_time']) + 3600),
                'color' => $color,
                'url' => site_url('workshop/bookings/detail/' . $booking['id']),
                'extendedProps' => [
                    'service_type' => $booking['service_type'],
                    'status' => $booking['status'],
                    'phone' => $booking['phone'] ?? '',
                    'vehicle' => ($booking['brand'] ?? '') . ' ' . ($booking['model'] ?? '')
                ]
            ];
        }

        // Add blocked dates as all-day events
        foreach ($blocked_dates as $blocked) {
            $events[] = [
                'id' => 'blocked_' . $blocked['id'],
                'title' => 'LIBUR: ' . ($blocked['reason'] ?? 'Hari Libur'),
                'start' => $blocked['blocked_date'],
                'allDay' => true,
                'color' => '#dc3545', // red
                'display' => 'background',
                'className' => 'blocked-date-event'
            ];
        }

        echo json_encode($events);
    }

    /**
     * Detail booking dari kalender
     */
    public function booking_detail($id)
    {
        $this->load->model('Booking_model');
        
        $workshop_id = $this->session->userdata('workshop_id');
        $booking = $this->Booking_model->get_booking($id);

        if (!$booking || $booking['workshop_id'] != $workshop_id) {
            $this->session->set_flashdata('error', 'Booking tidak ditemukan');
            redirect('workshop_schedule/calendar');
        }

        $data = [
            'title' => 'Detail Booking',
            'booking' => $booking
        ];

        $this->load->view('workshop/templates/header');
        $this->load->view('workshop/schedule/booking_detail', $data);
        $this->load->view('workshop/templates/footer');
    }
}
