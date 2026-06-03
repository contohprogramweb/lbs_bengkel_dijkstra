<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Review Controller
 * 
 * Handles review and rating functionality for users.
 * Implements FR-RVW-01~03, UC-USR-09, BR-65~69.
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Controllers
 * @version     4.1
 */
class Review extends Customer_Controller {

    /**
     * Review model instance
     */
    private $review_model;

    /**
     * Upload configuration for review photos
     */
    private $upload_config;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('review_model');
        $this->load->model('booking_model');
        $this->load->library(['form_validation', 'upload']);
        $this->load->helper(['form', 'url', 'text']);

        // Configure upload settings
        $this->upload_config = [
            'upload_path'   => $this->config->item('upload_path_reviews'),
            'allowed_types' => $this->config->item('allowed_types_reviews'),
            'max_size'      => $this->config->item('max_size_reviews'), // 2MB
            'max_width'     => 0,
            'max_height'    => 0,
            'encrypt_name'  => TRUE,
            'remove_spaces' => TRUE
        ];

        // Ensure upload directory exists
        if (!is_dir($this->upload_config['upload_path'])) {
            mkdir($this->upload_config['upload_path'], 0755, TRUE);
        }
    }

    // ================================================================
    // USER REVIEW MANAGEMENT
    // ================================================================

    /**
     * List user's pending reviews (completed bookings not yet reviewed)
     * URL: /review/my_reviews
     */
    public function my_reviews()
    {
        $data['page_title'] = 'Tulis Review';
        $data['user'] = $this->current_user;
        
        // Get bookings that can be reviewed
        $data['pending_reviews'] = $this->review_model->get_user_pending_reviews($this->user_id);
        
        // Get user's submitted reviews
        $data['submitted_reviews'] = $this->get_user_submitted_reviews();

        $this->render('user/reviews/my_reviews', $data);
    }

    /**
     * Get user's submitted reviews with pagination
     * 
     * @return array
     */
    private function get_user_submitted_reviews($limit = 10, $offset = 0)
    {
        $this->load->model('workshop_model');
        
        $this->db->select('r.*, w.name as workshop_name, w.logo, b.booking_number, b.scheduled_date');
        $this->db->from('reviews r');
        $this->db->join('workshops w', 'r.workshop_id = w.id');
        $this->db->join('bookings b', 'r.booking_id = b.id');
        $this->db->where('r.user_id', $this->user_id);
        $this->db->where('r.is_deleted', 0);
        $this->db->order_by('r.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $reviews = $this->db->get()->result_array();
        
        foreach ($reviews as &$review) {
            $review['photos'] = $this->review_model->get_review_photos($review['id']);
        }
        
        return $reviews;
    }

    /**
     * Create review form
     * URL: /review/create/{booking_id}
     */
    public function create($booking_id = NULL)
    {
        $data['page_title'] = 'Tulis Review';
        $data['user'] = $this->current_user;

        if (!$booking_id) {
            $this->session->set_flashdata('error', 'Booking tidak valid.');
            redirect('review/my_reviews');
        }

        // Check if user can review this booking
        $can_review = $this->review_model->can_review_booking($this->user_id, $booking_id);
        
        if (!$can_review['can_review']) {
            $this->session->set_flashdata('error', $can_review['message']);
            redirect('review/my_reviews');
        }

        $data['booking'] = $can_review['booking'];
        $data['max_photos'] = 3;
        $data['min_chars'] = 10;
        $data['max_chars'] = 500;

        if ($this->input->post()) {
            $this->_set_review_validation_rules();

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                // Handle photo uploads
                $photos = $this->_upload_photos();

                // Prepare review data
                $review_data = [
                    'booking_id' => $booking_id,
                    'user_id' => $this->user_id,
                    'workshop_id' => $data['booking']['workshop_id'],
                    'rating' => $this->input->post('rating', TRUE),
                    'review_text' => $this->input->post('review_text', TRUE),
                    'status' => 'active' // Default to active, can be changed by admin settings
                ];

                $result = $this->review_model->create_review($review_data, $photos);

                if ($result['success']) {
                    $this->session->set_flashdata('success', 'Review berhasil dikirim. Terima kasih atas feedback Anda!');
                    
                    // Send notification (optional - can be implemented later)
                    // $this->_send_review_notification($result['review_id']);
                    
                    redirect('review/my_reviews');
                } else {
                    $this->session->set_flashdata('error', $result['message']);
                    // Delete uploaded photos if review failed
                    if (!empty($photos)) {
                        foreach ($photos as $photo) {
                            if (file_exists(FCPATH . $photo['path'])) {
                                unlink(FCPATH . $photo['path']);
                            }
                        }
                    }
                }
            }
        }

        $this->render('user/reviews/create', $data);
    }

    /**
     * Set validation rules for review form
     */
    private function _set_review_validation_rules()
    {
        $this->form_validation->set_rules('rating', 'Rating', 'required|integer|greater_than_equal_to[1]|less_than_equal_to[5]');
        $this->form_validation->set_rules('review_text', 'Ulasan', 'trim|min_length[10]|max_length[500]');
    }

    /**
     * Upload and process review photos
     * Resizes images to max 800x800px before saving
     * 
     * @return array Array of photo info
     */
    private function _upload_photos()
    {
        $photos = [];
        
        if (empty($_FILES['photos']['name'][0])) {
            return $photos;
        }

        $this->load->library('image_lib');

        $count = 0;
        foreach ($_FILES['photos']['name'] as $key => $filename) {
            if ($count >= 3) break; // Max 3 photos
            
            if (empty($filename)) continue;

            $_FILES['photo']['name']     = $_FILES['photos']['name'][$key];
            $_FILES['photo']['type']     = $_FILES['photos']['type'][$key];
            $_FILES['photo']['tmp_name'] = $_FILES['photos']['tmp_name'][$key];
            $_FILES['photo']['error']    = $_FILES['photos']['error'][$key];
            $_FILES['photo']['size']     = $_FILES['photos']['size'][$key];

            $this->upload->initialize($this->upload_config);

            if ($this->upload->do_upload('photo')) {
                $upload_data = $this->upload->data();
                
                // Resize image to max 800x800px
                $resized_path = $this->_resize_image($upload_data);
                
                // Delete original if resized
                if ($resized_path && $resized_path !== $this->upload_config['upload_path'] . $upload_data['file_name']) {
                    unlink($this->upload_config['upload_path'] . $upload_data['file_name']);
                }

                $photos[] = [
                    'path' => 'uploads/reviews/' . basename($resized_path ?: $upload_data['full_path']),
                    'original_name' => $upload_data['orig_name'],
                    'size' => filesize(FCPATH . 'uploads/reviews/' . basename($resized_path ?: $upload_data['full_path'])),
                    'mime_type' => $upload_data['image_type']
                ];

                $count++;
            }
        }

        return $photos;
    }

    /**
     * Resize image to max 800x800px
     * 
     * @param array $upload_data Upload result from CI upload library
     * @return string Path to resized image
     */
    private function _resize_image($upload_data)
    {
        $config['image_library'] = 'gd2';
        $config['source_image'] = $upload_data['full_path'];
        $config['maintain_ratio'] = TRUE;
        $config['width'] = 800;
        $config['height'] = 800;

        $this->image_lib->initialize($config);

        if ($this->image_lib->resize()) {
            return $upload_data['full_path'];
        }

        return NULL;
    }

    /**
     * Edit review (only if no admin response yet)
     * URL: /review/edit/{review_id}
     */
    public function edit($review_id)
    {
        $review = $this->review_model->get_review_by_id($review_id);

        if (!$review || $review['user_id'] != $this->user_id) {
            $this->session->set_flashdata('error', 'Review tidak ditemukan atau tidak memiliki akses.');
            redirect('review/my_reviews');
        }

        // Cannot edit if admin has responded
        if (!empty($review['admin_response']) || $review['status'] === 'hidden') {
            $this->session->set_flashdata('error', 'Review tidak dapat diedit karena sudah ada respons admin.');
            redirect('review/my_reviews');
        }

        $data['page_title'] = 'Edit Review';
        $data['user'] = $this->current_user;
        $data['review'] = $review;
        $data['photos'] = $this->review_model->get_review_photos($review_id);

        if ($this->input->post()) {
            $this->_set_review_validation_rules();

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                // Handle new photo uploads
                $new_photos = $this->_upload_photos();

                // Update review
                $update_data = [
                    'rating' => $this->input->post('rating', TRUE),
                    'review_text' => $this->input->post('review_text', TRUE),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->db->where('id', $review_id);
                $this->db->update('reviews', $update_data);

                // Add new photos
                if (!empty($new_photos)) {
                    foreach ($new_photos as $photo) {
                        $this->db->insert('review_photos', [
                            'review_id' => $review_id,
                            'photo_path' => $photo['path'],
                            'photo_original_name' => $photo['original_name'],
                            'photo_size' => $photo['size'],
                            'photo_mime_type' => $photo['mime_type'],
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }

                $this->session->set_flashdata('success', 'Review berhasil diperbarui.');
                redirect('review/my_reviews');
            }
        }

        $this->render('user/reviews/edit', $data);
    }

    /**
     * Delete review (soft delete, only own reviews)
     * URL: /review/delete/{review_id}
     */
    public function delete($review_id)
    {
        $review = $this->review_model->get_review_by_id($review_id);

        if (!$review || $review['user_id'] != $this->user_id) {
            $this->json_error('Review tidak ditemukan atau tidak memiliki akses.', 404);
            return;
        }

        if ($this->review_model->delete_review($review_id)) {
            $this->json_response(['message' => 'Review berhasil dihapus.'], 200, 'Success');
        } else {
            $this->json_error('Gagal menghapus review.', 500);
        }
    }

    // ================================================================
    // REVIEW REPORTING (BR-68)
    // ================================================================

    /**
     * Report a review
     * URL: /review/report/{review_id}
     */
    public function report($review_id)
    {
        if ($this->input->post()) {
            $reason = $this->input->post('reason', TRUE);

            $result = $this->review_model->report_review($review_id, $this->user_id, $reason);

            if ($result['success']) {
                $message = $result['message'];
                if ($result['auto_hidden']) {
                    $message .= ' Review ini telah disembunyikan otomatis karena banyak laporan.';
                }
                $this->session->set_flashdata('success', $message);
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }

            // Redirect back to workshop detail page
            $review = $this->review_model->get_review_by_id($review_id);
            if ($review) {
                redirect('workshop/detail/' . $review['workshop_id'] . '#reviews');
            } else {
                redirect('map/search');
            }
        }
    }

    // ================================================================
    // AJAX METHODS
    // ================================================================

    /**
     * Check if booking can be reviewed (AJAX)
     * URL: /review/check_booking/{booking_id}
     */
    public function check_booking($booking_id)
    {
        $result = $this->review_model->can_review_booking($this->user_id, $booking_id);
        
        if ($result['can_review']) {
            $this->json_response($result, 200, 'OK');
        } else {
            $this->json_error($result['message'], 400);
        }
    }
}

/* End of file Review.php */
/* Location: ./application/controllers/Review.php */
