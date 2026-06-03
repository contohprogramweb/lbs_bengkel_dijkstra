<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Booking Management Controller (User/Customer Side)
 * 
 * Handles booking management for customers:
 * - View booking list and details with status tracking
 * - Respond to approval requests (Approve/Reject)
 * - Handle timeout scenarios
 * - Cancel bookings (when allowed)
 * 
 * Implements SRS State Diagram v4.0:
 * User can view status transitions and respond to approvals
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Controllers
 * @version     4.1
 */
class Booking_management extends Customer_Controller {

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
    // BOOKING LIST & DASHBOARD
    // ================================================================

    /**
     * Dashboard - Overview of user's bookings
     */
    public function index()
    {
        $data['page_title'] = 'Pesanan Saya';
        
        // Get summary statistics
        $data['stats'] = $this->booking_model->get_user_booking_stats($this->user_id);
        
        // Get recent bookings
        $data['recent_bookings'] = $this->booking_model->get_user_bookings($this->user_id, ['limit' => 10]);
        
        // Check for pending approvals that need attention
        $data['pending_approvals'] = $this->booking_model->get_user_pending_approvals($this->user_id);
        
        $this->render('user/bookings/index', $data);
    }

    /**
     * List all user bookings with filtering
     */
    public function bookings()
    {
        $data['page_title'] = 'Riwayat Pesanan';
        
        // Get filter parameters
        $filters = [
            'status' => $this->input->get('status', TRUE),
            'approval_status' => $this->input->get('approval', TRUE),
            'search' => $this->input->get('q', TRUE),
            'start_date' => $this->input->get('start_date', TRUE),
            'end_date' => $this->input->get('end_date', TRUE),
            'year' => $this->input->get('year', TRUE) ?: date('Y'),
            'month' => $this->input->get('month', TRUE) ?: date('m'),
        ];
        
        $data['filters'] = $filters;
        $data['bookings'] = $this->booking_model->get_user_bookings($this->user_id, $filters);
        
        $this->render('user/bookings/bookings', $data);
    }

    /**
     * View single booking detail with full status tracking
     * @param int $booking_id
     */
    public function detail($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking) {
            $this->session->set_flashdata('error', 'Pesanan tidak ditemukan.');
            redirect('booking_management/bookings');
        }
        
        // Verify ownership
        if ($booking['user_id'] != $this->user_id) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('booking_management/bookings');
        }
        
        $data['page_title'] = 'Detail Pesanan #' . $booking['booking_number'];
        $data['booking'] = $booking;
        
        // Get workshop info
        $this->load->model('workshop_model');
        $data['workshop'] = $this->workshop_model->find_by_id($booking['workshop_id']);
        
        // Get vehicle info if exists
        if (!empty($booking['vehicle_id'])) {
            $this->load->model('vehicle_model');
            $data['vehicle'] = $this->vehicle_model->find_by_id($booking['vehicle_id']);
        } else {
            $data['vehicle'] = NULL;
        }
        
        // Get approval history and current pending approval
        $data['approvals'] = $this->booking_model->get_booking_approvals($booking_id);
        $data['pending_approval'] = NULL;
        foreach ($data['approvals'] as $approval) {
            if ($approval['status'] === 'pending') {
                $data['pending_approval'] = $approval;
                break;
            }
        }
        
        // Get activity logs for status tracking timeline
        $data['activity_logs'] = $this->booking_model->get_booking_activity_logs($booking_id);
        
        // Calculate total cost including approved additions
        $data['total_cost'] = $booking['estimated_price'];
        foreach ($data['approvals'] as $approval) {
            if ($approval['status'] === 'approved') {
                $data['total_cost'] += $approval['additional_amount'];
            }
        }
        
        // Check if approval is close to timeout (within 6 hours)
        $data['approval_urgent'] = FALSE;
        if ($data['pending_approval']) {
            $expires_at = strtotime($data['pending_approval']['expires_at']);
            $now = time();
            $hours_remaining = ($expires_at - $now) / 3600;
            $data['approval_hours_remaining'] = max(0, floor($hours_remaining));
            $data['approval_urgent'] = $hours_remaining <= 6 && $hours_remaining > 0;
            $data['approval_expired'] = $hours_remaining <= 0;
        } else {
            $data['approval_hours_remaining'] = NULL;
            $data['approval_urgent'] = FALSE;
            $data['approval_expired'] = FALSE;
        }
        
        // Determine available actions based on state
        $data['can_cancel'] = in_array($booking['status'], ['pending', 'accepted']);
        $data['needs_approval_response'] = $booking['approval_status'] === 'pending' && $data['pending_approval'];
        $data['can_reschedule'] = $booking['status'] === 'pending';
        $data['can_review'] = $booking['status'] === 'completed' && !$this->_has_submitted_review($booking_id);
        
        $this->render('user/bookings/detail', $data);
    }

    // ================================================================
    // APPROVAL RESPONSE ACTIONS
    // ================================================================

    /**
     * Approve additional cost/finding (approval_status: pending → approved)
     * Updates total cost and allows workshop to continue
     * @param int $booking_id
     */
    public function approve_additional($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking || $booking['user_id'] != $this->user_id) {
            $this->json_error('Pesanan tidak ditemukan', 404);
            return;
        }
        
        if ($booking['approval_status'] !== 'pending') {
            $this->json_error('Tidak ada permintaan approval yang menunggu', 400);
            return;
        }
        
        // Get pending approval
        $pending_approvals = $this->booking_model->get_booking_approvals($booking_id, 'pending');
        $approval = reset($pending_approvals);
        
        if (!$approval) {
            $this->json_error('Approval tidak ditemukan', 404);
            return;
        }
        
        // Start transaction
        $this->db->trans_start();
        
        // Update approval record
        $update_result = $this->booking_model->update_approval($approval['id'], [
            'status' => 'approved',
            'responded_at' => date('Y-m-d H:i:s'),
            'response_note' => 'Disetujui oleh pelanggan'
        ]);
        
        if ($update_result) {
            // Update booking approval_status
            $this->booking_model->update_approval_status($booking_id, 'approved');
            
            // Update booking total estimated price
            $new_total = $booking['estimated_price'] + $approval['additional_amount'];
            $this->db->where('id', $booking_id)
                     ->update('bookings', ['estimated_price' => $new_total]);
            
            // Log activity
            $this->booking_model->log_activity($booking_id, 'approval_approved', 
                'Pelanggan menyetujui tambahan: ' . $approval['description'] . 
                ' (Rp ' . number_format($approval['additional_amount']) . ')', 
                $this->user_id);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() !== FALSE) {
                // Send notification to workshop owner
                $this->_send_notification_to_workshop($booking['workshop_id'], 'approval_approved', [
                    'booking_number' => $booking['booking_number'],
                    'approval_id' => $approval['id']
                ]);
                
                $this->json_response([
                    'redirect' => site_url('booking_management/detail/' . $booking_id)
                ], 200, 'Approval berhasil disetujui. Bengkel dapat melanjutkan pekerjaan.');
            }
        }
        
        $this->json_error('Gagal memproses approval', 500);
    }

    /**
     * Reject additional cost/finding (approval_status: pending → rejected)
     * Workshop continues with original scope only
     * @param int $booking_id
     */
    public function reject_additional($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking || $booking['user_id'] != $this->user_id) {
            $this->json_error('Pesanan tidak ditemukan', 404);
            return;
        }
        
        if ($booking['approval_status'] !== 'pending') {
            $this->json_error('Tidak ada permintaan approval yang menunggu', 400);
            return;
        }
        
        // Get pending approval
        $pending_approvals = $this->booking_model->get_booking_approvals($booking_id, 'pending');
        $approval = reset($pending_approvals);
        
        if (!$approval) {
            $this->json_error('Approval tidak ditemukan', 404);
            return;
        }
        
        $reason = $this->input->post('reason', TRUE);
        
        // Start transaction
        $this->db->trans_start();
        
        // Update approval record
        $update_result = $this->booking_model->update_approval($approval['id'], [
            'status' => 'rejected',
            'responded_at' => date('Y-m-d H:i:s'),
            'response_note' => $reason ?: 'Ditolak oleh pelanggan'
        ]);
        
        if ($update_result) {
            // Update booking approval_status to 'none' (continue with original work)
            $this->booking_model->update_approval_status($booking_id, 'none');
            
            // Log activity
            $this->booking_model->log_activity($booking_id, 'approval_rejected', 
                'Pelanggan menolak tambahan: ' . $approval['description'] . 
                ' - Alasan: ' . ($reason ?: 'Tidak disebutkan'), 
                $this->user_id);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() !== FALSE) {
                // Send notification to workshop owner
                $this->_send_notification_to_workshop($booking['workshop_id'], 'approval_rejected', [
                    'booking_number' => $booking['booking_number'],
                    'approval_id' => $approval['id'],
                    'reason' => $reason
                ]);
                
                $this->json_response([
                    'redirect' => site_url('booking_management/detail/' . $booking_id)
                ], 200, 'Approval ditolak. Bengkel akan melanjutkan pekerjaan awal saja.');
            }
        }
        
        $this->json_error('Gagal memproses approval', 500);
    }

    // ================================================================
    // BOOKING MANAGEMENT ACTIONS
    // ================================================================

    /**
     * Cancel booking (when allowed by business rules)
     * Only allowed for status: pending, accepted
     * @param int $booking_id
     */
    public function cancel($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking || $booking['user_id'] != $this->user_id) {
            $this->json_error('Pesanan tidak ditemukan', 404);
            return;
        }
        
        // BR-62: Only allow cancellation for certain statuses
        if (!in_array($booking['status'], ['pending', 'accepted'])) {
            $this->json_error('Pembatalan hanya dapat dilakukan untuk pesanan dengan status Pending atau Accepted', 400);
            return;
        }
        
        $reason = $this->input->post('reason', TRUE);
        
        if (empty($reason)) {
            $this->json_error('Alasan pembatalan harus diisi', 400);
            return;
        }
        
        // Start transaction
        $this->db->trans_start();
        
        // Cancel booking
        $result = $this->booking_model->cancel($booking_id, $this->user_id, $reason);
        
        if ($result) {
            // Log activity
            $this->booking_model->log_activity($booking_id, 'cancelled_by_user', 
                'Dibatalkan oleh pengguna: ' . $reason, 
                $this->user_id);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() !== FALSE) {
                $this->json_response([
                    'redirect' => site_url('booking_management/bookings')
                ], 200, 'Pesanan berhasil dibatalkan');
            }
        }
        
        $this->json_error('Gagal membatalkan pesanan', 500);
    }

    /**
     * Reschedule booking (BR-63: Only when status is pending)
     * @param int $booking_id
     */
    public function reschedule($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking || $booking['user_id'] != $this->user_id) {
            $this->json_error('Pesanan tidak ditemukan', 404);
            return;
        }
        
        // Validate input
        $this->form_validation->set_rules('new_date', 'Tanggal Baru', 'required|trim');
        $this->form_validation->set_rules('new_time', 'Waktu Baru', 'required|trim');
        
        if ($this->form_validation->run() === FALSE) {
            $this->json_error(validation_errors(), 400);
            return;
        }
        
        $new_date = $this->input->post('new_date', TRUE);
        $new_time = $this->input->post('new_time', TRUE);
        
        // Perform reschedule
        $result = $this->booking_model->reschedule($booking_id, $new_date, $new_time, $this->user_id);
        
        if ($result['success']) {
            // Log activity
            $this->booking_model->log_activity($booking_id, 'rescheduled', 
                'Jadwal diubah ke ' . $new_date . ' ' . $new_time, 
                $this->user_id);
            
            $this->json_response([
                'redirect' => site_url('booking_management/detail/' . $booking_id)
            ], 200, $result['message']);
        } else {
            $this->json_error($result['message'], 400);
        }
    }

    // ================================================================
    // NOTIFICATION & HELPERS
    // ================================================================

    /**
     * Get pending approvals count (for notification badge)
     */
    public function pending_approvals_count()
    {
        $count = $this->booking_model->count_user_pending_approvals($this->user_id);
        $this->json_response(['count' => $count], 200);
    }

    /**
     * Check if user has submitted review for completed booking
     * @param int $booking_id
     * @return bool
     */
    private function _has_submitted_review($booking_id)
    {
        $this->db->where('booking_id', $booking_id);
        $this->db->where('user_id', $this->user_id);
        return $this->db->get('reviews')->row_array() !== NULL;
    }

    /**
     * Send notification to workshop owner
     * @param int $workshop_id
     * @param string $event_key
     * @param array $data
     */
    private function _send_notification_to_workshop($workshop_id, $event_key, $data = [])
    {
        // Get workshop owner user_id
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->find_by_id($workshop_id);
        
        if ($workshop) {
            log_message('info', "Notification {$event_key} to workshop {$workshop_id}: " . json_encode($data));
            // Integration with notification system would go here
        }
    }
}

/* End of file Booking_management.php */
/* Location: ./application/controllers/Booking_management.php */
