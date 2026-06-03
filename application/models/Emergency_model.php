<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Emergency Request Model
 * 
 * Handles emergency roadside assistance requests including:
 * - Creating emergency requests
 * - Finding nearby workshops using Euclidean distance
 * - Managing request status and workshop responses
 * - Rate limiting enforcement
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Models
 * @version     4.1
 */
class Emergency_model extends CI_Model {
    
    const TABLE_EMERGENCY = 'emergency_requests';
    const TABLE_WORKSHOPS = 'workshops';
    const TABLE_USERS = 'users';
    const TABLE_VEHICLES = 'vehicles';
    const MAX_REQUESTS_PER_HOUR = 3; // BR-70, Reviewer #5
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    // ================================================================
    // EMERGENCY REQUEST CRUD
    // ================================================================
    
    /**
     * Create new emergency request
     * 
     * @param array $data Request data
     * @return array ['success' => bool, 'request_id' => int|null, 'message' => string, 'request_number' => string|null]
     */
    public function create_request($data)
    {
        $this->db->trans_start();
        
        try {
            // Generate request number: EMG-YYYYMMDD-XXXX
            $request_number = $this->generate_request_number();
            
            // Prepare request data
            $request_data = [
                'request_number' => $request_number,
                'user_id' => $data['user_id'],
                'vehicle_id' => $data['vehicle_id'] ?? NULL,
                'emergency_type' => $data['emergency_type'],
                'description' => $data['description'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'location_address' => $data['location_address'] ?? NULL,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert(self::TABLE_EMERGENCY, $request_data);
            $request_id = $this->db->insert_id();
            
            if ($this->db->trans_status() === FALSE) {
                return [
                    'success' => FALSE,
                    'request_id' => NULL,
                    'message' => 'Gagal membuat permintaan darurat.',
                    'request_number' => NULL
                ];
            }
            
            return [
                'success' => TRUE,
                'request_id' => $request_id,
                'message' => 'Permintaan darurat berhasil dibuat.',
                'request_number' => $request_number
            ];
            
        } catch (Exception $e) {
            return [
                'success' => FALSE,
                'request_id' => NULL,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'request_number' => NULL
            ];
        }
    }
    
    /**
     * Generate unique request number
     * Format: EMG-YYYYMMDD-XXXX
     * 
     * @return string
     */
    private function generate_request_number()
    {
        $date_part = date('Ymd');
        $prefix = 'EMG-' . $date_part . '-';
        
        $this->db->select_max('id');
        $this->db->from(self::TABLE_EMERGENCY);
        $this->db->like('request_number', $prefix, 'after');
        $result = $this->db->get()->row();
        
        $next_num = isset($result->id) ? intval(substr($result->id, -4)) + 1 : 1;
        return $prefix . str_pad($next_num, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get emergency request by ID
     * 
     * @param int $id Request ID
     * @return array|null
     */
    public function get_request_by_id($id)
    {
        $this->db->select('e.*, u.full_name as user_name, u.phone as user_phone, v.vehicle_number, v.vehicle_type, v.brand');
        $this->db->from(self::TABLE_EMERGENCY . ' e');
        $this->db->join('users u', 'e.user_id = u.id', 'left');
        $this->db->join('vehicles v', 'e.vehicle_id = v.id', 'left');
        $this->db->where('e.id', $id);
        $this->db->where('e.is_deleted', 0);
        
        return $this->db->get()->row_array();
    }
    
    /**
     * Get request by request number
     * 
     * @param string $request_number
     * @return array|null
     */
    public function get_request_by_number($request_number)
    {
        $this->db->select('e.*, u.full_name as user_name, u.phone as user_phone, u.email as user_email');
        $this->db->from(self::TABLE_EMERGENCY . ' e');
        $this->db->join('users u', 'e.user_id = u.id', 'left');
        $this->db->where('e.request_number', $request_number);
        $this->db->where('e.is_deleted', 0);
        
        return $this->db->get()->row_array();
    }
    
    /**
     * Get user's active emergency requests
     * 
     * @param int $user_id
     * @return array
     */
    public function get_user_active_requests($user_id)
    {
        $this->db->from(self::TABLE_EMERGENCY);
        $this->db->where('user_id', $user_id);
        $this->db->where_in('status', ['pending', 'assigned', 'in_progress']);
        $this->db->where('is_deleted', 0);
        $this->db->order_by('created_at', 'DESC');
        
        return $this->db->get()->result_array();
    }
    
    /**
     * Check if user has active request (BR-70: 1 active request per user)
     * 
     * @param int $user_id
     * @return bool
     */
    public function has_active_request($user_id)
    {
        $active_requests = $this->get_user_active_requests($user_id);
        return count($active_requests) > 0;
    }
    
    // ================================================================
    // WORKSHOP SEARCH (Euclidean Distance)
    // ================================================================
    
    /**
     * Find active workshops within radius using Euclidean distance
     * SRS: Use Euclidean distance for emergency if Dijkstra not available
     * 
     * @param float $latitude User latitude
     * @param float $longitude User longitude
     * @param float $radius_km Search radius in km (default 5km from system_settings)
     * @return array Array of workshops with distance
     */
    public function find_nearby_workshops($latitude, $longitude, $radius_km = 5.0)
    {
        // Get workshops with coordinates and active status
        $this->db->select('w.*, u.phone as owner_phone, u.email as owner_email');
        $this->db->from(self::TABLE_WORKSHOPS . ' w');
        $this->db->join('users u', 'w.user_id = u.id', 'left');
        $this->db->where('w.status', 'active');
        $this->db->where('w.is_deleted', 0);
        $this->db->where('w.latitude IS NOT NULL');
        $this->db->where('w.longitude IS NOT NULL');
        
        $workshops = $this->db->get()->result_array();
        
        $nearby_workshops = [];
        
        foreach ($workshops as $workshop) {
            $distance = $this->calculate_euclidean_distance(
                $latitude, 
                $longitude, 
                $workshop['latitude'], 
                $workshop['longitude']
            );
            
            if ($distance <= $radius_km) {
                $workshop['distance_km'] = round($distance, 2);
                $nearby_workshops[] = $workshop;
            }
        }
        
        // Sort by distance
        usort($nearby_workshops, function($a, $b) {
            return $a['distance_km'] <=> $b['distance_km'];
        });
        
        return $nearby_workshops;
    }
    
    /**
     * Calculate Euclidean distance between two points
     * Note: This is approximate - uses simple formula for emergency situations
     * 
     * @param float $lat1 Point 1 latitude
     * @param float $lon1 Point 1 longitude
     * @param float $lat2 Point 2 latitude
     * @param float $lon2 Point 2 longitude
     * @return float Distance in kilometers
     */
    private function calculate_euclidean_distance($lat1, $lon1, $lat2, $lon2)
    {
        // Convert to radians for more accurate calculation
        $lat1_rad = deg2rad($lat1);
        $lon1_rad = deg2rad($lon1);
        $lat2_rad = deg2rad($lat2);
        $lon2_rad = deg2rad($lon2);
        
        // Haversine formula for better accuracy
        $delta_lat = $lat2_rad - $lat1_rad;
        $delta_lon = $lon2_rad - $lon1_rad;
        
        $a = sin($delta_lat / 2) * sin($delta_lat / 2) +
             cos($lat1_rad) * cos($lat2_rad) *
             sin($delta_lon / 2) * sin($delta_lon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        // Earth radius in km
        $earth_radius = 6371;
        
        return $earth_radius * $c;
    }
    
    // ================================================================
    // REQUEST STATUS MANAGEMENT
    // ================================================================
    
    /**
     * Assign workshop to emergency request
     * 
     * @param int $request_id Request ID
     * @param int $workshop_id Workshop ID
     * @return bool
     */
    public function assign_workshop($request_id, $workshop_id)
    {
        $data = [
            'status' => 'assigned',
            'assigned_workshop_id' => $workshop_id,
            'assigned_at' => date('Y-m-d H:i:s'),
            'accepted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $request_id);
        return $this->db->update(self::TABLE_EMERGENCY, $data);
    }
    
    /**
     * Update request status
     * 
     * @param int $request_id
     * @param string $status New status
     * @param array $additional_data Additional fields to update
     * @return bool
     */
    public function update_status($request_id, $status, $additional_data = [])
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($additional_data)) {
            $data = array_merge($data, $additional_data);
        }
        
        $this->db->where('id', $request_id);
        return $this->db->update(self::TABLE_EMERGENCY, $data);
    }
    
    /**
     * Cancel emergency request
     * 
     * @param int $request_id
     * @param string $reason Cancellation reason
     * @return bool
     */
    public function cancel_request($request_id, $reason = '')
    {
        return $this->update_status($request_id, 'cancelled', [
            'cancelled_at' => date('Y-m-d H:i:s'),
            'cancellation_reason' => $reason
        ]);
    }
    
    /**
     * Auto-close requests after 2 hours without response (BR-71)
     * 
     * @return int Number of closed requests
     */
    public function auto_close_old_requests()
    {
        $this->db->where('status', 'pending');
        $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime('-2 hours')));
        $this->db->where('is_deleted', 0);
        
        $old_requests = $this->db->get(self::TABLE_EMERGENCY)->result_array();
        
        $closed_count = 0;
        foreach ($old_requests as $request) {
            $this->update_status($request['id'], 'cancelled', [
                'cancelled_at' => date('Y-m-d H:i:s'),
                'cancellation_reason' => 'Auto-close: Tidak ada respons dalam 2 jam'
            ]);
            $closed_count++;
        }
        
        return $closed_count;
    }
    
    // ================================================================
    // RATE LIMITING (Reviewer #5)
    // ================================================================
    
    /**
     * Check rate limit for emergency requests
     * Max 3 requests per hour per IP
     * 
     * @param string $ip_address
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => string]
     */
    public function check_rate_limit($ip_address)
    {
        $one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $this->db->select('created_at');
        $this->db->from(self::TABLE_EMERGENCY);
        $this->db->where('ip_address', $ip_address);
        $this->db->where('created_at >=', $one_hour_ago);
        $this->db->order_by('created_at', 'DESC');
        
        $recent_requests = $this->db->get()->result_array();
        $request_count = count($recent_requests);
        
        $remaining = max(0, self::MAX_REQUESTS_PER_HOUR - $request_count);
        $reset_time = '';
        
        if ($request_count > 0 && $remaining === 0) {
            $oldest_request = end($recent_requests);
            $reset_time = date('Y-m-d H:i:s', strtotime($oldest_request['created_at']) + 3600);
        }
        
        return [
            'allowed' => $remaining > 0,
            'remaining' => $remaining,
            'reset_time' => $reset_time,
            'current_count' => $request_count
        ];
    }
    
    /**
     * Record request with IP for rate limiting
     * Note: Requires adding ip_address column to emergency_requests table
     * 
     * @param int $request_id
     * @param string $ip_address
     * @return bool
     */
    public function record_ip($request_id, $ip_address)
    {
        $data = ['ip_address' => $ip_address];
        $this->db->where('id', $request_id);
        return $this->db->update(self::TABLE_EMERGENCY, $data);
    }
    
    // ================================================================
    // STATISTICS & REPORTS
    // ================================================================
    
    /**
     * Get emergency request statistics for workshop
     * 
     * @param int $workshop_id
     * @return array
     */
    public function get_workshop_statistics($workshop_id)
    {
        $stats = [];
        
        // Total received
        $this->db->select('COUNT(*) as total');
        $this->db->from(self::TABLE_EMERGENCY);
        $this->db->where('assigned_workshop_id', $workshop_id);
        $this->db->where('is_deleted', 0);
        $stats['total_received'] = $this->db->get()->row()->total;
        
        // Pending response
        $this->db->where('status', 'pending');
        $stats['pending'] = $this->db->get()->row()->total;
        
        // Accepted
        $this->db->where('status', 'assigned');
        $stats['accepted'] = $this->db->get()->row()->total;
        
        // Completed
        $this->db->where('status', 'completed');
        $stats['completed'] = $this->db->get()->row()->total;
        
        return $stats;
    }
    
    /**
     * Get pending emergency requests for workshop
     * 
     * @param int $workshop_id
     * @return array
     */
    public function get_pending_requests_for_workshop($workshop_id)
    {
        // Get all pending requests within workshop's typical service area
        $this->db->select('e.*, u.full_name as user_name, u.phone as user_phone');
        $this->db->from(self::TABLE_EMERGENCY . ' e');
        $this->db->join('users u', 'e.user_id = u.id', 'left');
        $this->db->where('e.status', 'pending');
        $this->db->where('e.is_deleted', 0);
        $this->db->order_by('e.created_at', 'DESC');
        
        return $this->db->get()->result_array();
    }
}

/* End of file Emergency_model.php */
/* Location: ./application/models/Emergency_model.php */
