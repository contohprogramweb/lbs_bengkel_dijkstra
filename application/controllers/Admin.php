<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin Controller
 * 
 * Handles admin dashboard and back office operations including:
 * - Dashboard statistics with charts
 * - User management (DataTables server-side)
 * - Workshop verification and management
 * - Review moderation
 * - System settings
 * - Activity logs (audit trail)
 * 
 * @package     Bengkel Terdekat
 * @version     4.1
 */
class Admin extends Admin_Controller {

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['user_model', 'admin_model', 'system_setting_model']);
        $this->load->library('form_validation');
    }

    // --------------------------------------------------------------------
    // Dashboard
    // --------------------------------------------------------------------

    /**
     * Admin dashboard with comprehensive statistics
     */
    public function dashboard()
    {
        $data['page_title'] = 'Dashboard Admin';
        $data['user'] = $this->current_user;

        // Load comprehensive statistics
        $data['stats'] = $this->admin_model->get_dashboard_stats();
        
        // Chart data for last 7 days
        $data['bookings_trend'] = $this->admin_model->get_bookings_trend(7);
        $data['workshop_trend'] = $this->admin_model->get_workshop_trend(7);

        $this->render('admin/dashboard', $data);
    }

    // --------------------------------------------------------------------
    // User Management (DataTables Server-Side)
    // --------------------------------------------------------------------

    /**
     * List all users with DataTables
     */
    public function users()
    {
        $data['page_title'] = 'Manajemen Pengguna';
        $data['user'] = $this->current_user;
        $data['roles'] = ['customer', 'workshop_owner', 'mechanic'];
        
        $this->render('admin/users', $data);
    }

    /**
     * DataTables server-side processing for users
     */
    public function users_data()
    {
        $request = $this->input->get();
        $role_filter = $this->input->get('role_filter');
        
        $result = $this->admin_model->get_users_datatables($request, $role_filter);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    /**
     * View user detail
     */
    public function view_user($id)
    {
        $data['page_title'] = 'Detail Pengguna';
        $data['user_detail'] = $this->user_model->find_by_id($id);

        if (!$data['user_detail']) {
            show_error('Pengguna tidak ditemukan.', 404);
        }

        // Get user's workshops if any
        $this->load->model('workshop_model');
        $data['workshops'] = $this->workshop_model->get_by_user_id($id);

        $this->render('admin/view_user', $data);
    }

    /**
     * Reset user password
     */
    public function reset_password($id)
    {
        if ($this->input->post()) {
            $new_password = $this->input->post('new_password');
            
            if (strlen($new_password) < 6) {
                $this->session->set_flashdata('error', 'Password minimal 6 karakter.');
                redirect('admin/view_user/' . $id);
            }
            
            if ($this->admin_model->reset_user_password($id, $new_password, $this->user_id)) {
                $this->session->set_flashdata('success', 'Password berhasil direset.');
            } else {
                $this->session->set_flashdata('error', 'Gagal mereset password.');
            }
            
            redirect('admin/view_user/' . $id);
        }
        
        show_404();
    }

    /**
     * Activate user
     */
    public function activate_user($id)
    {
        if ($this->user_model->activate($id)) {
            $this->admin_model->log_activity(
                $this->user_id,
                'USER_ACTIVATE',
                "Admin mengaktifkan user ID {$id}",
                $id
            );
            $this->session->set_flashdata('success', 'Pengguna berhasil diaktifkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengaktifkan pengguna.');
        }

        redirect('admin/users');
    }

    /**
     * Deactivate user
     */
    public function deactivate_user($id)
    {
        if ($this->user_model->deactivate($id)) {
            $this->admin_model->log_activity(
                $this->user_id,
                'USER_DEACTIVATE',
                "Admin menonaktifkan user ID {$id}",
                $id
            );
            $this->session->set_flashdata('success', 'Pengguna berhasil dinonaktifkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menonaktifkan pengguna.');
        }

        redirect('admin/users');
    }

    /**
     * Delete user (soft delete)
     */
    public function delete_user($id)
    {
        // Prevent deleting self
        if ($id == $this->user_id) {
            $this->session->set_flashdata('error', 'Tidak dapat menghapus akun Anda sendiri.');
            redirect('admin/users');
        }

        if ($this->user_model->soft_delete($id)) {
            $this->admin_model->log_activity(
                $this->user_id,
                'USER_DELETE',
                "Admin menghapus user ID {$id}",
                $id
            );
            $this->session->set_flashdata('success', 'Pengguna berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus pengguna.');
        }

        redirect('admin/users');
    }

    // --------------------------------------------------------------------
    // Workshop Management
    // --------------------------------------------------------------------

    /**
     * List all workshops with DataTables
     */
    public function workshops()
    {
        $data['page_title'] = 'Manajemen Bengkel';
        $data['user'] = $this->current_user;
        
        $this->render('admin/workshops', $data);
    }

    /**
     * DataTables server-side processing for workshops
     */
    public function workshops_data()
    {
        $request = $this->input->get();
        $verification_status = $this->input->get('verification_status');
        
        $result = $this->admin_model->get_workshops_datatables($request, $verification_status);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    /**
     * View workshop detail
     */
    public function view_workshop($id)
    {
        $data['page_title'] = 'Detail Bengkel';
        
        $this->load->model('workshop_model');
        $data['workshop'] = $this->workshop_model->find_by_id($id);

        if (!$data['workshop']) {
            show_error('Bengkel tidak ditemukan.', 404);
        }

        $this->render('admin/view_workshop', $data);
    }

    /**
     * Verify a workshop
     */
    public function verify_workshop($id)
    {
        if ($this->admin_model->verify_workshop($id, $this->user_id)) {
            $this->session->set_flashdata('success', 'Bengkel berhasil diverifikasi.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memverifikasi bengkel.');
        }

        redirect('admin/workshops');
    }

    /**
     * Set featured workshop
     */
    public function set_featured($id)
    {
        $is_featured = $this->input->post('is_featured') ? TRUE : FALSE;
        
        if ($this->admin_model->set_featured_workshop($id, $is_featured, $this->user_id)) {
            $msg = $is_featured ? 'Bengkel dijadikan featured.' : 'Status featured dihapus.';
            $this->session->set_flashdata('success', $msg);
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah status featured.');
        }

        redirect('admin/workshops');
    }

    /**
     * Pending verification workshops
     */
    public function pending_verification()
    {
        $data['page_title'] = 'Verifikasi Bengkel';
        $data['workshops'] = $this->admin_model->get_pending_verification_workshops();
        
        $this->render('admin/pending_verification', $data);
    }

    // --------------------------------------------------------------------
    // Review Moderation
    // --------------------------------------------------------------------

    /**
     * Review moderation panel
     */
    public function review_moderation()
    {
        $data['page_title'] = 'Moderasi Review';
        $data['pending_count'] = $this->admin_model->count_pending_reviews();
        
        $this->render('admin/review_moderation', $data);
    }

    /**
     * Get pending reviews (AJAX)
     */
    public function pending_reviews_data()
    {
        $limit = 50;
        $offset = max(0, (int)$this->input->get('start'));
        
        $reviews = $this->admin_model->get_pending_reviews($limit, $offset);
        $total = $this->admin_model->count_pending_reviews();
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'draw' => (int)$this->input->get('draw'),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $reviews
            ]));
    }

    /**
     * Approve a review
     */
    public function approve_review($id)
    {
        $notes = $this->input->post('notes');
        
        if ($this->admin_model->approve_review($id, $this->user_id, $notes)) {
            $this->session->set_flashdata('success', 'Review disetujui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyetujui review.');
        }

        redirect('admin/review_moderation');
    }

    /**
     * Reject a review
     */
    public function reject_review($id)
    {
        $notes = $this->input->post('notes');
        
        if (empty($notes)) {
            $this->session->set_flashdata('error', 'Alasan penolakan wajib diisi.');
            redirect('admin/review_moderation');
        }
        
        if ($this->admin_model->reject_review($id, $this->user_id, $notes)) {
            $this->session->set_flashdata('success', 'Review ditolak.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menolak review.');
        }

        redirect('admin/review_moderation');
    }

    // --------------------------------------------------------------------
    // System Settings
    // --------------------------------------------------------------------

    /**
     * System settings page
     */
    public function settings()
    {
        $data['page_title'] = 'Pengaturan Sistem';
        $data['user'] = $this->current_user;
        $data['settings'] = $this->system_setting_model->get_all_with_details();

        if ($this->input->post()) {
            $settings_data = $this->input->post('settings');
            $types = $this->input->post('types');
            
            foreach ($settings_data as $key => $value) {
                $type = isset($types[$key]) ? $types[$key] : 'string';
                $this->system_setting_model->update_setting($key, $value);
            }
            
            $this->admin_model->log_activity(
                $this->user_id,
                'SYSTEM_SETTING_UPDATE',
                'Admin memperbarui pengaturan sistem'
            );

            $this->session->set_flashdata('success', 'Pengaturan berhasil disimpan.');
            redirect('admin/settings');
        }

        $this->render('admin/settings', $data);
    }

    // --------------------------------------------------------------------
    // Activity Logs (Audit Trail)
    // --------------------------------------------------------------------

    /**
     * Activity logs page
     */
    public function activity_logs()
    {
        $data['page_title'] = 'Log Aktivitas';
        $data['user'] = $this->current_user;
        
        // Filters
        $filters = [
            'user_id' => $this->input->get('user_id'),
            'workshop_id' => $this->input->get('workshop_id'),
            'action_type' => $this->input->get('action_type'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to')
        ];
        
        $limit = 50;
        $offset = max(0, (int)$this->input->get('page')) * $limit;
        
        $data['logs'] = $this->admin_model->get_activity_logs($filters, $limit, $offset);
        $data['total_logs'] = $this->admin_model->count_activity_logs($filters);
        $data['action_types'] = $this->admin_model->get_action_types();
        $data['filters'] = $filters;
        
        $this->render('admin/activity_logs', $data);
    }
}

/* End of file Admin.php */
/* Location: ./application/controllers/Admin.php */
