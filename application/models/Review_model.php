<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Review Model
 * 
 * Handles review and rating operations for workshops.
 * Implements business rules BR-65~69, report mechanism BR-68,
 * and automatic rating_avg update BR-67.
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Models
 * @version     4.1
 */
class Review_model extends CI_Model {

    private $table_reviews = 'reviews';
    private $table_review_photos = 'review_photos';
    private $table_review_reports = 'review_reports';
    private $table_bookings = 'bookings';
    private $table_workshops = 'workshops';
    private $table_users = 'users';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ================================================================
    // REVIEW CREATION & VALIDATION
    // ================================================================

    /**
     * Check if user can review a booking (BR-65, BR-66, BR-69)
     * 
     * @param int $user_id
     * @param int $booking_id
     * @return array ['can_review' => bool, 'message' => string, 'booking' => array|null]
     */
    public function can_review_booking($user_id, $booking_id)
    {
        // Get booking details
        $this->db->select('b.*, w.id as workshop_id, w.name as workshop_name');
        $this->db->from($this->table_bookings . ' b');
        $this->db->join($this->table_workshops . ' w', 'b.workshop_id = w.id');
        $this->db->where('b.id', $booking_id);
        $this->db->where('b.user_id', $user_id);
        $this->db->where('b.is_deleted', 0);
        
        $booking = $this->db->get()->row_array();

        if (!$booking) {
            return [
                'can_review' => FALSE,
                'message' => 'Booking tidak ditemukan atau bukan milik Anda.',
                'booking' => NULL
            ];
        }

        // BR-69: User cannot review workshop they never ordered
        // (Already covered by checking booking belongs to user)

        // BR-66: Only completed bookings can be reviewed
        if ($booking['status'] !== 'completed') {
            return [
                'can_review' => FALSE,
                'message' => 'Hanya booking dengan status Completed yang dapat direview.',
                'booking' => $booking
            ];
        }

        // BR-65: One review per booking
        $existing_review = $this->get_review_by_booking($booking_id);
        if ($existing_review) {
            return [
                'can_review' => FALSE,
                'message' => 'Anda sudah memberikan review untuk booking ini.',
                'booking' => $booking
            ];
        }

        return [
            'can_review' => TRUE,
            'message' => 'OK',
            'booking' => $booking
        ];
    }

    /**
     * Get review by booking ID
     * 
     * @param int $booking_id
     * @return array|null
     */
    public function get_review_by_booking($booking_id)
    {
        return $this->db->get_where($this->table_reviews, [
            'booking_id' => $booking_id,
            'is_deleted' => 0
        ])->row_array();
    }

    /**
     * Create new review with photos
     * 
     * @param array $data Review data
     * @param array $photos Photo paths array
     * @return array ['success' => bool, 'review_id' => int|null, 'message' => string]
     */
    public function create_review($data, $photos = [])
    {
        $this->db->trans_start();

        try {
            // Prepare review data
            $review_data = [
                'booking_id' => $data['booking_id'],
                'user_id' => $data['user_id'],
                'workshop_id' => $data['workshop_id'],
                'rating' => $data['rating'],
                'review_text' => $data['review_text'] ?? NULL,
                'status' => $data['status'] ?? 'active', // 'active' or 'pending'
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert review
            $this->db->insert($this->table_reviews, $review_data);
            $review_id = $this->db->insert_id();

            if (!$review_id) {
                throw new Exception('Gagal menyimpan review');
            }

            // Insert photos if any
            if (!empty($photos)) {
                foreach ($photos as $photo) {
                    $photo_data = [
                        'review_id' => $review_id,
                        'photo_path' => $photo['path'],
                        'photo_original_name' => $photo['original_name'],
                        'photo_size' => $photo['size'],
                        'photo_mime_type' => $photo['mime_type'],
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $this->db->insert($this->table_review_photos, $photo_data);
                }
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                return [
                    'success' => FALSE,
                    'review_id' => NULL,
                    'message' => 'Terjadi kesalahan saat menyimpan review.'
                ];
            }

            // BR-67: Update workshop rating_avg after review submitted
            $this->update_workshop_rating($data['workshop_id']);

            return [
                'success' => TRUE,
                'review_id' => $review_id,
                'message' => 'Review berhasil dikirim.'
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [
                'success' => FALSE,
                'review_id' => NULL,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update workshop average rating (BR-67)
     * 
     * @param int $workshop_id
     * @return bool
     */
    public function update_workshop_rating($workshop_id)
    {
        // Calculate average rating from all active reviews
        $this->db->select('AVG(rating) as avg_rating, COUNT(*) as total_reviews');
        $this->db->from($this->table_reviews);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('is_deleted', 0);
        $this->db->where('status', 'active'); // Only count active reviews
        
        $result = $this->db->get()->row_array();

        $avg_rating = $result['avg_rating'] ?? 0;
        $total_reviews = $result['total_reviews'] ?? 0;

        // Update workshop table
        $update_data = [
            'rating_avg' => round($avg_rating, 2),
            'total_reviews' => $total_reviews,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $workshop_id);
        return $this->db->update($this->table_workshops, $update_data);
    }

    // ================================================================
    // REVIEW RETRIEVAL
    // ================================================================

    /**
     * Get reviews for a workshop with pagination
     * 
     * @param int $workshop_id
     * @param int $limit
     * @param int $offset
     * @param string $status Filter by status ('active', 'pending', 'all')
     * @return array ['reviews' => array, 'total' => int, 'avg_rating' => float]
     */
    public function get_workshop_reviews($workshop_id, $limit = 10, $offset = 0, $status = 'active')
    {
        // Get reviews with user info
        $this->db->select('r.*, u.full_name as user_name, u.avatar as user_avatar');
        $this->db->from($this->table_reviews . ' r');
        $this->db->join($this->table_users . ' u', 'r.user_id = u.id');
        $this->db->where('r.workshop_id', $workshop_id);
        $this->db->where('r.is_deleted', 0);
        
        if ($status !== 'all') {
            $this->db->where('r.status', $status);
        }
        
        $this->db->order_by('r.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $reviews = $this->db->get()->result_array();

        // Get photos for each review
        foreach ($reviews as &$review) {
            $review['photos'] = $this->get_review_photos($review['id']);
        }

        // Get total count
        $this->db->from($this->table_reviews);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('is_deleted', 0);
        if ($status !== 'all') {
            $this->db->where('status', $status);
        }
        $total = $this->db->count_all_results();

        // Get average rating
        $this->db->select('AVG(rating) as avg_rating');
        $this->db->from($this->table_reviews);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('is_deleted', 0);
        $this->db->where('status', 'active');
        $avg_result = $this->db->get()->row_array();
        $avg_rating = $avg_result['avg_rating'] ?? 0;

        return [
            'reviews' => $reviews,
            'total' => $total,
            'avg_rating' => round($avg_rating, 2)
        ];
    }

    /**
     * Get photos for a review
     * 
     * @param int $review_id
     * @return array
     */
    public function get_review_photos($review_id)
    {
        $this->db->select('id, photo_path, photo_original_name, photo_size');
        $this->db->from($this->table_review_photos);
        $this->db->where('review_id', $review_id);
        $this->db->where('is_deleted', 0);
        $this->db->order_by('created_at', 'ASC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Get review by ID
     * 
     * @param int $review_id
     * @return array|null
     */
    public function get_review_by_id($review_id)
    {
        $this->db->select('r.*, u.full_name as user_name, w.name as workshop_name');
        $this->db->from($this->table_reviews . ' r');
        $this->db->join($this->table_users . ' u', 'r.user_id = u.id');
        $this->db->join($this->table_workshops . ' w', 'r.workshop_id = w.id');
        $this->db->where('r.id', $review_id);
        $this->db->where('r.is_deleted', 0);
        
        return $this->db->get()->row_array();
    }

    // ================================================================
    // REVIEW REPORTING (BR-68)
    // ================================================================

    /**
     * Report a review
     * 
     * @param int $review_id
     * @param int $user_id User reporting
     * @param string $reason Report reason
     * @return array ['success' => bool, 'message' => string, 'auto_hidden' => bool]
     */
    public function report_review($review_id, $user_id, $reason = '')
    {
        $this->db->trans_start();

        try {
            // Check if review exists
            $review = $this->get_review_by_id($review_id);
            if (!$review) {
                return [
                    'success' => FALSE,
                    'message' => 'Review tidak ditemukan.',
                    'auto_hidden' => FALSE
                ];
            }

            // Check if user already reported this review
            $this->db->from($this->table_review_reports);
            $this->db->where('review_id', $review_id);
            $this->db->where('user_id', $user_id);
            $existing_report = $this->db->get()->row_array();

            if ($existing_report) {
                return [
                    'success' => FALSE,
                    'message' => 'Anda sudah melaporkan review ini.',
                    'auto_hidden' => FALSE
                ];
            }

            // Insert report
            $report_data = [
                'review_id' => $review_id,
                'user_id' => $user_id,
                'reason' => $reason,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert($this->table_review_reports, $report_data);

            // Count reports for this review
            $this->db->select('COUNT(*) as report_count');
            $this->db->from($this->table_review_reports);
            $this->db->where('review_id', $review_id);
            $count_result = $this->db->get()->row_array();
            $report_count = $count_result['report_count'];

            $auto_hidden = FALSE;

            // BR-68: If report_count >= 3, automatically change status to pending
            if ($report_count >= 3 && $review['status'] === 'active') {
                $this->db->where('id', $review_id);
                $this->db->update($this->table_reviews, [
                    'status' => 'pending',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $auto_hidden = TRUE;

                // Recalculate workshop rating
                $this->update_workshop_rating($review['workshop_id']);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                return [
                    'success' => FALSE,
                    'message' => 'Gagal melaporkan review.',
                    'auto_hidden' => FALSE
                ];
            }

            return [
                'success' => TRUE,
                'message' => 'Laporan berhasil dikirim.',
                'auto_hidden' => $auto_hidden
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [
                'success' => FALSE,
                'message' => $e->getMessage(),
                'auto_hidden' => FALSE
            ];
        }
    }

    /**
     * Get report count for a review
     * 
     * @param int $review_id
     * @return int
     */
    public function get_report_count($review_id)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from($this->table_review_reports);
        $this->db->where('review_id', $review_id);
        $result = $this->db->get()->row_array();
        return $result['count'] ?? 0;
    }

    // ================================================================
    // ADMIN MODERATION
    // ================================================================

    /**
     * Get all reviews for moderation (admin only)
     * 
     * @param string $status Filter by status
     * @param int $limit
     * @param int $offset
     * @return array ['reviews' => array, 'total' => int]
     */
    public function get_all_reviews_for_moderation($status = 'all', $limit = 20, $offset = 0)
    {
        $this->db->select('r.*, u.full_name as user_name, w.name as workshop_name, 
                          (SELECT COUNT(*) FROM ' . $this->table_review_reports . ' rr WHERE rr.review_id = r.id) as report_count');
        $this->db->from($this->table_reviews . ' r');
        $this->db->join($this->table_users . ' u', 'r.user_id = u.id');
        $this->db->join($this->table_workshops . ' w', 'r.workshop_id = w.id');
        $this->db->where('r.is_deleted', 0);
        
        if ($status !== 'all') {
            $this->db->where('r.status', $status);
        }
        
        $this->db->order_by('r.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $reviews = $this->db->get()->result_array();

        // Get total count
        $this->db->from($this->table_reviews);
        $this->db->where('is_deleted', 0);
        if ($status !== 'all') {
            $this->db->where('status', $status);
        }
        $total = $this->db->count_all_results();

        return [
            'reviews' => $reviews,
            'total' => $total
        ];
    }

    /**
     * Approve review (change status to active)
     * 
     * @param int $review_id
     * @param int $admin_id
     * @return bool
     */
    public function approve_review($review_id, $admin_id)
    {
        $review = $this->get_review_by_id($review_id);
        if (!$review) {
            return FALSE;
        }

        $this->db->where('id', $review_id);
        $result = $this->db->update($this->table_reviews, [
            'status' => 'active',
            'admin_response' => NULL,
            'responded_at' => date('Y-m-d H:i:s'),
            'responded_by' => $admin_id,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            // Recalculate workshop rating
            $this->update_workshop_rating($review['workshop_id']);
        }

        return $result;
    }

    /**
     * Reject review (change status to hidden with moderation note)
     * 
     * @param int $review_id
     * @param int $admin_id
     * @param string $moderation_note Reason for rejection
     * @return bool
     */
    public function reject_review($review_id, $admin_id, $moderation_note = '')
    {
        $review = $this->get_review_by_id($review_id);
        if (!$review) {
            return FALSE;
        }

        $this->db->where('id', $review_id);
        $result = $this->db->update($this->table_reviews, [
            'status' => 'hidden',
            'admin_response' => $moderation_note,
            'responded_at' => date('Y-m-d H:i:s'),
            'responded_by' => $admin_id,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            // Recalculate workshop rating
            $this->update_workshop_rating($review['workshop_id']);
        }

        return $result;
    }

    /**
     * Delete review (soft delete)
     * 
     * @param int $review_id
     * @return bool
     */
    public function delete_review($review_id)
    {
        $review = $this->get_review_by_id($review_id);
        if (!$review) {
            return FALSE;
        }

        $this->db->trans_start();

        try {
            // Soft delete review
            $this->db->where('id', $review_id);
            $this->db->update($this->table_reviews, [
                'is_deleted' => 1,
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

            // Soft delete associated photos
            $this->db->where('review_id', $review_id);
            $this->db->update($this->table_review_photos, [
                'is_deleted' => 1,
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                return FALSE;
            }

            // Recalculate workshop rating
            $this->update_workshop_rating($review['workshop_id']);

            return TRUE;

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return FALSE;
        }
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Get user's completed bookings that haven't been reviewed
     * 
     * @param int $user_id
     * @return array
     */
    public function get_user_pending_reviews($user_id)
    {
        $this->db->select('b.id as booking_id, b.booking_number, b.scheduled_date, b.scheduled_time,
                          w.id as workshop_id, w.name as workshop_name, w.logo,
                          v.vehicle_number, v.brand, v.model');
        $this->db->from($this->table_bookings . ' b');
        $this->db->join($this->table_workshops . ' w', 'b.workshop_id = w.id');
        $this->db->join($this->table_vehicles . ' v', 'b.vehicle_id = v.id', 'left');
        $this->db->where('b.user_id', $user_id);
        $this->db->where('b.status', 'completed');
        $this->db->where('b.is_deleted', 0);
        
        // Exclude bookings that already have reviews
        $this->db->where_not_in('b.id', function($sub) {
            $sub->select('booking_id');
            $sub->from($this->table_reviews);
            $sub->where('is_deleted', 0);
        });
        
        $this->db->order_by('b.completed_at', 'DESC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Check if user has booked a specific workshop (BR-69 helper)
     * 
     * @param int $user_id
     * @param int $workshop_id
     * @return bool
     */
    public function user_has_booked_workshop($user_id, $workshop_id)
    {
        $this->db->from($this->table_bookings);
        $this->db->where('user_id', $user_id);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('is_deleted', 0);
        
        return $this->db->count_all_results() > 0;
    }
}

/* End of file Review_model.php */
/* Location: ./application/models/Review_model.php */
