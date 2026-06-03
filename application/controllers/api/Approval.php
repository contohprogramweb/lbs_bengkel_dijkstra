<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Booking Approval Controller
 * 
 * RESTful API endpoints for booking approval workflow (SRS v4.0 Section 5.6)
 * Handles additional cost estimation and customer approval
 * BR-78: Estimasi tambahan, BR-79: Timeout 48 jam, BR-80: Log permanen
 */
class Approval extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('booking_model');
        
        // Require authentication for all API calls
        if (!$this->is_logged_in()) {
            $this->json_error('Authentication required.', 401);
            return;
        }
    }

    /**
     * GET /api/booking/approval/pending
     * Get all bookings pending customer approval (for workshop owner)
     */
    public function pending()
    {
        // Verify user is workshop owner
        $workshop = $this->db
            ->where('user_id', $this->user_id)
            ->where('is_deleted', 0)
            ->get('workshops')
            ->row_array();

        if (!$workshop) {
            $this->json_error('Akses ditolak. Hanya pemilik bengkel yang dapat mengakses.', 403);
            return;
        }

        $pending_approvals = $this->db
            ->select('b.*, u.full_name as customer_name, u.phone as customer_phone, v.vehicle_number')
            ->from('bookings b')
            ->join('users u', 'b.user_id = u.id')
            ->join('vehicles v', 'b.vehicle_id = v.id', 'left')
            ->where('b.workshop_id', $workshop['id'])
            ->where('b.approval_status', 'pending')
            ->where('b.status', 'processed')
            ->where('b.is_deleted', 0)
            ->order_by('b.created_at', 'DESC')
            ->get()
            ->result_array();

        $this->json_response([
            'success' => true,
            'data' => $pending_approvals
        ], 200, 'Pending approvals retrieved successfully.');
    }

    /**
     * POST /api/booking/approval/approve/{booking_id}
     * Customer approves additional cost estimate
     * BR-78: Approval required before work continues
     */
    public function approve($booking_id)
    {
        $booking = $this->booking_model->get_booking_by_id($booking_id);

        if (!$booking) {
            $this->json_error('Booking tidak ditemukan.', 404);
            return;
        }

        // Verify user is the booking owner
        if ($booking['user_id'] != $this->user_id) {
            $this->json_error('Anda tidak memiliki izin untuk menyetujui booking ini.', 403);
            return;
        }

        // Can only approve if status is processed and approval_status is pending
        if ($booking['status'] !== 'processed' || $booking['approval_status'] !== 'pending') {
            $this->json_error('Booking ini tidak memerlukan approval saat ini.', 400);
            return;
        }

        $notes = $this->input->post('notes', TRUE);

        // Start transaction
        $this->db->trans_begin();

        // Update booking approval_status to approved
        $this->db->where('id', $booking_id)
                 ->update('bookings', [
                     'approval_status' => 'approved',
                     'approved_at' => date('Y-m-d H:i:s'),
                     'approved_by' => $this->user_id,
                     'mechanic_notes' => $notes
                 ]);

        // Create approval log entry (BR-80: permanent log)
        $this->db->insert('booking_approvals', [
            'booking_id' => $booking_id,
            'action' => 'approved',
            'estimated_additional_cost' => $this->input->post('estimated_additional_cost', TRUE),
            'notes' => $notes,
            'user_id' => $this->user_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_error('Gagal memproses approval.', 500);
            return;
        }

        $this->db->trans_commit();

        $this->json_response([
            'success' => true
        ], 200, 'Approval berhasil disetujui. Bengkel akan melanjutkan pekerjaan.');
    }

    /**
     * POST /api/booking/approval/reject/{booking_id}
     * Customer rejects additional cost estimate
     * BR-78: Rejection can lead to cancellation or alternative solution
     */
    public function reject($booking_id)
    {
        $booking = $this->booking_model->get_booking_by_id($booking_id);

        if (!$booking) {
            $this->json_error('Booking tidak ditemukan.', 404);
            return;
        }

        // Verify user is the booking owner
        if ($booking['user_id'] != $this->user_id) {
            $this->json_error('Anda tidak memiliki izin untuk menolak booking ini.', 403);
            return;
        }

        // Can only reject if status is processed and approval_status is pending
        if ($booking['status'] !== 'processed' || $booking['approval_status'] !== 'pending') {
            $this->json_error('Booking ini tidak memerlukan approval saat ini.', 400);
            return;
        }

        $rejection_reason = $this->input->post('rejection_reason', TRUE);
        $action = $this->input->post('action', TRUE); // 'cancel' or 'discuss'

        // Start transaction
        $this->db->trans_begin();

        if ($action === 'cancel') {
            // Cancel the booking
            $this->db->where('id', $booking_id)
                     ->update('bookings', [
                         'status' => 'cancelled',
                         'approval_status' => 'rejected',
                         'cancelled_at' => date('Y-m-d H:i:s'),
                         'cancelled_by' => $this->user_id,
                         'cancellation_reason' => $rejection_reason
                     ]);
        } else {
            // Just mark as rejected but keep booking active for discussion
            $this->db->where('id', $booking_id)
                     ->update('bookings', [
                         'approval_status' => 'rejected',
                         'rejection_reason' => $rejection_reason
                     ]);
        }

        // Create approval log entry (BR-80: permanent log)
        $this->db->insert('booking_approvals', [
            'booking_id' => $booking_id,
            'action' => 'rejected',
            'notes' => $rejection_reason,
            'user_id' => $this->user_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->json_error('Gagal memproses penolakan.', 500);
            return;
        }

        $this->db->trans_commit();

        $this->json_response([
            'success' => true
        ], 200, $action === 'cancel' ? 'Booking dibatalkan.' : 'Penolakan dicatat. Silakan diskusikan dengan bengkel.');
    }

    /**
     * GET /api/booking/approval/history/{booking_id}
     * Get approval history for a booking (BR-80: permanent log)
     */
    public function history($booking_id)
    {
        $booking = $this->booking_model->get_booking_by_id($booking_id);

        if (!$booking) {
            $this->json_error('Booking tidak ditemukan.', 404);
            return;
        }

        // Verify user has access (owner or workshop owner)
        if ($booking['user_id'] != $this->user_id) {
            $workshop = $this->db->where('id', $booking['workshop_id'])->get('workshops')->row_array();
            if (!$workshop || $workshop['user_id'] != $this->user_id) {
                $this->json_error('Akses ditolak.', 403);
                return;
            }
        }

        $history = $this->db
            ->select('ba.*, u.full_name as user_name')
            ->from('booking_approvals ba')
            ->join('users u', 'ba.user_id = u.id', 'left')
            ->where('ba.booking_id', $booking_id)
            ->order_by('ba.created_at', 'DESC')
            ->get()
            ->result_array();

        $this->json_response([
            'success' => true,
            'data' => $history
        ], 200, 'Approval history retrieved successfully.');
    }
}
