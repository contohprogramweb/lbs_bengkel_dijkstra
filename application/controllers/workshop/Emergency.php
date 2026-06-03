<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Workshop Emergency Controller
 * 
 * Handles workshop-side emergency request management:
 * - View incoming emergency requests
 * - Accept/Decline emergency requests
 * - Track assigned emergency jobs
 * 
 * @package     Bengkel Terdekat
 * @version     4.1
 */
class Emergency extends Workshop_Controller {
    
    /**
     * Emergency model instance
     */
    private $emergency_model;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('emergency_model');
        $this->load->model('workshop_model');
        $this->load->library('form_validation');
    }
    
    // ================================================================
    // WORKSHOP DASHBOARD - EMERGENCY NOTIFICATIONS
    // ================================================================
    
    /**
     * Dashboard showing pending emergency requests
     */
    public function index()
    {
        $data['page_title'] = 'Permintaan Darurat';
        $data['user'] = $this->current_user;
        
        // Get workshop data
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if (!$workshop) {
            $this->session->set_flashdata('error', 'Workshop tidak ditemukan.');
            redirect('workshop/dashboard');
            return;
        }
        
        $data['workshop'] = $workshop;
        $data['workshop_id'] = $workshop->id;
        
        // Get all pending emergency requests (not yet assigned to any workshop)
        $data['pending_requests'] = $this->emergency_model->get_pending_requests_for_workshop($workshop->id);
        
        // Get accepted/assigned requests for this workshop
        $this->db->select('e.*, u.full_name as user_name, u.phone as user_phone');
        $this->db->from('emergency_requests e');
        $this->db->join('users u', 'e.user_id = u.id', 'left');
        $this->db->where('e.assigned_workshop_id', $workshop->id);
        $this->db->where_in('e.status', ['assigned', 'in_progress']);
        $this->db->order_by('e.accepted_at', 'DESC');
        $data['accepted_requests'] = $this->db->get()->result_array();
        
        // Statistics
        $data['stats'] = $this->emergency_model->get_workshop_statistics($workshop->id);
        
        $this->load->view('workshop/emergency/dashboard', $data);
    }
    
    // ================================================================
    // ACCEPT/DECLINE EMERGENCY REQUEST
    // ================================================================
    
    /**
     * Accept emergency request
     * First workshop to accept becomes the responder
     * 
     * @param int $request_id
     */
    public function accept($request_id)
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_error('Method not allowed', 405);
        }
        
        $request = $this->emergency_model->get_request_by_id($request_id);
        
        if (!$request) {
            $this->json_error('Permintaan tidak ditemukan.', 404);
            return;
        }
        
        // Check if already assigned
        if ($request['status'] !== 'pending') {
            $this->json_error('Permintaan sudah ditangani oleh bengkel lain.', 400);
            return;
        }
        
        // Get workshop
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if (!$workshop) {
            $this->json_error('Workshop tidak ditemukan.', 404);
            return;
        }
        
        // Assign workshop to request
        $this->emergency_model->assign_workshop($request_id, $workshop->id);
        
        // Send notification to user (optional - could be SMS/email)
        $this->notify_user_acceptance($request, $workshop);
        
        $this->json_response([
            'success' => TRUE,
            'message' => 'Permintaan darurat berhasil diterima. Anda sekarang menjadi responden aktif.'
        ]);
    }
    
    /**
     * Decline emergency request
     * 
     * @param int $request_id
     */
    public function decline($request_id)
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_error('Method not allowed', 405);
        }
        
        $request = $this->emergency_model->get_request_by_id($request_id);
        
        if (!$request) {
            $this->json_error('Permintaan tidak ditemukan.', 404);
            return;
        }
        
        // For now, we just log the decline
        // In future, could track which workshops declined
        
        $this->json_response([
            'success' => TRUE,
            'message' => 'Permintaan darurat ditolak.'
        ]);
    }
    
    /**
     * Notify user that workshop accepted their request
     * 
     * @param array $request
     * @param object $workshop
     */
    private function notify_user_acceptance($request, $workshop)
    {
        $this->load->library('CI_PHPMailer');
        
        // Get user email
        $this->db->select('email, full_name');
        $this->db->from('users');
        $this->db->where('id', $request['user_id']);
        $user = $this->db->get()->row_array();
        
        if (!$user || empty($user['email'])) {
            return;
        }
        
        $subject = 'Permintaan Darurat Diterima - ' . $request['request_number'];
        
        $body = "
        <h2>Permintaan Darurat Diterima</h2>
        <p>Yth. {$user['full_name']},</p>
        <p>Permintaan darurat Anda telah diterima oleh:</p>
        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
            <strong>{$workshop->name}</strong><br>
            Telepon: {$workshop->phone}<br>
            Alamat: {$workshop->address}
        </div>
        <p>Mekanik akan segera menghubungi Anda untuk konfirmasi lokasi dan estimasi waktu kedatangan.</p>
        <p><strong>Nomor Permintaan:</strong> {$request['request_number']}</p>
        <p>Terima kasih telah menggunakan layanan darurat kami.</p>
        ";
        
        try {
            $mailer = new CI_PHPMailer();
            $mailer->send($user['email'], $subject, $body);
        } catch (Exception $e) {
            log_message('error', 'Failed to send acceptance notification: ' . $e->getMessage());
        }
    }
    
    // ================================================================
    // UPDATE REQUEST STATUS
    // ================================================================
    
    /**
     * Update emergency request status (e.g., in_progress, completed)
     * 
     * @param int $request_id
     */
    public function update_status($request_id)
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_error('Method not allowed', 405);
        }
        
        $request = $this->emergency_model->get_request_by_id($request_id);
        
        if (!$request) {
            $this->json_error('Permintaan tidak ditemukan.', 404);
            return;
        }
        
        // Verify this workshop is assigned
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if ($request['assigned_workshop_id'] != $workshop->id) {
            $this->json_error('Anda tidak memiliki akses untuk permintaan ini.', 403);
            return;
        }
        
        $status = $this->input->post('status');
        $notes = $this->input->post('notes', TRUE);
        
        if (!in_array($status, ['in_progress', 'completed', 'cancelled'])) {
            $this->json_error('Status tidak valid.', 400);
            return;
        }
        
        $update_data = [];
        
        if ($status === 'in_progress') {
            $update_data['arrived_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'completed') {
            $update_data['completed_at'] = date('Y-m-d H:i:s');
            $service_cost = $this->input->post('service_cost', TRUE);
            if ($service_cost) {
                $update_data['service_cost'] = floatval($service_cost);
            }
        } elseif ($status === 'cancelled') {
            $update_data['cancelled_at'] = date('Y-m-d H:i:s');
            $update_data['cancellation_reason'] = $notes;
        }
        
        if ($notes && $status !== 'cancelled') {
            $update_data['notes'] = $notes;
        }
        
        $this->emergency_model->update_status($request_id, $status, $update_data);
        
        $this->json_response([
            'success' => TRUE,
            'message' => 'Status berhasil diperbarui.'
        ]);
    }
    
    // ================================================================
    // VIEW REQUEST DETAILS
    // ================================================================
    
    /**
     * View detailed emergency request information
     * 
     * @param int $request_id
     */
    public function view($request_id)
    {
        $data['page_title'] = 'Detail Permintaan Darurat';
        
        $request = $this->emergency_model->get_request_by_id($request_id);
        
        if (!$request) {
            $this->session->set_flashdata('error', 'Permintaan tidak ditemukan.');
            redirect('workshop/emergency');
            return;
        }
        
        // Verify access
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        // Allow viewing if assigned to this workshop or still pending
        if ($request['assigned_workshop_id'] && $request['assigned_workshop_id'] != $workshop->id) {
            show_error('Anda tidak memiliki akses untuk melihat permintaan ini.', 403);
            return;
        }
        
        $data['request'] = $request;
        $data['workshop'] = $workshop;
        
        $this->load->view('workshop/emergency/view_detail', $data);
    }
}

/* End of file Emergency.php */
/* Location: ./application/controllers/workshop/Emergency.php */
