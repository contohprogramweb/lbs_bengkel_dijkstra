<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Controller
 * 
 * Provides authentication, CSRF protection, and common functionality
 * for all controllers in the application.
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */

class MY_Controller extends CI_Controller {

    /**
     * Current logged-in user data
     * @var object|null
     */
    protected $current_user = NULL;

    /**
     * User ID if logged in
     * @var int|null
     */
    protected $user_id = NULL;

    /**
     * User role
     * @var string|null
     */
    protected $user_role = NULL;

    /**
     * Flash message data
     * @var array
     */
    protected $flash_data = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Load required libraries and helpers
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'form', 'security']);
        $this->load->helper('app');

        // Load app configuration
        $this->config->load('app');

        // Enable CSRF protection globally
        $this->security->csrf_protect = TRUE;

        // Initialize authenticated user
        $this->_init_auth();

        // Set timezone
        date_default_timezone_set($this->config->item('app_timezone'));
    }

    /**
     * Initialize authentication
     */
    protected function _init_auth()
    {
        $user_data = $this->session->userdata('logged_in');

        if ($user_data && isset($user_data['user_id'])) {
            $this->user_id = $user_data['user_id'];
            $this->user_role = $user_data['role'];
            $this->current_user = (object) $user_data;
            
            // Update last login time
            $this->load->model('user_model');
            $this->user_model->update_last_login($this->user_id);
        }
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    protected function is_logged_in()
    {
        return $this->user_id !== NULL;
    }

    /**
     * Require user to be logged in
     * Redirect to login page if not authenticated
     */
    protected function require_login()
    {
        if (!$this->is_logged_in()) {
            $this->session->set_flashdata('error', 'Anda harus login terlebih dahulu.');
            redirect('auth/login');
        }
    }

    /**
     * Require specific role(s)
     * @param string|array $roles Single role or array of roles
     */
    protected function require_role($roles)
    {
        $this->require_login();

        $roles = is_array($roles) ? $roles : [$roles];

        if (!in_array($this->user_role, $roles)) {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
    }

    /**
     * Require admin role
     */
    protected function require_admin()
    {
        $this->require_role('admin');
    }

    /**
     * Require workshop owner role
     */
    protected function require_workshop_owner()
    {
        $this->require_role(['workshop_owner', 'mechanic']);
    }

    /**
     * Require mechanic role
     */
    protected function require_mechanic()
    {
        $this->require_role('mechanic');
    }

    /**
     * Require customer role
     */
    protected function require_customer()
    {
        $this->require_role('customer');
    }

    /**
     * Hash password using BCRYPT
     * @param string $password Plain text password
     * @return string Hashed password
     */
    protected function hash_password($password)
    {
        $cost = $this->config->item('password_cost') ?: 10;
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    /**
     * Verify password
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool
     */
    protected function verify_password($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Set flash message
     * @param string $type success, error, warning, info
     * @param string $message Message content
     */
    protected function set_flash($type, $message)
    {
        $this->session->set_flashdata($type, $message);
    }

    /**
     * Get system setting value
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    protected function get_setting($key, $default = NULL)
    {
        static $settings = [];

        if (empty($settings)) {
            $this->load->model('system_setting_model');
            $settings = $this->system_setting_model->get_all_settings();
        }

        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    /**
     * Render view with layout
     * @param string $view View file path
     * @param array $data Data to pass to view
     * @param bool $return Whether to return instead of output
     * @param string|null $layout Layout to use (null for auto-detect)
     * @return string|void
     */
    protected function render($view, $data = [], $return = FALSE, $layout = NULL)
    {
        // Common data for all views
        $common_data = [
            'current_user' => $this->current_user,
            'user_id' => $this->user_id,
            'user_role' => $this->user_role,
            'app_name' => $this->config->item('app_name'),
            'app_version' => $this->config->item('app_version'),
        ];

        $data = array_merge($common_data, $data);


        // Render the view content first
        $content = $this->load->view($view, $data, TRUE);

        // Determine which layout to use based on user role or explicit parameter
        if ($layout === NULL) {
            $layout = 'layouts/user_layout';
            if ($this->user_role === 'customer') {
                $layout = 'layouts/user_layout';
            } elseif ($this->user_role === 'workshop_owner' || $this->user_role === 'mechanic') {
                $layout = 'layouts/workshop_layout';
            } elseif ($this->user_role === 'admin') {
                $layout = 'layouts/admin_layout';
            }
        }

        // Merge content into layout data
        $layout_data = array_merge($data, ['content_for_layout' => $content]);
        
        if ($return) {
             
			return $this->load->view($layout, $layout_data, TRUE);
        }

         
		$this->load->view($layout, $layout_data);
    }

    /**
     * JSON response helper
     * @param mixed $data Response data
     * @param int $status_code HTTP status code
     * @param string $message Optional message
     */
    protected function json_response($data, $status_code = 200, $message = 'Success')
    {
        $response = [
            'success' => $status_code >= 200 && $status_code < 300,
            'message' => $message,
            'data' => $data
        ];

        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * Error JSON response
     * @param string $message Error message
     * @param int $status_code HTTP status code
     * @param mixed $errors Additional error details
     */
    protected function json_error($message, $status_code = 400, $errors = NULL)
    {
        $response = [
            'success' => FALSE,
            'message' => $message,
        ];

        if ($errors !== NULL) {
            $response['errors'] = $errors;
        }

        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}

/**
 * Public Controller
 * 
 * Base controller for public-facing pages (no authentication required)
 */
class Public_Controller extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        // No authentication required
    }
}

/**
 * Authenticated Controller
 * 
 * Base controller for pages requiring authentication
 */
class Authenticated_Controller extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_login();
    }
}

/**
 * Admin Controller
 * 
 * Base controller for admin pages
 */
class Admin_Controller extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_admin();
        
        // Load admin-specific libraries/helpers
        $this->load->helper('admin');
    }
}

/**
 * Workshop Controller
 * 
 * Base controller for workshop owner pages
 */
class Workshop_Controller extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_workshop_owner();
    }
}

/**
 * Mechanic Controller
 * 
 * Base controller for mechanic pages
 */
class Mechanic_Controller extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_mechanic();
    }
}

/**
 * Customer Controller
 * 
 * Base controller for customer pages
 */
class Customer_Controller extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_customer();
    }
}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */
