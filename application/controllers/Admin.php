<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin Controller
 * 
 * Handles admin dashboard and user management.
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class Admin extends Admin_Controller {

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->library('form_validation');
    }

    // --------------------------------------------------------------------
    // Dashboard
    // --------------------------------------------------------------------

    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        $data['page_title'] = 'Dashboard Admin';
        $data['user'] = $this->current_user;

        // Load statistics
        $data['stats'] = [
            'total_users' => $this->user_model->count_all(),
            'total_customers' => $this->user_model->count_all('customer'),
            'total_workshop_owners' => $this->user_model->count_all('workshop_owner'),
            'total_mechanics' => $this->user_model->count_all('mechanic')
        ];

        $this->render('admin/dashboard', $data);
    }

    // --------------------------------------------------------------------
    // User Management
    // --------------------------------------------------------------------

    /**
     * List all users
     */
    public function users()
    {
        $data['page_title'] = 'Manajemen Pengguna';
        
        $role_filter = $this->input->get('role');
        $limit = 50;
        $offset = max(0, (int)$this->input->get('page'));

        $data['users'] = $this->user_model->get_all($limit, $offset, $role_filter);
        $data['roles'] = ['customer', 'workshop_owner', 'mechanic'];
        $data['current_role'] = $role_filter;

        $this->render('admin/users', $data);
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

        $this->render('admin/view_user', $data);
    }

    /**
     * Activate user
     */
    public function activate_user($id)
    {
        if ($this->user_model->activate($id)) {
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
            $this->session->set_flashdata('success', 'Pengguna berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus pengguna.');
        }

        redirect('admin/users');
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
        
        $this->load->model('system_setting_model');
        $data['settings'] = $this->system_setting_model->get_all_settings();

        if ($this->input->post()) {
            $settings_data = $this->input->post('settings');
            
            foreach ($settings_data as $key => $value) {
                $this->system_setting_model->update_setting($key, $value);
            }

            $this->session->set_flashdata('success', 'Pengaturan berhasil disimpan.');
            redirect('admin/settings');
        }

        $this->render('admin/settings', $data);
    }
}

/* End of file Admin.php */
/* Location: ./application/controllers/Admin.php */
