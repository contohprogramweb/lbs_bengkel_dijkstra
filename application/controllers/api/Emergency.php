<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Emergency Controller
 * 
 * RESTful API endpoints for emergency requests (SRS v4.0 Section 5.6)
 * Handles emergency roadside assistance requests
 * BR-73: Radius 5km, BR-74: Auto-close 2 jam, BR-75: 1 request aktif, BR-76: Rate limit 3/jam
 */
class Emergency extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('emergency_model');
        
        // Require authentication for all API calls
        if (!$this->is_logged_in()) {
            $this->json_error('Authentication required.', 401);
            return;
        }
    }

    /**
     * POST /api/emergency/request
     * Create emergency request
     * BR-73: Workshop radius 5km, BR-75: Max 1 active request per user
     */
    public function request()
    {
        // BR-75: Check if user already has active emergency request
        $active_request = $this->db
            ->where('user_id', $this->user_id)
            ->where('status', 'pending')
            ->where('is_deleted', 0)
            ->get('emergency_requests')
            ->row_array();

        if ($active_request) {
            $this->json_error('Anda sudah memiliki permintaan darurat yang aktif. Selesaikan terlebih dahulu sebelum membuat baru.', 409);
            return;
        }

        // BR-76: Rate limit - max 3 requests per hour per IP
        $ip_address = $this->input->ip_address();
        $one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $recent_requests = $this->db
            ->where('ip_address', $ip_address)
            ->where('created_at >=', $one_hour_ago)
            ->from('emergency_requests')
            ->count_all_results();

        if ($recent_requests >= 3) {
            $this->json_error('Rate limit: Maksimal 3 permintaan darurat per jam.', 429);
            return;
        }

        $latitude = $this->input->post('latitude', TRUE);
        $longitude = $this->input->post('longitude', TRUE);
        $vehicle_type = $this->input->post('vehicle_type', TRUE);
        $issue_description = $this->input->post('issue_description', TRUE);
        $phone = $this->input->post('phone', TRUE);

        // Validation
        $this->form_validation->set_rules('latitude', 'Latitude', 'required|numeric');
        $this->form_validation->set_rules('longitude', 'Longitude', 'required|numeric');
        $this->form_validation->set_rules('vehicle_type', 'Tipe Kendaraan', 'required|in_list[motorcycle,car,truck,bus,other]');
        $this->form_validation->set_rules('issue_description', 'Deskripsi Masalah', 'required|trim|min_length[20]');
        $this->form_validation->set_rules('phone', 'Nomor Telepon', 'required|trim|max_length[20]');

        if ($this->form_validation->run() === FALSE) {
            $this->json_error(validation_errors(), 400);
            return;
        }

        // Generate request number
        $request_number = 'EMG-' . date('YmdHis') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $emergency_data = [
            'request_number' => $request_number,
            'user_id' => $this->user_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $this->input->post('address', TRUE),
            'vehicle_type' => $vehicle_type,
            'issue_description' => $issue_description,
            'phone' => $phone,
            'status' => 'pending',
            'ip_address' => $ip_address
        ];

        $request_id = $this->emergency_model->create_request($emergency_data);

        if ($request_id) {
            // Find nearby workshops within 5km radius (BR-73)
            $nearby_workshops = $this->emergency_model->find_nearby_workshops($latitude, $longitude, 5);
            
            // Notify nearby workshops
            foreach ($nearby_workshops as $workshop) {
                $this->emergency_model->notify_workshop($workshop['id'], $request_id);
            }

            $this->json_response([
                'success' => true,
                'data' => [
                    'request_id' => $request_id,
                    'request_number' => $request_number,
                    'nearby_workshops_count' => count($nearby_workshops)
                ]
            ], 201, 'Permintaan darurat berhasil dibuat. Bengkel terdekat akan dihubungi.');
        } else {
            $this->json_error('Gagal membuat permintaan darurat.', 500);
        }
    }

    /**
     * GET /api/emergency/status/{request_number}
     * Get emergency request status
     */
    public function status($request_number)
    {
        $request = $this->db
            ->where('request_number', $request_number)
            ->where('user_id', $this->user_id)
            ->where('is_deleted', 0)
            ->get('emergency_requests')
            ->row_array();

        if (!$request) {
            $this->json_error('Permintaan darurat tidak ditemukan.', 404);
            return;
        }

        // Get assigned workshop info if any
        $workshop_info = null;
        if ($request['assigned_workshop_id']) {
            $workshop_info = $this->db
                ->select('id, name, address, phone, latitude, longitude')
                ->where('id', $request['assigned_workshop_id'])
                ->get('workshops')
                ->row_array();
        }

        $this->json_response([
            'success' => true,
            'data' => [
                'request' => $request,
                'workshop' => $workshop_info
            ]
        ], 200, 'Status retrieved successfully.');
    }

    /**
     * POST /api/emergency/cancel/{request_number}
     * Cancel emergency request
     */
    public function cancel($request_number)
    {
        $request = $this->db
            ->where('request_number', $request_number)
            ->where('user_id', $this->user_id)
            ->where('is_deleted', 0)
            ->get('emergency_requests')
            ->row_array();

        if (!$request) {
            $this->json_error('Permintaan darurat tidak ditemukan.', 404);
            return;
        }

        // Can only cancel pending requests
        if ($request['status'] !== 'pending') {
            $this->json_error('Permintaan dengan status "' . $request['status'] . '" tidak dapat dibatalkan.', 400);
            return;
        }

        $cancellation_reason = $this->input->post('cancellation_reason', TRUE);

        $update_data = [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'cancellation_reason' => $cancellation_reason
        ];

        $this->db->where('id', $request['id'])->update('emergency_requests', $update_data);

        $this->json_response([
            'success' => true
        ], 200, 'Permintaan darurat berhasil dibatalkan.');
    }

    /**
     * GET /api/emergency/nearby
     * Get nearby emergency workshops (for display purposes)
     */
    public function nearby()
    {
        $latitude = $this->input->get('latitude', TRUE);
        $longitude = $this->input->get('longitude', TRUE);
        $radius = $this->input->get('radius', TRUE) ?? 5; // Default 5km (BR-73)

        if (!$latitude || !$longitude) {
            $this->json_error('Latitude dan longitude diperlukan.', 400);
            return;
        }

        $workshops = $this->emergency_model->find_nearby_workshops($latitude, $longitude, $radius);

        $this->json_response([
            'success' => true,
            'data' => [
                'search_location' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ],
                'radius_km' => $radius,
                'workshops' => $workshops
            ]
        ], 200, 'Nearby workshops retrieved successfully.');
    }
}
