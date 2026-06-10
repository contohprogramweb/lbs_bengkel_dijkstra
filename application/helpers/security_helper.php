<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Security Helper
 * 
 * Additional security functions for XSS prevention, file validation, etc.
 */

/**
 * Escape output for XSS prevention
 * Use this instead of htmlspecialchars directly
 * 
 * @param string $str Input string
 * @return string Escaped string
 */
if (!function_exists('e')) {
    function e($str) {
        if (is_array($str)) {
            return array_map('e', $str);
        }
        return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

/**
 * Alias for e() function - compatible with CI3 esc()
 * 
 * @param string $str Input string
 * @return string Escaped string
 */
if (!function_exists('esc')) {
    function esc($str) {
        return e($str);
    }
}

/**
 * Validate uploaded file for security
 * 
 * @param array $file $_FILES['field'] array
 * @param array $allowed_types Allowed MIME types
 * @param int $max_size_kb Maximum size in KB
 * @return array ['valid' => bool, 'error' => string, 'safe_name' => string]
 */
if (!function_exists('validate_uploaded_file')) {
    function validate_uploaded_file($file, $allowed_types = ['image/jpeg', 'image/png'], $max_size_kb = 2048) {
        $result = ['valid' => FALSE, 'error' => '', 'safe_name' => ''];
        
        // Check if file was uploaded
        if (!isset($file) || $file['error'] == UPLOAD_ERR_NO_FILE) {
            $result['valid'] = TRUE; // No file is OK for optional uploads
            return $result;
        }
        
        // Check upload errors
        if ($file['error'] != UPLOAD_ERR_OK) {
            $result['error'] = 'Upload error code: ' . $file['error'];
            return $result;
        }
        
        // Check file size
        $file_size_kb = $file['size'] / 1024;
        if ($file_size_kb > $max_size_kb) {
            $result['error'] = 'File terlalu besar. Maksimal ' . $max_size_kb . ' KB.';
            return $result;
        }
        
        // Check MIME type (not just extension!)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            $result['error'] = 'Tipe file tidak diizinkan. Hanya ' . implode(', ', str_replace('image/', '', $allowed_types)) . '.';
            return $result;
        }
        
        // Generate random safe filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safe_name = bin2hex(random_bytes(16)) . '_' . time() . '.' . strtolower($extension);
        
        $result['valid'] = TRUE;
        $result['safe_name'] = $safe_name;
        
        return $result;
    }
}

/**
 * Sanitize input data recursively
 * 
 * @param mixed $data Input data (string or array)
 * @return mixed Sanitized data
 */
if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        if (is_array($data)) {
            return array_map('sanitize_input', $data);
        }
        // Remove tags and trim
        return trim(strip_tags($data ?? ''));
    }
}

/**
 * Generate CSRF token HTML input field
 * 
 * @return string HTML input field
 */
if (!function_exists('csrf_field')) {
    function csrf_field() {
        $CI =& get_instance();
        $token_name = $CI->security->get_csrf_token_name();
        $token_hash = $CI->security->get_csrf_hash();
        return '<input type="hidden" name="' . $token_name . '" value="' . $token_hash . '">';
    }
}

/**
 * Get CSRF token array for AJAX requests
 * 
 * @return array ['token_name' => string, 'token_hash' => string]
 */
if (!function_exists('csrf_array')) {
    function csrf_array() {
        $CI =& get_instance();
        return [
            $CI->security->get_csrf_token_name() => $CI->security->get_csrf_hash()
        ];
    }
}
