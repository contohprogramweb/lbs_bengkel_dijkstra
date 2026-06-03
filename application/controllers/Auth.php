<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Controller
 * 
 * Handles user authentication including login, register, logout.
 * Supports 3 roles: admin, workshop_owner, customer.
 * Admin account is pre-seeded (no public registration).
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class Auth extends Public_Controller {

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
    // Login
    // --------------------------------------------------------------------

    /**
     * Login page
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->is_logged_in()) {
            $this->_redirect_by_role();
        }

        $data['page_title'] = 'Login';
        $data['roles'] = ['customer', 'workshop_owner'];

        $this->render('auth/login', $data);
    }

    /**
     * Process login
     */
    public function process_login()
    {
        // Validate form
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('auth/login');
        }

        $email = $this->input->post('email', TRUE);
        $password = $this->input->post('password', TRUE);

        // Authenticate user
        $user = $this->user_model->authenticate($email, $password);

        if (!$user) {
            $this->session->set_flashdata('error', 'Email atau password salah.');
            redirect('auth/login');
        }

        // Create session data
        $session_data = [
            'user_id' => $user->id,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'role' => $user->role,
            'avatar' => $user->avatar,
            'logged_in' => TRUE,
            'login_time' => time()
        ];

        $this->session->set_userdata('logged_in', $session_data);

        // Regenerate session ID for security
        $this->session->sess_regenerate(TRUE);

        $this->session->set_flashdata('success', 'Selamat datang, ' . $user->full_name . '!');

        // Redirect based on role
        $this->_redirect_by_role($user->role);
    }

    // --------------------------------------------------------------------
    // Register
    // --------------------------------------------------------------------

    /**
     * Register page
     */
    public function register()
    {
        // Redirect if already logged in
        if ($this->is_logged_in()) {
            $this->_redirect_by_role();
        }

        $data['page_title'] = 'Daftar Akun Baru';
        $data['roles'] = ['customer', 'workshop_owner'];

        $this->render('auth/register', $data);
    }

    /**
     * Process registration
     */
    public function process_register()
    {
        // Validate form
        $this->form_validation->set_rules('full_name', 'Nama Lengkap', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|max_length[191]|is_unique[users.email]');
        $this->form_validation->set_rules('phone', 'Telepon', 'trim|max_length[20]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|max_length[50]');
        $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|matches[password]');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[customer,workshop_owner]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('auth/register');
        }

        $data = [
            'full_name' => $this->input->post('full_name', TRUE),
            'email' => $this->input->post('email', TRUE),
            'phone' => $this->input->post('phone', TRUE),
            'password' => $this->input->post('password', TRUE),
            'role' => $this->input->post('role', TRUE)
        ];

        // Check email exists
        if ($this->user_model->email_exists($data['email'])) {
            $this->session->set_flashdata('error', 'Email sudah terdaftar.');
            redirect('auth/register');
        }

        // Register user
        $user_id = $this->user_model->register($data);

        if (!$user_id) {
            $this->session->set_flashdata('error', 'Gagal mendaftar. Silakan coba lagi.');
            redirect('auth/register');
        }

        $this->session->set_flashdata('success', 'Pendaftaran berhasil! Silakan login dengan akun Anda.');
        redirect('auth/login');
    }

    // --------------------------------------------------------------------
    // Logout
    // --------------------------------------------------------------------

    /**
     * Logout user
     */
    public function logout()
    {
        // Destroy session
        $this->session->sess_destroy();

        // Redirect to login
        redirect('auth/login');
    }

    // --------------------------------------------------------------------
    // Helper Methods
    // --------------------------------------------------------------------

    /**
     * Redirect user based on role
     * 
     * @param string|null $role User role
     */
    private function _redirect_by_role($role = NULL)
    {
        if ($role === NULL) {
            $role = $this->user_role;
        }

        switch ($role) {
            case 'admin':
                redirect('admin/dashboard');
                break;
            case 'workshop_owner':
                redirect('workshop/dashboard');
                break;
            case 'customer':
                redirect('user/dashboard');
                break;
            default:
                redirect('/');
        }
    }
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */
