<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API User Notifications Controller
 * 
 * RESTful API endpoints for user notification management (SRS v4.0 Section 5.6)
 * Handles inbox, read status, and reminder snooze functionality
 * BR-85: Snooze 30 hari, max 1 per 7 hari
 */
class Notifications extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('notification_model');
        
        // Require authentication for all API calls
        if (!$this->is_logged_in()) {
            $this->json_error('Authentication required.', 401);
            return;
        }
    }

    /**
     * GET /api/user/notifications/list
     * Get all notifications for current user
     */
    public function list()
    {
        $page = $this->input->get('page', TRUE) ?? 1;
        $per_page = $this->input->get('per_page', TRUE) ?? 20;
        $type = $this->input->get('type', TRUE); // 'booking', 'reminder', 'system'
        $unread_only = $this->input->get('unread_only', TRUE) === 'true';

        $notifications = $this->notification_model->get_user_notifications(
            $this->user_id,
            $page,
            $per_page,
            $type,
            $unread_only
        );

        $this->json_response([
            'success' => true,
            'data' => $notifications
        ], 200, 'Notifications retrieved successfully.');
    }

    /**
     * GET /api/user/notifications/unread_count
     * Get count of unread notifications (for badge display)
     */
    public function unread_count()
    {
        $count = $this->notification_model->get_unread_count($this->user_id);

        $this->json_response([
            'success' => true,
            'data' => ['unread_count' => $count]
        ], 200, 'Unread count retrieved successfully.');
    }

    /**
     * POST /api/user/notifications/mark_read/{notification_id}
     * Mark a single notification as read
     */
    public function mark_read($notification_id)
    {
        $notification = $this->db
            ->where('id', $notification_id)
            ->where('user_id', $this->user_id)
            ->get('notifications')
            ->row_array();

        if (!$notification) {
            $this->json_error('Notifikasi tidak ditemukan.', 404);
            return;
        }

        $this->db->where('id', $notification_id)
                 ->update('notifications', [
                     'is_read' => 1,
                     'read_at' => date('Y-m-d H:i:s')
                 ]);

        $this->json_response([
            'success' => true
        ], 200, 'Notifikasi ditandai sebagai sudah dibaca.');
    }

    /**
     * POST /api/user/notifications/mark_all_read
     * Mark all notifications as read
     */
    public function mark_all_read()
    {
        $this->db->where('user_id', $this->user_id)
                 ->where('is_read', 0)
                 ->update('notifications', [
                     'is_read' => 1,
                     'read_at' => date('Y-m-d H:i:s')
                 ]);

        $this->json_response([
            'success' => true
        ], 200, 'Semua notifikasi ditandai sebagai sudah dibaca.');
    }

    /**
     * POST /api/user/notifications/snooze
     * Snooze reminder for a vehicle (UC-USR-11 Alternative Flow A2)
     * BR-85: Snooze 30 hari, max 1 snooze per 7 hari
     */
    public function snooze()
    {
        $vehicle_id = $this->input->post('vehicle_id', TRUE);
        $days = $this->input->post('days', TRUE) ?? 30; // Default 30 days (BR-85)

        $this->form_validation->set_rules('vehicle_id', 'ID Kendaraan', 'required|numeric');
        $this->form_validation->set_rules('days', 'Jumlah Hari', 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[90]');

        if ($this->form_validation->run() === FALSE) {
            $this->json_error(validation_errors(), 400);
            return;
        }

        // Verify vehicle ownership
        $vehicle = $this->db
            ->where('id', $vehicle_id)
            ->where('user_id', $this->user_id)
            ->where('is_deleted', 0)
            ->get('vehicles')
            ->row_array();

        if (!$vehicle) {
            $this->json_error('Kendaraan tidak ditemukan.', 404);
            return;
        }

        // BR-85: Check if already snoozed in the last 7 days
        $last_snooze = $this->db
            ->select('reminder_snoozed_until')
            ->where('id', $vehicle_id)
            ->where('user_id', $this->user_id)
            ->get('vehicles')
            ->row_array();

        if (!empty($last_snooze['reminder_snoozed_until'])) {
            $snooze_until = strtotime($last_snooze['reminder_snoozed_until']);
            $seven_days_ago = strtotime('-7 days');
            
            if ($snooze_until > $seven_days_ago) {
                $days_remaining = ceil(($snooze_until - $seven_days_ago) / 86400);
                $this->json_error(
                    "Anda baru saja menunda pengingat ini. Silakan tunggu {$days_remaining} hari lagi untuk menunda kembali (BR-85).",
                    429
                );
                return;
            }
        }

        // Calculate snooze until date
        $snooze_until = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        // Update vehicle with snooze date
        $this->db->where('id', $vehicle_id)
                 ->update('vehicles', [
                     'reminder_snoozed_until' => $snooze_until,
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);

        // Log notification
        $this->db->insert('notification_logs', [
            'user_id' => $this->user_id,
            'action' => 'snooze_reminder',
            'vehicle_id' => $vehicle_id,
            'details' => json_encode(['days' => $days, 'until' => $snooze_until]),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $until_date = date('d/m/Y', strtotime($snooze_until));
        $this->json_response([
            'success' => true,
            'data' => [
                'snoozed_until' => $snooze_until,
                'formatted_until' => $until_date
            ]
        ], 200, "Pengingat ditunda hingga {$until_date}.");
    }
}
