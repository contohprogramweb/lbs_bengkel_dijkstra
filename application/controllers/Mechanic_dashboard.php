<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mechanic Dashboard Controller
 * 
 * Handles mechanic-specific features:
 * - View assigned bookings
 * - Update work status
 * - Add work notes
 * - View personal productivity
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Controllers
 * @version     4.1
 */
class Mechanic_dashboard extends Mechanic_Controller {

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('booking_model');
        $this->load->model('mechanic_model');
        $this->load->helper(['text', 'date']);
    }

    /**
     * Dashboard - Overview for mechanic
     */
    public function index()
    {
        $this->dashboard();
    }

    /**
     * Main dashboard page
     */
    public function dashboard()
    {
        $data['page_title'] = 'Dashboard Mekanik';
        
        // Get mechanic profile
        $mechanic = $this->mechanic_model->find_by_user_id($this->user_id);
        
        if (!$mechanic) {
            $this->session->set_flashdata('error', 'Profil mekanik tidak ditemukan. Hubungi admin bengkel.');
            redirect('auth/logout');
            return;
        }
        
        $data['mechanic'] = $mechanic;
        
        // Get workshop info
        $this->load->model('workshop_model');
        $workshop_id = $mechanic['workshop_id'];
        $data['workshop'] = null;
        
        if ($workshop_id) {
            $query = $this->db->get_where('workshops', ['id' => $workshop_id]);
            $data['workshop'] = $query->row_array();
        }
        
        // Get statistics
        $data['stats'] = [
            'total_assigned' => $this->booking_model->count_mechanic_bookings($mechanic['id']),
            'in_progress' => $this->booking_model->count_mechanic_bookings_by_status($mechanic['id'], 'in_progress'),
            'completed' => $this->booking_model->count_mechanic_bookings_by_status($mechanic['id'], 'completed'),
            'pending' => $this->booking_model->count_mechanic_bookings_by_status($mechanic['id'], 'accepted')
        ];
        
        // Get today's bookings
        $data['today_bookings'] = $this->booking_model->get_mechanic_bookings_today($mechanic['id']);
        
        // Get recent bookings
        $data['recent_bookings'] = $this->booking_model->get_mechanic_recent_bookings($mechanic['id'], 5);
        
        $this->render('mechanic/dashboard/index', $data);
    }

    /**
     * View all assigned bookings
     */
    public function my_bookings()
    {
        $data['page_title'] = 'Order Saya';
        
        $mechanic = $this->mechanic_model->find_by_user_id($this->user_id);
        
        if (!$mechanic) {
            show_error('Profil mekanik tidak ditemukan.', 404);
            return;
        }
        
        $data['mechanic'] = $mechanic;
        $status_filter = $this->input->get('status', TRUE) ?? 'all';
        
        $data['bookings'] = $this->booking_model->get_mechanic_all_bookings($mechanic['id'], $status_filter);
        $data['status_filter'] = $status_filter;
        
        $this->render('mechanic/bookings/index', $data);
    }

    /**
     * View booking detail
     * @param int $booking_id
     */
    public function booking_detail($booking_id)
    {
        $data['page_title'] = 'Detail Order';
        
        $mechanic = $this->mechanic_model->find_by_user_id($this->user_id);
        
        if (!$mechanic) {
            show_error('Profil mekanik tidak ditemukan.', 404);
            return;
        }
        
        // Verify this booking is assigned to this mechanic
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!$booking || !$this->booking_model->is_mechanic_assigned($booking_id, $mechanic['id'])) {
            show_error('Anda tidak ditugaskan pada order ini.', 403);
            return;
        }
        
        $data['booking'] = $booking;
        $data['mechanic'] = $mechanic;
        
        // Get workshop info
        $workshop_id = $booking['workshop_id'];
        $data['workshop'] = null;
        if ($workshop_id) {
            $query = $this->db->get_where('workshops', ['id' => $workshop_id]);
            $data['workshop'] = $query->row_array();
        }
        
        $data['assigned_mechanics'] = $this->mechanic_model->get_booking_mechanics($booking_id);
        
        $this->render('mechanic/bookings/detail', $data);
    }

    /**
     * Update booking status (mechanic side)
     */
    public function update_status()
    {
        $booking_id = $this->input->post('booking_id', TRUE);
        $new_status = $this->input->post('status', TRUE);
        $notes = $this->input->post('notes', TRUE);
        
        if (!$booking_id || !$new_status) {
            $this->json_error('Data tidak lengkap', 400);
            return;
        }
        
        $mechanic = $this->mechanic_model->find_by_user_id($this->user_id);
        
        if (!$mechanic) {
            $this->json_error('Profil mekanik tidak ditemukan', 404);
            return;
        }
        
        // Verify assignment
        if (!$this->booking_model->is_mechanic_assigned($booking_id, $mechanic['id'])) {
            $this->json_error('Anda tidak ditugaskan pada order ini', 403);
            return;
        }
        
        // Allowed status transitions for mechanic
        $allowed_transitions = [
            'accepted' => 'in_progress',
            'in_progress' => 'completed',
            'completed' => 'waiting_approval'
        ];
        
        $booking = $this->booking_model->find_by_id($booking_id);
        
        if (!isset($allowed_transitions[$booking['status']]) || 
            $allowed_transitions[$booking['status']] !== $new_status) {
            $this->json_error('Transisi status tidak valid', 400);
            return;
        }
        
        $update_data = [
            'status' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // If completing, record completion time
        if ($new_status === 'completed') {
            $update_data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        if ($this->booking_model->update($booking_id, $update_data)) {
            // Log activity
            $this->booking_model->log_activity(
                $booking_id,
                'status_updated',
                "Status diubah menjadi {$new_status} oleh mekanik",
                $this->user_id,
                'mechanic'
            );
            
            // Add notes if provided
            if ($notes) {
                $this->booking_model->add_mechanic_note($booking_id, $mechanic['id'], $notes);
            }
            
            $this->json_response([
                'redirect' => site_url('mechanic/booking_detail/' . $booking_id)
            ], 200, 'Status berhasil diperbarui');
        } else {
            $this->json_error('Gagal memperbarui status', 500);
        }
    }

    /**
     * Add work notes / sparepart usage
     */
    public function add_work_note()
    {
        $booking_id = $this->input->post('booking_id', TRUE);
        $note_type = $this->input->post('note_type', TRUE); // 'work_note' or 'sparepart'
        $content = $this->input->post('content', TRUE);
        $sparepart_cost = $this->input->post('sparepart_cost', TRUE) ?? 0;
        
        if (!$booking_id || !$content) {
            $this->json_error('Data tidak lengkap', 400);
            return;
        }
        
        $mechanic = $this->mechanic_model->find_by_user_id($this->user_id);
        
        if (!$mechanic) {
            $this->json_error('Profil mekanik tidak ditemukan', 404);
            return;
        }
        
        // Verify assignment
        if (!$this->booking_model->is_mechanic_assigned($booking_id, $mechanic['id'])) {
            $this->json_error('Anda tidak ditugaskan pada order ini', 403);
            return;
        }
        
        $note_data = [
            'booking_id' => $booking_id,
            'mechanic_id' => $mechanic['id'],
            'note_type' => $note_type,
            'content' => $content,
            'sparepart_cost' => $sparepart_cost,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->mechanic_model->add_work_note($note_data)) {
            $this->json_response([], 200, 'Catatan berhasil ditambahkan');
        } else {
            $this->json_error('Gagal menambahkan catatan', 500);
        }
    }

    /**
     * View personal productivity stats
     */
    public function my_productivity()
    {
        $data['page_title'] = 'Produktivitas Saya';
        
        $mechanic = $this->mechanic_model->find_by_user_id($this->user_id);
        
        if (!$mechanic) {
            show_error('Profil mekanik tidak ditemukan.', 404);
            return;
        }
        
        $data['mechanic'] = $mechanic;
        
        // Get date range
        $start_date = $this->input->get('start_date', TRUE) ?? date('Y-m-01');
        $end_date = $this->input->get('end_date', TRUE) ?? date('Y-m-t');
        
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        
        // Get productivity data
        $data['stats'] = $this->mechanic_model->get_mechanic_productivity($mechanic['id'], $start_date, $end_date);
        $data['completed_bookings'] = $this->booking_model->get_mechanic_completed_bookings($mechanic['id'], $start_date, $end_date);
        
        $this->render('mechanic/productivity/index', $data);
    }

    /**
     * View and edit profile
     */
    public function profile()
    {
        $data['page_title'] = 'Profil Saya';
        
        $mechanic = $this->mechanic_model->find_by_user_id($this->user_id);
        
        if (!$mechanic) {
            show_error('Profil mekanik tidak ditemukan.', 404);
            return;
        }
        
        $data['mechanic'] = $mechanic;
        $data['user'] = $this->current_user;
        $data['specializations'] = ['mesin', 'kelistrikan', 'body', 'ban', 'oli', 'ac', 'transmisi', 'rem'];
        
        $this->render('mechanic/profile/index', $data);
    }

    /**
     * Update profile
     */
    public function update_profile()
    {
        // Read JSON input
        $input = json_decode(file_get_contents('php://input'), TRUE);
        
        $mechanic = $this->mechanic_model->find_by_user_id($this->user_id);
        
        if (!$mechanic) {
            $this->json_error('Profil mekanik tidak ditemukan', 404);
            return;
        }
        
        // Get specialization from JSON input
        $specialization = isset($input['specialization']) ? $input['specialization'] : [];
        $experience_years = isset($input['experience_years']) ? (int)$input['experience_years'] : 0;
        $certification = isset($input['certification']) ? $input['certification'] : '';
        
        // Validate
        if (empty($specialization)) {
            $this->json_error('Spesialisasi harus dipilih', 400);
            return;
        }
        
        if ($experience_years < 0 || $experience_years > 50) {
            $this->json_error('Pengalaman harus antara 0-50 tahun', 400);
            return;
        }
        
        $update_data = [
            'specialization' => $specialization,
            'experience_years' => $experience_years,
            'certification' => $certification
        ];
        
        if ($this->mechanic_model->update_mechanic($mechanic['id'], $update_data)) {
            $this->json_response([
                'redirect' => site_url('mechanic/profile')
            ], 200, 'Profil berhasil diperbarui');
        } else {
            $this->json_error('Gagal memperbarui profil', 500);
        }
    }

    /**
     * Toggle availability status
     */
    public function toggle_availability()
    {
        $mechanic = $this->mechanic_model->find_by_user_id($this->user_id);
        
        if (!$mechanic) {
            $this->json_error('Profil mekanik tidak ditemukan', 404);
            return;
        }
        
        // Get the desired status from POST data, or toggle if not provided
        $is_available_param = $this->input->post('is_available');
        
        if ($is_available_param !== null) {
            $new_status = (int)$is_available_param;
        } else {
            // Toggle behavior (old way)
            $new_status = $mechanic['is_available'] ? 0 : 1;
        }
        
        if ($this->mechanic_model->update_mechanic($mechanic['id'], ['is_available' => $new_status])) {
            $this->json_response([
                'success' => true,
                'is_available' => (bool)$new_status
            ], 200, 'Status ketersediaan berhasil diubah');
        } else {
            $this->json_error('Gagal mengubah status', 500);
        }
    }
}
