<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Workshop_schedule_model extends CI_Model {

    private $table_schedules = 'workshop_schedules';
    private $table_blocked_dates = 'workshop_blocked_dates';
    private $table_blocked_slots = 'workshop_blocked_slots';
    private $table_bookings = 'bookings';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ==================== KONFIGURASI JADWAL HARIAN ====================

    /**
     * Get semua konfigurasi jadwal untuk workshop tertentu
     */
    public function get_schedules($workshop_id)
    {
        return $this->db->where('workshop_id', $workshop_id)
                        ->order_by('day_of_week', 'ASC')
                        ->get($this->table_schedules)
                        ->result_array();
    }

    /**
     * Get konfigurasi jadwal per hari
     */
    public function get_schedule_by_day($workshop_id, $day_of_week)
    {
        return $this->db->where('workshop_id', $workshop_id)
                        ->where('day_of_week', $day_of_week)
                        ->get($this->table_schedules)
                        ->row_array();
    }

    /**
     * Insert atau update konfigurasi jadwal (upsert)
     */
    public function save_schedule($data)
    {
        $existing = $this->get_schedule_by_day($data['workshop_id'], $data['day_of_week']);
        
        if ($existing) {
            $this->db->where('id', $existing['id'])
                     ->update($this->table_schedules, $data);
            return $existing['id'];
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table_schedules, $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Validasi BR-82 & BR-83: Interval min 30 menit, max 240 menit (4 jam)
     */
    public function validate_interval($interval)
    {
        return ($interval >= 30 && $interval <= 240 && ($interval % 30 == 0));
    }

    /**
     * Validasi kapasitas: 1-20 kendaraan
     */
    public function validate_capacity($capacity)
    {
        return ($capacity >= 1 && $capacity <= 20);
    }

    // ==================== HARI LIBUR / BLOKIR TANGGAL ====================

    /**
     * Get semua tanggal yang diblokir
     */
    public function get_blocked_dates($workshop_id, $start_date = null, $end_date = null)
    {
        $this->db->where('workshop_id', $workshop_id);
        
        if ($start_date) {
            $this->db->where('blocked_date >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('blocked_date <=', $end_date);
        }
        
        return $this->db->order_by('blocked_date', 'DESC')
                        ->get($this->table_blocked_dates)
                        ->result_array();
    }

    /**
     * Cek apakah tanggal tertentu diblokir (full day)
     */
    public function is_date_blocked($workshop_id, $date)
    {
        return $this->db->where('workshop_id', $workshop_id)
                        ->where('blocked_date', $date)
                        ->where('is_full_day', 1)
                        ->count_all_results($this->table_blocked_dates) > 0;
    }

    /**
     * Blokir tanggal (hari libur)
     */
    public function block_date($workshop_id, $date, $reason = '', $is_full_day = 1)
    {
        // Cek duplikat
        $existing = $this->db->where('workshop_id', $workshop_id)
                             ->where('blocked_date', $date)
                             ->get($this->table_blocked_dates)
                             ->row_array();
        
        if ($existing) {
            return false; // Sudah ada
        }

        $data = [
            'workshop_id' => $workshop_id,
            'blocked_date' => $date,
            'reason' => $reason,
            'is_full_day' => $is_full_day,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->table_blocked_dates, $data);
        return $this->db->insert_id();
    }

    /**
     * Buka kembali tanggal yang diblokir
     */
    public function unblock_date($workshop_id, $date)
    {
        return $this->db->where('workshop_id', $workshop_id)
                        ->where('blocked_date', $date)
                        ->delete($this->table_blocked_dates);
    }

    // ==================== SLOT TERBLOKIR SPESIFIK ====================

    /**
     * Get slot-slot yang diblokir pada tanggal tertentu
     */
    public function get_blocked_slots($workshop_id, $date)
    {
        return $this->db->where('workshop_id', $workshop_id)
                        ->where('slot_date', $date)
                        ->get($this->table_blocked_slots)
                        ->result_array();
    }

    /**
     * Blokir slot spesifik
     */
    public function block_slot($workshop_id, $date, $time, $reason = '')
    {
        $data = [
            'workshop_id' => $workshop_id,
            'slot_date' => $date,
            'slot_time' => $time,
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->table_blocked_slots, $data);
        return $this->db->insert_id();
    }

    /**
     * Buka blokir slot
     */
    public function unblock_slot($workshop_id, $date, $time)
    {
        return $this->db->where('workshop_id', $workshop_id)
                        ->where('slot_date', $date)
                        ->where('slot_time', $time)
                        ->delete($this->table_blocked_slots);
    }

    // ==================== AUTO-GENERATE SLOT (REAL-TIME CALCULATION) ====================

    /**
     * Generate slots tersedia untuk tanggal tertentu
     * Menggunakan perhitungan real-time berdasarkan konfigurasi + blocked slots
     * BR-81: Perubahan konfigurasi tidak mempengaruhi booking yang sudah ada
     */
    public function generate_available_slots($workshop_id, $date)
    {
        $day_of_week = date('N', strtotime($date)) % 7; // 0=Sun, 1=Mon, ... 6=Sat
        
        // Get konfigurasi jadwal untuk hari ini
        $schedule = $this->get_schedule_by_day($workshop_id, $day_of_week);
        
        // Jika hari ini tidak aktif/tidak ada konfigurasi
        if (!$schedule || !$schedule['is_open']) {
            return [];
        }

        // Cek apakah tanggal ini diblokir (full day)
        if ($this->is_date_blocked($workshop_id, $date)) {
            return [];
        }

        $open_time = $schedule['open_time'];
        $close_time = $schedule['close_time'];
        $interval = $schedule['slot_interval'];
        $capacity = $schedule['capacity_per_slot'];

        // Get blocked slots untuk tanggal ini
        $blocked_slots = $this->get_blocked_slots($workshop_id, $date);
        $blocked_times = array_column($blocked_slots, 'slot_time');

        // Get bookings yang sudah ada untuk tanggal ini (status: pending, accepted, in_progress)
        $bookings = $this->db->where('workshop_id', $workshop_id)
                             ->where('appointment_date', $date)
                             ->where_in('status', ['pending', 'accepted', 'in_progress'])
                             ->get($this->table_bookings)
                             ->result_array();
        
        // Hitung jumlah booking per slot time
        $booking_counts = [];
        foreach ($bookings as $booking) {
            $slot_time = $booking['appointment_time'];
            if (!isset($booking_counts[$slot_time])) {
                $booking_counts[$slot_time] = 0;
            }
            $booking_counts[$slot_time]++;
        }

        // Generate slots
        $slots = [];
        $current_time = strtotime($open_time);
        $end_time = strtotime($close_time);

        while ($current_time < $end_time) {
            $time_str = date('H:i:s', $current_time);
            $time_display = date('H:i', $current_time);
            
            // Skip jika slot ini diblokir
            if (in_array($time_str, $blocked_times)) {
                $current_time += ($interval * 60);
                continue;
            }

            $booked = isset($booking_counts[$time_str]) ? $booking_counts[$time_str] : 0;
            $available = max(0, $capacity - $booked);

            $slots[] = [
                'time' => $time_display,
                'time_full' => $time_str,
                'capacity' => $capacity,
                'booked' => $booked,
                'available' => $available,
                'is_available' => ($available > 0)
            ];

            $current_time += ($interval * 60);
        }

        return $slots;
    }

    /**
     * Get slot info untuk tanggal dan waktu tertentu
     */
    public function get_slot_info($workshop_id, $date, $time)
    {
        $slots = $this->generate_available_slots($workshop_id, $date);
        
        foreach ($slots as $slot) {
            if ($slot['time_full'] === $time || $slot['time'] === $time) {
                return $slot;
            }
        }
        
        return null;
    }

    /**
     * Reserve slot (saat booking dibuat) - BR-64
     */
    public function reserve_slot($workshop_id, $date, $time, $booking_id)
    {
        // Slot di-reserve otomatis saat booking dibuat dengan status pending/accepted
        // Tidak perlu tabel terpisah, cukup tracking via bookings table
        return true;
    }

    /**
     * Release slot (saat booking dibatalkan) - BR-64
     */
    public function release_slot($workshop_id, $date, $time, $booking_id)
    {
        // Slot akan otomatis tersedia kembali karena query menghitung available = capacity - booked
        // Saat booking di-cancel, status berubah sehingga tidak dihitung lagi
        return true;
    }

    // ==================== KALENDER MANAJEMEN MEKANIK ====================

    /**
     * Get bookings untuk tampilan kalender mingguan
     */
    public function get_calendar_bookings($workshop_id, $start_date, $end_date)
    {
        return $this->db->select('b.*, u.full_name as user_name, u.phone, v.brand, v.model, v.vehicle_number')
                        ->from('bookings b')
                        ->join('users u', 'b.user_id = u.id', 'left')
                        ->join('vehicles v', 'b.vehicle_id = v.id', 'left')
                        ->where('b.workshop_id', $workshop_id)
                        ->where('b.appointment_date >=', $start_date)
                        ->where('b.appointment_date <=', $end_date)
                        ->where_in('b.status', ['pending', 'accepted', 'in_progress', 'completed'])
                        ->order_by('b.appointment_date', 'ASC')
                        ->order_by('b.appointment_time', 'ASC')
                        ->get()
                        ->result_array();
    }

    /**
     * Get statistik slot per hari untuk periode tertentu
     */
    public function get_slot_statistics($workshop_id, $start_date, $end_date)
    {
        $stats = [];
        $current = strtotime($start_date);
        $end = strtotime($end_date);

        while ($current <= $end) {
            $date = date('Y-m-d', $current);
            $slots = $this->generate_available_slots($workshop_id, $date);
            
            $total_capacity = 0;
            $total_booked = 0;
            
            foreach ($slots as $slot) {
                $total_capacity += $slot['capacity'];
                $total_booked += $slot['booked'];
            }

            $stats[] = [
                'date' => $date,
                'day_name' => date('l', $current),
                'total_slots' => count($slots),
                'total_capacity' => $total_capacity,
                'total_booked' => $total_booked,
                'utilization' => ($total_capacity > 0) ? round(($total_booked / $total_capacity) * 100, 2) : 0
            ];

            $current += (86400); // +1 hari
        }

        return $stats;
    }

    // ==================== HELPER FUNCTIONS ====================

    /**
     * Get nama hari dalam bahasa Indonesia
     */
    public function get_day_names()
    {
        return [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu'
        ];
    }

    /**
     * Get interval options
     */
    public function get_interval_options()
    {
        return [
            30 => '30 menit',
            60 => '60 menit (1 jam)',
            90 => '90 menit (1.5 jam)',
            120 => '120 menit (2 jam)',
            180 => '180 menit (3 jam)',
            240 => '240 menit (4 jam)'
        ];
    }

    /**
     * Initialize default schedules untuk workshop baru
     */
    public function initialize_default_schedules($workshop_id)
    {
        $days = [
            ['day' => 1, 'open' => '08:00:00', 'close' => '17:00:00'], // Senin
            ['day' => 2, 'open' => '08:00:00', 'close' => '17:00:00'], // Selasa
            ['day' => 3, 'open' => '08:00:00', 'close' => '17:00:00'], // Rabu
            ['day' => 4, 'open' => '08:00:00', 'close' => '17:00:00'], // Kamis
            ['day' => 5, 'open' => '08:00:00', 'close' => '17:00:00'], // Jumat
            ['day' => 6, 'open' => '08:00:00', 'close' => '14:00:00'], // Sabtu
            ['day' => 0, 'open' => null, 'close' => null]  // Minggu (tutup)
        ];

        foreach ($days as $day) {
            $data = [
                'workshop_id' => $workshop_id,
                'day_of_week' => $day['day'],
                'is_open' => ($day['open'] !== null) ? 1 : 0,
                'open_time' => $day['open'],
                'close_time' => $day['close'],
                'slot_interval' => 60,
                'capacity_per_slot' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->save_schedule($data);
        }
    }
}
