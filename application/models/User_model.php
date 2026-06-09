<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Model
 * 
 * Handles user data operations including authentication, registration, and profile management.
 * Supports soft delete mechanism.
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class User_model extends CI_Model {

    /**
     * Table name
     */
    private $table = 'users';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // --------------------------------------------------------------------
    // Authentication Methods
    // --------------------------------------------------------------------

    /**
     * Find user by email (excluding deleted)
     * 
     * @param string $email User email
     * @return object|null User object or NULL
     */
    public function find_by_email($email)
    {
        return $this->db
            ->where('email', $email)
            ->where('is_deleted', 0)
            ->get($this->table)
            ->row();
    }

    /**
     * Find user by ID (excluding deleted)
     * 
     * @param int $id User ID
     * @return object|null User object or NULL
     */
    public function find_by_id($id)
    {
        return $this->db
            ->select('id, email, password, full_name, phone, role, is_active, is_deleted, avatar, last_login_at as last_login, created_at, updated_at, deleted_at')
            ->where('id', $id)
            ->where('is_deleted', 0)
            ->get($this->table)
            ->row();
    }

    /**
     * Authenticate user
     * 
     * @param string $email User email
     * @param string $password Plain text password
     * @return object|null User object if authenticated, NULL otherwise
     */
    public function authenticate($email, $password)
    {
        $user = $this->find_by_email($email);

        if (!$user) {
            return NULL;
        }

        if (!$user->is_active) {
            return NULL;
        }

        if (!password_verify($password, $user->password)) {
            return NULL;
        }

        return $user;
    }

    /**
     * Register new user
     * 
     * @param array $data User data
     * @return int|bool Insert ID or FALSE on failure
     */
    public function register($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
        $data['is_active'] = 1;
        $data['is_deleted'] = 0;
        $data['created_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }

        return FALSE;
    }

    /**
     * Check if email exists (excluding deleted)
     * 
     * @param string $email Email to check
     * @param int|null $exclude_id Exclude this ID from check
     * @return bool TRUE if exists, FALSE otherwise
     */
    public function email_exists($email, $exclude_id = NULL)
    {
        $this->db->where('email', $email)->where('is_deleted', 0);
        
        if ($exclude_id !== NULL) {
            $this->db->where('id !=', $exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    // --------------------------------------------------------------------
    // Profile Management
    // --------------------------------------------------------------------

    /**
     * Update user profile
     * 
     * @param int $user_id User ID
     * @param array $data Profile data
     * @return bool TRUE on success, FALSE on failure
     */
    public function update_profile($user_id, $data)
    {
        unset($data['password']);
        unset($data['role']);
        unset($data['is_active']);
        unset($data['is_deleted']);

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db
            ->where('id', $user_id)
            ->where('is_deleted', 0)
            ->update($this->table, $data);
    }

    /**
     * Update password
     * 
     * @param int $user_id User ID
     * @param string $new_password New password
     * @return bool TRUE on success, FALSE on failure
     */
    public function update_password($user_id, $new_password)
    {
        $data = [
            'password' => password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 10]),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->db
            ->where('id', $user_id)
            ->where('is_deleted', 0)
            ->update($this->table, $data);
    }

    /**
     * Update last login timestamp
     * 
     * @param int $user_id User ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function update_last_login($user_id)
    {
        $data = ['last_login_at' => date('Y-m-d H:i:s')];

        return $this->db
            ->where('id', $user_id)
            ->update($this->table, $data);
    }

    /**
     * Set email as verified
     * 
     * @param int $user_id User ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function verify_email($user_id)
    {
        $data = ['email_verified_at' => date('Y-m-d H:i:s')];

        return $this->db
            ->where('id', $user_id)
            ->update($this->table, $data);
    }

    // --------------------------------------------------------------------
    // Soft Delete Methods
    // --------------------------------------------------------------------

    /**
     * Soft delete user
     * 
     * @param int $user_id User ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function soft_delete($user_id)
    {
        $data = [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
            'is_active' => 0
        ];

        return $this->db
            ->where('id', $user_id)
            ->update($this->table, $data);
    }

    /**
     * Restore soft deleted user
     * 
     * @param int $user_id User ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function restore($user_id)
    {
        $data = [
            'is_deleted' => 0,
            'deleted_at' => NULL,
            'is_active' => 1
        ];

        return $this->db
            ->where('id', $user_id)
            ->update($this->table, $data);
    }

    // --------------------------------------------------------------------
    // Admin Methods
    // --------------------------------------------------------------------

    /**
     * Get all users (for admin)
     * 
     * @param int $limit Limit results
     * @param int $offset Offset
     * @param string|null $role Filter by role
     * @return array Users array
     */
    public function get_all($limit = 100, $offset = 0, $role = NULL)
    {
        $this->db->where('is_deleted', 0);
        
        if ($role !== NULL) {
            $this->db->where('role', $role);
        }

        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get($this->table)
            ->result();
    }

    /**
     * Count users
     * 
     * @param string|null $role Filter by role
     * @return int Total count
     */
    public function count_all($role = NULL)
    {
        $this->db->where('is_deleted', 0);
        
        if ($role !== NULL) {
            $this->db->where('role', $role);
        }

        return $this->db->count_all_results($this->table);
    }

    /**
     * Deactivate user
     * 
     * @param int $user_id User ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function deactivate($user_id)
    {
        return $this->db
            ->where('id', $user_id)
            ->update($this->table, ['is_active' => 0]);
    }

    /**
     * Activate user
     * 
     * @param int $user_id User ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function activate($user_id)
    {
        return $this->db
            ->where('id', $user_id)
            ->update($this->table, ['is_active' => 1]);
    }
}

/* End of file User_model.php */
/* Location: ./application/models/User_model.php */
