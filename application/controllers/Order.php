<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Order Management Controller (Workshop Owner Side)
 * 
 * Handles booking management for workshop owners:
 * - Accept/Reject bookings (Pending → Accepted/Cancelled)
 * - Update status to Processed
 * - Add findings & request approval (UC-WRK-08)
 * - Complete bookings (Processed → Completed)
 * - Handle timeout scenarios (48 hours)
 * 
 * Implements SRS State Diagram v4.0:
 * Pending → Accepted → Processed → [waiting_approval] → Processed/Completed/Cancelled
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Controllers
 * @version     4.1
 */
class Order extends Workshop_Controller {

    /**
     * Booking model instance
     */
    private $booking_model;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('booking_model');
        $this->load->library('form_validation');
        $this->load->helper(['text', 'date']);
    }

    // ================================================================
    // DASHBOARD & LISTINGS
    // ================================================================

    /**
     * Dashboard - Overview of all orders
     */
    public function index()
    {
        $data['page_title'] = 'Manajemen Pesanan';
        
        // Get workshop ID for current owner
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if (!$workshop) {
            $this->session->set_flashdata('error', 'Anda harus membuat profil bengkel terlebih dahulu.');
            redirect('workshop/create');
        }
        
        $data['workshop'] = $workshop;
        
        // Get summary statistics
        $data['stats'] = $this->booking_model->get_workshop_booking_stats($workshop->id);
        
        // Get recent bookings
        $data['recent_bookings'] = $this->booking_model->get_workshop_bookings($workshop->id, ['limit' => 10]);
        
        $this->render('workshop/orders/index', $data);
    }

    /**
     * List all bookings for workshop with filtering
     */
    public function bookings()
    {
        $data['page_title'] = 'Daftar Pesanan';
        
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if (!$workshop) {
            $this->session->set_flashdata('error', 'Profil bengkel tidak ditemukan.');
            redirect('workshop/dashboard');
        }
        
        $data['workshop'] = $workshop;
        
        // Get filter parameters
        $filters = [
            'status' => $this->input->get('status', TRUE),
            'approval_status' => $this->input->get('approval', TRUE),
            'search' => $this->input->get('q', TRUE),
            'start_date' => $this->input->get('start_date', TRUE),
            'end_date' => $this->input->get('end_date', TRUE),
        ];
        
        $data['filters'] = $filters;
        $data['bookings'] = $this->booking_model->get_workshop_bookings($workshop->id, $filters);
        
        $this->render('workshop/orders/bookings', $data);
    }

    /**
     * View single booking detail
     * @param int $booking_id
     */
    public function detail($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking) {
            $this->session->set_flashdata('error', 'Pesanan tidak ditemukan.');
            redirect('order/bookings');
        }
        
        // Verify ownership
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if ($booking['workshop_id'] != $workshop->id) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('order/bookings');
        }
        
        $data['page_title'] = 'Detail Pesanan #' . $booking['booking_number'];
        $data['booking'] = $booking;
        $data['workshop'] = $workshop;
        
        // Get user info
        $this->load->model('user_model');
        $data['user'] = $this->user_model->find_by_id($booking['user_id']);
        
        // Get vehicle info if exists
        if (!empty($booking['vehicle_id'])) {
            $this->load->model('vehicle_model');
            $data['vehicle'] = $this->vehicle_model->find_by_id($booking['vehicle_id']);
        } else {
            $data['vehicle'] = NULL;
        }
        
        // Get approval history
        $data['approvals'] = $this->booking_model->get_booking_approvals($booking_id);
        
        // Get activity logs
        $data['activity_logs'] = $this->booking_model->get_booking_activity_logs($booking_id);
        
        // Check if can perform actions based on state
        $data['can_accept'] = $booking['status'] === 'pending';
        $data['can_process'] = $booking['status'] === 'accepted';
        $data['can_add_finding'] = in_array($booking['status'], ['accepted', 'in_progress']) && $booking['approval_status'] !== 'pending';
        $data['can_complete'] = $booking['status'] === 'in_progress' && $booking['approval_status'] !== 'pending';
        $data['can_cancel'] = in_array($booking['status'], ['pending', 'accepted']);
        
        // Check timeout for pending approvals
        $data['approval_timeout_expired'] = FALSE;
        if ($booking['status'] === 'in_progress' && $booking['approval_status'] === 'pending') {
            $latest_approval = reset($data['approvals']);
            if ($latest_approval && isset($latest_approval['expires_at'])) {
                $data['approval_timeout_expired'] = strtotime($latest_approval['expires_at']) < time();
            }
        }
        
        $this->render('workshop/orders/detail', $data);
    }

    // ================================================================
    // STATE TRANSITIONS - WORKSHOP OWNER ACTIONS
    // ================================================================

    /**
     * Accept booking (Pending → Accepted)
     * @param int $booking_id
     */
    public function accept($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking || $booking['status'] !== 'pending') {
            $this->json_error('Pesanan tidak dapat diterima (status: ' . $booking['status'] . ')', 400);
            return;
        }
        
        // Verify workshop ownership
        if (!$this->_verify_workshop_ownership($booking['workshop_id'])) {
            $this->json_error('Akses ditolak', 403);
            return;
        }
        
        // Update status
        $result = $this->booking_model->update_status($booking_id, 'accepted', $this->user_id);
        
        if ($result) {
            // Log activity
            $this->booking_model->log_activity($booking_id, 'accepted', 'Pesanan diterima oleh bengkel', $this->user_id);
            
            // Send notification to user
            $this->_send_notification($booking['user_id'], 'booking_accepted', [
                'booking_number' => $booking['booking_number'],
                'workshop_name' => $booking['workshop_name'] ?? 'Bengkel'
            ]);
            
            $this->json_response([
                'redirect' => site_url('order/detail/' . $booking_id)
            ], 200, 'Pesanan berhasil diterima');
        } else {
            $this->json_error('Gagal menerima pesanan', 500);
        }
    }

    /**
     * Reject/Cancel booking (Pending/Accepted → Cancelled)
     * @param int $booking_id
     */
    public function reject($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking || !in_array($booking['status'], ['pending', 'accepted'])) {
            $this->json_error('Pesanan tidak dapat ditolak (status: ' . $booking['status'] . ')', 400);
            return;
        }
        
        // Verify workshop ownership
        if (!$this->_verify_workshop_ownership($booking['workshop_id'])) {
            $this->json_error('Akses ditolak', 403);
            return;
        }
        
        $reason = $this->input->post('reason', TRUE);
        
        // Start transaction
        $this->db->trans_start();
        
        // Update status
        $result = $this->booking_model->update_status($booking_id, 'cancelled', $this->user_id, $reason);
        
        if ($result) {
            // Release slot if cancelled before scheduled date
            if (strtotime($booking['scheduled_date']) >= strtotime(date('Y-m-d'))) {
                $this->booking_model->release_booking_slot($booking);
            }
            
            // Log activity
            $this->booking_model->log_activity($booking_id, 'rejected', 'Pesanan ditolak: ' . $reason, $this->user_id);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() !== FALSE) {
                // Send notification
                $this->_send_notification($booking['user_id'], 'booking_rejected', [
                    'booking_number' => $booking['booking_number'],
                    'reason' => $reason
                ]);
                
                $this->json_response([
                    'redirect' => site_url('order/bookings')
                ], 200, 'Pesanan berhasil ditolak');
            }
        }
        
        $this->json_error('Gagal menolak pesanan', 500);
    }

    /**
     * Start processing (Accepted → In Progress)
     * @param int $booking_id
     */
    public function start_processing($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking || $booking['status'] !== 'accepted') {
            $this->json_error('Pesanan tidak dapat diproses (status: ' . $booking['status'] . ')', 400);
            return;
        }
        
        // Verify workshop ownership
        if (!$this->_verify_workshop_ownership($booking['workshop_id'])) {
            $this->json_error('Akses ditolak', 403);
            return;
        }
        
        // Update status
        $result = $this->booking_model->update_status($booking_id, 'in_progress', $this->user_id);
        
        if ($result) {
            // Log activity
            $this->booking_model->log_activity($booking_id, 'in_progress', 'Pekerjaan dimulai', $this->user_id);
            
            $this->json_response([
                'redirect' => site_url('order/detail/' . $booking_id)
            ], 200, 'Status diubah menjadi Sedang Dikerjakan');
        } else {
            $this->json_error('Gagal mengubah status', 500);
        }
    }

    /**
     * Add finding & request approval (UC-WRK-08)
     * Creates entry in booking_approvals, sets approval_status to 'pending'
     * @param int $booking_id
     */
    public function add_finding($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking || !in_array($booking['status'], ['accepted', 'in_progress'])) {
            $this->json_error('Tidak dapat menambah temuan pada pesanan dengan status ini', 400);
            return;
        }
        
        // Verify workshop ownership
        if (!$this->_verify_workshop_ownership($booking['workshop_id'])) {
            $this->json_error('Akses ditolak', 403);
            return;
        }
        
        // Check if there's already pending approval
        if ($booking['approval_status'] === 'pending') {
            $this->json_error('Masih ada permintaan approval yang menunggu respons user', 400);
            return;
        }
        
        // Validate input
        $this->form_validation->set_rules('description', 'Deskripsi Temuan', 'required|trim|max_length[500]');
        $this->form_validation->set_rules('additional_amount', 'Estimasi Biaya Tambahan', 'required|numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('spareparts', 'Sparepart', 'trim|max_length[500]');
        
        if ($this->form_validation->run() === FALSE) {
            $this->json_error(validation_errors(), 400);
            return;
        }
        
        $description = $this->input->post('description', TRUE);
        $additional_amount = $this->input->post('additional_amount', TRUE);
        $spareparts = $this->input->post('spareparts', TRUE);
        
        // Create approval request
        $approval_data = [
            'booking_id' => $booking_id,
            'requested_by' => $this->user_id,
            'description' => $description,
            'additional_amount' => $additional_amount,
            'spareparts' => $spareparts,
            'status' => 'pending',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+48 hours')), // BR-80: 48 hour timeout
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $approval_id = $this->booking_model->create_approval($approval_data);
        
        if ($approval_id) {
            // Update booking approval_status
            $this->booking_model->update_approval_status($booking_id, 'pending');
            
            // Log activity
            $this->booking_model->log_activity($booking_id, 'approval_requested', 
                'Permintaan approval tambahan: ' . $description . ' (Rp ' . number_format($additional_amount) . ')', 
                $this->user_id);
            
            // Send notification to user
            $this->_send_notification($booking['user_id'], 'approval_requested', [
                'booking_number' => $booking['booking_number'],
                'description' => $description,
                'amount' => $additional_amount,
                'expires_at' => $approval_data['expires_at']
            ]);
            
            $this->json_response([
                'redirect' => site_url('order/detail/' . $booking_id)
            ], 200, 'Permintaan approval berhasil dikirim ke pelanggan');
        } else {
            $this->json_error('Gagal mengirim permintaan approval', 500);
        }
    }

    /**
     * Complete booking (In Progress → Completed)
     * @param int $booking_id
     */
    public function complete($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking || $booking['status'] !== 'in_progress') {
            $this->json_error('Pesanan belum dapat diselesaikan (status: ' . $booking['status'] . ')', 400);
            return;
        }
        
        // Verify workshop ownership
        if (!$this->_verify_workshop_ownership($booking['workshop_id'])) {
            $this->json_error('Akses ditolak', 403);
            return;
        }
        
        // Check if there's pending approval - must be resolved first
        if ($booking['approval_status'] === 'pending') {
            $this->json_error('Masih ada approval yang menunggu respons pelanggan', 400);
            return;
        }
        
        // Get final cost from input
        $final_cost = $this->input->post('final_cost', TRUE);
        $notes = $this->input->post('notes', TRUE);
        
        // Calculate total (original + approved additions)
        $approved_total = $booking['estimated_price'];
        $approvals = $this->booking_model->get_booking_approvals($booking_id, 'approved');
        foreach ($approvals as $approval) {
            $approved_total += $approval['additional_amount'];
        }
        
        // Start transaction
        $this->db->trans_start();
        
        // Update booking
        $update_data = [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'final_cost' => $final_cost ?: $approved_total,
            'additional_notes' => $notes
        ];
        
        $result = $this->db->where('id', $booking_id)->update('bookings', $update_data);
        
        if ($result) {
            // Log activity
            $this->booking_model->log_activity($booking_id, 'completed', 'Pesanan selesai', $this->user_id);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() !== FALSE) {
                // Send notification
                $this->_send_notification($booking['user_id'], 'booking_completed', [
                    'booking_number' => $booking['booking_number'],
                    'final_cost' => $update_data['final_cost']
                ]);
                
                $this->json_response([
                    'redirect' => site_url('order/detail/' . $booking_id)
                ], 200, 'Pesanan berhasil diselesaikan');
            }
        }
        
        $this->json_error('Gagal menyelesaikan pesanan', 500);
    }

    /**
     * Handle approval timeout - Continue or Cancel additional work
     * Called when user doesn't respond within 48 hours
     * @param int $booking_id
     * @param string $action 'continue' or 'cancel_additional'
     */
    public function handle_timeout($booking_id, $action = 'continue')
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking || $booking['status'] !== 'in_progress' || $booking['approval_status'] !== 'pending') {
            $this->json_error('Tidak ada approval yang menunggu timeout', 400);
            return;
        }
        
        // Verify workshop ownership
        if (!$this->_verify_workshop_ownership($booking['workshop_id'])) {
            $this->json_error('Akses ditolak', 403);
            return;
        }
        
        // Check if timeout has actually expired
        $approvals = $this->booking_model->get_booking_approvals($booking_id, 'pending');
        $latest_approval = reset($approvals);
        
        if (!$latest_approval || strtotime($latest_approval['expires_at']) > time()) {
            $this->json_error('Belum melewati batas waktu 48 jam', 400);
            return;
        }
        
        if ($action === 'continue') {
            // Auto-approve and continue with original scope only
            $this->booking_model->update_approval_status($booking_id, 'rejected');
            $this->booking_model->log_activity($booking_id, 'approval_timeout', 
                'Approval timeout - melanjutkan pekerjaan awal saja', $this->user_id);
            
            $this->json_response([], 200, 'Melanjutkan pekerjaan awal (tanpa tambahan)');
            
        } elseif ($action === 'cancel_additional') {
            // Mark approval as rejected/cancelled
            $this->booking_model->update_approval($latest_approval['id'], [
                'status' => 'rejected',
                'response_note' => 'Timeout - User tidak merespons dalam 48 jam',
                'responded_at' => date('Y-m-d H:i:s')
            ]);
            $this->booking_model->update_approval_status($booking_id, 'none');
            
            $this->booking_model->log_activity($booking_id, 'approval_cancelled', 
                'Temuan tambahan dibatalkan karena timeout', $this->user_id);
            
            $this->json_response([], 200, 'Temuan tambahan dibatalkan');
        }
        
        $this->json_error('Aksi tidak valid', 400);
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Verify workshop ownership
     * @param int $workshop_id
     * @return bool
     */
    private function _verify_workshop_ownership($workshop_id)
    {
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        return $workshop && $workshop->id == $workshop_id;
    }

    /**
     * Send notification to user
     * @param int $user_id
     * @param string $event_key
     * @param array $data
     */
    private function _send_notification($user_id, $event_key, $data = [])
    {
        // This would integrate with notification system
        // For now, just log it
        log_message('info', "Notification {$event_key} to user {$user_id}: " . json_encode($data));
    }
}

/* End of file Order.php */
/* Location: ./application/controllers/Order.php */
