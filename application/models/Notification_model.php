<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notification Model
 * 
 * Handles notification templates, sending emails, logging,
 * and service reminder logic.
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Models
 * @version     4.0
 */
class Notification_model extends CI_Model {

    private $table_templates = 'notification_templates';
    private $table_logs = 'notification_logs';
    private $table_vehicles = 'vehicles';
    private $table_bookings = 'bookings';
    private $table_workshops = 'workshops';
    private $table_users = 'users';
    private $table_system_settings = 'system_settings';

    // Default template fallbacks (BR-85)
    private $default_templates = [
        'booking_accepted' => [
            'subject' => 'Booking Anda Diterima - {{kode_booking}}',
            'body' => '<p>Halo {{nama_pengguna}},</p><p>Booking Anda dengan kode <strong>{{kode_booking}}</strong> telah diterima oleh <strong>{{nama_bengkel}}</strong>.</p><p>Silakan datang sesuai jadwal yang telah ditentukan.</p><p>Terima kasih.</p>'
        ],
        'booking_processed' => [
            'subject' => 'Booking Sedang Dikerjakan - {{kode_booking}}',
            'body' => '<p>Halo {{nama_pengguna}},</p><p>Booking Anda <strong>{{kode_booking}}</strong> sedang dikerjakan di <strong>{{nama_bengkel}}</strong>.</p><p>Kami akan menginformasikan jika ada perubahan.</p>'
        ],
        'booking_completed' => [
            'subject' => 'Booking Selesai - {{kode_booking}}',
            'body' => '<p>Halo {{nama_pengguna}},</p><p>Booking Anda <strong>{{kode_booking}}</strong> telah selesai dikerjakan di <strong>{{nama_bengkel}}</strong>.</p><p>Silakan lakukan pembayaran dan ambil kendaraan Anda.</p><p>Terima kasih telah menggunakan layanan kami.</p>'
        ],
        'booking_cancelled' => [
            'subject' => 'Booking Dibatalkan - {{kode_booking}}',
            'body' => '<p>Halo {{nama_pengguna}},</p><p>Booking Anda <strong>{{kode_booking}}</strong> telah dibatalkan.</p><p>Jika ada pertanyaan, silakan hubungi bengkel terkait.</p>'
        ],
        'service_reminder' => [
            'subject' => 'Saatnya Servis Kendaraan Anda!',
            'body' => '<p>Halo {{nama_pengguna}},</p><p>Kendaraan <strong>{{kendaraan}}</strong> Anda sudah saatnya untuk servis berkala.</p><p>Kilometer terakhir: {{km_terakhir}} km</p><p>Estimasi kilometer saat ini: {{km_estimasi}} km</p><p>Waktu servis terakhir: {{tanggal_servis}}</p><p>Berikut rekomendasi bengkel terdekat untuk Anda:</p><p>{{rekomendasi_bengkel}}</p><p>Segera jadwalkan servis untuk menjaga kondisi kendaraan Anda.</p><p>Terima kasih.</p>'
        ]
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('CI_PHPMailer');
    }

    // ================================================================
    // TEMPLATE MANAGEMENT (UC-ADM-06, BR-84, BR-85)
    // ================================================================

    /**
     * Get all notification templates
     * @return array
     */
    public function get_all_templates()
    {
        return $this->db
            ->where('is_deleted', 0)
            ->order_by('event_name', 'ASC')
            ->get($this->table_templates)
            ->result_array();
    }

    /**
     * Get template by event key
     * @param string $event_key
     * @return array|null
     */
    public function get_template_by_event($event_key)
    {
        $template = $this->db
            ->where('event_key', $event_key)
            ->where('is_deleted', 0)
            ->get($this->table_templates)
            ->row_array();

        // If not found or inactive, return default fallback (BR-85)
        if (!$template || !$template['is_active']) {
            if (isset($this->default_templates[$event_key])) {
                return [
                    'event_key' => $event_key,
                    'subject_template' => $this->default_templates[$event_key]['subject'],
                    'body_template' => $this->default_templates[$event_key]['body'],
                    'is_active' => 0,
                    'is_default' => TRUE
                ];
            }
        }

        return $template;
    }

    /**
     * Get template by ID
     * @param int $id
     * @return array|null
     */
    public function get_template_by_id($id)
    {
        return $this->db
            ->where('id', $id)
            ->where('is_deleted', 0)
            ->get($this->table_templates)
            ->row_array();
    }

    /**
     * Create new template
     * @param array $data
     * @return int Insert ID
     */
    public function create_template($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['is_deleted'] = 0;
        
        // Extract variables from body template (BR-84)
        $data['variables'] = $this->extract_variables($data['body_template']);
        
        $this->db->insert($this->table_templates, $data);
        return $this->db->insert_id();
    }

    /**
     * Update template
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_template($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Extract variables from body template (BR-84)
        if (isset($data['body_template'])) {
            $data['variables'] = $this->extract_variables($data['body_template']);
        }
        
        return $this->db
            ->where('id', $id)
            ->update($this->table_templates, $data);
    }

    /**
     * Deactivate template (soft delete - BR-85)
     * @param int $id
     * @return bool
     */
    public function deactivate_template($id)
    {
        return $this->db
            ->where('id', $id)
            ->update($this->table_templates, ['is_active' => 0]);
    }

    /**
     * Activate template
     * @param int $id
     * @return bool
     */
    public function activate_template($id)
    {
        return $this->db
            ->where('id', $id)
            ->update($this->table_templates, ['is_active' => 1]);
    }

    /**
     * Extract template variables from body (BR-84: format {{nama_variabel}})
     * @param string $body
     * @return array
     */
    private function extract_variables($body)
    {
        preg_match_all('/\{\{([a-zA-Z_][a-zA-Z0-9_]*)\}\}/', $body, $matches);
        return array_unique($matches[1]);
    }

    /**
     * Replace template variables with actual values (BR-84)
     * @param string $template
     * @param array $variables
     * @return string
     */
    public function replace_variables($template, $variables)
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }

    /**
     * Send notification (unified method for all notification types)
     * @param array $data Notification data
     * @return bool
     */
    public function send_notification($data)
    {
        // If template_key is provided, use template system
        if (!empty($data['template_key'])) {
            $template = $this->get_template_by_event($data['template_key']);
            
            if (!$template) {
                log_message('error', 'Template not found: ' . $data['template_key']);
                return FALSE;
            }
            
            // Prepare variables from data
            $variables = isset($data['data']) ? $data['data'] : [];
            
            $subject = $this->replace_variables($template['subject_template'], $variables);
            $body = $this->replace_variables($template['body_template'], $variables);
            
            return $this->send_email(
                $data['recipient_email'],
                $data['recipient_name'] ?? 'User',
                $data['template_key'],
                $subject,
                $body,
                $data['metadata'] ?? []
            );
        }
        
        // Direct email without template
        $subject = $data['subject'] ?? 'Notification';
        $body = $data['body'] ?? '';
        $event_key = $data['event_key'] ?? 'direct_email';
        
        return $this->send_email(
            $data['recipient_email'],
            $data['recipient_name'] ?? 'User',
            $event_key,
            $subject,
            $body,
            $data['metadata'] ?? []
        );
    }

    // ================================================================
    // EMAIL NOTIFICATION (FR-NOT-02)
    // ================================================================

    /**
     * Send booking status notification email
     * @param int $booking_id
     * @param string $new_status
     * @return bool
     */
    public function send_booking_status_notification($booking_id, $new_status)
    {
        // Get booking details
        $booking = $this->db
            ->select('b.*, u.email as user_email, u.full_name as user_name, w.name as workshop_name')
            ->from('bookings b')
            ->join('users u', 'b.user_id = u.id')
            ->join('workshops w', 'b.workshop_id = w.id')
            ->where('b.id', $booking_id)
            ->get()
            ->row_array();

        if (!$booking) {
            log_message('error', 'Booking not found for notification: ' . $booking_id);
            return FALSE;
        }

        // Map booking status to event key
        $event_map = [
            'accepted' => 'booking_accepted',
            'in_progress' => 'booking_processed',
            'completed' => 'booking_completed',
            'cancelled' => 'booking_cancelled',
            'rejected' => 'booking_rejected'
        ];

        $event_key = isset($event_map[$new_status]) ? $event_map[$new_status] : null;
        if (!$event_key) {
            return FALSE;
        }

        // Get template
        $template = $this->get_template_by_event($event_key);
        if (!$template) {
            log_message('error', 'Template not found for event: ' . $event_key);
            return FALSE;
        }

        // Prepare variables
        $variables = [
            'nama_pengguna' => $booking['user_name'],
            'kode_booking' => $booking['booking_code'],
            'nama_bengkel' => $booking['workshop_name'],
            'tanggal_booking' => date('d/m/Y', strtotime($booking['booking_date'])),
            'waktu_booking' => $booking['slot_time'] ?? '-'
        ];

        // Replace variables in template
        $subject = $this->replace_variables($template['subject_template'], $variables);
        $body = $this->replace_variables($template['body_template'], $variables);

        // Send email
        return $this->send_email(
            $booking['user_email'],
            $booking['user_name'],
            $event_key,
            $subject,
            $body,
            ['booking_id' => $booking_id, 'status' => $new_status]
        );
    }

    /**
     * Send service reminder email (FR-NOT-03, UC-USR-11)
     * @param array $vehicle_data
     * @param array $user_data
     * @param array $recommended_workshops
     * @return bool
     */
    public function send_service_reminder($vehicle_data, $user_data, $recommended_workshops = [])
    {
        // Get template
        $template = $this->get_template_by_event('service_reminder');
        if (!$template) {
            log_message('error', 'Service reminder template not found');
            return FALSE;
        }

        // Format workshop recommendations
        $workshop_list = '';
        if (!empty($recommended_workshops)) {
            foreach ($recommended_workshops as $ws) {
                $workshop_list .= '<li><strong>' . htmlspecialchars($ws['name']) . '</strong><br>';
                $workshop_list .= 'Alamat: ' . htmlspecialchars($ws['address']) . '<br>';
                $workshop_list .= 'Jarak: ' . number_format($ws['distance'], 1) . ' km<br>';
                $workshop_list .= 'Rating: ' . number_format($ws['rating_avg'], 1) . ' ⭐</li>';
            }
        } else {
            $workshop_list = '<li>Silakan cari bengkel terdekat melalui aplikasi kami.</li>';
        }

        // Prepare variables
        $variables = [
            'nama_pengguna' => $user_data['full_name'],
            'kendaraan' => $vehicle_data['vehicle_number'] . ' - ' . $vehicle_data['brand'] . ' ' . $vehicle_data['model'],
            'km_terakhir' => number_format($vehicle_data['last_service_km'] ?? 0),
            'km_estimasi' => number_format($vehicle_data['current_km'] ?? 0),
            'tanggal_servis' => $vehicle_data['last_service_date'] ? date('d/m/Y', strtotime($vehicle_data['last_service_date'])) : 'Tidak tercatat',
            'rekomendasi_bengkel' => '<ul>' . $workshop_list . '</ul>'
        ];

        // Replace variables
        $subject = $this->replace_variables($template['subject_template'], $variables);
        $body = $this->replace_variables($template['body_template'], $variables);

        // Send email
        return $this->send_email(
            $user_data['email'],
            $user_data['full_name'],
            'service_reminder',
            $subject,
            $body,
            ['vehicle_id' => $vehicle_data['id'], 'reminder_type' => 'periodic']
        );
    }

    /**
     * Send test notification to admin email
     * @param string $to_email
     * @param string $event_key
     * @return bool
     */
    public function send_test_notification($to_email, $event_key)
    {
        $template = $this->get_template_by_event($event_key);
        if (!$template) {
            return FALSE;
        }

        // Test variables
        $test_variables = [
            'nama_pengguna' => 'Admin Tester',
            'kode_booking' => 'B-' . date('Ymd') . '-TEST',
            'nama_bengkel' => 'Bengkel Test',
            'kendaraan' => 'B 1234 CD - Toyota Avanza',
            'km_terakhir' => '10,000',
            'km_estimasi' => '15,500',
            'tanggal_servis' => date('d/m/Y'),
            'rekomendasi_bengkel' => '<ul><li>Bengkel Test - Jakarta (0.5 km)</li></ul>'
        ];

        $subject = $this->replace_variables($template['subject_template'], $test_variables);
        $body = $this->replace_variables($template['body_template'], $test_variables);
        $body .= '<p><em>(Ini adalah email test notifikasi)</em></p>';

        return $this->send_email($to_email, 'Admin Tester', $event_key, $subject, $body, ['test' => TRUE]);
    }

    /**
     * Send email using PHPMailer
     * @param string $to_email
     * @param string $to_name
     * @param string $event_key
     * @param string $subject
     * @param string $body
     * @param array $metadata
     * @return bool
     */
    private function send_email($to_email, $to_name, $event_key, $subject, $body, $metadata = [])
    {
        // Log notification first
        $log_id = $this->log_notification([
            'recipient_email' => $to_email,
            'recipient_name' => $to_name,
            'event_key' => $event_key,
            'subject' => $subject,
            'body' => $body,
            'status' => 'pending',
            'metadata' => $metadata
        ]);

        try {
            $result = $this->ci_phpmailer->send($to_email, $subject, $body);
            
            // Update log
            $this->db->where('id', $log_id)->update($this->table_logs, [
                'status' => $result ? 'sent' : 'failed',
                'sent_at' => $result ? date('Y-m-d H:i:s') : NULL
            ]);

            return $result;
        } catch (Exception $e) {
            log_message('error', 'Email sending failed: ' . $e->getMessage());
            
            $this->db->where('id', $log_id)->update($this->table_logs, [
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            return FALSE;
        }
    }

    // ================================================================
    // NOTIFICATION LOGS (Inbox System)
    // ================================================================

    /**
     * Log notification
     * @param array $data
     * @return int
     */
    public function log_notification($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['is_deleted'] = 0;
        
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $data['metadata'] = json_encode($data['metadata']);
        }
        
        $this->db->insert($this->table_logs, $data);
        return $this->db->insert_id();
    }

    /**
     * Get notifications for user inbox
     * @param string $email
     * @param int $limit
     * @param int $offset
     * @param bool $unread_only
     * @return array
     */
    public function get_user_notifications($email, $limit = 20, $offset = 0, $unread_only = FALSE)
    {
        $this->db
            ->where('recipient_email', $email)
            ->where('is_deleted', 0);

        if ($unread_only) {
            $this->db->where('opened_at IS NULL', NULL, FALSE);
        }

        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get($this->table_logs)
            ->result_array();
    }

    /**
     * Count unread notifications for user
     * @param string $email
     * @return int
     */
    public function count_unread_notifications($email)
    {
        return $this->db
            ->where('recipient_email', $email)
            ->where('opened_at IS NULL', NULL, FALSE)
            ->where('is_deleted', 0)
            ->count_all_results($this->table_logs);
    }

    /**
     * Mark notification as read
     * @param int $log_id
     * @param string $email (for security check)
     * @return bool
     */
    public function mark_as_read($log_id, $email)
    {
        return $this->db
            ->where('id', $log_id)
            ->where('recipient_email', $email)
            ->update($this->table_logs, ['opened_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Mark all notifications as read for user
     * @param string $email
     * @return bool
     */
    public function mark_all_as_read($email)
    {
        return $this->db
            ->where('recipient_email', $email)
            ->where('opened_at IS NULL', NULL, FALSE)
            ->update($this->table_logs, ['opened_at' => date('Y-m-d H:i:s')]);
    }

    // ================================================================
    // REMINDER LOGIC (UC-USR-11, BR-73, BR-74, BR-75)
    // ================================================================

    /**
     * Get vehicles that need service reminder
     * Evaluates based on KM threshold or time since last service
     * 
     * @return array
     */
    public function get_vehicles_needing_reminder()
    {
        // Get system settings for thresholds (BR-75)
        $km_threshold = $this->get_setting('reminder_interval_km', 5000);
        $months_threshold = $this->get_setting('reminder_interval_months', 6);

        $today = date('Y-m-d');
        $six_months_ago = date('Y-m-d', strtotime("-{$months_threshold} months"));

        // Query vehicles with conditions:
        // 1. Has last_service_km recorded
        // 2. Not disabled for reminders (BR-74)
        // 3. Either KM threshold exceeded OR time threshold exceeded
        // 4. No reminder sent in last 7 days (BR-73)
        
        $this->db
            ->select('v.*, u.email, u.full_name, u.default_city')
            ->from('vehicles v')
            ->join('users u', 'v.user_id = u.id')
            ->where('v.is_deleted', 0)
            ->where('u.is_deleted', 0)
            ->where('v.reminder_enabled', 1) // BR-74: user can disable per vehicle
            ->group_start()
                ->where("v.last_service_km IS NOT NULL AND v.current_km > 0")
                ->where("(v.current_km - v.last_service_km) >= {$km_threshold}")
            ->group_end()
            ->or_group_start()
                ->where("v.last_service_date IS NOT NULL")
                ->where("v.last_service_date <= '{$six_months_ago}'")
            ->group_end();

        $vehicles = $this->db->get()->result_array();

        // Filter out vehicles that received reminder in last 7 days (BR-73)
        $filtered_vehicles = [];
        foreach ($vehicles as $vehicle) {
            if (!$this->has_recent_reminder($vehicle['id'], 7)) {
                $filtered_vehicles[] = $vehicle;
            }
        }

        return $filtered_vehicles;
    }

    /**
     * Check if vehicle has received reminder in last N days (BR-73)
     * @param int $vehicle_id
     * @param int $days
     * @return bool
     */
    private function has_recent_reminder($vehicle_id, $days = 7)
    {
        $cutoff_date = date('Y-m-d', strtotime("-{$days} days"));
        
        $this->db
            ->where('event_key', 'service_reminder')
            ->where('DATE(created_at) >=', $cutoff_date)
            ->where('JSON_EXTRACT(metadata, "$.vehicle_id")', $vehicle_id, FALSE);
        
        $count = $this->db->count_all_results($this->table_logs);
        
        return $count > 0;
    }

    /**
     * Get setting value from system_settings or config
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function get_setting($key, $default = NULL)
    {
        static $settings = [];

        if (empty($settings)) {
            // Try database first
            $db_settings = $this->db->get($this->table_system_settings)->result_array();
            foreach ($db_settings as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }

            // Fallback to config
            $this->config->load('app');
            $config_defaults = [
                'reminder_interval_km' => $this->config->item('default_reminder_interval_km'),
                'reminder_interval_months' => $this->config->item('default_reminder_interval_months')
            ];

            $settings = array_merge($config_defaults, $settings);
        }

        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    /**
     * Find nearest workshops for user
     * @param string $city
     * @param int $limit
     * @return array
     */
    public function find_nearest_workshops($city, $limit = 3)
    {
        $this->db
            ->select('id, name, address, city, latitude, longitude, rating_avg')
            ->from('workshops')
            ->where('status', 'active')
            ->where('is_deleted', 0);
        
        if (!empty($city)) {
            $this->db->where('city', $city);
        }
        
        $this->db
            ->order_by('rating_avg', 'DESC')
            ->limit($limit);
        
        $workshops = $this->db->get()->result_array();
        
        // Add distance placeholder (actual calculation would require user coordinates)
        foreach ($workshops as &$ws) {
            $ws['distance'] = rand(1, 10); // Placeholder
        }
        
        return $workshops;
    }

    /**
     * Snooze reminder for vehicle (UC-USR-11 Alternative Flow A2)
     * @param int $vehicle_id
     * @param int $days Default 30 days
     * @return bool
     */
    public function snooze_reminder($vehicle_id, $days = 30)
    {
        // Store snooze info in vehicle table
        $snooze_until = date('Y-m-d', strtotime("+{$days} days"));
        
        return $this->db
            ->where('id', $vehicle_id)
            ->update($this->table_vehicles, [
                'reminder_snoozed_until' => $snooze_until
            ]);
    }

    /**
     * Enable/disable reminder for vehicle (BR-74)
     * @param int $vehicle_id
     * @param bool $enabled
     * @return bool
     */
    public function set_reminder_enabled($vehicle_id, $enabled)
    {
        return $this->db
            ->where('id', $vehicle_id)
            ->update($this->table_vehicles, [
                'reminder_enabled' => $enabled ? 1 : 0
            ]);
    }

    /**
     * Calculate estimated current KM based on usage pattern
     * @param array $vehicle
     * @return int
     */
    public function estimate_current_km($vehicle)
    {
        if (empty($vehicle['last_service_km']) || empty($vehicle['last_service_date'])) {
            return $vehicle['current_km'] ?? 0;
        }

        // Simple estimation: assume average 50km/day for motorcycle, 30km/day for car
        $days_since_service = floor((strtotime(date('Y-m-d')) - strtotime($vehicle['last_service_date'])) / 86400);
        $avg_daily_km = ($vehicle['vehicle_type'] === 'motorcycle') ? 50 : 30;
        
        return $vehicle['last_service_km'] + ($days_since_service * $avg_daily_km);
    }
}

/* End of file Notification_model.php */
/* Location: ./application/models/Notification_model.php */
