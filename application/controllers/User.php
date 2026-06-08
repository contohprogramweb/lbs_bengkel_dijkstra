<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Controller
 * 
 * Handles customer/user dashboard and profile management.
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class User extends Customer_Controller {

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
     * User dashboard
     */
    public function dashboard()
    {
        $data['page_title'] = 'Dashboard';
        $data['user'] = $this->current_user;

        // Load user statistics
        $this->load->model('booking_model', TRUE);
        $this->load->model('vehicle_model', TRUE);

        // Get booking stats
        $data['stats'] = [
            'total_bookings' => $this->booking_model->count_by_user($this->user_id),
            'pending_bookings' => $this->booking_model->count_by_user($this->user_id, 'pending'),
            'completed_bookings' => $this->booking_model->count_by_user($this->user_id, 'completed'),
            'total_vehicles' => $this->vehicle_model->count_by_user($this->user_id)
        ];

        // Get recent bookings
        $data['recent_bookings'] = $this->booking_model->get_recent_by_user($this->user_id, 5);

        $this->render('user/dashboard', $data);
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

        $this->render('user/profile', $data);
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
                    redirect('user/profile');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui profil.');
                }
            }
        }

        $this->render('user/edit_profile', $data);
    }

    /**
     * Change password
     */
    public function change_password()
    {
        $data['page_title'] = 'Ubah Password';

        if ($this->input->post()) {
            // Validate form
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
                        redirect('user/profile');
                    } else {
                        $this->session->set_flashdata('error', 'Gagal mengubah password.');
                    }
                }
            }
        }

        $this->render('user/change_password', $data);
    }

    /**
     * Delete account (soft delete)
     */
    public function delete_account()
    {
        if ($this->input->post('confirm')) {
            if ($this->user_model->soft_delete($this->user_id)) {
                $this->session->sess_destroy();
                $this->session->set_flashdata('success', 'Akun Anda telah dihapus.');
                redirect('auth/login');
            } else {
                $this->session->set_flashdata('error', 'Gagal menghapus akun.');
            }
        }

        $data['page_title'] = 'Hapus Akun';
        $this->render('user/delete_account', $data);
    }

    // --------------------------------------------------------------------
    // Vehicle Management (Modul 6 - FR-VEH-01~03, UC-USR-07)
    // --------------------------------------------------------------------

    /**
     * My Vehicles page - list all vehicles with UI cards
     */
    public function vehicles()
    {
        $data['page_title'] = 'Kendaraan Saya';
        $data['user'] = $this->current_user;
        
        $this->load->model('vehicle_model');
        
        // Get all vehicles for user
        $data['vehicles'] = $this->vehicle_model->get_by_user($this->user_id);
        
        // Check if user can add more vehicles (BR-58)
        $data['can_add'] = $this->vehicle_model->can_add_vehicle($this->user_id);
        
        $this->render('user/vehicles', $data);
    }

    /**
     * Add new vehicle
     */
    public function vehicle_add()
    {
        $data['page_title'] = 'Tambah Kendaraan';
        
        $this->load->model('vehicle_model');
        
        // Check if user can add more vehicles (BR-58)
        $can_add = $this->vehicle_model->can_add_vehicle($this->user_id);
        if (!$can_add['can_add']) {
            $this->session->set_flashdata('error', 'Anda telah mencapai batas maksimal ' . $can_add['max'] . ' kendaraan.');
            redirect('user/vehicles');
        }
        
        if ($this->input->post()) {
            // Validate form
            $this->form_validation->set_rules('vehicle_number', 'Nomor Polisi', 'required|trim|max_length[20]');
            $this->form_validation->set_rules('brand', 'Merk', 'required|trim|max_length[50]');
            $this->form_validation->set_rules('model', 'Model', 'trim|max_length[50]');
            $this->form_validation->set_rules('year', 'Tahun', 'required|integer');
            $this->form_validation->set_rules('fuel_type', 'Jenis Bahan Bakar', 'required|in_list[petrol,diesel,electric,hybrid]');
            $this->form_validation->set_rules('current_km', 'Kilometer Terakhir', 'required|integer|min_length[0]');
            $this->form_validation->set_rules('vin', 'VIN', 'trim|max_length[17]|regex_match[/^[A-HJ-NPR-Z0-9]{17}$/i]');
            
            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $vehicle_number = $this->input->post('vehicle_number', TRUE);
                $year = (int) $this->input->post('year', TRUE);
                
                // Check duplicate plate number (BR-59)
                if ($this->vehicle_model->vehicle_number_exists($vehicle_number, $this->user_id)) {
                    $this->session->set_flashdata('error', 'Kendaraan dengan nomor polisi ini sudah terdaftar.');
                } else {
                    // Validate year (BR-59, BR-60)
                    $year_validation = $this->vehicle_model->validate_year($year);
                    if (!$year_validation['valid']) {
                        $this->session->set_flashdata('error', $year_validation['message']);
                    } else {
                        $insert_data = [
                            'user_id' => $this->user_id,
                            'vehicle_number' => $this->vehicle_model->normalize_plate_number($vehicle_number),
                            'vehicle_type' => $this->input->post('vehicle_type', TRUE) ?: 'car',
                            'brand' => $this->input->post('brand', TRUE),
                            'model' => $this->input->post('model', TRUE),
                            'year' => $year,
                            'fuel_type' => $this->input->post('fuel_type', TRUE),
                            'current_km' => (int) $this->input->post('current_km', TRUE),
                            'vin' => !empty($this->input->post('vin', TRUE)) ? strtoupper($this->input->post('vin', TRUE)) : NULL,
                            'transmission' => $this->input->post('transmission', TRUE) ?: 'manual',
                            'color' => $this->input->post('color', TRUE),
                            'notes' => $this->input->post('notes', TRUE)
                        ];
                        
                        if ($this->vehicle_model->insert($insert_data)) {
                            $this->session->set_flashdata('success', 'Kendaraan berhasil ditambahkan.');
                            redirect('user/vehicles');
                        } else {
                            $this->session->set_flashdata('error', 'Gagal menambahkan kendaraan.');
                        }
                    }
                }
            }
        }
        
        // Prepare dropdown data
        $data['brands'] = $this->vehicle_model->get_brands();
        $data['fuel_types'] = $this->vehicle_model->get_fuel_types();
        $data['transmissions'] = $this->vehicle_model->get_transmissions();
        $data['vehicle_types'] = $this->vehicle_model->get_vehicle_types();
        $data['years'] = range(date('Y') + 1, 1980);
        
        $this->render('user/vehicle_form', $data);
    }

    /**
     * Edit vehicle
     * 
     * @param int $id Vehicle ID
     */
    public function vehicle_edit($id)
    {
        $data['page_title'] = 'Edit Kendaraan';
        
        $this->load->model('vehicle_model');
        
        $vehicle = $this->vehicle_model->find_by_id($id);
        
        if (!$vehicle) {
            $this->session->set_flashdata('error', 'Kendaraan tidak ditemukan.');
            redirect('user/vehicles');
        }
        
        // Verify ownership
        if ($vehicle->user_id != $this->user_id) {
            show_error('Anda tidak memiliki akses ke kendaraan ini.', 403);
        }
        
        if ($this->input->post()) {
            // Validate form
            $this->form_validation->set_rules('vehicle_number', 'Nomor Polisi', 'required|trim|max_length[20]');
            $this->form_validation->set_rules('brand', 'Merk', 'required|trim|max_length[50]');
            $this->form_validation->set_rules('model', 'Model', 'trim|max_length[50]');
            $this->form_validation->set_rules('year', 'Tahun', 'required|integer');
            $this->form_validation->set_rules('fuel_type', 'Jenis Bahan Bakar', 'required|in_list[petrol,diesel,electric,hybrid]');
            $this->form_validation->set_rules('current_km', 'Kilometer Terakhir', 'required|integer|min_length[0]');
            $this->form_validation->set_rules('vin', 'VIN', 'trim|max_length[17]|regex_match[/^[A-HJ-NPR-Z0-9]{17}$/i]');
            
            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $vehicle_number = $this->input->post('vehicle_number', TRUE);
                $year = (int) $this->input->post('year', TRUE);
                
                // Check duplicate plate number (BR-59) - exclude current vehicle
                if ($this->vehicle_model->vehicle_number_exists($vehicle_number, $this->user_id, $id)) {
                    $this->session->set_flashdata('error', 'Kendaraan dengan nomor polisi ini sudah terdaftar.');
                } else {
                    // Validate year (BR-59, BR-60)
                    $year_validation = $this->vehicle_model->validate_year($year);
                    if (!$year_validation['valid']) {
                        $this->session->set_flashdata('error', $year_validation['message']);
                    } else {
                        $update_data = [
                            'vehicle_number' => $this->vehicle_model->normalize_plate_number($vehicle_number),
                            'vehicle_type' => $this->input->post('vehicle_type', TRUE) ?: 'car',
                            'brand' => $this->input->post('brand', TRUE),
                            'model' => $this->input->post('model', TRUE),
                            'year' => $year,
                            'fuel_type' => $this->input->post('fuel_type', TRUE),
                            'current_km' => (int) $this->input->post('current_km', TRUE),
                            'vin' => !empty($this->input->post('vin', TRUE)) ? strtoupper($this->input->post('vin', TRUE)) : NULL,
                            'transmission' => $this->input->post('transmission', TRUE) ?: 'manual',
                            'color' => $this->input->post('color', TRUE),
                            'notes' => $this->input->post('notes', TRUE)
                        ];
                        
                        if ($this->vehicle_model->update($id, $update_data)) {
                            $this->session->set_flashdata('success', 'Kendaraan berhasil diperbarui.');
                            redirect('user/vehicles');
                        } else {
                            $this->session->set_flashdata('error', 'Gagal memperbarui kendaraan.');
                        }
                    }
                }
            }
        }
        
        $data['vehicle'] = $vehicle;
        $data['brands'] = $this->vehicle_model->get_brands();
        $data['fuel_types'] = $this->vehicle_model->get_fuel_types();
        $data['transmissions'] = $this->vehicle_model->get_transmissions();
        $data['vehicle_types'] = $this->vehicle_model->get_vehicle_types();
        $data['years'] = range(date('Y') + 1, 1980);
        
        $this->render('user/vehicle_form', $data);
    }

    /**
     * Delete vehicle (soft delete - BR-61)
     * 
     * @param int $id Vehicle ID
     */
    public function vehicle_delete($id)
    {
        $this->load->model('vehicle_model');
        
        $vehicle = $this->vehicle_model->find_by_id($id);
        
        if (!$vehicle || $vehicle->user_id != $this->user_id) {
            $this->json_error('Kendaraan tidak ditemukan atau tidak ada akses.', 404);
            return;
        }
        
        // Check for active bookings (BR-61)
        $active_check = $this->vehicle_model->has_active_bookings($id);
        
        if ($active_check['has_active']) {
            $this->json_error(
                'Kendaraan memiliki ' . $active_check['count'] . ' pesanan aktif. Selesaikan pesanan terlebih dahulu sebelum menghapus kendaraan.',
                400,
                ['bookings' => $active_check['bookings']]
            );
            return;
        }
        
        if ($this->vehicle_model->soft_delete($id)) {
            $this->json_response(['deleted' => TRUE], 200, 'Kendaraan berhasil dihapus.');
        } else {
            $this->json_error('Gagal menghapus kendaraan.', 500);
        }
    }

    /**
     * Vehicle detail with service history tab
     * 
     * @param int $id Vehicle ID
     */
    public function vehicle_detail($id)
    {
        $data['page_title'] = 'Detail Kendaraan';
        
        $this->load->model('vehicle_model');
        
        $vehicle = $this->vehicle_model->find_by_id($id);
        
        if (!$vehicle || $vehicle->user_id != $this->user_id) {
            $this->session->set_flashdata('error', 'Kendaraan tidak ditemukan.');
            redirect('user/vehicles');
        }
        
        $data['vehicle'] = $vehicle;
        
        // Get service history
        $data['service_history'] = $this->vehicle_model->get_service_history($id);
        
        // Get service recommendation
        $data['recommendation'] = $this->vehicle_model->get_service_recommendation($id);
        
        $this->render('user/vehicle_detail', $data);
    }

    /**
     * AJAX: Check vehicle number availability (real-time validation)
     */
    public function check_vehicle_number()
    {
        $this->load->model('vehicle_model');
        
        $vehicle_number = $this->input->get('vehicle_number', TRUE);
        $exclude_id = $this->input->get('exclude_id', TRUE);
        
        if (empty($vehicle_number)) {
            $this->json_error('Nomor polisi tidak boleh kosong', 400);
            return;
        }
        
        $exists = $this->vehicle_model->vehicle_number_exists(
            $vehicle_number, 
            $this->user_id, 
            !empty($exclude_id) ? (int) $exclude_id : NULL
        );
        
        $this->json_response([
            'available' => !$exists,
            'normalized' => $this->vehicle_model->normalize_plate_number($vehicle_number)
        ], 200, $exists ? 'Nomor polisi sudah terdaftar' : 'Nomor polisi tersedia');
    }

    /**
     * AJAX: Validate year
     */
    public function validate_year()
    {
        $this->load->model('vehicle_model');
        
        $year = $this->input->get('year', TRUE);
        
        if (empty($year)) {
            $this->json_error('Tahun tidak boleh kosong', 400);
            return;
        }
        
        $validation = $this->vehicle_model->validate_year($year);
        
        $this->json_response([
            'valid' => $validation['valid']
        ], 200, $validation['message']);
    }

    /**
     * AJAX: Update odometer (with BR-60 validation)
     */
    public function update_odometer()
    {
        $this->load->model('vehicle_model');
        
        $vehicle_id = $this->input->post('vehicle_id', TRUE);
        $new_km = $this->input->post('current_km', TRUE);
        
        if (empty($vehicle_id) || empty($new_km)) {
            $this->json_error('Data tidak lengkap', 400);
            return;
        }
        
        $vehicle = $this->vehicle_model->find_by_id($vehicle_id);
        
        if (!$vehicle || $vehicle->user_id != $this->user_id) {
            $this->json_error('Kendaraan tidak ditemukan', 404);
            return;
        }
        
        $result = $this->vehicle_model->update_odometer($vehicle_id, $new_km);
        
        if ($result['success']) {
            $this->json_response(['current_km' => $new_km], 200, $result['message']);
        } else {
            $this->json_error($result['message'], 400);
        }
    }
}

/* End of file User.php */
/* Location: ./application/controllers/User.php */
