<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Emergency Controller (Public/User Side)
 * 
 * Handles emergency roadside assistance requests:
 * - FR-EMG-01: Emergency button accessible to all users
 * - FR-EMG-02: Emergency request form with GPS location
 * - FR-EMG-03: Track emergency request status
 * - UC-USR-10: Request emergency assistance
 * - BR-70: 1 active request per user
 * - BR-71: Auto-close after 2 hours
 * - BR-72: No upfront payment required
 * - Reviewer #5: Rate limiting (3 requests/hour per IP)
 * 
 * @package     Bengkel Terdekat
 * @version     4.1
 */
class Emergency extends MY_Controller {
    
    /**
     * Emergency model instance
     */
    private $emergency_model;
    
    /**
     * Default search radius in km (from system_settings)
     */
    private $default_radius = 5.0;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('emergency_model');
        $this->load->model('vehicle_model');
        $this->load->library('form_validation');
        $this->load->helper(['form', 'url']);
        
        // Load default radius from settings
        $this->default_radius = floatval($this->get_setting('emergency_radius_km', 5.0));
    }
    
    // ================================================================
    // PUBLIC EMERGENCY PAGE (Accessible without login)
    // ================================================================
    
    /**
     * Emergency request page - main entry point
     * Shows the emergency button and form
     * Accessible by guests and logged-in users
     */
    public function index()
    {
        $data['page_title'] = 'Layanan Darurat';
        $data['user'] = $this->current_user;
        
        // Pre-fill user data if logged in
        if ($this->is_logged_in()) {
            $data['user_name'] = $this->current_user->full_name;
            $data['user_phone'] = $this->current_user->phone;
            $data['user_email'] = $this->current_user->email;
            
            // Get user's vehicles
            $data['vehicles'] = $this->vehicle_model->get_user_vehicles($this->user_id);
            
            // Check if user has active request (BR-70)
            $data['has_active_request'] = $this->emergency_model->has_active_request($this->user_id);
            if ($data['has_active_request']) {
                $data['active_requests'] = $this->emergency_model->get_user_active_requests($this->user_id);
            }
        } else {
            $data['user_name'] = '';
            $data['user_phone'] = '';
            $data['user_email'] = '';
            $data['vehicles'] = [];
            $data['has_active_request'] = FALSE;
        }
        
        // Emergency types dropdown
        $data['emergency_types'] = [
            'flat_tire' => 'Ban Bocor',
            'breakdown' => 'Mesin Mati/Rusak',
            'accident' => 'Kecelakaan',
            'battery' => 'Aki Habis/Rusak',
            'fuel' => 'Kehabisan Bensin',
            'lockout' => 'Kunci Tertinggal',
            'other' => 'Lainnya'
        ];
        
        $this->load->view('emergency/emergency_form', $data);
    }
    
    // ================================================================
    // CREATE EMERGENCY REQUEST
    // ================================================================
    
    /**
     * Submit emergency request
     * Validates, creates request, notifies nearby workshops
     */
    public function create()
    {
        // Only accept POST
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_error('Method not allowed', 405);
        }
        
        // Rate limiting check (Reviewer #5)
        $ip_address = $this->input->ip_address();
        $rate_limit = $this->emergency_model->check_rate_limit($ip_address);
        
        if (!$rate_limit['allowed']) {
            $this->json_error(
                'Terlalu banyak permintaan darurat. Maksimal 3 permintaan per jam.',
                429,
                ['reset_time' => $rate_limit['reset_time']]
            );
            return;
        }
        
        // Validation rules
        $this->form_validation->set_rules('latitude', 'Lokasi Latitude', 'required|trim');
        $this->form_validation->set_rules('longitude', 'Lokasi Longitude', 'required|trim');
        $this->form_validation->set_rules('emergency_type', 'Jenis Darurat', 'required|trim');
        $this->form_validation->set_rules('description', 'Deskripsi', 'required|trim|max_length[500]');
        
        if (!$this->form_validation->run()) {
            $this->json_error(validation_errors(), 400);
            return;
        }
        
        // Get input data
        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');
        $emergency_type = $this->input->post('emergency_type');
        $description = $this->input->post('description');
        $location_address = $this->input->post('location_address', TRUE);
        $vehicle_id = $this->input->post('vehicle_id', TRUE);
        
        // User ID - use logged-in user or create guest session
        if ($this->is_logged_in()) {
            $user_id = $this->user_id;
            
            // Check BR-70: 1 active request per user
            if ($this->emergency_model->has_active_request($user_id)) {
                $this->json_error('Anda sudah memiliki permintaan darurat yang aktif.', 400);
                return;
            }
        } else {
            // For guests, we need to create a temporary user or use session-based tracking
            // For simplicity, we'll require login for emergency requests
            $this->json_error('Silakan login untuk membuat permintaan darurat.', 401);
            return;
        }
        
        // Find nearby workshops within radius
        $nearby_workshops = $this->emergency_model->find_nearby_workshops(
            $latitude, 
            $longitude, 
            $this->default_radius
        );
        
        if (empty($nearby_workshops)) {
            $this->json_error(
                'Tidak ada bengkel tersedia dalam radius ' . $this->default_radius . ' km. Coba perbesar area atau hubungi layanan darurat lokal.',
                404
            );
            return;
        }
        
        // Create emergency request
        $request_data = [
            'user_id' => $user_id,
            'vehicle_id' => !empty($vehicle_id) ? $vehicle_id : NULL,
            'emergency_type' => $emergency_type,
            'description' => $description,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location_address' => $location_address
        ];
        
        $result = $this->emergency_model->create_request($request_data);
        
        if (!$result['success']) {
            $this->json_error($result['message'], 500);
            return;
        }
        
        $request_id = $result['request_id'];
        $request_number = $result['request_number'];
        
        // Record IP for rate limiting
        $this->emergency_model->record_ip($request_id, $ip_address);
        
        // Send notifications to nearby workshops
        $notified_workshops = $this->notify_workshops($request_id, $request_number, $nearby_workshops);
        
        // Return success with workshop list
        $workshop_list = [];
        foreach ($notified_workshops as $workshop) {
            $workshop_list[] = [
                'id' => $workshop['id'],
                'name' => $workshop['name'],
                'phone' => $workshop['phone'] ?? $workshop['owner_phone'],
                'distance' => $workshop['distance_km'],
                'status' => 'Menunggu respons'
            ];
        }
        
        $this->json_response([
            'request_id' => $request_id,
            'request_number' => $request_number,
            'message' => 'Permintaan darurat berhasil dibuat. Bengkel terdekat telah dihubungi.',
            'workshops' => $workshop_list,
            'total_notified' => count($workshop_list)
        ], 201);
    }
    
    /**
     * Notify workshops about emergency request via email
     * Uses PHPMailer and notification_templates
     * 
     * @param int $request_id Request ID
     * @param string $request_number Request number
     * @param array $workshops Array of workshop data
     * @return array Notified workshops
     */
    private function notify_workshops($request_id, $request_number, $workshops)
    {
        $this->load->library('CI_PHPMailer');
        $this->load->model('notification_template_model');
        
        // Get email template
        $template = $this->db->get_where('notification_templates', [
            'event_key' => 'emergency_request_new',
            'is_active' => 1
        ])->row_array();
        
        if (empty($template)) {
            // Fallback template
            $subject = 'Permintaan Darurat Baru - {request_number}';
            $body_template = 'Ada permintaan darurat baru di area Anda.';
        } else {
            $subject = $template['subject_template'];
            $body_template = $template['body_template'];
        }
        
        $notified = [];
        
        foreach ($workshops as $workshop) {
            if (empty($workshop['email'])) {
                continue;
            }
            
            // Replace template variables
            $email_subject = str_replace(
                ['{request_number}', '{workshop_name}', '{emergency_type}'],
                [$request_number, $workshop['name'], $request_id],
                $subject
            );
            
            $email_body = str_replace(
                ['{request_number}', '{workshop_name}', '{emergency_type}', '{distance}'],
                [$request_number, $workshop['name'], $request_id, $workshop['distance_km']],
                $body_template
            );
            
            try {
                $mailer = new CI_PHPMailer();
                $mailer->send(
                    $workshop['email'],
                    $email_subject,
                    $email_body
                );
                
                $notified[] = $workshop;
                
            } catch (Exception $e) {
                log_message('error', 'Failed to send emergency notification to ' . $workshop['email'] . ': ' . $e->getMessage());
                // Continue notifying other workshops even if one fails
                $notified[] = $workshop;
            }
        }
        
        return $notified;
    }
    
    // ================================================================
    // TRACK REQUEST STATUS
    // ================================================================
    
    /**
     * View emergency request status
     * Shows current status and responding workshop
     * 
     * @param string $request_number
     */
    public function track($request_number = NULL)
    {
        $data['page_title'] = 'Lacak Permintaan Darurat';
        
        if (!$request_number) {
            // Show form to enter request number
            $this->load->view('emergency/track_form', $data);
            return;
        }
        
        $request = $this->emergency_model->get_request_by_number($request_number);
        
        if (!$request) {
            $this->session->set_flashdata('error', 'Permintaan darurat tidak ditemukan.');
            redirect('emergency/track');
            return;
        }
        
        // Check authorization - only owner can view
        if ($this->is_logged_in() && $request['user_id'] != $this->user_id) {
            show_error('Anda tidak memiliki akses untuk melihat permintaan ini.', 403);
            return;
        }
        
        $data['request'] = $request;
        
        // Get assigned workshop info if any
        if ($request['assigned_workshop_id']) {
            $this->load->model('workshop_model');
            $data['workshop'] = $this->workshop_model->find_by_id($request['assigned_workshop_id']);
        } else {
            $data['workshop'] = NULL;
        }
        
        $this->load->view('emergency/track_detail', $data);
    }
    
    /**
     * AJAX: Get request status update
     * 
     * @param int $request_id
     */
    public function get_status($request_id)
    {
        $request = $this->emergency_model->get_request_by_id($request_id);
        
        if (!$request) {
            $this->json_error('Permintaan tidak ditemukan.', 404);
            return;
        }
        
        // Check authorization
        if ($this->is_logged_in() && $request['user_id'] != $this->user_id) {
            $this->json_error('Akses ditolak.', 403);
            return;
        }
        
        $this->json_response([
            'status' => $request['status'],
            'assigned_workshop' => $request['assigned_workshop_id'],
            'created_at' => $request['created_at'],
            'accepted_at' => $request['accepted_at']
        ]);
    }
    
    // ================================================================
    // CANCEL REQUEST
    // ================================================================
    
    /**
     * Cancel emergency request
     * 
     * @param int $request_id
     */
    public function cancel($request_id)
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_error('Method not allowed', 405);
        }
        
        $request = $this->emergency_model->get_request_by_id($request_id);
        
        if (!$request) {
            $this->json_error('Permintaan tidak ditemukan.', 404);
            return;
        }
        
        // Check authorization
        if ($this->is_logged_in() && $request['user_id'] != $this->user_id) {
            $this->json_error('Akses ditolak.', 403);
            return;
        }
        
        // Can only cancel pending or assigned requests
        if (!in_array($request['status'], ['pending', 'assigned'])) {
            $this->json_error('Permintaan tidak dapat dibatalkan pada status ini.', 400);
            return;
        }
        
        $reason = $this->input->post('reason', TRUE);
        
        $this->emergency_model->cancel_request($request_id, $reason);
        
        $this->json_response([
            'message' => 'Permintaan darurat berhasil dibatalkan.'
        ]);
    }
}

/* End of file Emergency.php */
/* Location: ./application/controllers/Emergency.php */
