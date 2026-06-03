<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Workshop Model
 * 
 * Handles all database operations for workshops and workshop services.
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class Workshop_model extends CI_Model {

    /**
     * Table name for workshops
     */
    const TABLE_WORKSHOPS = 'workshops';
    
    /**
     * Table name for workshop services
     */
    const TABLE_SERVICES = 'workshop_services';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ============================================================
    // WORKSHOP CRUD OPERATIONS
    // ============================================================

    /**
     * Get all workshops (with filtering options)
     * @param array $filters Filtering options
     * @param int $limit Limit results
     * @param int $offset Offset results
     * @return array Array of workshop objects
     */
    public function get_all($filters = [], $limit = NULL, $offset = 0)
    {
        $this->db->select('w.*, u.full_name as owner_name, u.email as owner_email, u.phone as owner_phone');
        $this->db->from(self::TABLE_WORKSHOPS . ' w');
        $this->db->join('users u', 'w.user_id = u.id', 'left');
        
        // Only show non-deleted workshops
        $this->db->where('w.is_deleted', 0);
        
        // Filter by status (only active by default)
        if (!isset($filters['status'])) {
            $filters['status'] = 'active';
        }
        
        if ($filters['status']) {
            $this->db->where('w.status', $filters['status']);
        }
        
        // Filter by city
        if (!empty($filters['city'])) {
            $this->db->like('w.city', $filters['city']);
        }
        
        // Filter by province
        if (!empty($filters['province'])) {
            $this->db->like('w.province', $filters['province']);
        }
        
        // Filter by service category
        if (!empty($filters['service_category'])) {
            $this->db->join(self::TABLE_SERVICES . ' ws', 'w.id = ws.workshop_id', 'inner');
            $this->db->where('ws.service_category', $filters['service_category']);
            $this->db->where('ws.is_deleted', 0);
            $this->db->where('ws.is_available', 1);
        }
        
        // Search by name or description
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('w.name', $filters['search']);
            $this->db->or_like('w.description', $filters['search']);
            $this->db->group_end();
        }
        
        // Order by rating (highest first)
        $this->db->order_by('w.rating_avg', 'DESC');
        $this->db->order_by('w.created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get workshop by ID
     * @param int $id Workshop ID
     * @param bool $include_deleted Include deleted workshops
     * @return object|null Workshop object or NULL
     */
    public function find_by_id($id, $include_deleted = FALSE)
    {
        $this->db->select('w.*, u.full_name as owner_name, u.email as owner_email, u.phone as owner_phone');
        $this->db->from(self::TABLE_WORKSHOPS . ' w');
        $this->db->join('users u', 'w.user_id = u.id', 'left');
        $this->db->where('w.id', $id);
        
        if (!$include_deleted) {
            $this->db->where('w.is_deleted', 0);
        }
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get workshop by owner user ID
     * @param int $user_id Owner user ID
     * @param bool $include_deleted Include deleted workshops
     * @return object|null Workshop object or NULL
     */
    public function get_by_owner($user_id, $include_deleted = FALSE)
    {
        $this->db->from(self::TABLE_WORKSHOPS);
        $this->db->where('user_id', $user_id);
        
        if (!$include_deleted) {
            $this->db->where('is_deleted', 0);
        }
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get all workshops by owner user ID (for admin view)
     * @param int $user_id Owner user ID
     * @return array Array of workshop objects
     */
    public function get_all_by_owner($user_id)
    {
        $this->db->from(self::TABLE_WORKSHOPS);
        $this->db->where('user_id', $user_id);
        $this->db->where('is_deleted', 0);
        $this->db->order_by('created_at', 'DESC');
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Insert new workshop
     * @param array $data Workshop data
     * @return int|bool New workshop ID or FALSE on failure
     */
    public function insert($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        if ($this->db->insert(self::TABLE_WORKSHOPS, $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    /**
     * Update workshop
     * @param int $id Workshop ID
     * @param array $data Workshop data to update
     * @return bool TRUE on success, FALSE on failure
     */
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        return $this->db->update(self::TABLE_WORKSHOPS, $data);
    }

    /**
     * Soft delete workshop
     * @param int $id Workshop ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function delete($id)
    {
        $data = [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $id);
        return $this->db->update(self::TABLE_WORKSHOPS, $data);
    }

    /**
     * Hard delete workshop (permanent)
     * @param int $id Workshop ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function hard_delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete(self::TABLE_WORKSHOPS);
    }

    /**
     * Update workshop status
     * @param int $id Workshop ID
     * @param string $status New status (pending, active, inactive, suspended)
     * @return bool TRUE on success, FALSE on failure
     */
    public function update_status($id, $status)
    {
        $data = ['status' => $status];
        
        if ($status === 'active') {
            $data['verified_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($id, $data);
    }

    /**
     * Update workshop rating average
     * @param int $id Workshop ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function update_rating($id)
    {
        $this->db->select('AVG(rating) as avg_rating, COUNT(*) as total_reviews');
        $this->db->from('reviews');
        $this->db->where('workshop_id', $id);
        $this->db->where('is_deleted', 0);
        $query = $this->db->get();
        $result = $query->row();
        
        $avg_rating = $result->avg_rating ?: 0;
        $total_reviews = $result->total_reviews ?: 0;
        
        return $this->update($id, [
            'rating_avg' => round($avg_rating, 2),
            'total_reviews' => $total_reviews
        ]);
    }

    /**
     * Geocode address using Nominatim OSM API
     * @param string $address Full address
     * @return array|FALSE Array with lat/lng or FALSE on failure
     */
    public function geocode_address($address)
    {
        // Use Nominatim OpenStreetMap API (free, no API key required)
        $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($address);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'BengkelTerdekat/4.0 (contact@bengkelterdekat.com)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $results = json_decode($response, TRUE);
            
            if (is_array($results) && !empty($results)) {
                return [
                    'latitude' => $results[0]['lat'],
                    'longitude' => $results[0]['lon'],
                    'display_name' => $results[0]['display_name']
                ];
            }
        }
        
        return FALSE;
    }

    // ============================================================
    // WORKSHOP SERVICES CRUD OPERATIONS
    // ============================================================

    /**
     * Get all services for a workshop
     * @param int $workshop_id Workshop ID
     * @param bool $only_available Only available services
     * @return array Array of service objects
     */
    public function get_services($workshop_id, $only_available = TRUE)
    {
        $this->db->from(self::TABLE_SERVICES);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('is_deleted', 0);
        
        if ($only_available) {
            $this->db->where('is_available', 1);
        }
        
        $this->db->order_by('service_category', 'ASC');
        $this->db->order_by('service_name', 'ASC');
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get service by ID
     * @param int $id Service ID
     * @return object|null Service object or NULL
     */
    public function get_service_by_id($id)
    {
        $this->db->from(self::TABLE_SERVICES);
        $this->db->where('id', $id);
        $this->db->where('is_deleted', 0);
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Insert new workshop service
     * @param array $data Service data
     * @return int|bool New service ID or FALSE on failure
     */
    public function insert_service($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        if ($this->db->insert(self::TABLE_SERVICES, $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    /**
     * Update workshop service
     * @param int $id Service ID
     * @param array $data Service data to update
     * @return bool TRUE on success, FALSE on failure
     */
    public function update_service($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        return $this->db->update(self::TABLE_SERVICES, $data);
    }

    /**
     * Soft delete workshop service
     * @param int $id Service ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function delete_service($id)
    {
        $data = [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $id);
        return $this->db->update(self::TABLE_SERVICES, $data);
    }

    /**
     * Get service categories
     * @return array Associative array of categories
     */
    public function get_service_categories()
    {
        return [
            'sparepart' => 'Sparepart',
            'servis' => 'Servis',
            'cat' => 'Cat Body',
            'ban' => 'Ban & Velg',
            'aki' => 'Aki & Kelistrikan',
            'tuning' => 'Tuning & Performance',
            'lainnya' => 'Lainnya'
        ];
    }

    /**
     * Count total workshops
     * @param string $status Filter by status
     * @return int Total count
     */
    public function count_all($status = NULL)
    {
        $this->db->from(self::TABLE_WORKSHOPS);
        $this->db->where('is_deleted', 0);
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Count services for a workshop
     * @param int $workshop_id Workshop ID
     * @return int Total count
     */
    public function count_services($workshop_id)
    {
        $this->db->from(self::TABLE_SERVICES);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('is_deleted', 0);
        
        return $this->db->count_all_results();
    }
}

/* End of file Workshop_model.php */
/* Location: ./application/models/Workshop_model.php */
