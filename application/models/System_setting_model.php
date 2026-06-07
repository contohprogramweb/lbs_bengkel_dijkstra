<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * System Setting Model
 * 
 * Handles system-wide configuration settings.
 * Settings are cached for performance.
 * 
 * @package     Bengkel Terdekat
 * @version     4.1
 */
class System_setting_model extends CI_Model {

    /**
     * Table name
     */
    private $table = 'system_settings';

    /**
     * Cache for settings
     */
    private $cache = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // --------------------------------------------------------------------
    // Core Methods
    // --------------------------------------------------------------------

    /**
     * Get all settings as key-value array
     * 
     * @return array Associative array of settings
     */
    public function get_all_settings()
    {
        $query = $this->db->get($this->table);
        $result = $query->result();

        $settings = [];
        foreach ($result as $row) {
            $settings[$row->setting_key] = $this->cast_value($row->setting_value, $row->setting_type);
        }

        return $settings;
    }

    /**
     * Get a single setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed Setting value or default
     */
    public function get_setting($key, $default = NULL)
    {
        // Check cache first
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $row = $this->db->where('setting_key', $key)->get($this->table)->row();

        if ($row) {
            $value = $this->cast_value($row->setting_value, $row->setting_type);
            $this->cache[$key] = $value;
            return $value;
        }

        return $default;
    }

    /**
     * Update a setting value
     * 
     * @param string $key Setting key
     * @param mixed $value New value
     * @return bool TRUE on success, FALSE on failure
     */
    public function update_setting($key, $value)
    {
        $data = [
            'setting_value' => (string)$value,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->db
            ->where('setting_key', $key)
            ->update($this->table, $data);

        // Clear cache for this key
        unset($this->cache[$key]);

        return $result;
    }

    /**
     * Insert or update a setting
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param string $type Setting type (string, integer, decimal, boolean)
     * @param string $description Setting description
     * @return int|bool Insert ID or TRUE on update, FALSE on failure
     */
    public function save_setting($key, $value, $type = 'string', $description = '')
    {
        $data = [
            'setting_key' => $key,
            'setting_value' => (string)$value,
            'setting_type' => $type,
            'description' => $description,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Check if exists
        $existing = $this->db->where('setting_key', $key)->get($this->table)->row();

        if ($existing) {
            unset($data['setting_key']);
            unset($data['setting_type']);
            unset($data['description']);
            
            $result = $this->db
                ->where('setting_key', $key)
                ->update($this->table, $data);
            
            unset($this->cache[$key]);
            return $result;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->db->insert($this->table, $data);
            return $result ? $this->db->insert_id() : FALSE;
        }
    }

    /**
     * Get settings by category/type
     * 
     * @param string $type Filter by setting type
     * @return array Settings array
     */
    public function get_by_type($type)
    {
        $query = $this->db->where('setting_type', $type)->get($this->table);
        $result = $query->result();

        $settings = [];
        foreach ($result as $row) {
            $settings[$row->setting_key] = [
                'value' => $this->cast_value($row->setting_value, $row->setting_type),
                'type' => $row->setting_type,
                'description' => $row->description
            ];
        }

        return $settings;
    }

    /**
     * Get all settings with full details including category
     * 
     * @return array Settings with metadata
     */
    public function get_all_with_details()
    {
        $query = $this->db->order_by('category', 'ASC')->order_by('setting_key', 'ASC')->get($this->table);
        return $query->result();
    }

    /**
     * Get settings grouped by category
     * 
     * @return array Settings grouped by category
     */
    public function get_grouped_by_category()
    {
        $query = $this->db->order_by('setting_key', 'ASC')->get($this->table);
        $result = $query->result();
        
        $grouped = [];
        foreach ($result as $setting) {
            $category = $setting->category ?? 'general';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $setting;
        }
        
        return $grouped;
    }

    // --------------------------------------------------------------------
    // Helper Methods
    // --------------------------------------------------------------------

    /**
     * Cast value based on type
     * 
     * @param string $value Raw value from database
     * @param string $type Setting type
     * @return mixed Casted value
     */
    private function cast_value($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int)$value;
            case 'decimal':
                return (float)$value;
            case 'boolean':
                return in_array($value, ['1', 'true', 'TRUE', 'yes', 'YES'], true);
            case 'json':
                return json_decode($value, true) ?: $value;
            default:
                return $value;
        }
    }

    /**
     * Clear cache
     */
    public function clear_cache()
    {
        $this->cache = [];
    }

    // --------------------------------------------------------------------
    // Convenience Methods for Common Settings
    // --------------------------------------------------------------------

    /**
     * Get emergency radius
     * 
     * @return float Emergency radius in km
     */
    public function get_emergency_radius()
    {
        return (float)$this->get_setting('radius_darurat', 5.0);
    }

    /**
     * Get reminder interval in km
     * 
     * @return int Kilometer interval
     */
    public function get_reminder_interval_km()
    {
        return (int)$this->get_setting('reminder_interval_km', 5000);
    }

    /**
     * Get reminder interval in months
     * 
     * @return int Month interval
     */
    public function get_reminder_interval_months()
    {
        return (int)$this->get_setting('reminder_interval_months', 6);
    }

    /**
     * Check if same-day booking is enabled
     * 
     * @return bool TRUE if enabled
     */
    public function is_same_day_booking_enabled()
    {
        return (bool)$this->get_setting('same_day_booking', TRUE);
    }

    /**
     * Check if strict review moderation is enabled
     * 
     * @return bool TRUE if enabled
     */
    public function is_strict_review_moderation_enabled()
    {
        return (bool)$this->get_setting('moderasi_review_ketat', FALSE);
    }

    /**
     * Get invoice tax rate
     * 
     * @return float Tax rate percentage
     */
    public function get_tax_rate()
    {
        return (float)$this->get_setting('invoice_tax_rate', 11.0);
    }

    /**
     * Get invoice due days
     * 
     * @return int Due days
     */
    public function get_invoice_due_days()
    {
        return (int)$this->get_setting('invoice_due_days', 7);
    }

    /**
     * Get max upload size in MB
     * 
     * @return int Max size in MB
     */
    public function get_max_upload_size_mb()
    {
        return (int)$this->get_setting('max_upload_size_mb', 5);
    }

    /**
     * Get allowed file types
     * 
     * @return array Array of allowed extensions
     */
    public function get_allowed_file_types()
    {
        $types = $this->get_setting('allowed_file_types', 'jpg,jpeg,png,pdf');
        return array_map('trim', explode(',', $types));
    }

    /**
     * Get featured workshop limit
     * 
     * @return int Maximum featured workshops
     */
    public function get_featured_workshop_limit()
    {
        return (int)$this->get_setting('featured_workshop_limit', 10);
    }
}

/* End of file System_setting_model.php */
/* Location: ./application/models/System_setting_model.php */
