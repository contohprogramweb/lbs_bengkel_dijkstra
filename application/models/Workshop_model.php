<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Workshop Model
 * 
 * Handles workshop/garage data operations including CRUD,
 * services management, and workshop-related queries.
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Models
 * @version     4.0
 */
class Workshop_model extends CI_Model {

    const TABLE_WORKSHOPS = 'workshops';
    const TABLE_SERVICES = 'workshop_services';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ================================================================
    // WORKSHOP CRUD OPERATIONS
    // ================================================================

    /**
     * Get all workshops with optional filters
     * @param array $filters Filtering options
     * @return array Array of workshops
     */
    public function get_all($filters = [])
    {
        $this->db->select('w.*, u.full_name as owner_name, u.email as owner_email');
        $this->db->from(self::TABLE_WORKSHOPS . ' w');
        $this->db->join('users u', 'w.user_id = u.id', 'left');
        $this->db->where('w.is_deleted', 0);

        if (!empty($filters['status'])) {
            $this->db->where('w.status', $filters['status']);
        }

        if (!empty($filters['city'])) {
            $this->db->like('w.city', $filters['city']);
        }

        if (!empty($filters['province'])) {
            $this->db->where('w.province', $filters['province']);
        }

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('w.name', $filters['search']);
            $this->db->or_like('w.description', $filters['search']);
            $this->db->or_like('w.city', $filters['search']);
            $this->db->group_end();
        }

        $this->db->order_by('w.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Get active workshops only
     * @return array
     */
    public function get_active_workshops()
    {
        return $this->get_all(['status' => 'active']);
    }

    /**
     * Find workshop by ID
     * @param int $id Workshop ID
     * @return array|null Workshop data
     */
    public function find_by_id($id)
    {
        return $this->db->get_where(self::TABLE_WORKSHOPS, [
            'id' => $id,
            'is_deleted' => 0
        ])->row_array();
    }

    /**
     * Create new workshop
     * @param array $data Workshop data
     * @return int Insert ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(self::TABLE_WORKSHOPS, $data);
        return $this->db->insert_id();
    }

    /**
     * Update workshop
     * @param int $id Workshop ID
     * @param array $data Data to update
     * @return bool
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
     * @return bool
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

    // ================================================================
    // WORKSHOP SERVICES
    // ================================================================

    /**
     * Get all services for a workshop
     * @param int $workshop_id Workshop ID
     * @return array Array of services
     */
    public function get_workshop_services($workshop_id)
    {
        return $this->db->where('workshop_id', $workshop_id)
                        ->where('is_deleted', 0)
                        ->where('is_available', 1)
                        ->order_by('service_category', 'ASC')
                        ->order_by('service_name', 'ASC')
                        ->get(self::TABLE_SERVICES)
                        ->result_array();
    }

    /**
     * Add service to workshop
     * @param int $workshop_id Workshop ID
     * @param array $data Service data
     * @return int Insert ID
     */
    public function add_service($workshop_id, $data)
    {
        $data['workshop_id'] = $workshop_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(self::TABLE_SERVICES, $data);
        return $this->db->insert_id();
    }

    /**
     * Update service
     * @param int $service_id Service ID
     * @param array $data Data to update
     * @return bool
     */
    public function update_service($service_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $service_id);
        return $this->db->update(self::TABLE_SERVICES, $data);
    }

    /**
     * Delete service (soft delete)
     * @param int $service_id Service ID
     * @return bool
     */
    public function delete_service($service_id)
    {
        $data = [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $service_id);
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

    // ================================================================
    // STATISTICS & COUNTS
    // ================================================================

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

    /**
     * Get workshops by user ID (owner)
     * @param int $user_id User ID
     * @return object|null Workshop data as object or null if not found
     */
    public function get_by_owner($user_id)
    {
        $this->db->select('*');
        $this->db->from(self::TABLE_WORKSHOPS);
        $this->db->where('user_id', $user_id);
        $this->db->where('is_deleted', 0);
        $this->db->order_by('created_at', 'DESC');
        
        return $this->db->get()->row();
    }

    /**
     * Get all services for a workshop
     * @param int $workshop_id Workshop ID
     * @param bool $available_only Only return available services
     * @return array Array of services
     */
    public function get_services($workshop_id, $available_only = TRUE)
    {
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('is_deleted', 0);
        
        if ($available_only) {
            $this->db->where('is_available', 1);
        }
        
        $this->db->order_by('service_category', 'ASC');
        $this->db->order_by('service_name', 'ASC');
        
        return $this->db->get(self::TABLE_SERVICES)->result();
    }

    /**
     * Get service by ID
     * @param int $id Service ID
     * @return object|null Service data
     */
    public function get_service_by_id($id)
    {
        return $this->db->get_where(self::TABLE_SERVICES, [
            'id' => $id,
            'is_deleted' => 0
        ])->row();
    }

    /**
     * Insert service
     * @param array $data Service data
     * @return int Insert ID
     */
    public function insert_service($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(self::TABLE_SERVICES, $data);
        return $this->db->insert_id();
    }

    /**
     * Get workshops by user ID
     * @param int $user_id User ID
     * @return array Array of workshops
     */
    public function get_by_user_id($user_id)
    {
        $this->db->select('*');
        $this->db->from(self::TABLE_WORKSHOPS);
        $this->db->where('user_id', $user_id);
        $this->db->where('is_deleted', 0);
        $this->db->order_by('created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Geocode address to get latitude and longitude
     * @param string $address Full address
     * @return array|null Array with latitude and longitude, or null if failed
     */
    public function geocode_address($address)
    {
        // Simple implementation - in production, use Google Maps API or similar
        // For now, return default coordinates (Jakarta)
        // You can integrate with actual geocoding service later
        
        // Try to get coordinates from cached data or external API
        $ci =& get_instance();
        $ci->load->helper('file');
        
        // Default to Jakarta coordinates if geocoding fails
        return [
            'latitude' => -6.2088,
            'longitude' => 106.8456
        ];
    }
}

/* End of file Workshop_model.php */
/* Location: ./application/models/Workshop_model.php */
