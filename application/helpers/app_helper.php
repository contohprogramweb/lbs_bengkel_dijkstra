<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Application Helper
 * 
 * Common helper functions for the application
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */

// --------------------------------------------------------------------
// Date/Time Helpers
// --------------------------------------------------------------------

if ( ! function_exists('format_date'))
{
    /**
     * Format date for display
     * @param string $date Date string
     * @param string $format Output format (default: d/m/Y)
     * @return string
     */
    function format_date($date, $format = NULL)
    {
        if (empty($date)) return '';
        
        $CI =& get_instance();
        $format = $format ?: $CI->config->item('display_date_format');
        
        if ($date instanceof DateTime) {
            return $date->format($format);
        }
        
        return date($format, strtotime($date));
    }
}

if ( ! function_exists('format_datetime'))
{
    /**
     * Format datetime for display
     * @param string $datetime Datetime string
     * @param string $format Output format
     * @return string
     */
    function format_datetime($datetime, $format = NULL)
    {
        if (empty($datetime)) return '';
        
        $CI =& get_instance();
        $format = $format ?: $CI->config->item('display_datetime_format');
        
        if ($datetime instanceof DateTime) {
            return $datetime->format($format);
        }
        
        return date($format, strtotime($datetime));
    }
}

if ( ! function_exists('time_ago'))
{
    /**
     * Convert datetime to time ago format
     * @param string $datetime Datetime string
     * @return string
     */
    function time_ago($datetime)
    {
        if (empty($datetime)) return '';
        
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' menit yang lalu';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' jam yang lalu';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' hari yang lalu';
        } else {
            return format_date($datetime);
        }
    }
}

// --------------------------------------------------------------------
// Status Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_booking_status_label'))
{
    /**
     * Get booking status label
     * @param string $status Status key
     * @return string
     */
    function get_booking_status_label($status)
    {
        $CI =& get_instance();
        $statuses = $CI->config->item('booking_status');
        return isset($statuses[$status]) ? $statuses[$status] : $status;
    }
}

if ( ! function_exists('get_booking_status_class'))
{
    /**
     * Get Bootstrap class for booking status
     * @param string $status Status key
     * @return string
     */
    function get_booking_status_class($status)
    {
        $classes = [
            'pending' => 'warning',
            'accepted' => 'info',
            'rejected' => 'danger',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'secondary',
            'no_show' => 'dark'
        ];
        
        return isset($classes[$status]) ? $classes[$status] : 'secondary';
    }
}

if ( ! function_exists('get_workshop_status_label'))
{
    /**
     * Get workshop status label
     * @param string $status Status key
     * @return string
     */
    function get_workshop_status_label($status)
    {
        $CI =& get_instance();
        $statuses = $CI->config->item('workshop_status');
        return isset($statuses[$status]) ? $statuses[$status] : $status;
    }
}

if ( ! function_exists('get_user_role_label'))
{
    /**
     * Get user role label
     * @param string $role Role key
     * @return string
     */
    function get_user_role_label($role)
    {
        $CI =& get_instance();
        $roles = $CI->config->item('user_roles');
        return isset($roles[$role]) ? $roles[$role] : $role;
    }
}

// --------------------------------------------------------------------
// Rating Helpers
// --------------------------------------------------------------------

if ( ! function_exists('render_stars'))
{
    /**
     * Render star rating
     * @param float $rating Rating value (0-5)
     * @param int $max Max stars
     * @return string HTML
     */
    function render_stars($rating, $max = 5)
    {
        $rating = min(max(0, $rating), 5);
        $full_stars = floor($rating);
        $has_half = ($rating - $full_stars) >= 0.5;
        $empty_stars = $max - $full_stars - ($has_half ? 1 : 0);
        
        $html = '<span class="star-rating">';
        $html .= str_repeat('<i class="fas fa-star text-warning"></i>', $full_stars);
        $html .= $has_half ? '<i class="fas fa-star-half-alt text-warning"></i>' : '';
        $html .= str_repeat('<i class="far fa-star text-warning"></i>', $empty_stars);
        $html .= '</span>';
        
        return $html;
    }
}

// --------------------------------------------------------------------
// String Helpers
// --------------------------------------------------------------------

if ( ! function_exists('generate_booking_number'))
{
    /**
     * Generate unique booking number
     * @return string
     */
    function generate_booking_number()
    {
        return 'BKG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}

if ( ! function_exists('generate_emergency_number'))
{
    /**
     * Generate unique emergency request number
     * @return string
     */
    function generate_emergency_number()
    {
        return 'EMG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}

if ( ! function_exists('truncate_text'))
{
    /**
     * Truncate text with ellipsis
     * @param string $text Text to truncate
     * @param int $length Max length
     * @return string
     */
    function truncate_text($text, $length = 100)
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . '...';
    }
}

// --------------------------------------------------------------------
// URL Helpers
// --------------------------------------------------------------------

if ( ! function_exists('asset_url'))
{
    /**
     * Get asset URL
     * @param string $path Asset path
     * @return string
     */
    function asset_url($path = '')
    {
        return base_url('assets/' . ltrim($path, '/'));
    }
}

if ( ! function_exists('upload_url'))
{
    /**
     * Get upload file URL
     * @param string $path File path relative to uploads folder
     * @return string
     */
    function upload_url($path = '')
    {
        return base_url('uploads/' . ltrim($path, '/'));
    }
}

// --------------------------------------------------------------------
// Misc Helpers
// --------------------------------------------------------------------

if ( ! function_exists('is_active'))
{
    /**
     * Check if current URI matches given path
     * @param string $uri URI to check
     * @param bool $strict Strict match or starts with
     * @return string 'active' class or empty
     */
    function is_active($uri, $strict = FALSE)
    {
        $CI =& get_instance();
        $current = $CI->uri->uri_string();
        
        if ($strict) {
            return $current === $uri ? 'active' : '';
        }
        
        return strpos($current, $uri) === 0 ? 'active' : '';
    }
}

if ( ! function_exists('gravatar'))
{
    /**
     * Get Gravatar URL
     * @param string $email Email address
     * @param int $size Size in pixels
     * @return string
     */
    function gravatar($email, $size = 80)
    {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
    }
}

if ( ! function_exists('calculate_distance'))
{
    /**
     * Calculate distance between two coordinates (Haversine formula)
     * @param float $lat1 Latitude 1
     * @param float $lon1 Longitude 1
     * @param float $lat2 Latitude 2
     * @param float $lon2 Longitude 2
     * @return float Distance in kilometers
     */
    function calculate_distance($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earth_radius * $c;
    }
}

/* End of file app_helper.php */
/* Location: ./application/helpers/app_helper.php */
