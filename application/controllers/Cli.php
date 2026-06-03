<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CLI Controller for Cron Jobs
 * 
 * Handles automated tasks:
 * - Daily service reminders
 * - Emergency request timeout handling
 * - Booking status cleanup
 * 
 * Usage:
 * php index.php cli reminder daily
 * php index.php cli emergency close_timeout
 * php index.php cli booking cleanup
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Controllers
 * @version     4.0
 */
class Cli extends CI_Controller {

    /**
     * Constructor - Check if running from CLI
     */
    public function __construct()
    {
        parent::__construct();
        
        // Security: Only allow CLI access
        if (PHP_SAPI !== 'cli') {
            show_error('CLI scripts can only be executed from the command line.', 403);
        }
        
        $this->load->model('notification_model');
        $this->load->model('booking_model');
        $this->load->model('emergency_model');
        $this->load->model('vehicle_model');
        $this->load->library('email');
        
        log_message('info', 'CLI Script started: ' . implode(' ', $this->input->server('argv')));
    }

    /**
     * Default method
     */
    public function index()
    {
        echo "Available CLI commands:\n";
        echo "  php index.php cli reminder daily    - Send daily service reminders\n";
        echo "  php index.php cli emergency close_timeout - Close timed-out emergency requests\n";
        echo "  php index.php cli booking cleanup   - Clean up old pending bookings\n";
        echo "\n";
    }

    // ================================================================
    // REMINDER COMMANDS
    // ================================================================

    /**
     * Send daily service reminders
     * Usage: php index.php cli reminder daily
     */
    public function reminder($type = 'daily')
    {
        if ($type === 'daily') {
            $this->_send_daily_reminders();
        } else {
            echo "Unknown reminder type: $type\n";
        }
    }

    /**
     * Send daily service reminders to users whose vehicles are due for service
     */
    private function _send_daily_reminders()
    {
        echo "Starting daily service reminders...\n";
        
        // Get system settings for reminder intervals
        $this->load->model('system_setting_model');
        $reminder_km = $this->system_setting_model->get_value('reminder_interval_km', 5000);
        $reminder_months = $this->system_setting_model->get_value('reminder_interval_months', 6);
        
        // Get vehicles that need service reminder
        $vehicles = $this->vehicle_model->get_vehicles_due_for_service($reminder_km, $reminder_months);
        
        echo "Found " . count($vehicles) . " vehicles due for service reminder.\n";
        
        $sent_count = 0;
        $failed_count = 0;
        
        foreach ($vehicles as $vehicle) {
            try {
                // Get user info
                $user = $this->db->get_where('users', ['id' => $vehicle['user_id']])->row_array();
                
                if (!$user || empty($user['email'])) {
                    echo "Skipping vehicle {$vehicle['id']} - No user email\n";
                    continue;
                }
                
                // Calculate next service due
                $next_service_km = $vehicle['current_km'] + $reminder_km;
                $months_since_service = $this->_calculate_months_since($vehicle['last_service_date']);
                
                // Prepare email data
                $email_data = [
                    'recipient_email' => $user['email'],
                    'recipient_name' => $user['full_name'],
                    'template_key' => 'reminder_service',
                    'data' => [
                        'user_name' => $user['full_name'],
                        'vehicle_name' => $vehicle['vehicle_name'],
                        'vehicle_plate' => $vehicle['plate_number'],
                        'current_km' => number_format($vehicle['current_km'], 0, ',', '.'),
                        'next_service_km' => number_format($next_service_km, 0, ',', '.'),
                        'months_since_service' => $months_since_service,
                        'recommended_action' => 'Jadwalkan servis berkala untuk menjaga kondisi kendaraan Anda.'
                    ]
                ];
                
                // Send notification via model (handles email + log)
                $result = $this->notification_model->send_notification($email_data);
                
                if ($result) {
                    echo "✓ Reminder sent to {$user['email']} for vehicle {$vehicle['plate_number']}\n";
                    $sent_count++;
                } else {
                    echo "✗ Failed to send reminder to {$user['email']}\n";
                    $failed_count++;
                }
                
            } catch (Exception $e) {
                log_message('error', 'Reminder error for vehicle ' . $vehicle['id'] . ': ' . $e->getMessage());
                $failed_count++;
            }
        }
        
        echo "\n=== Reminder Summary ===\n";
        echo "Sent: $sent_count\n";
        echo "Failed: $failed_count\n";
        echo "========================\n\n";
        
        log_message('info', "Daily reminders completed: $sent_count sent, $failed_count failed");
    }

    // ================================================================
    // EMERGENCY COMMANDS
    // ================================================================

    /**
     * Close timed-out emergency requests
     * Usage: php index.php cli emergency close_timeout
     */
    public function emergency($action = 'close_timeout')
    {
        if ($action === 'close_timeout') {
            $this->_close_timedout_emergencies();
        } else {
            echo "Unknown emergency action: $action\n";
        }
    }

    /**
     * Close emergency requests that have timed out (no response within 30 minutes)
     */
    private function _close_timedout_emergencies()
    {
        echo "Checking for timed-out emergency requests...\n";
        
        // Timeout in minutes (configurable)
        $timeout_minutes = 30;
        
        // Get emergency requests that are still pending and older than timeout
        $this->db->where('status', 'pending');
        $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime("-$timeout_minutes minutes")));
        $emergencies = $this->db->get('emergency_requests')->result_array();
        
        echo "Found " . count($emergencies) . " timed-out emergency requests.\n";
        
        $closed_count = 0;
        
        foreach ($emergencies as $emergency) {
            try {
                // Update status to timeout
                $this->db->update('emergency_requests', [
                    'status' => 'timeout',
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['id' => $emergency['id']]);
                
                // Log the timeout
                $this->notification_model->log_notification(
                    'emergency_timeout',
                    'system',
                    $emergency['user_id'],
                    'Permintaan darurat timeout - tidak ada bengkel yang merespons',
                    ['emergency_id' => $emergency['id']]
                );
                
                echo "✓ Closed emergency #{$emergency['id']} (timeout)\n";
                $closed_count++;
                
            } catch (Exception $e) {
                log_message('error', 'Emergency timeout error for ID ' . $emergency['id'] . ': ' . $e->getMessage());
            }
        }
        
        echo "\n=== Emergency Timeout Summary ===\n";
        echo "Closed: $closed_count\n";
        echo "=================================\n\n";
        
        log_message('info', "Emergency timeout check completed: $closed_count closed");
    }

    // ================================================================
    // BOOKING COMMANDS
    // ================================================================

    /**
     * Clean up old pending bookings
     * Usage: php index.php cli booking cleanup
     */
    public function booking($action = 'cleanup')
    {
        if ($action === 'cleanup') {
            $this->_cleanup_old_bookings();
        } else {
            echo "Unknown booking action: $action\n";
        }
    }

    /**
     * Cancel bookings that have been pending for too long (> 24 hours without response)
     */
    private function _cleanup_old_bookings()
    {
        echo "Cleaning up old pending bookings...\n";
        
        // Timeout in hours
        $timeout_hours = 24;
        
        // Get pending bookings older than timeout
        $this->db->where('status', 'pending');
        $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime("-$timeout_hours hours")));
        $bookings = $this->db->get('bookings')->result_array();
        
        echo "Found " . count($bookings) . " old pending bookings.\n";
        
        $cancelled_count = 0;
        
        foreach ($bookings as $booking) {
            try {
                // Update booking status
                $this->db->update('bookings', [
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Timeout - Workshop tidak merespons dalam 24 jam',
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['id' => $booking['id']]);
                
                // Notify user
                $user = $this->db->get_where('users', ['id' => $booking['user_id']])->row_array();
                
                if ($user && !empty($user['email'])) {
                    $email_data = [
                        'recipient_email' => $user['email'],
                        'recipient_name' => $user['full_name'],
                        'template_key' => 'booking_cancelled',
                        'data' => [
                            'user_name' => $user['full_name'],
                            'booking_code' => $booking['booking_code'],
                            'reason' => 'Bengkel tidak merespons dalam waktu 24 jam'
                        ]
                    ];
                    
                    $this->notification_model->send_notification($email_data);
                }
                
                echo "✓ Cancelled booking #{$booking['booking_code']}\n";
                $cancelled_count++;
                
            } catch (Exception $e) {
                log_message('error', 'Booking cleanup error for ID ' . $booking['id'] . ': ' . $e->getMessage());
            }
        }
        
        echo "\n=== Booking Cleanup Summary ===\n";
        echo "Cancelled: $cancelled_count\n";
        echo "===============================\n\n";
        
        log_message('info', "Booking cleanup completed: $cancelled_count cancelled");
    }

    // ================================================================
    // TEST EMAIL COMMAND
    // ================================================================

    /**
     * Send test email
     * Usage: php index.php cli test_email admin@example.com
     */
    public function test_email($recipient = null)
    {
        if (!$recipient) {
            echo "Usage: php index.php cli test_email <email>\n";
            return;
        }
        
        echo "Sending test email to $recipient...\n";
        
        $email_data = [
            'recipient_email' => $recipient,
            'recipient_name' => 'Test User',
            'subject' => 'Test Email - Bengkel Terdekat',
            'body' => 'Ini adalah email test dari sistem Bengkel Terdekat. Jika Anda menerima email ini, konfigurasi SMTP berfungsi dengan baik.',
            'template_key' => null
        ];
        
        $result = $this->notification_model->send_notification($email_data);
        
        if ($result) {
            echo "✓ Test email sent successfully!\n";
        } else {
            echo "✗ Failed to send test email.\n";
        }
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Calculate months between two dates
     */
    private function _calculate_months_since($date_string)
    {
        if (empty($date_string)) {
            return 999; // Large number if no date
        }
        
        $last_service = new DateTime($date_string);
        $now = new DateTime();
        $interval = $last_service->diff($now);
        
        return $interval->m + ($interval->y * 12);
    }
}
