<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin Model
 * 
 * Handles admin-specific data operations including dashboard statistics,
 * user management, workshop verification, review moderation, and audit logging.
 * 
 * @package     Bengkel Terdekat
 * @version     4.1
 */
class Admin_model extends CI_Model {

    /**
     * Table name for users
     */
    private $users_table = 'users';

    /**
     * Table name for workshops
     */
    private $workshops_table = 'workshops';

    /**
     * Table name for reviews
     */
    private $reviews_table = 'reviews';

    /**
     * Table name for bookings
     */
    private $bookings_table = 'bookings';

    /**
     * Table name for activity logs
     */
    private $activity_logs_table = 'activity_logs';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // --------------------------------------------------------------------
    // Dashboard Statistics
    // --------------------------------------------------------------------

    /**
     * Get comprehensive dashboard statistics
     * 
     * @return array Dashboard statistics
     */
    public function get_dashboard_stats()
    {
        // Total counts
        $stats['total_users'] = $this->db->where('is_deleted', 0)->count_all_results($this->users_table);
        $stats['total_customers'] = $this->db->where('is_deleted', 0)->where('role', 'customer')->count_all_results($this->users_table);
        $stats['total_workshop_owners'] = $this->db->where('is_deleted', 0)->where('role', 'workshop_owner')->count_all_results($this->users_table);
        $stats['total_mechanics'] = $this->db->where('is_deleted', 0)->where('role', 'mechanic')->count_all_results($this->users_table);
        
        // Workshop counts
        $stats['total_workshops'] = $this->db->where('is_deleted', 0)->count_all_results($this->workshops_table);
        $stats['verified_workshops'] = $this->db->where('is_deleted', 0)->where('verified_at IS NOT NULL', NULL, FALSE)->count_all_results($this->workshops_table);
        $stats['pending_verification_workshops'] = $this->db->where('is_deleted', 0)->where('verified_at IS NULL', NULL, FALSE)->count_all_results($this->workshops_table);
        $stats['featured_workshops'] = $this->db->where('is_deleted', 0)->where('is_featured', 1)->count_all_results($this->workshops_table);
        
        // Today's bookings
        $today = date('Y-m-d');
        $stats['bookings_today'] = $this->db
            ->where('DATE(created_at)', $today)
            ->where('is_deleted', 0)
            ->count_all_results($this->bookings_table);
        
        // Active emergency requests (last 24 hours)
        $last_24h = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $stats['emergency_requests_active'] = $this->db
            ->where('created_at >=', $last_24h)
            ->where_in('status', ['pending', 'dispatched', 'accepted'])
            ->count_all_results('emergency_requests');
        
        // Reviews pending moderation
        $stats['reviews_pending_moderation'] = $this->db
            ->where('moderation_status', 'pending')
            ->where('is_deleted', 0)
            ->count_all_results($this->reviews_table);
        
        // Auto-flagged reviews (report_count >= 3)
        $stats['reviews_flagged'] = $this->db
            ->where('report_count >=', 3)
            ->where('is_deleted', 0)
            ->count_all_results($this->reviews_table);
        
        // Bookings by status today
        $stats['bookings_by_status'] = [
            'pending' => $this->db->where('DATE(created_at)', $today)->where('status', 'pending')->where('is_deleted', 0)->count_all_results($this->bookings_table),
            'accepted' => $this->db->where('DATE(created_at)', $today)->where('status', 'accepted')->where('is_deleted', 0)->count_all_results($this->bookings_table),
            'processed' => $this->db->where('DATE(created_at)', $today)->where('status', 'processed')->where('is_deleted', 0)->count_all_results($this->bookings_table),
            'completed' => $this->db->where('DATE(created_at)', $today)->where('status', 'completed')->where('is_deleted', 0)->count_all_results($this->bookings_table),
            'cancelled' => $this->db->where('DATE(created_at)', $today)->where('status', 'cancelled')->where('is_deleted', 0)->count_all_results($this->bookings_table)
        ];
        
        // Revenue today (from completed bookings with invoices)
        $this->db->select('SUM(total_amount) as total_revenue');
        $this->db->from('invoices');
        $this->db->join('bookings', 'invoices.booking_id = bookings.id');
        $this->db->where('DATE(bookings.created_at)', $today);
        $this->db->where('bookings.status', 'completed');
        $row = $this->db->get()->row();
        $stats['revenue_today'] = $row->total_revenue ?: 0;
        
        return $stats;
    }

    /**
     * Get chart data for bookings trend (last 7 days)
     * 
     * @return array Chart data
     */
    public function get_bookings_trend($days = 7)
    {
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $label = date('d/m', strtotime("-{$i} days"));
            
            $count = $this->db
                ->where('DATE(created_at)', $date)
                ->where('is_deleted', 0)
                ->count_all_results($this->bookings_table);
            
            $data[] = [
                'date' => $date,
                'label' => $label,
                'count' => $count
            ];
        }
        
        return $data;
    }

    /**
     * Get workshop registration trend (last 7 days)
     * 
     * @return array Chart data
     */
    public function get_workshop_trend($days = 7)
    {
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $label = date('d/m', strtotime("-{$i} days"));
            
            $count = $this->db
                ->where('DATE(created_at)', $date)
                ->where('is_deleted', 0)
                ->count_all_results($this->workshops_table);
            
            $data[] = [
                'date' => $date,
                'label' => $label,
                'count' => $count
            ];
        }
        
        return $data;
    }

    // --------------------------------------------------------------------
    // User Management (DataTables Server-Side)
    // --------------------------------------------------------------------

    /**
     * Get users for DataTables server-side processing
     * 
     * @param array $request DataTables request parameters
     * @param string|null $role_filter Role filter
     * @return array DataTables response
     */
    public function get_users_datatables($request, $role_filter = NULL)
    {
        // Columns
        $columns = [
            0 => 'u.id',
            1 => 'u.full_name',
            2 => 'u.email',
            3 => 'u.role',
            4 => 'u.is_active',
            5 => 'u.created_at'
        ];
        
        // Count total before filtering
        $this->db->from('users u');
        $this->db->where('u.is_deleted', 0);
        if ($role_filter !== NULL && $role_filter !== '') {
            $this->db->where('u.role', $role_filter);
        }
        $total_records = $this->db->count_all_results();
        
        // Reset for actual query with search
        $this->db->reset_query();
        $this->db->select('u.*, w.name as workshop_name');
        $this->db->from('users u');
        $this->db->where('u.is_deleted', 0);
        $this->db->join('workshops w', 'w.user_id = u.id AND w.is_deleted = 0', 'left');
        
        // Re-apply role filter
        if ($role_filter !== NULL && $role_filter !== '') {
            $this->db->where('u.role', $role_filter);
        }
        
        // Search
        if (!empty($request['search']['value'])) {
            $search = $request['search']['value'];
            $this->db->group_start();
            $this->db->like('u.full_name', $search);
            $this->db->or_like('u.email', $search);
            $this->db->or_like('u.role', $search);
            $this->db->group_end();
        }
        
        // Ordering
        if (!empty($request['order'])) {
            foreach ($request['order'] as $order) {
                $column_index = $order['column'];
                if (isset($columns[$column_index])) {
                    $column_name = $columns[$column_index];
                    $dir = $order['dir'];
                    $this->db->order_by($column_name, $dir);
                }
            }
        } else {
            $this->db->order_by('u.created_at', 'DESC');
        }
        
        // Pagination - use null coalescing to handle missing parameters
        $limit = isset($request['length']) ? (int)$request['length'] : 10;
        $offset = isset($request['start']) ? (int)$request['start'] : 0;
        
        // Only apply limit if length is not -1 (DataTables convention for "no limit")
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }
        
        // Execute query
        $query = $this->db->get();
        $result = $query->result_array();
        
        // Count filtered records
        $this->db->reset_query();
        $this->db->select('u.*');
        $this->db->from('users u');
        $this->db->where('u.is_deleted', 0);
        if ($role_filter !== NULL && $role_filter !== '') {
            $this->db->where('u.role', $role_filter);
        }
        if (!empty($request['search']['value'])) {
            $search = $request['search']['value'];
            $this->db->group_start();
            $this->db->like('u.full_name', $search);
            $this->db->or_like('u.email', $search);
            $this->db->or_like('u.role', $search);
            $this->db->group_end();
        }
        $filtered_records = $this->db->count_all_results();
        
        return [
            'draw' => isset($request['draw']) ? intval($request['draw']) : 1,
            'recordsTotal' => $total_records,
            'recordsFiltered' => $filtered_records,
            'data' => $result
        ];
    }

    /**
     * Reset user password
     * 
     * @param int $user_id User ID
     * @param string $new_password New password
     * @param int $admin_id Admin performing the action
     * @return bool TRUE on success
     */
    public function reset_user_password($user_id, $new_password, $admin_id)
    {
        $hashed = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 10]);
        
        $result = $this->db
            ->where('id', $user_id)
            ->update($this->users_table, [
                'password' => $hashed,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        if ($result) {
            $this->log_activity(
                $admin_id,
                'USER_PASSWORD_RESET',
                "Admin mereset password user ID {$user_id}",
                $user_id
            );
        }
        
        return $result;
    }

    // --------------------------------------------------------------------
    // Workshop Management
    // --------------------------------------------------------------------

    /**
     * Get workshops for DataTables server-side processing
     * 
     * @param array $request DataTables request parameters
     * @param string|null $verification_status Filter by verification status
     * @return array DataTables response
     */
    public function get_workshops_datatables($request, $verification_status = NULL)
    {
        // Columns
        $columns = [
            0 => 'w.id',
            1 => 'w.name',
            2 => 'w.city',
            3 => 'w.is_active',
            4 => 'w.is_featured',
            5 => 'w.verified_at',
            6 => 'w.created_at'
        ];
        
        // Count total records (no search filter)
        $this->db->from('workshops w');
        $this->db->where('w.is_deleted', 0);
        if ($verification_status === 'pending') {
            $this->db->where('w.verified_at IS NULL', NULL, FALSE);
        } elseif ($verification_status === 'verified') {
            $this->db->where('w.verified_at IS NOT NULL', NULL, FALSE);
        }
        $total_records = $this->db->count_all_results();

        // Base query with search
        $this->db->select('w.*, u.full_name as owner_name, u.email as owner_email');
        $this->db->from('workshops w');
        $this->db->join('users u', 'u.id = w.user_id', 'left');
        $this->db->where('w.is_deleted', 0);
        
        // Verification status filter
        if ($verification_status === 'pending') {
            $this->db->where('w.verified_at IS NULL', NULL, FALSE);
        } elseif ($verification_status === 'verified') {
            $this->db->where('w.verified_at IS NOT NULL', NULL, FALSE);
        }
        
        // Search
        if (!empty($request['search']['value'])) {
            $search = $this->db->escape_like_string($request['search']['value']);
            $this->db->group_start();
            $this->db->like('w.name', $search);
            $this->db->or_like('w.city', $search);
            $this->db->or_like('u.full_name', $search);
            $this->db->group_end();
        }

        // Count filtered records (after search)
        $filtered_records = $this->db->count_all_results();

        // Re-apply base query for actual data fetch
        $this->db->select('w.*, u.full_name as owner_name, u.email as owner_email');
        $this->db->from('workshops w');
        $this->db->join('users u', 'u.id = w.user_id', 'left');
        $this->db->where('w.is_deleted', 0);
        if ($verification_status === 'pending') {
            $this->db->where('w.verified_at IS NULL', NULL, FALSE);
        } elseif ($verification_status === 'verified') {
            $this->db->where('w.verified_at IS NOT NULL', NULL, FALSE);
        }
        if (!empty($request['search']['value'])) {
            $search = $this->db->escape_like_string($request['search']['value']);
            $this->db->group_start();
            $this->db->like('w.name', $search);
            $this->db->or_like('w.city', $search);
            $this->db->or_like('u.full_name', $search);
            $this->db->group_end();
        }
        
        // Ordering
        if (!empty($request['order'])) {
            foreach ($request['order'] as $order) {
                $column_index = $order['column'];
                $column_name = $columns[$column_index];
                $dir = $order['dir'];
                $this->db->order_by($column_name, $dir);
            }
        } else {
            $this->db->order_by('w.created_at', 'DESC');
        }
        
        // Pagination - use null coalescing to handle missing parameters
        $limit = isset($request['length']) ? (int)$request['length'] : 10;
        $offset = isset($request['start']) ? (int)$request['start'] : 0;
        
        // Only apply limit if length is not -1 (DataTables convention for "no limit")
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }
        
        // Execute query
        $query = $this->db->get();
        $result = $query->result_array();
        
        return [
            'draw' => isset($request['draw']) ? intval($request['draw']) : 1,
            'recordsTotal' => $total_records,
            'recordsFiltered' => $filtered_records,
            'data' => $result
        ];
    }

    /**
     * Verify a workshop
     * 
     * @param int $workshop_id Workshop ID
     * @param int $admin_id Admin performing verification
     * @return bool TRUE on success
     */
    public function verify_workshop($workshop_id, $admin_id)
    {
        $result = $this->db
            ->where('id', $workshop_id)
            ->update($this->workshops_table, [
                'verified_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        if ($result) {
            $this->log_activity(
                $admin_id,
                'WORKSHOP_VERIFY',
                "Admin memverifikasi bengkel ID {$workshop_id}",
                NULL,
                $workshop_id
            );
        }
        
        return $result;
    }

    /**
     * Set/unset featured workshop
     * 
     * @param int $workshop_id Workshop ID
     * @param bool $is_featured Featured status
     * @param int $admin_id Admin performing the action
     * @return bool TRUE on success
     */
    public function set_featured_workshop($workshop_id, $is_featured, $admin_id)
    {
        $result = $this->db
            ->where('id', $workshop_id)
            ->update($this->workshops_table, [
                'is_featured' => $is_featured ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        if ($result) {
            $action = $is_featured ? 'WORKSHOP_FEATURE' : 'WORKSHOP_UNFEATURE';
            $desc = $is_featured ? 'Menjadikan bengkel featured' : 'Menghapus status featured';
            
            $this->log_activity(
                $admin_id,
                $action,
                "{$desc} bengkel ID {$workshop_id}",
                NULL,
                $workshop_id
            );
        }
        
        return $result;
    }

    /**
     * Get pending verification workshops
     * 
     * @return array Workshops awaiting verification
     */
    public function get_pending_verification_workshops()
    {
        return $this->db
            ->select('w.*, u.full_name as owner_name, u.email as owner_email')
            ->from('workshops w')
            ->join('users u', 'u.id = w.user_id')
            ->where('w.is_deleted', 0)
            ->where('w.verified_at IS NULL', NULL, FALSE)
            ->order_by('w.created_at', 'DESC')
            ->get()
            ->result();
    }

    // --------------------------------------------------------------------
    // Review Moderation
    // --------------------------------------------------------------------

    /**
     * Get reviews pending moderation
     * 
     * @param int $limit Limit results
     * @param int $offset Offset
     * @return array Reviews
     */
    public function get_pending_reviews($limit = 50, $offset = 0)
    {
        return $this->db
            ->select('r.*, u.full_name as reviewer_name, u.email as reviewer_email, w.name as workshop_name, COUNT(rl.id) as report_count')
            ->from('reviews r')
            ->join('users u', 'u.id = r.user_id')
            ->join('workshops w', 'w.id = r.workshop_id')
            ->join('review_reports rl', 'rl.review_id = r.id', 'left')
            ->where('r.is_deleted', 0)
            ->where('r.moderation_status', 'pending')
            ->group_by('r.id')
            ->order_by('COUNT(rl.id)', 'DESC')
            ->order_by('r.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    /**
     * Count pending reviews
     * 
     * @return int Count
     */
    public function count_pending_reviews()
    {
        return $this->db
            ->where('moderation_status', 'pending')
            ->where('is_deleted', 0)
            ->count_all_results($this->reviews_table);
    }

    /**
     * Approve a review
     * 
     * @param int $review_id Review ID
     * @param int $admin_id Admin ID
     * @param string|null $notes Moderation notes
     * @return bool TRUE on success
     */
    public function approve_review($review_id, $admin_id, $notes = NULL)
    {
        $result = $this->db
            ->where('id', $review_id)
            ->update($this->reviews_table, [
                'moderation_status' => 'approved',
                'moderated_by' => $admin_id,
                'moderated_at' => date('Y-m-d H:i:s'),
                'moderation_notes' => $notes,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        if ($result) {
            $this->log_activity(
                $admin_id,
                'REVIEW_APPROVE',
                "Admin menyetujui review ID {$review_id}" . ($notes ? ": {$notes}" : ''),
                NULL,
                NULL
            );
        }
        
        return $result;
    }

    /**
     * Reject a review
     * 
     * @param int $review_id Review ID
     * @param int $admin_id Admin ID
     * @param string $notes Rejection reason
     * @return bool TRUE on success
     */
    public function reject_review($review_id, $admin_id, $notes = '')
    {
        $result = $this->db
            ->where('id', $review_id)
            ->update($this->reviews_table, [
                'moderation_status' => 'rejected',
                'moderated_by' => $admin_id,
                'moderated_at' => date('Y-m-d H:i:s'),
                'moderation_notes' => $notes,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        if ($result) {
            $this->log_activity(
                $admin_id,
                'REVIEW_REJECT',
                "Admin menolak review ID {$review_id}: {$notes}",
                NULL,
                NULL
            );
        }
        
        return $result;
    }

    /**
     * Auto-flag reviews with high report count
     * 
     * @param int $threshold Report count threshold (default: 3)
     * @return int Number of flagged reviews
     */
    public function auto_flag_reviews($threshold = 3)
    {
        $this->db
            ->where('moderation_status', 'approved')
            ->where('is_deleted', 0)
            ->where('report_count >=', $threshold)
            ->update($this->reviews_table, [
                'moderation_status' => 'pending',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        return $this->db->affected_rows();
    }

    // --------------------------------------------------------------------
    // Activity Logging (Audit Trail)
    // --------------------------------------------------------------------

    /**
     * Log an activity
     * 
     * @param int $user_id Actor user ID
     * @param string $action_type Action type code
     * @param string $description Action description
     * @param int|null $target_user_id Target user ID (if applicable)
     * @param int|null $target_workshop_id Target workshop ID (if applicable)
     * @return int Insert ID
     */
    public function log_activity($user_id, $action_type, $description, $target_user_id = NULL, $target_workshop_id = NULL)
    {
        $data = [
            'user_id' => $user_id,
            'action_type' => $action_type,
            'action_description' => $description,
            'target_user_id' => $target_user_id,
            'target_workshop_id' => $target_workshop_id,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert($this->activity_logs_table, $data);
        return $this->db->insert_id();
    }

    /**
     * Get activity logs with filters
     * 
     * @param array $filters Filter criteria
     * @param int $limit Limit results
     * @param int $offset Offset
     * @return array Activity logs
     */
    public function get_activity_logs($filters = [], $limit = 100, $offset = 0)
    {
        $this->db->select('al.*, u.full_name as user_name, u.email as user_email');
        $this->db->from('activity_logs al');
        $this->db->join('users u', 'u.id = al.user_id', 'left');
        
        // Apply filters
        if (!empty($filters['user_id'])) {
            $this->db->where('al.user_id', $filters['user_id']);
        }
        if (!empty($filters['workshop_id'])) {
            $this->db->where('al.target_workshop_id', $filters['workshop_id']);
        }
        if (!empty($filters['action_type'])) {
            $this->db->where('al.action_type', $filters['action_type']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('al.created_at >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('al.created_at <=', $filters['date_to']);
        }
        
        $this->db->order_by('al.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        $results = $query->result();
        
        // Add target_type and target_id for compatibility with view
        foreach ($results as $row) {
            if (!empty($row->target_user_id)) {
                $row->target_type = 'user';
                $row->target_id = $row->target_user_id;
            } elseif (!empty($row->target_workshop_id)) {
                $row->target_type = 'workshop';
                $row->target_id = $row->target_workshop_id;
            } else {
                $row->target_type = null;
                $row->target_id = null;
            }
        }
        
        return $results;
    }

    /**
     * Count activity logs with filters
     * 
     * @param array $filters Filter criteria
     * @return int Count
     */
    public function count_activity_logs($filters = [])
    {
        $this->db->from('activity_logs al');
        
        if (!empty($filters['user_id'])) {
            $this->db->where('al.user_id', $filters['user_id']);
        }
        if (!empty($filters['workshop_id'])) {
            $this->db->where('al.target_workshop_id', $filters['workshop_id']);
        }
        if (!empty($filters['action_type'])) {
            $this->db->where('al.action_type', $filters['action_type']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('al.created_at >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('al.created_at <=', $filters['date_to']);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Get distinct action types for filter dropdown
     * 
     * @return array Action types
     */
    public function get_action_types()
    {
        return $this->db
            ->distinct()
            ->select('action_type')
            ->order_by('action_type', 'ASC')
            ->get($this->activity_logs_table)
            ->result_array();
    }
}

/* End of file Admin_model.php */
/* Location: ./application/models/Admin_model.php */