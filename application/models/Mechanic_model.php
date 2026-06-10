<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mechanic Model
 * 
 * Handles mechanic management operations:
 * - CRUD mechanics
 * - Assignment to bookings
 * - Availability checking (BR-76: overlapping schedule prevention)
 * - Productivity reporting
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Models
 * @version     4.1
 */
class Mechanic_model extends CI_Model {

    private $table_mechanics = 'mechanics';
    private $table_booking_mechanics = 'booking_mechanics';
    private $table_bookings = 'bookings';
    private $table_users = 'users';
    private $table_workshops = 'workshops';
    private $table_reviews = 'reviews';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ================================================================
    // MECHANIC CRUD OPERATIONS
    // ================================================================

    /**
     * Create new mechanic
     * 
     * @param array $data Mechanic data
     * @return array ['success' => bool, 'mechanic_id' => int|null, 'message' => string]
     */
    public function create_mechanic($data)
    {
        $this->db->trans_start();

        try {
            // Validate user exists and is mechanic role
            $user = $this->db->get_where($this->table_users, ['id' => $data['user_id']])->row_array();
            
            if (!$user) {
                return [
                    'success' => FALSE,
                    'mechanic_id' => NULL,
                    'message' => 'User tidak ditemukan'
                ];
            }

            // Update user role to mechanic if not already
            if ($user['role'] !== 'mechanic') {
                $this->db->where('id', $data['user_id'])
                         ->update($this->table_users, ['role' => 'mechanic']);
            }

            // Prepare mechanic data
            $mechanic_data = [
                'user_id' => $data['user_id'],
                'workshop_id' => $data['workshop_id'],
                'specialization' => json_encode($data['specialization'] ?? []),
                'experience_years' => $data['experience_years'] ?? 0,
                'certification' => $data['certification'] ?? '',
                'is_available' => $data['is_available'] ?? 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert($this->table_mechanics, $mechanic_data);
            $mechanic_id = $this->db->insert_id();

            if (!$mechanic_id) {
                throw new Exception('Gagal menyimpan data mekanik');
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                return [
                    'success' => FALSE,
                    'mechanic_id' => NULL,
                    'message' => 'Terjadi kesalahan saat menyimpan mekanik.'
                ];
            }

            return [
                'success' => TRUE,
                'mechanic_id' => $mechanic_id,
                'message' => 'Mekanik berhasil ditambahkan.'
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [
                'success' => FALSE,
                'mechanic_id' => NULL,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update mechanic data
     * 
     * @param int $mechanic_id
     * @param array $data
     * @return bool
     */
    public function update_mechanic($mechanic_id, $data)
    {
        $update_data = [];

        if (isset($data['specialization'])) {
            $update_data['specialization'] = json_encode($data['specialization']);
        }
        if (isset($data['experience_years'])) {
            $update_data['experience_years'] = $data['experience_years'];
        }
        if (isset($data['certification'])) {
            $update_data['certification'] = $data['certification'];
        }
        if (isset($data['is_available'])) {
            $update_data['is_available'] = $data['is_available'];
        }
        if (isset($data['is_deleted'])) {
            $update_data['is_deleted'] = $data['is_deleted'];
            $update_data['deleted_at'] = date('Y-m-d H:i:s');
        }

        $this->db->where('id', $mechanic_id);
        return $this->db->update($this->table_mechanics, $update_data);
    }

    /**
     * Get mechanic by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function find_by_id($id)
    {
        $this->db->select('m.*, u.name, u.email, u.phone, w.name as workshop_name');
        $this->db->from($this->table_mechanics . ' m');
        $this->db->join($this->table_users . ' u', 'm.user_id = u.id', 'left');
        $this->db->join($this->table_workshops . ' w', 'm.workshop_id = w.id', 'left');
        $this->db->where('m.id', $id);
        
        return $this->db->get()->row_array();
    }

    /**
     * Find mechanic by user ID
     *
     * @param int $user_id
     * @return array|null
     */
    public function find_by_user_id($user_id)
    {
        $this->db->select('m.*, u.name, u.email, u.phone, w.name as workshop_name');
        $this->db->from($this->table_mechanics . ' m');
        $this->db->join($this->table_users . ' u', 'm.user_id = u.id', 'left');
        $this->db->join($this->table_workshops . ' w', 'm.workshop_id = w.id', 'left');
        $this->db->where('m.user_id', $user_id);

        return $this->db->get()->row_array();
    }

    /**
     * Get all mechanics for a workshop
     * 
     * @param int $workshop_id
     * @param array $filters
     * @return array
     */
    public function get_by_workshop($workshop_id, $filters = [])
    {
        $this->db->select('m.*, u.name, u.email, u.phone');
        $this->db->from($this->table_mechanics . ' m');
        $this->db->join($this->table_users . ' u', 'm.user_id = u.id', 'left');
        $this->db->where('m.workshop_id', $workshop_id);
        $this->db->where('m.is_deleted', 0);

        if (isset($filters['is_available'])) {
            $this->db->where('m.is_available', $filters['is_available']);
        }

        if (!empty($filters['specialization'])) {
            $this->db->like('specialization', json_encode($filters['specialization']));
        }

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('u.name', $filters['search']);
            $this->db->or_like('u.email', $filters['search']);
            $this->db->group_end();
        }

        $this->db->order_by('u.name', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get active mechanics for a workshop
     * 
     * @param int $workshop_id
     * @return array
     */
    public function get_active_mechanics($workshop_id)
    {
        return $this->get_by_workshop($workshop_id, ['is_available' => 1]);
    }

    /**
     * Delete mechanic (soft delete)
     * 
     * @param int $mechanic_id
     * @return bool
     */
    public function delete_mechanic($mechanic_id)
    {
        return $this->update_mechanic($mechanic_id, ['is_deleted' => 1]);
    }

    // ================================================================
    // MECHANIC ASSIGNMENT (UC-WRK-07, BR-76)
    // ================================================================

    /**
     * Assign mechanic(s) to a booking
     * Validates no overlapping schedule (BR-76)
     * 
     * @param int $booking_id
     * @param array $mechanic_ids Array of mechanic IDs (1-3 mechanics)
     * @param int $assigned_by User ID who assigns
     * @param string $notes Optional notes
     * @return array ['success' => bool, 'message' => string, 'conflicts' => array]
     */
    public function assign_mechanics($booking_id, $mechanic_ids, $assigned_by, $notes = '')
    {
        // Validate mechanic count (max 3)
        if (count($mechanic_ids) > 3) {
            return [
                'success' => FALSE,
                'message' => 'Maksimal 3 mekanik per pesanan',
                'conflicts' => []
            ];
        }

        // Get booking details
        $booking = $this->db->get_where($this->table_bookings, ['id' => $booking_id])->row_array();
        
        if (!$booking) {
            return [
                'success' => FALSE,
                'message' => 'Pesanan tidak ditemukan',
                'conflicts' => []
            ];
        }

        // Check overlapping schedules (BR-76)
        $conflicts = $this->check_mechanic_availability(
            $mechanic_ids,
            $booking['scheduled_date'],
            $booking['scheduled_time'],
            $booking['estimated_duration'] ?? 60,
            $booking_id // Exclude current booking
        );

        if (!empty($conflicts)) {
            return [
                'success' => FALSE,
                'message' => 'Beberapa mekanik memiliki jadwal bentrok',
                'conflicts' => $conflicts
            ];
        }

        $this->db->trans_start();

        try {
            foreach ($mechanic_ids as $mechanic_id) {
                // Check if already assigned
                $existing = $this->db->get_where($this->table_booking_mechanics, [
                    'booking_id' => $booking_id,
                    'mechanic_id' => $mechanic_id,
                    'is_deleted' => 0
                ])->row_array();

                if ($existing) {
                    continue; // Skip if already assigned
                }

                // Insert assignment
                $assignment_data = [
                    'booking_id' => $booking_id,
                    'mechanic_id' => $mechanic_id,
                    'assigned_by' => $assigned_by,
                    'notes' => $notes,
                    'assigned_at' => date('Y-m-d H:i:s'),
                    'is_deleted' => 0
                ];

                $this->db->insert($this->table_booking_mechanics, $assignment_data);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                return [
                    'success' => FALSE,
                    'message' => 'Gagal menugaskan mekanik',
                    'conflicts' => []
                ];
            }

            return [
                'success' => TRUE,
                'message' => 'Mekanik berhasil ditugaskan',
                'conflicts' => []
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [
                'success' => FALSE,
                'message' => $e->getMessage(),
                'conflicts' => []
            ];
        }
    }

    /**
     * Check mechanic availability for a time slot (BR-76)
     * Prevents double-booking of mechanics
     * 
     * @param array $mechanic_ids
     * @param string $date
     * @param string $time
     * @param int $duration_minutes
     * @param int $exclude_booking_id Booking to exclude from check
     * @return array List of conflicts
     */
    public function check_mechanic_availability($mechanic_ids, $date, $time, $duration_minutes, $exclude_booking_id = NULL)
    {
        $conflicts = [];

        foreach ($mechanic_ids as $mechanic_id) {
            // Get mechanic's other bookings on the same date
            $this->db->select('b.id, b.booking_number, b.scheduled_time, b.estimated_duration, b.status');
            $this->db->from($this->table_booking_mechanics . ' bm');
            $this->db->join($this->table_bookings . ' b', 'bm.booking_id = b.id');
            $this->db->where('bm.mechanic_id', $mechanic_id);
            $this->db->where('b.scheduled_date', $date);
            $this->db->where('b.is_deleted', 0);
            
            if ($exclude_booking_id) {
                $this->db->where('b.id !=', $exclude_booking_id);
            }

            // Only check active bookings
            $this->db->where_in('b.status', ['pending', 'accepted', 'processed', 'in_progress']);

            $bookings = $this->db->get()->result_array();

            // Check for time overlap
            $new_start = strtotime($date . ' ' . $time);
            $new_end = $new_start + ($duration_minutes * 60);

            foreach ($bookings as $booking) {
                $existing_start = strtotime($date . ' ' . $booking['scheduled_time']);
                $existing_end = $existing_start + (($booking['estimated_duration'] ?? 60) * 60);

                // Check overlap: NOT (new_end <= existing_start OR new_start >= existing_end)
                if (!($new_end <= $existing_start || $new_start >= $existing_end)) {
                    $conflicts[] = [
                        'mechanic_id' => $mechanic_id,
                        'booking_id' => $booking['id'],
                        'booking_number' => $booking['booking_number'],
                        'scheduled_time' => $booking['scheduled_time'],
                        'duration' => $booking['estimated_duration']
                    ];
                }
            }
        }

        return $conflicts;
    }

    /**
     * Get mechanics assigned to a booking
     * 
     * @param int $booking_id
     * @return array
     */
    public function get_booking_mechanics($booking_id)
    {
        $this->db->select('bm.*, m.specialization, u.name as mechanic_name, u.phone');
        $this->db->from($this->table_booking_mechanics . ' bm');
        $this->db->join($this->table_mechanics . ' m', 'bm.mechanic_id = m.id');
        $this->db->join($this->table_users . ' u', 'm.user_id = u.id');
        $this->db->where('bm.booking_id', $booking_id);
        $this->db->where('bm.is_deleted', 0);

        return $this->db->get()->result_array();
    }

    /**
     * Remove mechanic from booking
     * 
     * @param int $booking_id
     * @param int $mechanic_id
     * @return bool
     */
    public function remove_mechanic($booking_id, $mechanic_id)
    {
        $this->db->where('booking_id', $booking_id);
        $this->db->where('mechanic_id', $mechanic_id);
        $this->db->update($this->table_booking_mechanics, [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        return $this->db->affected_rows() > 0;
    }

    // ================================================================
    // PRODUCTIVITY REPORTING (FR-MEC-03)
    // ================================================================

    /**
     * Get mechanic productivity report
     * Shows completed bookings count and average rating
     * 
     * @param int $workshop_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function get_productivity_report($workshop_id, $start_date, $end_date)
    {
        $this->db->select('m.id as mechanic_id, m.user_id, u.name, u.email, u.phone');
        $this->db->select('m.specialization, m.experience_years, m.is_available');
        $this->db->select('COUNT(DISTINCT b.id) as total_bookings');
        $this->db->select('SUM(CASE WHEN b.status = "completed" THEN 1 ELSE 0 END) as completed_count');
        $this->db->select('AVG(b.customer_rating) as avg_rating');
        $this->db->select('COUNT(r.id) as review_count');
        
        $this->db->from($this->table_mechanics . ' m');
        $this->db->join($this->table_users . ' u', 'm.user_id = u.id');
        $this->db->join($this->table_booking_mechanics . ' bm', 'm.id = bm.mechanic_id AND bm.is_deleted = 0', 'left');
        $this->db->join($this->table_bookings . ' b', 'bm.booking_id = b.id AND b.is_deleted = 0', 'left');
        $this->db->join($this->table_reviews . ' r', 'b.id = r.booking_id AND r.is_deleted = 0', 'left');
        
        $this->db->where('m.workshop_id', $workshop_id);
        $this->db->where('m.is_deleted', 0);
        
        if ($start_date && $end_date) {
            $this->db->group_start();
            $this->db->where('b.scheduled_date >=', $start_date);
            $this->db->where('b.scheduled_date <=', $end_date);
            $this->db->or_group_start();
            $this->db->where('b.id IS NULL'); // Include mechanics with no bookings in range
            $this->db->group_end();
            $this->db->group_end();
        }
        
        $this->db->group_by('m.id, u.id');
        $this->db->order_by('completed_count', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Get mechanic statistics summary
     * 
     * @param int $workshop_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function get_mechanic_stats_summary($workshop_id, $start_date, $end_date)
    {
        $productivity = $this->get_productivity_report($workshop_id, $start_date, $end_date);

        $total_mechanics = count($productivity);
        $total_completed = 0;
        $total_ratings = 0;
        $rating_count = 0;

        foreach ($productivity as $mech) {
            $total_completed += (int) $mech['completed_count'];
            if (!empty($mech['avg_rating'])) {
                $total_ratings += $mech['avg_rating'] * $mech['review_count'];
                $rating_count += $mech['review_count'];
            }
        }

        return [
            'total_mechanics' => $total_mechanics,
            'total_completed_bookings' => $total_completed,
            'average_rating' => $rating_count > 0 ? round($total_ratings / $rating_count, 2) : 0,
            'top_performer' => !empty($productivity[0]) ? $productivity[0] : NULL
        ];
    }

    /**
     * Get mechanic's booking history
     * 
     * @param int $mechanic_id
     * @param array $filters
     * @return array
     */
    public function get_mechanic_bookings($mechanic_id, $filters = [])
    {
        $this->db->select('b.*, bm.assigned_at, bm.notes as assignment_notes');
        $this->db->from($this->table_booking_mechanics . ' bm');
        $this->db->join($this->table_bookings . ' b', 'bm.booking_id = b.id');
        $this->db->where('bm.mechanic_id', $mechanic_id);
        $this->db->where('bm.is_deleted', 0);
        $this->db->where('b.is_deleted', 0);

        if (!empty($filters['status'])) {
            $this->db->where('b.status', $filters['status']);
        }

        if (!empty($filters['start_date'])) {
            $this->db->where('b.scheduled_date >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $this->db->where('b.scheduled_date <=', $filters['end_date']);
        }

        $this->db->order_by('b.scheduled_date', 'DESC');
        $this->db->order_by('b.scheduled_time', 'DESC');

        return $this->db->get()->result_array();
    }
}
