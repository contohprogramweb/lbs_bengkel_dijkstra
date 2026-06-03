<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Review Controller
 * 
 * RESTful API endpoints for review management (SRS v4.0 Section 5.6)
 * Handles review creation, listing, reporting, and photo uploads
 */
class Review extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('review_model');
        
        // Require authentication for all API calls
        if (!$this->is_logged_in()) {
            $this->json_error('Authentication required.', 401);
            return;
        }
    }

    /**
     * POST /api/review/create
     * Create a new review for a workshop
     * BR-64: Rating 1-5, BR-65: Max 3 foto, BR-67: Arithmetic average
     */
    public function create()
    {
        $booking_id = $this->input->post('booking_id', TRUE);
        $workshop_id = $this->input->post('workshop_id', TRUE);
        $rating = $this->input->post('rating', TRUE);
        $review_text = $this->input->post('review_text', TRUE);

        // Validation
        $this->form_validation->set_rules('booking_id', 'Booking ID', 'required|numeric');
        $this->form_validation->set_rules('workshop_id', 'Workshop ID', 'required|numeric');
        $this->form_validation->set_rules('rating', 'Rating', 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[5]');

        if ($this->form_validation->run() === FALSE) {
            $this->json_error(validation_errors(), 400);
            return;
        }

        // Verify user completed this booking
        $booking = $this->db
            ->where('id', $booking_id)
            ->where('user_id', $this->user_id)
            ->where('status', 'completed')
            ->get('bookings')
            ->row_array();

        if (!$booking) {
            $this->json_error('Anda hanya dapat memberikan ulasan untuk booking yang telah selesai.', 403);
            return;
        }

        // Check if already reviewed
        $existing_review = $this->db
            ->where('booking_id', $booking_id)
            ->where('is_deleted', 0)
            ->get('reviews')
            ->row_array();

        if ($existing_review) {
            $this->json_error('Anda sudah memberikan ulasan untuk booking ini.', 409);
            return;
        }

        // Create review
        $review_data = [
            'booking_id' => $booking_id,
            'user_id' => $this->user_id,
            'workshop_id' => $workshop_id,
            'rating' => $rating,
            'review_text' => $review_text,
            'is_visible' => 1
        ];

        $review_id = $this->review_model->create_review($review_data);

        if ($review_id) {
            // Handle photo uploads (BR-65: max 3 photos)
            if (!empty($_FILES['photos']['name'][0])) {
                $this->upload_review_photos($review_id);
            }

            // Update workshop rating (BR-67: arithmetic average)
            $this->review_model->update_workshop_rating($workshop_id);

            $this->json_response([
                'success' => true,
                'data' => ['review_id' => $review_id]
            ], 201, 'Ulasan berhasil dibuat.');
        } else {
            $this->json_error('Gagal membuat ulasan.', 500);
        }
    }

    /**
     * GET /api/review/list/{workshop_id}
     * Get all reviews for a workshop
     */
    public function list($workshop_id)
    {
        $page = $this->input->get('page', TRUE) ?? 1;
        $per_page = $this->input->get('per_page', TRUE) ?? 10;
        $min_rating = $this->input->get('min_rating', TRUE);

        $reviews = $this->review_model->get_workshop_reviews(
            $workshop_id, 
            $page, 
            $per_page, 
            $min_rating
        );

        $this->json_response([
            'success' => true,
            'data' => $reviews
        ], 200, 'Reviews retrieved successfully.');
    }

    /**
     * POST /api/review/report
     * Report an inappropriate review (BR-68)
     * Auto-pending jika report >= 3
     */
    public function report()
    {
        $review_id = $this->input->post('review_id', TRUE);
        $reason = $this->input->post('reason', TRUE);

        $this->form_validation->set_rules('review_id', 'Review ID', 'required|numeric');
        $this->form_validation->set_rules('reason', 'Alasan', 'required|trim|min_length[10]');

        if ($this->form_validation->run() === FALSE) {
            $this->json_error(validation_errors(), 400);
            return;
        }

        // Check if review exists
        $review = $this->db->where('id', $review_id)->get('reviews')->row_array();
        if (!$review) {
            $this->json_error('Review tidak ditemukan.', 404);
            return;
        }

        // Check if user already reported this review
        $existing_report = $this->db
            ->where('review_id', $review_id)
            ->where('user_id', $this->user_id)
            ->get('review_reports')
            ->row_array();

        if ($existing_report) {
            $this->json_error('Anda sudah melaporkan review ini sebelumnya.', 409);
            return;
        }

        // Create report
        $report_data = [
            'review_id' => $review_id,
            'user_id' => $this->user_id,
            'reason' => $reason,
            'status' => 'pending'
        ];

        $this->db->insert('review_reports', $report_data);

        // Check if auto-pending threshold reached (BR-68)
        $report_count = $this->db
            ->where('review_id', $review_id)
            ->where('status', 'pending')
            ->from('review_reports')
            ->count_all_results();

        if ($report_count >= 3) {
            $this->db->where('id', $review_id)
                     ->update('reviews', ['is_visible' => 0]);
        }

        $this->json_response([
            'success' => true
        ], 201, 'Laporan berhasil dikirim. Terima kasih atas feedback Anda.');
    }

    /**
     * POST /api/review/photo/upload
     * Upload photos for a review (max 3)
     */
    private function upload_review_photos($review_id)
    {
        $upload_path = './uploads/reviews/';
        
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, TRUE);
        }

        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'jpg|jpeg|png',
            'max_size' => 2048, // 2MB
            'max_width' => 1920,
            'max_height' => 1920,
            'encrypt_name' => TRUE
        ];

        $this->load->library('upload', $config);

        $uploaded_count = 0;
        $files = $_FILES['photos'];
        
        foreach ($files['name'] as $key => $value) {
            if ($uploaded_count >= 3) break; // BR-65: max 3 photos

            $_FILES['photo'] = [
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];

            if ($this->upload->do_upload('photo')) {
                $upload_data = $this->upload->data();
                
                $this->db->insert('review_photos', [
                    'review_id' => $review_id,
                    'photo_path' => 'uploads/reviews/' . $upload_data['file_name'],
                    'photo_original_name' => $upload_data['orig_name'],
                    'photo_size' => $upload_data['file_size'],
                    'photo_mime_type' => $upload_data['file_type']
                ]);
                
                $uploaded_count++;
            }
        }

        return $uploaded_count;
    }
}
