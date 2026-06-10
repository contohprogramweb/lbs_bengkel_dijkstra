<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Booking Model
 * 
 * Handles booking operations including slot management,
 * race condition handling, and booking lifecycle.
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Models
 * @version     4.0
 */
class Booking_model extends CI_Model {

    private $table_bookings = 'bookings';
    private $table_booking_slots = 'booking_slots';
    private $table_vehicles = 'vehicles';
    private $table_workshops = 'workshops';
    private $table_workshop_services = 'workshop_services';
    private $table_users = 'users';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ================================================================
    // BOOKING CRUD OPERATIONS
    // ================================================================

    /**
     * Create new booking with transaction support
     * Handles race conditions for slot availability
     * 
     * @param array $data Booking data
     * @return array ['success' => bool, 'booking_id' => int|null, 'message' => string]
     */
    public function create_booking($data)
    {
        // Start transaction for race condition handling
        $this->db->trans_start();

        try {
            // Validate slot availability (double-check for race condition)
            $slot_available = $this->check_slot_availability(
                $data['workshop_id'],
                $data['scheduled_date'],
                $data['scheduled_time']
            );

            if (!$slot_available['available']) {
                return [
                    'success' => FALSE,
                    'booking_id' => NULL,
                    'message' => 'Slot baru saja terisi. Silakan pilih slot waktu lain.'
                ];
            }

            // Generate booking number: B-YYYYMMDD-XXXX
            $booking_number = $this->generate_booking_number($data['scheduled_date']);

            // Prepare booking data
            $booking_data = [
                'booking_number' => $booking_number,
                'user_id' => $data['user_id'],
                'workshop_id' => $data['workshop_id'],
                'vehicle_id' => $data['vehicle_id'] ?? NULL,
                'service_type' => $data['service_type'] ?? 'regular',
                'service_description' => $data['service_description'] ?? '',
                'scheduled_date' => $data['scheduled_date'],
                'scheduled_time' => $data['scheduled_time'],
                'estimated_duration' => $data['estimated_duration'] ?? 60,
                'estimated_price' => $data['estimated_price'] ?? 0,
                'status' => 'pending',
                'approval_status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert booking
            $this->db->insert($this->table_bookings, $booking_data);
            $booking_id = $this->db->insert_id();

            if (!$booking_id) {
                throw new Exception('Gagal menyimpan booking');
            }

            // Decrement slot capacity
            $this->decrement_slot_capacity(
                $data['workshop_id'],
                $data['scheduled_date'],
                $data['scheduled_time']
            );

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                return [
                    'success' => FALSE,
                    'booking_id' => NULL,
                    'message' => 'Terjadi kesalahan saat menyimpan booking.'
                ];
            }

            return [
                'success' => TRUE,
                'booking_id' => $booking_id,
                'booking_number' => $booking_number,
                'message' => 'Booking berhasil dibuat.'
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [
                'success' => FALSE,
                'booking_id' => NULL,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check slot availability (for race condition prevention)
     * 
     * @param int $workshop_id
     * @param string $date
     * @param string $time
     * @return array ['available' => bool, 'remaining' => int]
     */
    public function check_slot_availability($workshop_id, $date, $time)
    {
        // Lock the row for update to prevent race condition
        $this->db->select('remaining_capacity');
        $this->db->from($this->table_booking_slots);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('slot_date', $date);
        $this->db->where('slot_time', $time);
        $this->db->where('is_active', 1);
        
        // Use FOR UPDATE lock
        $this->db->query('LOCK TABLES ' . $this->table_booking_slots . ' WRITE');
        
        $slot = $this->db->get()->row_array();
        
        $this->db->query('UNLOCK TABLES');

        if (!$slot) {
            return ['available' => FALSE, 'remaining' => 0];
        }

        return [
            'available' => $slot['remaining_capacity'] > 0,
            'remaining' => (int) $slot['remaining_capacity']
        ];
    }

    /**
     * Decrement slot capacity after successful booking
     * 
     * @param int $workshop_id
     * @param string $date
     * @param string $time
     * @return bool
     */
    private function decrement_slot_capacity($workshop_id, $date, $time)
    {
        $this->db->set('booked_count', 'booked_count + 1', FALSE);
        $this->db->set('remaining_capacity', 'remaining_capacity - 1', FALSE);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('slot_date', $date);
        $this->db->where('slot_time', $time);
        $this->db->update($this->table_booking_slots);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Generate unique booking number
     * Format: B-YYYYMMDD-XXXX
     * 
     * @param string $date
     * @return string
     */
    private function generate_booking_number($date)
    {
        $date_prefix = date('Ymd', strtotime($date));
        $prefix = 'B-' . $date_prefix . '-';

        // Get last booking number for this date
        $this->db->select('booking_number');
        $this->db->like('booking_number', $prefix, 'after');
        $this->db->order_by('booking_number', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get($this->table_bookings)->row_array();

        if ($last) {
            // Extract last 4 digits and increment
            $last_num = (int) substr($last['booking_number'], -4);
            $new_num = str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $new_num = '0001';
        }

        return $prefix . $new_num;
    }

    /**
     * Get booking by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function find_by_id($id)
    {
        return $this->db->get_where($this->table_bookings, ['id' => $id])->row_array();
    }

    /**
     * Get booking by booking number
     * 
     * @param string $booking_number
     * @return array|null
     */
    public function find_by_number($booking_number)
    {
        return $this->db->get_where($this->table_bookings, ['booking_number' => $booking_number])->row_array();
    }

    /**
     * Get bookings by user ID
     * 
     * @param int $user_id
     * @param array $filters Additional filters
     * @return array
     */
    public function get_user_bookings($user_id, $filters = [])
    {
        $this->db->select('b.*, w.name as workshop_name, v.vehicle_number, v.vehicle_type, v.brand, v.model as vehicle_model, v.year as vehicle_year');
        $this->db->from($this->table_bookings . ' b');
        $this->db->join($this->table_workshops . ' w', 'b.workshop_id = w.id', 'left');
        $this->db->join($this->table_vehicles . ' v', 'b.vehicle_id = v.id', 'left');
        $this->db->where('b.user_id', $user_id);
        $this->db->where('b.is_deleted', 0);

        if (!empty($filters['status'])) {
            $this->db->where('b.status', $filters['status']);
        }

        if (!empty($filters['approval_status'])) {
            $this->db->where('b.approval_status', $filters['approval_status']);
        }

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('b.booking_number', $filters['search']);
            $this->db->or_like('w.name', $filters['search']);
            $this->db->group_end();
        }

        if (!empty($filters['year']) && !empty($filters['month'])) {
            $start_date = $filters['year'] . '-' . str_pad($filters['month'], 2, '0', STR_PAD_LEFT) . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));
            $this->db->where('b.scheduled_date >=', $start_date);
            $this->db->where('b.scheduled_date <=', $end_date);
        } elseif (!empty($filters['start_date'])) {
            $this->db->where('b.scheduled_date >=', $filters['start_date']);
        } elseif (!empty($filters['end_date'])) {
            $this->db->where('b.scheduled_date <=', $filters['end_date']);
        }

        // Support limit parameter
        $limit = $filters['limit'] ?? NULL;
        if ($limit) {
            $this->db->limit($limit);
        }

        $this->db->order_by('b.scheduled_date', 'DESC');
        $this->db->order_by('b.scheduled_time', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Update booking status
     * 
     * @param int $booking_id
     * @param string $status
     * @param int|null $actor_user_id User who performed the action
     * @param string|null $reason Cancellation/rejection reason
     * @return bool
     */
    public function update_status($booking_id, $status, $actor_user_id = NULL, $reason = NULL)
    {
        $update_data = ['status' => $status];

        if ($status === 'cancelled' && $actor_user_id) {
            $update_data['cancelled_by'] = $actor_user_id;
            $update_data['cancelled_at'] = date('Y-m-d H:i:s');
            $update_data['cancellation_reason'] = $reason;
        }

        $this->db->where('id', $booking_id);
        return $this->db->update($this->table_bookings, $update_data);
    }

    /**
     * Reschedule booking (BR-63: User can reschedule while pending)
     * 
     * @param int $booking_id
     * @param string $new_date
     * @param string $new_time
     * @param int $user_id
     * @return array ['success' => bool, 'message' => string]
     */
    public function reschedule($booking_id, $new_date, $new_time, $user_id)
    {
        // Get current booking
        $booking = $this->find_by_id($booking_id);

        if (!$booking) {
            return ['success' => FALSE, 'message' => 'Booking tidak ditemukan'];
        }

        // BR-63: Only allow reschedule if status is pending
        if ($booking['status'] !== 'pending') {
            return ['success' => FALSE, 'message' => 'Hanya booking dengan status Pending yang dapat diubah jadwalnya'];
        }

        // Check slot availability for new time
        $slot_available = $this->check_slot_availability(
            $booking['workshop_id'],
            $new_date,
            $new_time
        );

        if (!$slot_available['available']) {
            return ['success' => FALSE, 'message' => 'Slot waktu baru tidak tersedia'];
        }

        // Start transaction
        $this->db->trans_start();

        try {
            // Increment old slot capacity (release the old slot)
            $this->increment_slot_capacity(
                $booking['workshop_id'],
                $booking['scheduled_date'],
                $booking['scheduled_time']
            );

            // Update booking with new schedule
            $this->db->where('id', $booking_id);
            $this->db->update($this->table_bookings, [
                'scheduled_date' => $new_date,
                'scheduled_time' => $new_time,
                'reschedule_count' => $booking['reschedule_count'] + 1,
                'last_rescheduled_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Decrement new slot capacity
            $this->decrement_slot_capacity(
                $booking['workshop_id'],
                $new_date,
                $new_time
            );

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                return ['success' => FALSE, 'message' => 'Gagal melakukan reschedule'];
            }

            return ['success' => TRUE, 'message' => 'Jadwal berhasil diubah'];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['success' => FALSE, 'message' => $e->getMessage()];
        }
    }

    /**
     * Increment slot capacity (for reschedule/cancellation)
     * 
     * @param int $workshop_id
     * @param string $date
     * @param string $time
     * @return bool
     */
    private function increment_slot_capacity($workshop_id, $date, $time)
    {
        $this->db->set('booked_count', 'GREATEST(booked_count - 1, 0)', FALSE);
        $this->db->set('remaining_capacity', 'remaining_capacity + 1', FALSE);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('slot_date', $date);
        $this->db->where('slot_time', $time);
        $this->db->update($this->table_booking_slots);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Cancel booking
     * 
     * @param int $booking_id
     * @param int $user_id User cancelling
     * @param string $reason Cancellation reason
     * @return bool
     */
    public function cancel($booking_id, $user_id, $reason = '')
    {
        $booking = $this->find_by_id($booking_id);

        if (!$booking) {
            return FALSE;
        }

        // Only allow cancellation for certain statuses
        $allowed_statuses = ['pending', 'accepted', 'approved'];
        if (!in_array($booking['status'], $allowed_statuses)) {
            return FALSE;
        }

        $this->db->trans_start();

        try {
            // Update booking status
            $this->update_status($booking_id, 'cancelled', $user_id, $reason);

            // Release the slot
            $this->increment_slot_capacity(
                $booking['workshop_id'],
                $booking['scheduled_date'],
                $booking['scheduled_time']
            );

            $this->db->trans_complete();
            return $this->db->trans_status() !== FALSE;

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return FALSE;
        }
    }

    // ================================================================
    // SLOT MANAGEMENT
    // ================================================================

    /**
     * Get or create slots for a workshop on a specific date
     * 
     * @param int $workshop_id
     * @param string $date
     * @return array
     */
    public function get_or_create_slots($workshop_id, $date)
    {
        // Check if slots already exist for this date
        $existing_slots = $this->db->get_where($this->table_booking_slots, [
            'workshop_id' => $workshop_id,
            'slot_date' => $date
        ])->result_array();

        if (!empty($existing_slots)) {
            return $existing_slots;
        }

        // Create default slots based on workshop schedule
        return $this->create_default_slots($workshop_id, $date);
    }

    /**
     * Create default time slots for a date
     * Based on workshop operating hours
     * 
     * @param int $workshop_id
     * @param string $date
     * @return array
     */
    private function create_default_slots($workshop_id, $date)
    {
        $this->load->model('workshop_schedule_model');

        // Get workshop schedule for the day of week
        $day_of_week = date('w', strtotime($date)); // 0=Sunday, 1=Monday, etc.
        $schedule = $this->workshop_schedule_model->get_schedule_by_day($workshop_id, $day_of_week);

        // If no schedule, use default hours
        if (!$schedule || !$schedule['is_open']) {
            return [];
        }

        $open_time = $schedule['open_time'] ?? '08:00:00';
        $close_time = $schedule['close_time'] ?? '17:00:00';
        $slot_duration = $schedule['slot_duration_minutes'] ?? 60;
        $capacity = $schedule['max_bookings_per_slot'] ?? 5;

        // Get system settings
        $default_capacity = $this->get_setting('booking_default_capacity', 5);
        $capacity = $capacity ?: $default_capacity;

        // Generate time slots
        $slots = [];
        $current_time = strtotime($open_time);
        $end_time = strtotime($close_time);

        while ($current_time < $end_time) {
            $slot_time = date('H:i:s', $current_time);
            
            $slot_data = [
                'workshop_id' => $workshop_id,
                'slot_date' => $date,
                'slot_time' => $slot_time,
                'slot_capacity' => $capacity,
                'booked_count' => 0,
                'remaining_capacity' => $capacity,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert slot
            $this->db->insert($this->table_booking_slots, $slot_data);
            $slot_data['id'] = $this->db->insert_id();
            $slots[] = $slot_data;

            $current_time += ($slot_duration * 60);
        }

        return $slots;
    }

    /**
     * Get available slots for a workshop on a specific date
     * 
     * @param int $workshop_id
     * @param string $date
     * @return array
     */
    public function get_available_slots($workshop_id, $date)
    {
        // First ensure slots exist
        $this->get_or_create_slots($workshop_id, $date);

        // Get slots with remaining capacity
        $this->db->select('*');
        $this->db->from($this->table_booking_slots);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('slot_date', $date);
        $this->db->where('remaining_capacity >', 0);
        $this->db->where('is_active', 1);
        $this->db->order_by('slot_time', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get all slots (including fully booked) for a date
     * 
     * @param int $workshop_id
     * @param string $date
     * @return array
     */
    public function get_all_slots($workshop_id, $date)
    {
        // First ensure slots exist
        $this->get_or_create_slots($workshop_id, $date);

        $this->db->select('*');
        $this->db->from($this->table_booking_slots);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('slot_date', $date);
        $this->db->order_by('slot_time', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Check if a date is available for booking
     * 
     * @param int $workshop_id
     * @param string $date
     * @return array ['available' => bool, 'has_slots' => bool, 'is_blocked' => bool]
     */
    public function check_date_availability($workshop_id, $date)
    {
        $this->load->model('workshop_schedule_model');

        // Check if date is blocked (holiday)
        $is_blocked = $this->workshop_schedule_model->is_date_blocked($workshop_id, $date);
        if ($is_blocked) {
            return ['available' => FALSE, 'has_slots' => FALSE, 'is_blocked' => TRUE];
        }

        // Check if workshop operates on this day
        $day_of_week = date('w', strtotime($date));
        $schedule = $this->workshop_schedule_model->get_schedule_by_day($workshop_id, $day_of_week);

        if (!$schedule || !$schedule['is_open']) {
            return ['available' => FALSE, 'has_slots' => FALSE, 'is_closed' => TRUE];
        }

        // Check if there are available slots
        $available_slots = $this->get_available_slots($workshop_id, $date);
        $has_available = count($available_slots) > 0;

        return [
            'available' => $has_available,
            'has_slots' => TRUE,
            'is_blocked' => FALSE,
            'available_slots_count' => count($available_slots)
        ];
    }

    // ================================================================
    // BUSINESS RULES VALIDATION
    // ================================================================

    /**
     * Validate booking date against business rules
     * BR-62: Booking minimal H+1 kecuali same-day diizinkan
     * 
     * @param string $date
     * @param string $time
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validate_booking_date($date, $time = NULL)
    {
        $today = date('Y-m-d');
        $min_date = $this->get_setting('booking_min_days_advance', 1);
        $same_day_allowed = $this->get_setting('booking_same_day_allowed', 0);
        $max_date = $this->get_setting('booking_max_days_advance', 30);

        // Check if date is in the past
        if ($date < $today) {
            return ['valid' => FALSE, 'message' => 'Tanggal tidak boleh di masa lalu'];
        }

        // Check same-day booking
        if ($date === $today && !$same_day_allowed) {
            return ['valid' => FALSE, 'message' => 'Same-day booking tidak diizinkan. Minimal booking H+1'];
        }

        // Check minimum advance days
        $min_booking_date = date('Y-m-d', strtotime($today . ' + ' . $min_date . ' days'));
        if ($date < $min_booking_date && $date !== $today) {
            return ['valid' => FALSE, 'message' => 'Booking minimal ' . $min_date . ' hari sebelum tanggal layanan'];
        }

        // Check maximum advance days
        $max_booking_date = date('Y-m-d', strtotime($today . ' + ' . $max_date . ' days'));
        if ($date > $max_booking_date) {
            return ['valid' => FALSE, 'message' => 'Booking maksimal ' . $max_date . ' hari ke depan'];
        }

        return ['valid' => TRUE, 'message' => ''];
    }

    /**
     * Get system setting value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function get_setting($key, $default = NULL)
    {
        static $settings = [];

        if (empty($settings)) {
            $this->load->model('system_setting_model');
            $settings = $this->system_setting_model->get_all_settings();
        }

        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    // ================================================================
    // STATISTICS & REPORTS
    // ================================================================

    /**
     * Get booking statistics for a user
     * 
     * @param int $user_id
     * @return array
     */
    public function get_user_statistics($user_id)
    {
        $stats = [];

        // Total bookings
        $this->db->select('COUNT(*) as total');
        $this->db->from($this->table_bookings);
        $this->db->where('user_id', $user_id);
        $this->db->where('is_deleted', 0);
        $stats['total'] = $this->db->get()->row()->total;

        // Pending bookings
        $this->db->select('COUNT(*) as pending');
		$this->db->from($this->table_bookings);
        $this->db->where('user_id', $user_id);
        $this->db->where('is_deleted', 0);
        $this->db->where('status', 'pending');
        $stats['pending'] = $this->db->get()->row()->pending;

        // Completed bookings
        $this->db->select('COUNT(*) as completed');
		$this->db->from($this->table_bookings);
        $this->db->where('user_id', $user_id);
        $this->db->where('is_deleted', 0);
        $this->db->where('status', 'completed');
        $stats['completed'] = $this->db->get()->row()->completed;

        // Upcoming bookings
        $this->db->select('COUNT(*) as upcoming');
		$this->db->from($this->table_bookings);
        $this->db->where('user_id', $user_id);
        $this->db->where('is_deleted', 0);
        $this->db->where_in('status', ['pending', 'accepted', 'approved']);
        $this->db->where('scheduled_date >=', date('Y-m-d'));
        $stats['upcoming'] = $this->db->get()->row()->upcoming;

        return $stats;
    }

    /**
     * Get bookings for calendar view
     * 
     * @param int $user_id
     * @param string $year
     * @param string $month
     * @return array
     */
    public function get_calendar_bookings($user_id, $year, $month)
    {
        $start_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $end_date = date('Y-m-t', strtotime($start_date)); // Last day of month

        $this->db->select('id, scheduled_date, scheduled_time, status, booking_number');
        $this->db->from($this->table_bookings);
        $this->db->where('user_id', $user_id);
        $this->db->where('scheduled_date >=', $start_date);
        $this->db->where('scheduled_date <=', $end_date);
        $this->db->where('is_deleted', 0);

        return $this->db->get()->result_array();
    }

    // ================================================================
    // WORKSHOP BOOKING MANAGEMENT (for Order Controller)
    // ================================================================

    /**
     * Get bookings for a workshop with filtering
     *
     * @param int $workshop_id
     * @param array $filters
     * @return array
     */
    public function get_workshop_bookings($workshop_id, $filters = [])
    {
        $this->db->select('b.*, u.full_name as user_name, u.phone as user_phone, v.brand, v.model, v.vehicle_number');
        $this->db->from($this->table_bookings . ' b');
        $this->db->join($this->table_users . ' u', 'b.user_id = u.id', 'left');
        $this->db->join($this->table_vehicles . ' v', 'b.vehicle_id = v.id', 'left');
        $this->db->where('b.workshop_id', $workshop_id);
        $this->db->where('b.is_deleted', 0);

        if (!empty($filters['status'])) {
            $this->db->where('b.status', $filters['status']);
        }

        if (!empty($filters['approval_status'])) {
            $this->db->where('b.approval_status', $filters['approval_status']);
        }

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('b.booking_number', $filters['search']);
            $this->db->or_like('u.full_name', $filters['search']);
            $this->db->or_like('v.vehicle_number', $filters['search']);
            $this->db->group_end();
        }

        if (!empty($filters['start_date'])) {
            $this->db->where('b.scheduled_date >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $this->db->where('b.scheduled_date <=', $filters['end_date']);
        }

        if (!empty($filters['limit'])) {
            $this->db->limit($filters['limit']);
        }

        $this->db->order_by('b.scheduled_date', 'DESC');
        $this->db->order_by('b.scheduled_time', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Get booking statistics for workshop
     *
     * @param int $workshop_id
     * @return array
     */
    public function get_workshop_booking_stats($workshop_id)
    {
        $stats = [];

        // Total bookings
        $this->db->select('COUNT(*) as total');
        $this->db->from($this->table_bookings);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('is_deleted', 0);
        $stats['total'] = $this->db->get()->row()->total ?? 0;

        // Pending bookings
        $this->db->select('COUNT(*) as pending');
        $this->db->from($this->table_bookings);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('status', 'pending');
        $this->db->where('is_deleted', 0);
        $stats['pending'] = $this->db->get()->row()->pending ?? 0;

        // Accepted bookings
        $this->db->select('COUNT(*) as accepted');
        $this->db->from($this->table_bookings);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('status', 'accepted');
        $this->db->where('is_deleted', 0);
        $stats['accepted'] = $this->db->get()->row()->accepted ?? 0;

        // Processed bookings
        $this->db->select('COUNT(*) as processed');
        $this->db->from($this->table_bookings);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('status', 'processed');
        $this->db->where('is_deleted', 0);
        $stats['processed'] = $this->db->get()->row()->processed ?? 0;

        // Completed bookings
        $this->db->select('COUNT(*) as completed');
        $this->db->from($this->table_bookings);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('status', 'completed');
        $this->db->where('is_deleted', 0);
        $stats['completed'] = $this->db->get()->row()->completed ?? 0;

        // Pending approvals
        $this->db->select('COUNT(*) as pending_approval');
        $this->db->from($this->table_bookings);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('approval_status', 'pending');
        $this->db->where('is_deleted', 0);
        $stats['pending_approval'] = $this->db->get()->row()->pending_approval ?? 0;

        return $stats;
    }

    // ================================================================
    // USER BOOKING STATISTICS (for Booking_management Controller)
    // ================================================================

    /**
     * Get booking statistics for user
     *
     * @param int $user_id
     * @return array
     */
    public function get_user_booking_stats($user_id)
    {
        $stats = [];

        // Total bookings
        $this->db->select('COUNT(*) as total');
        $this->db->from($this->table_bookings);
        $this->db->where('user_id', $user_id);
        $this->db->where('is_deleted', 0);
        $stats['total'] = $this->db->get()->row()->total ?? 0;

        // Pending bookings
        $this->db->select('COUNT(*) as pending');
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'pending');
        $stats['pending'] = $this->db->get()->row()->pending ?? 0;

        // Completed bookings
        $this->db->select('COUNT(*) as completed');
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'completed');
        $stats['completed'] = $this->db->get()->row()->completed ?? 0;

        // In Progress bookings
        $this->db->select('COUNT(*) as in_progress');
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'in_progress');
        $stats['in_progress'] = $this->db->get()->row()->in_progress ?? 0;

        return $stats;
    }

    /**
     * Count bookings by user
     *
     * @param int $user_id
     * @param string|null $status Optional status filter
     * @return int
     */
    public function count_by_user($user_id, $status = NULL)
    {
        $this->db->select('COUNT(*) as total');
        $this->db->from($this->table_bookings);
        $this->db->where('user_id', $user_id);
        $this->db->where('is_deleted', 0);
        
        if ($status !== NULL) {
            $this->db->where('status', $status);
        }
        
        return (int) $this->db->get()->row()->total;
    }

    /**
     * Get recent bookings by user
     *
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function get_recent_by_user($user_id, $limit = 5)
    {
        $this->db->select('b.*, w.name as workshop_name');
        $this->db->from($this->table_bookings . ' b');
        $this->db->join('workshops w', 'b.workshop_id = w.id', 'left');
        $this->db->where('b.user_id', $user_id);
        $this->db->where('b.is_deleted', 0);
        $this->db->order_by('b.created_at', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }

    /**
     * Get pending approvals for user
     *
     * @param int $user_id
     * @return array
     */
    public function get_user_pending_approvals($user_id)
    {
        $this->db->select('ba.*, b.booking_number, b.status as booking_status, w.name as workshop_name');
        $this->db->from('booking_approvals ba');
        $this->db->join('bookings b', 'ba.booking_id = b.id');
        $this->db->join('workshops w', 'b.workshop_id = w.id');
        $this->db->where('b.user_id', $user_id);
        $this->db->where('ba.status', 'pending');
        $this->db->where('ba.expires_at >', date('Y-m-d H:i:s'));
        $this->db->order_by('ba.expires_at', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Count pending approvals for user
     *
     * @param int $user_id
     * @return int
     */
    public function count_user_pending_approvals($user_id)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from('booking_approvals ba');
        $this->db->join('bookings b', 'ba.booking_id = b.id');
        $this->db->where('b.user_id', $user_id);
        $this->db->where('ba.status', 'pending');
        return (int) $this->db->get()->row()->count;
    }

    // ================================================================
    // BOOKING APPROVALS MANAGEMENT
    // ================================================================

    /**
     * Get booking approvals
     *
     * @param int $booking_id
     * @param string|null $status Filter by status
     * @return array
     */
    public function get_booking_approvals($booking_id, $status = NULL)
    {
        $this->db->select('ba.*, u.full_name as requested_by_name');
        $this->db->from('booking_approvals ba');
        $this->db->join('users u', 'ba.requested_by = u.id', 'left');
        $this->db->where('ba.booking_id', $booking_id);

        if ($status !== NULL) {
            $this->db->where('ba.status', $status);
        }

        $this->db->order_by('ba.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Create approval request
     *
     * @param array $data
     * @return int|false Approval ID or FALSE on failure
     */
    public function create_approval($data)
    {
        $result = $this->db->insert('booking_approvals', $data);
        return $result ? $this->db->insert_id() : FALSE;
    }

    /**
     * Update approval record
     *
     * @param int $approval_id
     * @param array $data
     * @return bool
     */
    public function update_approval($approval_id, $data)
    {
        $this->db->where('id', $approval_id);
        return $this->db->update('booking_approvals', $data);
    }

    /**
     * Update booking approval_status
     *
     * @param int $booking_id
     * @param string $status
     * @return bool
     */
    public function update_approval_status($booking_id, $status)
    {
        $this->db->where('id', $booking_id);
        return $this->db->update('bookings', ['approval_status' => $status]);
    }

    // ================================================================
    // ACTIVITY LOGGING (Audit Trail)
    // ================================================================

    /**
     * Log booking activity
     *
     * @param int $booking_id
     * @param string $action
     * @param string $description
     * @param int|null $user_id
     * @return int|false Activity log ID or FALSE on failure
     */
    public function log_activity($booking_id, $action, $description = '', $user_id = NULL)
    {
        $data = [
            'booking_id' => $booking_id,
            'action' => $action,
            'description' => $description,
            'user_id' => $user_id,
            'ip_address' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->db->insert('booking_activity_logs', $data);
        return $result ? $this->db->insert_id() : FALSE;
    }

    /**
     * Get booking activity logs
     *
     * @param int $booking_id
     * @return array
     */
    public function get_booking_activity_logs($booking_id)
    {
        $this->db->select('bal.*, u.full_name as user_name');
        $this->db->from('booking_activity_logs bal');
        $this->db->join('users u', 'bal.user_id = u.id', 'left');
        $this->db->where('bal.booking_id', $booking_id);
        $this->db->order_by('bal.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    // ================================================================
    // SLOT RELEASE (for cancellation)
    // ================================================================

    /**
     * Release booking slot (increment capacity)
     *
     * @param array $booking
     * @return bool
     */
    public function release_booking_slot($booking)
    {
        $this->db->set('booked_count', 'GREATEST(booked_count - 1, 0)', FALSE);
        $this->db->set('remaining_capacity', 'remaining_capacity + 1', FALSE);
        $this->db->where('workshop_id', $booking['workshop_id']);
        $this->db->where('slot_date', $booking['scheduled_date']);
        $this->db->where('slot_time', $booking['scheduled_time']);
        $this->db->update('booking_slots');

        return $this->db->affected_rows() > 0;
    }
}

/* End of file Booking_model.php */
/* Location: ./application/models/Booking_model.php */