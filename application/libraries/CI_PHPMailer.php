<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PHPMailer Library for CodeIgniter 3
 * 
 * Integrates PHPMailer with CI3 for sending emails via SMTP
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once APPPATH . '../vendor/autoload.php';

class CI_PHPMailer {

    /**
     * PHPMailer instance
     * @var PHPMailer
     */
    protected $mailer;

    /**
     * CI instance
     * @var CI_Controller
     */
    protected $CI;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('app');
        
        $this->initialize();
    }

    /**
     * Initialize PHPMailer with config settings
     */
    protected function initialize()
    {
        $this->mailer = new PHPMailer(TRUE);

        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host       = $this->CI->config->item('smtp_host');
        $this->mailer->SMTPAuth   = TRUE;
        $this->mailer->Username   = $this->CI->config->item('smtp_user');
        $this->mailer->Password   = $this->CI->config->item('smtp_pass');
        $this->mailer->SMTPSecure = $this->CI->config->item('smtp_crypto');
        $this->mailer->Port       = $this->CI->config->item('smtp_port');
        
        // Debug mode
        if ($this->CI->config->item('smtp_debug')) {
            $this->mailer->SMTPDebug = 2;
            $this->mailer->Debugoutput = 'html';
        }

        // Default sender
        $this->mailer->setFrom(
            $this->CI->config->item('mail_from_email'),
            $this->CI->config->item('mail_from_name')
        );

        $this->mailer->isHTML(TRUE);
        $this->mailer->CharSet = 'UTF-8';
    }

    /**
     * Send email
     * 
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string $alt_body Alternative plain text body
     * @param array $attachments Array of file paths to attach
     * @return bool
     * @throws Exception
     */
    public function send($to, $subject, $body, $alt_body = '', $attachments = [])
    {
        try {
            // Clear all addresses and attachments
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Add recipients
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addAddress($name);
                    } else {
                        $this->mailer->addAddress($email, $name);
                    }
                }
            } else {
                $this->mailer->addAddress($to);
            }

            // Set subject and body
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            
            if (!empty($alt_body)) {
                $this->mailer->AltBody = $alt_body;
            } else {
                // Generate plain text from HTML if not provided
                $this->mailer->AltBody = strip_tags($body);
            }

            // Add attachments
            if (!empty($attachments) && is_array($attachments)) {
                foreach ($attachments as $file) {
                    $this->mailer->addAttachment($file);
                }
            }

            // Send
            return $this->mailer->send();

        } catch (Exception $e) {
            log_message('error', 'PHPMailer Error: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Send email with CC
     * 
     * @param string|array $to Recipient email(s)
     * @param string|array $cc CC email(s)
     * @param string $subject Email subject
     * @param string $body Email body
     * @return bool
     */
    public function send_with_cc($to, $cc, $subject, $body)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearCCs();

            // Add main recipients
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addAddress($name);
                    } else {
                        $this->mailer->addAddress($email, $name);
                    }
                }
            } else {
                $this->mailer->addAddress($to);
            }

            // Add CC recipients
            if (is_array($cc)) {
                foreach ($cc as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addCC($name);
                    } else {
                        $this->mailer->addCC($email, $name);
                    }
                }
            } else {
                $this->mailer->addCC($cc);
            }

            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = strip_tags($body);

            return $this->mailer->send();

        } catch (Exception $e) {
            log_message('error', 'PHPMailer Error: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Get the underlying PHPMailer instance
     * @return PHPMailer
     */
    public function get_mailer()
    {
        return $this->mailer;
    }

    /**
     * Test SMTP connection
     * @return bool
     */
    public function test_connection()
    {
        try {
            $this->mailer->SMTPDebug = 2;
            return $this->mailer->smtpConnect();
        } catch (Exception $e) {
            log_message('error', 'SMTP Connection Test Failed: ' . $e->getMessage());
            return FALSE;
        }
    }
}

/* End of file CI_PHPMailer.php */
/* Location: ./application/libraries/CI_PHPMailer.php */
