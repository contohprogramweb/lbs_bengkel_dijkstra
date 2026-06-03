<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Workshop Controller
 * 
 * Handles workshop owner dashboard and profile management.
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class Workshop extends Workshop_Controller {

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
     * Workshop owner dashboard
     */
    public function dashboard()
    {
        $data['page_title'] = 'Dashboard Workshop';
        $data['user'] = $this->current_user;

        // Load workshop data
        $this->load->model('workshop_model', TRUE);
        $data['workshop'] = $this->workshop_model->get_by_owner($this->user_id);

        // Statistics
        $data['stats'] = [
            'total_bookings' => 0,
            'pending_bookings' => 0,
            'completed_bookings' => 0,
            'total_reviews' => 0,
            'avg_rating' => 0
        ];

        $this->render('workshop/dashboard', $data);
    }

    // --------------------------------------------------------------------
    // Profile Management
    // --------------------------------------------------------------------

    /**
     * View profile
     */
    public function profile()
    {
        $data['page_title'] = 'Profil Saya';
        $data['user'] = $this->user_model->find_by_id($this->user_id);

        $this->render('workshop/profile', $data);
    }

    /**
     * Edit profile
     */
    public function edit_profile()
    {
        $data['page_title'] = 'Edit Profil';
        $data['user'] = $this->user_model->find_by_id($this->user_id);

        if ($this->input->post()) {
            // Validate form
            $this->form_validation->set_rules('full_name', 'Nama Lengkap', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('phone', 'Telepon', 'trim|max_length[20]');
            $this->form_validation->set_rules('address', 'Alamat', 'trim|max_length[255]');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $update_data = [
                    'full_name' => $this->input->post('full_name', TRUE),
                    'phone' => $this->input->post('phone', TRUE),
                    'address' => $this->input->post('address', TRUE)
                ];

                // Handle avatar upload
                if (!empty($_FILES['avatar']['name'])) {
                    $config['upload_path'] = $this->config->item('upload_path_profiles');
                    $config['allowed_types'] = $this->config->item('allowed_types_profiles');
                    $config['max_size'] = $this->config->item('max_size_profiles');
                    $config['file_name'] = 'avatar_' . $this->user_id . '_' . time();

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('avatar')) {
                        $upload_data = $this->upload->data();
                        
                        // Delete old avatar if exists
                        if ($data['user']->avatar && file_exists(FCPATH . $data['user']->avatar)) {
                            unlink(FCPATH . $data['user']->avatar);
                        }

                        $update_data['avatar'] = 'uploads/profiles/' . $upload_data['file_name'];
                    } else {
                        $this->session->set_flashdata('error', $this->upload->display_errors());
                    }
                }

                if ($this->user_model->update_profile($this->user_id, $update_data)) {
                    // Update session data
                    $session_data = $this->session->userdata('logged_in');
                    $session_data['full_name'] = $update_data['full_name'];
                    $this->session->set_userdata('logged_in', $session_data);

                    $this->session->set_flashdata('success', 'Profil berhasil diperbarui.');
                    redirect('workshop/profile');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui profil.');
                }
            }
        }

        $this->render('workshop/edit_profile', $data);
    }

    /**
     * Change password
     */
    public function change_password()
    {
        $data['page_title'] = 'Ubah Password';

        if ($this->input->post()) {
            $this->form_validation->set_rules('current_password', 'Password Saat Ini', 'required');
            $this->form_validation->set_rules('new_password', 'Password Baru', 'required|min_length[6]|max_length[50]');
            $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|matches[new_password]');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $user = $this->user_model->find_by_id($this->user_id);
                $current_password = $this->input->post('current_password', TRUE);
                $new_password = $this->input->post('new_password', TRUE);

                if (!password_verify($current_password, $user->password)) {
                    $this->session->set_flashdata('error', 'Password saat ini salah.');
                } else {
                    if ($this->user_model->update_password($this->user_id, $new_password)) {
                        $this->session->set_flashdata('success', 'Password berhasil diubah.');
                        redirect('workshop/profile');
                    } else {
                        $this->session->set_flashdata('error', 'Gagal mengubah password.');
                    }
                }
            }
        }

        $this->render('workshop/change_password', $data);
    }
}

/* End of file Workshop.php */
/* Location: ./application/controllers/Workshop.php */
