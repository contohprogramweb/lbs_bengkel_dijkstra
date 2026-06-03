<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Notification Controller (User Side)
 * 
 * Handles user inbox, notification viewing, and reminder settings.
 * Implements UC-USR-11: Reminder Servis Berkala (user interactions)
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class Notifications extends Customer_Controller {

    /**
     * Notification model instance
     * @var Notification_model
     */
    private $notification_model;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Notification_model');
        $this->load->model('Vehicle_model');
        $this->notification_model = $this->Notification_model;
    }

    // --------------------------------------------------------------------
    // INBOX NOTIFICATIONS
    // --------------------------------------------------------------------

    /**
     * User inbox - view all notifications
     */
    public function inbox()
    {
        $data['page_title'] = 'Kotak Masuk Notifikasi';
        $data['user'] = $this->current_user;
        
        $filter = $this->input->get('filter', TRUE);
        $limit = 20;
        $offset = max(0, (int)$this->input->get('page'));

        $unread_only = ($filter === 'unread');
        
        $data['notifications'] = $this->notification_model->get_user_notifications(
            $this->current_user->email, 
            $limit, 
            $offset, 
            $unread_only
        );
        
        $data['unread_count'] = $this->notification_model->count_unread_notifications($this->current_user->email);
        $data['current_filter'] = $filter;

        $this->render('user/notifications/inbox', $data);
    }

    /**
     * View single notification detail and mark as read
     * @param int $id
     */
    public function view($id)
    {
        $notification = $this->db
            ->where('id', $id)
            ->where('recipient_email', $this->current_user->email)
            ->where('is_deleted', 0)
            ->get('notification_logs')
            ->row_array();

        if (!$notification) {
            $this->session->set_flashdata('error', 'Notifikasi tidak ditemukan.');
            redirect('notifications/inbox');
        }

        // Mark as read
        $this->notification_model->mark_as_read($id, $this->current_user->email);

        $data['page_title'] = 'Detail Notifikasi';
        $data['user'] = $this->current_user;
        $data['notification'] = $notification;

        $this->render('user/notifications/view', $data);
    }

    /**
     * Mark notification as read via AJAX
     */
    public function mark_read()
    {
        $id = $this->input->post('id');
        
        if (!$id) {
            $this->json_error('ID notifikasi tidak valid.', 400);
            return;
        }

        if ($this->notification_model->mark_as_read($id, $this->current_user->email)) {
            $this->json_response([], 200, 'Notifikasi ditandai sebagai dibaca.');
        } else {
            $this->json_error('Gagal menandai notifikasi sebagai dibaca.', 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function mark_all_read()
    {
        if ($this->notification_model->mark_all_as_read($this->current_user->email)) {
            $this->session->set_flashdata('success', 'Semua notifikasi ditandai sebagai dibaca.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui notifikasi.');
        }

        redirect('notifications/inbox');
    }

    // --------------------------------------------------------------------
    // REMINDER SETTINGS (UC-USR-11, BR-74)
    // --------------------------------------------------------------------

    /**
     * Manage reminder settings per vehicle
     */
    public function reminder_settings()
    {
        $data['page_title'] = 'Pengaturan Pengingat Servis';
        $data['user'] = $this->current_user;

        // Get all vehicles for this user
        $data['vehicles'] = $this->Vehicle_model->get_by_user($this->user_id);

        $this->render('user/notifications/reminder_settings', $data);
    }

    /**
     * Toggle reminder enabled for a vehicle (BR-74)
     */
    public function toggle_reminder()
    {
        $vehicle_id = $this->input->post('vehicle_id');
        
        if (!$vehicle_id) {
            $this->json_error('ID kendaraan tidak valid.', 400);
            return;
        }

        // Verify ownership
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

        $enabled = !$vehicle['reminder_enabled'];
        
        if ($this->notification_model->set_reminder_enabled($vehicle_id, $enabled)) {
            $message = $enabled ? 'Pengingat diaktifkan untuk kendaraan ini.' : 'Pengingat dinonaktifkan untuk kendaraan ini.';
            $this->json_response(['enabled' => $enabled], 200, $message);
        } else {
            $this->json_error('Gagal mengubah pengaturan pengingat.', 500);
        }
    }

    /**
     * Snooze reminder for a vehicle (UC-USR-11 Alternative Flow A2)
     * Tunda pengingat selama 30 hari
     */
    public function snooze_reminder()
    {
        $vehicle_id = $this->input->post('vehicle_id');
        $days = $this->input->post('days', 30); // Default 30 days
        
        if (!$vehicle_id) {
            $this->json_error('ID kendaraan tidak valid.', 400);
            return;
        }

        // Verify ownership
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

        if ($this->notification_model->snooze_reminder($vehicle_id, $days)) {
            $until_date = date('d/m/Y', strtotime("+{$days} days"));
            $this->json_response([], 200, "Pengingat ditunda hingga {$until_date}.");
        } else {
            $this->json_error('Gagal menunda pengingat.', 500);
        }
    }

    // --------------------------------------------------------------------
    // API ENDPOINTS
    // --------------------------------------------------------------------

    /**
     * Get unread notification count (for badge display)
     */
    public function unread_count()
    {
        $count = $this->notification_model->count_unread_notifications($this->current_user->email);
        $this->json_response(['count' => $count], 200);
    }

    /**
     * Get recent notifications (for dropdown/popup)
     */
    public function recent()
    {
        $limit = 5;
        $notifications = $this->notification_model->get_user_notifications(
            $this->current_user->email, 
            $limit, 
            0, 
            FALSE
        );

        $formatted = [];
        foreach ($notifications as $notif) {
            $formatted[] = [
                'id' => $notif['id'],
                'subject' => $notif['subject'],
                'created_at' => $notif['created_at'],
                'is_read' => !empty($notif['opened_at']),
                'event_key' => $notif['event_key']
            ];
        }

        $this->json_response([
            'notifications' => $formatted,
            'unread_count' => $this->notification_model->count_unread_notifications($this->current_user->email)
        ], 200);
    }
}

/* End of file Notifications.php */
/* Location: ./application/controllers/Notifications.php */
