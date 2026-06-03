<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vehicle Model
 * 
 * Handles vehicle data operations for customer vehicles.
 * Supports soft delete mechanism.
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class Vehicle_model extends CI_Model {

    /**
     * Table name
     */
    private $table = 'vehicles';

    /**
     * Max vehicles per user (BR-58)
     */
    const MAX_VEHICLES_PER_USER = 5;

    /**
     * Minimum year (BR-59)
     */
    const MIN_YEAR = 1980;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // --------------------------------------------------------------------
    // Basic CRUD Methods
    // --------------------------------------------------------------------

    /**
     * Find vehicle by ID (excluding deleted)
     * 
     * @param int $id Vehicle ID
     * @return object|null Vehicle object or NULL
     */
    public function find_by_id($id)
    {
        return $this->db
            ->where('id', $id)
            ->where('is_deleted', 0)
            ->get($this->table)
            ->row();
    }

    /**
     * Get all vehicles for a user
     * 
     * @param int $user_id User ID
     * @return array Vehicles array
     */
    public function get_by_user($user_id)
    {
        return $this->db
            ->where('user_id', $user_id)
            ->where('is_deleted', 0)
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
    }

    /**
     * Count vehicles for a user
     * 
     * @param int $user_id User ID
     * @return int Total count
     */
    public function count_by_user($user_id)
    {
        return $this->db
            ->where('user_id', $user_id)
            ->where('is_deleted', 0)
            ->count_all_results($this->table);
    }

    /**
     * Check if vehicle number exists for user (case-insensitive, BR-59)
     * 
     * @param string $vehicle_number Vehicle number
     * @param int $user_id User ID
     * @param int|null $exclude_id Exclude this ID from check
     * @return bool TRUE if exists, FALSE otherwise
     */
    public function vehicle_number_exists($vehicle_number, $user_id, $exclude_id = NULL)
    {
        // Normalize plate number: uppercase, remove extra spaces
        $normalized = $this->normalize_plate_number($vehicle_number);
        
        $this->db
            ->where('user_id', $user_id)
            ->where('is_deleted', 0)
            ->where('UPPER(TRIM(vehicle_number))', strtoupper(trim($vehicle_number)));
        
        if ($exclude_id !== NULL) {
            $this->db->where('id !=', $exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    /**
     * Normalize plate number (remove extra spaces, uppercase)
     * 
     * @param string $plate Plate number
     * @return string Normalized plate number
     */
    public function normalize_plate_number($plate)
    {
        // Remove multiple spaces, trim, uppercase
        return strtoupper(preg_replace('/\s+/', ' ', trim($plate)));
    }

    /**
     * Insert new vehicle
     * 
     * @param array $data Vehicle data
     * @return int|bool Insert ID or FALSE on failure
     */
    public function insert($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }

        return FALSE;
    }

    /**
     * Update vehicle
     * 
     * @param int $vehicle_id Vehicle ID
     * @param array $data Vehicle data
     * @return bool TRUE on success, FALSE on failure
     */
    public function update($vehicle_id, $data)
    {
        unset($data['user_id']);
        unset($data['is_deleted']);
        
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db
            ->where('id', $vehicle_id)
            ->where('is_deleted', 0)
            ->update($this->table, $data);
    }

    /**
     * Update odometer (with validation BR-60: can only increase)
     * 
     * @param int $vehicle_id Vehicle ID
     * @param int $new_km New kilometer value
     * @return array ['success' => bool, 'message' => string]
     */
    public function update_odometer($vehicle_id, $new_km)
    {
        $vehicle = $this->find_by_id($vehicle_id);
        
        if (!$vehicle) {
            return ['success' => FALSE, 'message' => 'Kendaraan tidak ditemukan'];
        }

        $current_km = (int) $vehicle->current_km;
        $new_km = (int) $new_km;

        if ($new_km < $current_km) {
            return [
                'success' => FALSE, 
                'message' => 'Kilometer baru harus lebih besar atau sama dengan kilometer sebelumnya (' . number_format($current_km) . ' km)'
            ];
        }

        return [
            'success' => $this->update($vehicle_id, ['current_km' => $new_km]),
            'message' => 'Kilometer berhasil diperbarui'
        ];
    }

    /**
     * Soft delete vehicle (BR-61)
     * 
     * @param int $vehicle_id Vehicle ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function soft_delete($vehicle_id)
    {
        $data = [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ];

        return $this->db
            ->where('id', $vehicle_id)
            ->update($this->table, $data);
    }

    // --------------------------------------------------------------------
    // Business Rule Validation
    // --------------------------------------------------------------------

    /**
     * Check if user can add more vehicles (BR-58)
     * 
     * @param int $user_id User ID
     * @return array ['can_add' => bool, 'count' => int, 'max' => int]
     */
    public function can_add_vehicle($user_id)
    {
        $count = $this->count_by_user($user_id);
        return [
            'can_add' => $count < self::MAX_VEHICLES_PER_USER,
            'count' => $count,
            'max' => self::MAX_VEHICLES_PER_USER
        ];
    }

    /**
     * Validate year (BR-59, BR-60)
     * 
     * @param int $year Year to validate
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validate_year($year)
    {
        $year = (int) $year;
        $current_year = (int) date('Y');
        $max_year = $current_year + 1;

        if ($year < self::MIN_YEAR) {
            return [
                'valid' => FALSE,
                'message' => 'Tahun kendaraan minimal ' . self::MIN_YEAR
            ];
        }

        if ($year > $max_year) {
            return [
                'valid' => FALSE,
                'message' => 'Tahun kendaraan maksimal ' . $max_year
            ];
        }

        return ['valid' => TRUE, 'message' => ''];
    }

    /**
     * Check if vehicle has active bookings (for delete validation BR-61)
     * Active statuses: pending, accepted, in_progress (processed)
     * 
     * @param int $vehicle_id Vehicle ID
     * @return array ['has_active' => bool, 'count' => int, 'bookings' => array]
     */
    public function has_active_bookings($vehicle_id)
    {
        $active_statuses = ['pending', 'accepted', 'in_progress'];
        
        $this->db
            ->select('id, booking_number, status, scheduled_date')
            ->from('bookings')
            ->where('vehicle_id', $vehicle_id)
            ->where('is_deleted', 0)
            ->where_in('status', $active_statuses);
        
        $bookings = $this->db->get()->result_array();
        
        return [
            'has_active' => count($bookings) > 0,
            'count' => count($bookings),
            'bookings' => $bookings
        ];
    }

    // --------------------------------------------------------------------
    // Service History & Recommendations
    // --------------------------------------------------------------------

    /**
     * Get service history for a vehicle
     * 
     * @param int $vehicle_id Vehicle ID
     * @param int $limit Limit results
     * @return array Bookings with completed status
     */
    public function get_service_history($vehicle_id, $limit = 50)
    {
        $this->db
            ->select('b.*, w.name as workshop_name, w.city')
            ->from('bookings b')
            ->join('workshops w', 'w.id = b.workshop_id', 'left')
            ->where('b.vehicle_id', $vehicle_id)
            ->where('b.is_deleted', 0)
            ->where_in('b.status', ['completed', 'cancelled'])
            ->order_by('b.completed_at', 'DESC')
            ->limit($limit);
        
        return $this->db->get()->result_array();
    }

    /**
     * Get next service recommendation based on odometer
     * Uses system_settings for interval configuration
     * 
     * @param int $vehicle_id Vehicle ID
     * @return array Recommendation data
     */
    public function get_service_recommendation($vehicle_id)
    {
        $vehicle = $this->find_by_id($vehicle_id);
        
        if (!$vehicle) {
            return null;
        }

        // Default interval: 5000km (BR-75)
        $service_interval = (int) $this->get_setting('service_interval_km', 5000);
        
        $current_km = (int) $vehicle->current_km;
        $last_service_km = (int) $vehicle->last_service_km;
        $next_service_km = $last_service_km + $service_interval;
        $km_until_service = max(0, $next_service_km - $current_km);
        
        // Calculate percentage used
        $percentage_used = $service_interval > 0 ? min(100, (($current_km - $last_service_km) / $service_interval) * 100) : 0;
        
        return [
            'current_km' => $current_km,
            'last_service_km' => $last_service_km,
            'next_service_km' => $next_service_km,
            'km_until_service' => $km_until_service,
            'percentage_used' => round($percentage_used, 1),
            'interval_km' => $service_interval,
            'needs_service_soon' => $km_until_service <= 500, // Alert when within 500km
            'overdue' => $current_km >= $next_service_km && $last_service_km > 0
        ];
    }

    /**
     * Get system setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed
     */
    private function get_setting($key, $default = NULL)
    {
        static $settings = [];

        if (empty($settings)) {
            $this->db->select('setting_key, setting_value');
            $query = $this->db->get('system_settings');
            
            foreach ($query->result() as $row) {
                $settings[$row->setting_key] = $row->setting_value;
            }
        }

        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    // --------------------------------------------------------------------
    // Dropdown Data Helpers
    // --------------------------------------------------------------------

    /**
     * Get fuel type options
     * 
     * @return array
     */
    public function get_fuel_types()
    {
        return [
            'petrol' => 'Bensin',
            'diesel' => 'Solar',
            'electric' => 'Listrik',
            'hybrid' => 'Hybrid'
        ];
    }

    /**
     * Get vehicle brand options (common brands)
     * In production, this could come from a master table
     * 
     * @return array
     */
    public function get_brands()
    {
        return [
            'Toyota', 'Honda', 'Suzuki', 'Daihatsu', 'Mitsubishi',
            'Nissan', 'Mazda', 'Subaru', 'Isuzu', 'Ford',
            'Chevrolet', 'Hyundai', 'Kia', 'BMW', 'Mercedes-Benz',
            'Audi', 'Volkswagen', 'Peugeot', 'Renault', 'Volvo',
            'Lexus', 'Infiniti', 'Acura', 'Jeep', 'Land Rover',
            'Porsche', 'Ferrari', 'Lamborghini', 'Tesla', 'BYD',
            'Wuling', 'Chery', 'Geely', 'Haval', 'MG',
            'Maxus', 'DFS Glory', 'Jetour', 'Omoda', 'Exeed',
            'Yamaha', 'Kawasaki', 'Suzuki Motor', 'Honda Motor', 'Vespa'
        ];
    }

    /**
     * Get transmission options
     * 
     * @return array
     */
    public function get_transmissions()
    {
        return [
            'manual' => 'Manual',
            'automatic' => 'Otomatis',
            'cvt' => 'CVT'
        ];
    }

    /**
     * Get vehicle types
     * 
     * @return array
     */
    public function get_vehicle_types()
    {
        return [
            'motorcycle' => 'Motor',
            'car' => 'Mobil',
            'truck' => 'Truk',
            'bus' => 'Bus',
            'other' => 'Lainnya'
        ];
    }

    // --------------------------------------------------------------------
    // Service Reminder Methods
    // --------------------------------------------------------------------

    /**
     * Get vehicles due for service reminder
     * Based on kilometer threshold or months since last service
     * 
     * @param int $km_threshold Kilometer threshold (e.g., 5000)
     * @param int $month_threshold Months threshold (e.g., 6)
     * @return array Vehicles due for service
     */
    public function get_vehicles_due_for_service($km_threshold = 5000, $month_threshold = 6)
    {
        $current_date = date('Y-m-d');
        $threshold_date = date('Y-m-d', strtotime("-$month_threshold months"));
        
        // Get vehicles where:
        // 1. current_km >= last_service_km + km_threshold OR
        // 2. last_service_date < threshold_date
        // 3. Vehicle is not deleted
        // 4. User has valid email
        
        $this->db->select('v.*, u.email, u.full_name')
            ->from('vehicles v')
            ->join('users u', 'u.id = v.user_id', 'left')
            ->where('v.is_deleted', 0)
            ->where('u.is_deleted', 0)
            ->where('u.email IS NOT NULL')
            ->where('u.email !=', '')
            ->group_start()
                // Check kilometer threshold
                ->where('v.current_km >= (v.last_service_km + ' . (int)$km_threshold . ')')
                ->or_where('v.last_service_date <', $threshold_date)
            ->group_end();
        
        return $this->db->get()->result_array();
    }
}

/* End of file Vehicle_model.php */
/* Location: ./application/models/Vehicle_model.php */
