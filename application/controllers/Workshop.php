<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Workshop Controller
 * 
 * Handles workshop management for workshop owners and admin.
 * Implements CRUD for workshop profile and services.
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class Workshop extends Workshop_Controller {

    /**
     * Workshop model instance
     */
    private $workshop_model;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('workshop_model');
        $this->load->library('form_validation');
        $this->load->helper(['form', 'url', 'text']);
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
        $data['workshop'] = $this->workshop_model->get_by_owner($this->user_id);
        
        // Get services count
        if ($data['workshop']) {
            $data['services_count'] = $this->workshop_model->count_services($data['workshop']->id);
            $data['services'] = $this->workshop_model->get_services($data['workshop']->id);
        } else {
            $data['services_count'] = 0;
            $data['services'] = [];
        }

        // Statistics (placeholder - will be implemented with bookings)
        $data['stats'] = [
            'total_bookings' => 0,
            'pending_bookings' => 0,
            'completed_bookings' => 0,
             'total_reviews' => isset($data['workshop']) && isset($data['workshop']->total_reviews) ? $data['workshop']->total_reviews : 0,
            'avg_rating' => isset($data['workshop']) && isset($data['workshop']->rating_avg) ? $data['workshop']->rating_avg : 0
        ];

        $this->render('workshop/dashboard', $data);
    }

    // --------------------------------------------------------------------
    // Workshop Profile Management
    // --------------------------------------------------------------------

    /**
     * View workshop profile
     */
    public function profile()
    {
        $data['page_title'] = 'Profil Bengkel';
        $data['user'] = $this->current_user;
        $data['workshop'] = $this->workshop_model->get_by_owner($this->user_id);
        
        if (!$data['workshop']) {
            $this->session->set_flashdata('info', 'Anda belum memiliki data bengkel. Silakan buat profil bengkel terlebih dahulu.');
            redirect('workshop/create');
        }

        $data['services'] = $this->workshop_model->get_services($data['workshop']->id, FALSE);
        $data['categories'] = $this->workshop_model->get_service_categories();

        $this->render('workshop/profile', $data);
    }

    /**
     * Create new workshop profile
     */
    public function create()
    {
        $data['page_title'] = 'Buat Profil Bengkel';
        $data['user'] = $this->current_user;
        
        // Check if user already has a workshop
        $existing = $this->workshop_model->get_by_owner($this->user_id);
        if ($existing) {
            $this->session->set_flashdata('warning', 'Anda sudah memiliki profil bengkel.');
            redirect('workshop/profile');
        }

        if ($this->input->post()) {
            // Set validation rules
            $this->_set_workshop_validation_rules();

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                // Prepare workshop data
                $workshop_data = $this->_prepare_workshop_data();
                
                // Geocode address to get lat/lng
                $full_address = $workshop_data['address'] . ', ' . $workshop_data['city'] . ', ' . $workshop_data['province'];
                $geo_result = $this->workshop_model->geocode_address($full_address);
                
                if ($geo_result) {
                    $workshop_data['latitude'] = $geo_result['latitude'];
                    $workshop_data['longitude'] = $geo_result['longitude'];
                } else {
                    // Allow manual input if geocoding fails
                    $workshop_data['latitude'] = $this->input->post('latitude', TRUE) ?: 0;
                    $workshop_data['longitude'] = $this->input->post('longitude', TRUE) ?: 0;
                }

                $workshop_id = $this->workshop_model->create($workshop_data);

                if ($workshop_id) {
                    $this->session->set_flashdata('success', 'Profil bengkel berhasil dibuat. Menunggu verifikasi admin.');
                    redirect('workshop/profile');
                } else {
                    $this->session->set_flashdata('error', 'Gagal membuat profil bengkel. Silakan coba lagi.');
                }
            }
        }

        $data['categories'] = $this->workshop_model->get_service_categories();
        $this->render('workshop/create', $data);
    }

    /**
     * Edit workshop profile
     */
    public function edit()
    {
        $data['page_title'] = 'Edit Profil Bengkel';
        $data['user'] = $this->current_user;
        $data['workshop'] = $this->workshop_model->get_by_owner($this->user_id);

        if (!$data['workshop']) {
            $this->session->set_flashdata('error', 'Profil bengkel tidak ditemukan.');
            redirect('workshop/create');
        }

        if ($this->input->post()) {
            $this->_set_workshop_validation_rules();

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $workshop_data = $this->_prepare_workshop_data();
                
                // Handle logo upload
                if (!empty($_FILES['logo']['name'])) {
                    $config['upload_path'] = $this->config->item('upload_path_workshops');
                    $config['allowed_types'] = $this->config->item('allowed_types_workshops');
                    $config['max_size'] = $this->config->item('max_size_workshops');
                    $config['file_name'] = 'logo_' . $data['workshop']->id . '_' . time();

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('logo')) {
                        $upload_data = $this->upload->data();
                        
                        // Delete old logo if exists
                        if ($data['workshop']->logo && file_exists(FCPATH . $data['workshop']->logo)) {
                            unlink(FCPATH . $data['workshop']->logo);
                        }

                        $workshop_data['logo'] = 'uploads/workshops/' . $upload_data['file_name'];
                    } else {
                        $this->session->set_flashdata('error', $this->upload->display_errors());
                    }
                }

                // Geocode address if changed
                $full_address = $workshop_data['address'] . ', ' . $workshop_data['city'] . ', ' . $workshop_data['province'];
                if ($full_address !== $data['workshop']->address . ', ' . $data['workshop']->city . ', ' . $data['workshop']->province) {
                    $geo_result = $this->workshop_model->geocode_address($full_address);
                    
                    if ($geo_result) {
                        $workshop_data['latitude'] = $geo_result['latitude'];
                        $workshop_data['longitude'] = $geo_result['longitude'];
                    }
                }

                if ($this->workshop_model->update($data['workshop']->id, $workshop_data)) {
                    $this->session->set_flashdata('success', 'Profil bengkel berhasil diperbarui.');
                    redirect('workshop/profile');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui profil bengkel.');
                }
            }
        }

        $data['categories'] = $this->workshop_model->get_service_categories();
        $this->render('workshop/edit', $data);
    }

    /**
     * Delete workshop (soft delete)
     */
    public function delete()
    {
        $workshop = $this->workshop_model->get_by_owner($this->user_id);

        if (!$workshop) {
            $this->json_error('Workshop tidak ditemukan.', 404);
            return;
        }

        if ($this->workshop_model->delete($workshop->id)) {
            $this->json_response(['message' => 'Workshop berhasil dihapus.'], 200, 'Success');
        } else {
            $this->json_error('Gagal menghapus workshop.', 500);
        }
    }

    // --------------------------------------------------------------------
    // Workshop Services Management
    // --------------------------------------------------------------------

    /**
     * Add new service
     */
    public function add_service()
    {
        $workshop = $this->workshop_model->get_by_owner($this->user_id);

        if (!$workshop) {
            $this->session->set_flashdata('error', 'Anda harus membuat profil bengkel terlebih dahulu.');
            redirect('workshop/create');
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('service_name', 'Nama Layanan', 'required|trim|max_length[150]');
            $this->form_validation->set_rules('service_category', 'Kategori', 'required|in_list[sparepart,servis,cat,ban,aki,tuning,lainnya]');
            $this->form_validation->set_rules('price_min', 'Harga Minimum', 'required|numeric|greater_than_equal_to[0]');
            $this->form_validation->set_rules('price_max', 'Harga Maximum', 'numeric|greater_than_equal_to[0]');
            $this->form_validation->set_rules('duration_minutes', 'Durasi (menit)', 'integer|greater_than_equal_to[0]');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $service_data = [
                    'workshop_id' => $workshop->id,
                    'service_name' => $this->input->post('service_name', TRUE),
                    'service_category' => $this->input->post('service_category', TRUE),
                    'description' => $this->input->post('description', TRUE),
                    'price_min' => $this->input->post('price_min', TRUE),
                    'price_max' => $this->input->post('price_max', TRUE) ?: $this->input->post('price_min', TRUE),
                    'unit' => $this->input->post('unit', TRUE) ?: 'fixed',
                    'duration_minutes' => $this->input->post('duration_minutes', TRUE) ?: 60,
                    'is_available' => $this->input->post('is_available', TRUE) ? 1 : 0
                ];

                $service_id = $this->workshop_model->insert_service($service_data);

                if ($service_id) {
                    $this->session->set_flashdata('success', 'Layanan berhasil ditambahkan.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan layanan.');
                }
            }
        }

        redirect('workshop/services');
    }

    /**
     * Edit service
     */
    public function edit_service($id)
    {
        $workshop = $this->workshop_model->get_by_owner($this->user_id);

        if (!$workshop) {
            $this->json_error('Workshop tidak ditemukan.', 404);
            return;
        }

        $service = $this->workshop_model->get_service_by_id($id);

        if (!$service || $service->workshop_id != $workshop->id) {
            $this->json_error('Layanan tidak ditemukan.', 404);
            return;
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('service_name', 'Nama Layanan', 'required|trim|max_length[150]');
            $this->form_validation->set_rules('service_category', 'Kategori', 'required|in_list[sparepart,servis,cat,ban,aki,tuning,lainnya]');
            $this->form_validation->set_rules('price_min', 'Harga Minimum', 'required|numeric|greater_than_equal_to[0]');
            $this->form_validation->set_rules('price_max', 'Harga Maximum', 'numeric|greater_than_equal_to[0]');

            if ($this->form_validation->run() === FALSE) {
                $this->json_error(validation_errors(), 400);
                return;
            }

            $service_data = [
                'service_name' => $this->input->post('service_name', TRUE),
                'service_category' => $this->input->post('service_category', TRUE),
                'description' => $this->input->post('description', TRUE),
                'price_min' => $this->input->post('price_min', TRUE),
                'price_max' => $this->input->post('price_max', TRUE) ?: $this->input->post('price_min', TRUE),
                'unit' => $this->input->post('unit', TRUE) ?: 'fixed',
                'duration_minutes' => $this->input->post('duration_minutes', TRUE) ?: 60,
                'is_available' => $this->input->post('is_available', TRUE) ? 1 : 0
            ];

            if ($this->workshop_model->update_service($id, $service_data)) {
                $this->json_response(['message' => 'Layanan berhasil diperbarui.'], 200, 'Success');
            } else {
                $this->json_error('Gagal memperbarui layanan.', 500);
            }
        } else {
            $this->json_response($service, 200, 'Success');
        }
    }

    /**
     * Delete service (soft delete)
     */
    public function delete_service($id)
    {
        $workshop = $this->workshop_model->get_by_owner($this->user_id);

        if (!$workshop) {
            $this->json_error('Workshop tidak ditemukan.', 404);
            return;
        }

        $service = $this->workshop_model->get_service_by_id($id);

        if (!$service || $service->workshop_id != $workshop->id) {
            $this->json_error('Layanan tidak ditemukan.', 404);
            return;
        }

        if ($this->workshop_model->delete_service($id)) {
            $this->json_response(['message' => 'Layanan berhasil dihapus.'], 200, 'Success');
        } else {
            $this->json_error('Gagal menghapus layanan.', 500);
        }
    }

    /**
     * Toggle service availability
     */
    public function toggle_service($id)
    {
        $workshop = $this->workshop_model->get_by_owner($this->user_id);

        if (!$workshop) {
            $this->json_error('Workshop tidak ditemukan.', 404);
            return;
        }

        $service = $this->workshop_model->get_service_by_id($id);

        if (!$service || $service->workshop_id != $workshop->id) {
            $this->json_error('Layanan tidak ditemukan.', 404);
            return;
        }

        $new_status = $service->is_available ? 0 : 1;

        if ($this->workshop_model->update_service($id, ['is_available' => $new_status])) {
            $this->json_response(['is_available' => $new_status], 200, 'Success');
        } else {
            $this->json_error('Gagal mengubah status layanan.', 500);
        }
    }

    /**
     * View all services
     */
    public function services()
    {
        $data['page_title'] = 'Kelola Layanan';
        $data['user'] = $this->current_user;
        $data['workshop'] = $this->workshop_model->get_by_owner($this->user_id);

        if (!$data['workshop']) {
            $this->session->set_flashdata('error', 'Anda harus membuat profil bengkel terlebih dahulu.');
            redirect('workshop/create');
        }

        $data['services'] = $this->workshop_model->get_services($data['workshop']->id, FALSE);
        $data['categories'] = $this->workshop_model->get_service_categories();

        $this->render('workshop/services', $data);
    }

    // --------------------------------------------------------------------
    // Helper Methods
    // --------------------------------------------------------------------

    /**
     * Set validation rules for workshop form
     */
    private function _set_workshop_validation_rules()
    {
        $this->form_validation->set_rules('name', 'Nama Bengkel', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('description', 'Deskripsi', 'trim|max_length[1000]');
        $this->form_validation->set_rules('address', 'Alamat Lengkap', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('city', 'Kota/Kabupaten', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('province', 'Provinsi', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('postal_code', 'Kode Pos', 'trim|max_length[10]');
        $this->form_validation->set_rules('phone', 'Telepon', 'trim|max_length[20]');
        $this->form_validation->set_rules('whatsapp', 'WhatsApp', 'trim|max_length[20]');
    }

    /**
     * Prepare workshop data from POST
     * @return array Workshop data
     */
    private function _prepare_workshop_data()
    {
        return [
            'user_id' => $this->user_id,
            'name' => $this->input->post('name', TRUE),
            'description' => $this->input->post('description', TRUE),
            'address' => $this->input->post('address', TRUE),
            'city' => $this->input->post('city', TRUE),
            'province' => $this->input->post('province', TRUE),
            'postal_code' => $this->input->post('postal_code', TRUE),
            'phone' => $this->input->post('phone', TRUE),
            'whatsapp' => $this->input->post('whatsapp', TRUE),
            'status' => 'pending', // Default to pending, admin will approve
            'operating_hours' => json_encode([
                'monday' => ['open' => $this->input->post('open_monday', TRUE) ?: '08:00', 'close' => $this->input->post('close_monday', TRUE) ?: '17:00'],
                'tuesday' => ['open' => $this->input->post('open_tuesday', TRUE) ?: '08:00', 'close' => $this->input->post('close_tuesday', TRUE) ?: '17:00'],
                'wednesday' => ['open' => $this->input->post('open_wednesday', TRUE) ?: '08:00', 'close' => $this->input->post('close_wednesday', TRUE) ?: '17:00'],
                'thursday' => ['open' => $this->input->post('open_thursday', TRUE) ?: '08:00', 'close' => $this->input->post('close_thursday', TRUE) ?: '17:00'],
                'friday' => ['open' => $this->input->post('open_friday', TRUE) ?: '08:00', 'close' => $this->input->post('close_friday', TRUE) ?: '17:00'],
                'saturday' => ['open' => $this->input->post('open_saturday', TRUE) ?: '08:00', 'close' => $this->input->post('close_saturday', TRUE) ?: '15:00'],
                'sunday' => null
            ]),
            'services_offered' => json_encode($this->input->post('services_offered', TRUE) ?: [])
        ];
    }
}

/* End of file Workshop.php */
/* Location: ./application/controllers/Workshop.php */
