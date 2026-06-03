<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mechanic Management Controller (Workshop Owner Side)
 * 
 * Handles mechanic management for workshop owners:
 * - CRUD mechanics (FR-MEC-01)
 * - Assign mechanics to bookings (UC-WRK-07, FR-MEC-02)
 * - Check availability/overlapping schedules (BR-76)
 * - Productivity reports (FR-MEC-03)
 * - Optional assignment (BR-77)
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Controllers
 * @version     4.1
 */
class Mechanic extends Workshop_Controller {

    /**
     * Mechanic model instance
     */
    private $mechanic_model;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('mechanic_model');
        $this->load->library('form_validation');
        $this->load->helper(['text', 'date']);
    }

    // ================================================================
    // DASHBOARD & LISTINGS
    // ================================================================

    /**
     * Dashboard - Overview of all mechanics
     */
    public function index()
    {
        $data['page_title'] = 'Manajemen Mekanik';
        
        // Get workshop ID for current owner
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if (!$workshop) {
            $this->session->set_flashdata('error', 'Anda harus membuat profil bengkel terlebih dahulu.');
            redirect('workshop/create');
        }
        
        $data['workshop'] = $workshop;
        
        // Get all mechanics
        $data['mechanics'] = $this->mechanic_model->get_by_workshop($workshop->id);
        
        // Get statistics
        $data['stats'] = [
            'total' => count($data['mechanics']),
            'available' => count(array_filter($data['mechanics'], fn($m) => $m['is_available'] == 1)),
            'unavailable' => count(array_filter($data['mechanics'], fn($m) => $m['is_available'] == 0))
        ];
        
        $this->render('workshop/mechanics/index', $data);
    }

    /**
     * Create new mechanic form
     */
    public function create()
    {
        $data['page_title'] = 'Tambah Mekanik';
        
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if (!$workshop) {
            $this->session->set_flashdata('error', 'Profil bengkel tidak ditemukan.');
            redirect('mechanic');
        }
        
        $data['workshop'] = $workshop;
        $data['specializations'] = ['mesin', 'kelistrikan', 'body', 'ban', 'oli', 'ac'];
        
        $this->render('workshop/mechanics/form', $data);
    }

    /**
     * Store new mechanic
     */
    public function store()
    {
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if (!$workshop) {
            $this->json_error('Profil bengkel tidak ditemukan', 400);
            return;
        }

        // Validate input
        $this->form_validation->set_rules('user_id', 'User', 'required|integer');
        $this->form_validation->set_rules('specialization[]', 'Spesialisasi', 'required');
        $this->form_validation->set_rules('experience_years', 'Pengalaman (tahun)', 'integer|greater_than_equal_to[0]');
        
        if ($this->form_validation->run() === FALSE) {
            $this->json_error(validation_errors(), 400);
            return;
        }

        $user_id = $this->input->post('user_id', TRUE);
        $specialization = $this->input->post('specialization', TRUE);
        $experience_years = $this->input->post('experience_years', TRUE) ?? 0;
        $certification = $this->input->post('certification', TRUE);
        $is_available = $this->input->post('is_available', TRUE) ?? 1;

        // Create mechanic
        $result = $this->mechanic_model->create_mechanic([
            'user_id' => $user_id,
            'workshop_id' => $workshop->id,
            'specialization' => $specialization,
            'experience_years' => $experience_years,
            'certification' => $certification,
            'is_available' => $is_available
        ]);

        if ($result['success']) {
            $this->json_response([
                'redirect' => site_url('mechanic')
            ], 200, $result['message']);
        } else {
            $this->json_error($result['message'], 400);
        }
    }

    /**
     * Edit mechanic form
     * @param int $id
     */
    public function edit($id)
    {
        $mechanic = $this->mechanic_model->find_by_id($id);
        
        if (!$mechanic) {
            $this->session->set_flashdata('error', 'Mekanik tidak ditemukan.');
            redirect('mechanic');
        }
        
        // Verify workshop ownership
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if ($mechanic['workshop_id'] != $workshop->id) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('mechanic');
        }
        
        $data['page_title'] = 'Edit Mekanik';
        $data['mechanic'] = $mechanic;
        $data['workshop'] = $workshop;
        $data['specializations'] = ['mesin', 'kelistrikan', 'body', 'ban', 'oli', 'ac'];
        
        $this->render('workshop/mechanics/form', $data);
    }

    /**
     * Update mechanic
     * @param int $id
     */
    public function update($id)
    {
        $mechanic = $this->mechanic_model->find_by_id($id);
        
        if (!$mechanic) {
            $this->json_error('Mekanik tidak ditemukan', 400);
            return;
        }
        
        // Verify workshop ownership
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if ($mechanic['workshop_id'] != $workshop->id) {
            $this->json_error('Akses ditolak', 403);
            return;
        }

        // Validate input
        $this->form_validation->set_rules('specialization[]', 'Spesialisasi', 'required');
        $this->form_validation->set_rules('experience_years', 'Pengalaman (tahun)', 'integer|greater_than_equal_to[0]');
        
        if ($this->form_validation->run() === FALSE) {
            $this->json_error(validation_errors(), 400);
            return;
        }

        $update_data = [
            'specialization' => $this->input->post('specialization', TRUE),
            'experience_years' => $this->input->post('experience_years', TRUE) ?? 0,
            'certification' => $this->input->post('certification', TRUE),
            'is_available' => $this->input->post('is_available', TRUE) ?? 1
        ];

        $result = $this->mechanic_model->update_mechanic($id, $update_data);

        if ($result) {
            $this->json_response([
                'redirect' => site_url('mechanic')
            ], 200, 'Data mekanik berhasil diperbarui');
        } else {
            $this->json_error('Gagal memperbarui data mekanik', 500);
        }
    }

    /**
     * Delete mechanic (soft delete)
     * @param int $id
     */
    public function delete($id)
    {
        $mechanic = $this->mechanic_model->find_by_id($id);
        
        if (!$mechanic) {
            $this->json_error('Mekanik tidak ditemukan', 400);
            return;
        }
        
        // Verify workshop ownership
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if ($mechanic['workshop_id'] != $workshop->id) {
            $this->json_error('Akses ditolak', 403);
            return;
        }

        $result = $this->mechanic_model->delete_mechanic($id);

        if ($result) {
            $this->json_response([], 200, 'Mekanik berhasil dihapus');
        } else {
            $this->json_error('Gagal menghapus mekanik', 500);
        }
    }

    /**
     * Toggle mechanic availability
     * @param int $id
     */
    public function toggle_availability($id)
    {
        $mechanic = $this->mechanic_model->find_by_id($id);
        
        if (!$mechanic) {
            $this->json_error('Mekanik tidak ditemukan', 400);
            return;
        }
        
        // Verify workshop ownership
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if ($mechanic['workshop_id'] != $workshop->id) {
            $this->json_error('Akses ditolak', 403);
            return;
        }

        $new_status = $mechanic['is_available'] ? 0 : 1;
        $result = $this->mechanic_model->update_mechanic($id, ['is_available' => $new_status]);

        if ($result) {
            $this->json_response([
                'is_available' => $new_status
            ], 200, 'Status ketersediaan berhasil diubah');
        } else {
            $this->json_error('Gagal mengubah status', 500);
        }
    }

    // ================================================================
    // MECHANIC ASSIGNMENT TO BOOKINGS (UC-WRK-07)
    // ================================================================

    /**
     * Assign mechanics to a booking
     * Called from booking detail modal
     */
    public function assign_to_booking()
    {
        $booking_id = $this->input->post('booking_id', TRUE);
        $mechanic_ids = $this->input->post('mechanic_ids', TRUE);
        $notes = $this->input->post('notes', TRUE);

        if (!$booking_id) {
            $this->json_error('ID pesanan diperlukan', 400);
            return;
        }

        if (empty($mechanic_ids)) {
            // BR-77: Assignment is optional, allow empty
            $this->json_response([], 200, 'Pesanan dapat diproses tanpa mekanik');
            return;
        }

        // Verify workshop ownership
        $this->load->model('booking_model');
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking) {
            $this->json_error('Pesanan tidak ditemukan', 400);
            return;
        }

        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if ($booking['workshop_id'] != $workshop->id) {
            $this->json_error('Akses ditolak', 403);
            return;
        }

        // Only allow assignment for accepted/processed bookings
        if (!in_array($booking['status'], ['accepted', 'processed', 'in_progress'])) {
            $this->json_error('Penugasan hanya tersedia untuk pesanan yang sedang diproses', 400);
            return;
        }

        // Assign mechanics
        $result = $this->mechanic_model->assign_mechanics(
            $booking_id,
            $mechanic_ids,
            $this->user_id,
            $notes
        );

        if ($result['success']) {
            // Log activity
            $mechanic_count = count($mechanic_ids);
            $this->booking_model->log_activity(
                $booking_id,
                'mechanics_assigned',
                "{$mechanic_count} mekanik ditugaskan",
                $this->user_id
            );

            $this->json_response([
                'redirect' => site_url('order/detail/' . $booking_id)
            ], 200, $result['message']);
        } else {
            $this->json_error($result['message'], 400, [
                'conflicts' => $result['conflicts']
            ]);
        }
    }

    /**
     * Remove mechanic from booking
     */
    public function remove_from_booking()
    {
        $booking_id = $this->input->post('booking_id', TRUE);
        $mechanic_id = $this->input->post('mechanic_id', TRUE);

        if (!$booking_id || !$mechanic_id) {
            $this->json_error('Data tidak lengkap', 400);
            return;
        }

        // Verify workshop ownership
        $this->load->model('booking_model');
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking) {
            $this->json_error('Pesanan tidak ditemukan', 400);
            return;
        }

        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if ($booking['workshop_id'] != $workshop->id) {
            $this->json_error('Akses ditolak', 403);
            return;
        }

        $result = $this->mechanic_model->remove_mechanic($booking_id, $mechanic_id);

        if ($result) {
            // Log activity
            $this->booking_model->log_activity(
                $booking_id,
                'mechanic_removed',
                'Mekanik dilepas dari tugas',
                $this->user_id
            );

            $this->json_response([], 200, 'Mekanik berhasil dilepas dari pesanan');
        } else {
            $this->json_error('Gagal melepas mekanik', 500);
        }
    }

    /**
     * Get available mechanics for a booking (AJAX)
     * Returns mechanics without schedule conflicts
     */
    public function get_available_for_booking($booking_id)
    {
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking) {
            $this->json_error('Pesanan tidak ditemukan', 400);
            return;
        }

        // Verify workshop ownership
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if ($booking['workshop_id'] != $workshop->id) {
            $this->json_error('Akses ditolak', 403);
            return;
        }

        // Get all active mechanics
        $mechanics = $this->mechanic_model->get_active_mechanics($workshop->id);

        // Check availability for each
        $available = [];
        foreach ($mechanics as $mech) {
            $conflicts = $this->mechanic_model->check_mechanic_availability(
                [$mech['id']],
                $booking['scheduled_date'],
                $booking['scheduled_time'],
                $booking['estimated_duration'] ?? 60,
                $booking_id
            );

            $mech['has_conflict'] = !empty($conflicts);
            $mech['conflicts'] = $conflicts;
            $available[] = $mech;
        }

        $this->json_response(['mechanics' => $available]);
    }

    // ================================================================
    // PRODUCTIVITY REPORTING (FR-MEC-03)
    // ================================================================

    /**
     * Productivity report dashboard
     */
    public function productivity()
    {
        $data['page_title'] = 'Laporan Produktivitas Mekanik';
        
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if (!$workshop) {
            $this->session->set_flashdata('error', 'Profil bengkel tidak ditemukan.');
            redirect('mechanic');
        }
        
        $data['workshop'] = $workshop;
        
        // Get date range from input
        $start_date = $this->input->get('start_date', TRUE) ?? date('Y-m-01');
        $end_date = $this->input->get('end_date', TRUE) ?? date('Y-m-t');
        
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        
        // Get productivity data
        $data['productivity'] = $this->mechanic_model->get_productivity_report($workshop->id, $start_date, $end_date);
        $data['summary'] = $this->mechanic_model->get_mechanic_stats_summary($workshop->id, $start_date, $end_date);
        
        $this->render('workshop/mechanics/productivity', $data);
    }

    /**
     * View mechanic detail with booking history
     * @param int $id
     */
    public function detail($id)
    {
        $mechanic = $this->mechanic_model->find_by_id($id);
        
        if (!$mechanic) {
            $this->session->set_flashdata('error', 'Mekanik tidak ditemukan.');
            redirect('mechanic');
        }
        
        // Verify workshop ownership
        $this->load->model('workshop_model');
        $workshop = $this->workshop_model->get_by_owner($this->user_id);
        
        if ($mechanic['workshop_id'] != $workshop->id) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('mechanic');
        }
        
        $data['page_title'] = 'Detail Mekanik: ' . $mechanic['name'];
        $data['mechanic'] = $mechanic;
        $data['workshop'] = $workshop;
        
        // Get assigned bookings
        $filters = [
            'start_date' => $this->input->get('start_date', TRUE),
            'end_date' => $this->input->get('end_date', TRUE),
            'status' => $this->input->get('status', TRUE)
        ];
        
        $data['bookings'] = $this->mechanic_model->get_mechanic_bookings($id, $filters);
        
        // Get stats
        $data['stats'] = [
            'total_bookings' => count($data['bookings']),
            'completed' => count(array_filter($data['bookings'], fn($b) => $b['status'] === 'completed'))
        ];
        
        $this->render('workshop/mechanics/detail', $data);
    }
}
