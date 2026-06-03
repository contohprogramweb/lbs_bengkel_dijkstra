<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Vehicle Controller
 * 
 * RESTful API endpoints for vehicle management (SRS v4.0 Section 5.6)
 * Handles CRUD operations for user vehicles
 */
class Vehicle extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('vehicle_model');
        
        // Require authentication for all API calls
        if (!$this->is_logged_in()) {
            $this->json_error('Authentication required.', 401);
            return;
        }
    }

    /**
     * GET /api/vehicle/list
     * Get all vehicles for current user
     */
    public function list()
    {
        $vehicles = $this->vehicle_model->get_user_vehicles($this->user_id);
        $this->json_response([
            'success' => true,
            'data' => $vehicles,
            'count' => count($vehicles)
        ], 200, 'Vehicle list retrieved successfully.');
    }

    /**
     * GET /api/vehicle/detail/{id}
     * Get single vehicle details
     */
    public function detail($id)
    {
        $vehicle = $this->vehicle_model->get_vehicle_by_id($id, $this->user_id);
        
        if (!$vehicle) {
            $this->json_error('Vehicle not found or access denied.', 404);
            return;
        }

        $this->json_response([
            'success' => true,
            'data' => $vehicle
        ], 200, 'Vehicle details retrieved successfully.');
    }

    /**
     * POST /api/vehicle/create
     * Create new vehicle (BR-58: max 5 vehicles per user)
     */
    public function create()
    {
        // Check vehicle limit (BR-58)
        $current_count = $this->vehicle_model->count_user_vehicles($this->user_id);
        if ($current_count >= 5) {
            $this->json_error('Maksimal 5 kendaraan per pengguna (BR-58).', 400);
            return;
        }

        $data = [
            'user_id' => $this->user_id,
            'vehicle_number' => $this->input->post('vehicle_number', TRUE),
            'vehicle_type' => $this->input->post('vehicle_type', TRUE),
            'brand' => $this->input->post('brand', TRUE),
            'model' => $this->input->post('model', TRUE),
            'year' => $this->input->post('year', TRUE),
            'color' => $this->input->post('color', TRUE),
            'transmission' => $this->input->post('transmission', TRUE),
            'fuel_type' => $this->input->post('fuel_type', TRUE),
            'last_service_date' => $this->input->post('last_service_date', TRUE),
            'last_service_km' => $this->input->post('last_service_km', TRUE),
            'current_km' => $this->input->post('current_km', TRUE),
            'notes' => $this->input->post('notes', TRUE),
            'is_primary' => $this->input->post('is_primary', TRUE) ? 1 : 0
        ];

        // Validation
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('vehicle_number', 'Nomor Polisi', 'required|trim|max_length[20]');
        $this->form_validation->set_rules('vehicle_type', 'Tipe Kendaraan', 'required|in_list[motorcycle,car,truck,bus,other]');
        $this->form_validation->set_rules('brand', 'Merk', 'required|trim|max_length[50]');

        if ($this->form_validation->run() === FALSE) {
            $this->json_error(validation_errors(), 400);
            return;
        }

        $vehicle_id = $this->vehicle_model->create_vehicle($data);

        if ($vehicle_id) {
            $this->json_response([
                'success' => true,
                'data' => ['id' => $vehicle_id]
            ], 201, 'Kendaraan berhasil ditambahkan.');
        } else {
            $this->json_error('Gagal menambahkan kendaraan.', 500);
        }
    }

    /**
     * PUT /api/vehicle/update/{id}
     * Update existing vehicle
     */
    public function update($id)
    {
        $vehicle = $this->vehicle_model->get_vehicle_by_id($id, $this->user_id);
        
        if (!$vehicle) {
            $this->json_error('Vehicle not found or access denied.', 404);
            return;
        }

        $data = [
            'vehicle_number' => $this->input->post('vehicle_number', TRUE),
            'vehicle_type' => $this->input->post('vehicle_type', TRUE),
            'brand' => $this->input->post('brand', TRUE),
            'model' => $this->input->post('model', TRUE),
            'year' => $this->input->post('year', TRUE),
            'color' => $this->input->post('color', TRUE),
            'transmission' => $this->input->post('transmission', TRUE),
            'fuel_type' => $this->input->post('fuel_type', TRUE),
            'last_service_date' => $this->input->post('last_service_date', TRUE),
            'last_service_km' => $this->input->post('last_service_km', TRUE),
            'current_km' => $this->input->post('current_km', TRUE),
            'notes' => $this->input->post('notes', TRUE)
        ];

        // Validation
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('vehicle_number', 'Nomor Polisi', 'required|trim|max_length[20]');
        $this->form_validation->set_rules('vehicle_type', 'Tipe Kendaraan', 'required|in_list[motorcycle,car,truck,bus,other]');
        $this->form_validation->set_rules('brand', 'Merk', 'required|trim|max_length[50]');

        if ($this->form_validation->run() === FALSE) {
            $this->json_error(validation_errors(), 400);
            return;
        }

        if ($this->vehicle_model->update_vehicle($id, $data)) {
            $this->json_response([
                'success' => true,
                'data' => ['id' => $id]
            ], 200, 'Kendaraan berhasil diperbarui.');
        } else {
            $this->json_error('Gagal memperbarui kendaraan.', 500);
        }
    }

    /**
     * DELETE /api/vehicle/delete/{id}
     * Soft delete vehicle (BR-59)
     */
    public function delete($id)
    {
        $vehicle = $this->vehicle_model->get_vehicle_by_id($id, $this->user_id);
        
        if (!$vehicle) {
            $this->json_error('Vehicle not found or access denied.', 404);
            return;
        }

        if ($this->vehicle_model->soft_delete_vehicle($id)) {
            $this->json_response([
                'success' => true
            ], 200, 'Kendaraan berhasil dihapus.');
        } else {
            $this->json_error('Gagal menghapus kendaraan.', 500);
        }
    }

    /**
     * POST /api/vehicle/set_primary/{id}
     * Set vehicle as primary
     */
    public function set_primary($id)
    {
        $vehicle = $this->vehicle_model->get_vehicle_by_id($id, $this->user_id);
        
        if (!$vehicle) {
            $this->json_error('Vehicle not found or access denied.', 404);
            return;
        }

        if ($this->vehicle_model->set_primary_vehicle($id, $this->user_id)) {
            $this->json_response([
                'success' => true
            ], 200, 'Kendaraan utama berhasil diubah.');
        } else {
            $this->json_error('Gagal mengubah kendaraan utama.', 500);
        }
    }
}
