<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin Helper
 * 
 * Provides helper functions specific to admin operations.
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */

// --------------------------------------------------------------------
// Admin Dashboard Helpers
// --------------------------------------------------------------------

if ( ! function_exists('format_admin_stats'))
{
    /**
     * Format statistics for admin dashboard
     * 
     * @param int $number Raw number
     * @return string Formatted number
     */
    function format_admin_stats($number)
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }
        return number_format($number);
    }
}

// --------------------------------------------------------------------
// User Management Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_role_label'))
{
    /**
     * Get human-readable role label
     * 
     * @param string $role Role slug
     * @return string Role label
     */
    function get_role_label($role)
    {
        $labels = [
            'admin' => 'Administrator',
            'workshop_owner' => 'Pemilik Bengkel',
            'mechanic' => 'Mekanik',
            'customer' => 'Pelanggan'
        ];
        
        return isset($labels[$role]) ? $labels[$role] : ucfirst($role);
    }
}

if ( ! function_exists('get_role_badge_class'))
{
    /**
     * Get Bootstrap badge class for role
     * 
     * @param string $role Role slug
     * @return string CSS class
     */
    function get_role_badge_class($role)
    {
        $classes = [
            'admin' => 'badge-danger',
            'workshop_owner' => 'badge-warning',
            'mechanic' => 'badge-info',
            'customer' => 'badge-success'
        ];
        
        return isset($classes[$role]) ? $classes[$role] : 'badge-secondary';
    }
}

if ( ! function_exists('get_user_status_label'))
{
    /**
     * Get user status label
     * 
     * @param int $status Status code (1=active, 0=inactive)
     * @return string Status label
     */
    function get_user_status_label($status)
    {
        return $status == 1 ? 'Aktif' : 'Nonaktif';
    }
}

if ( ! function_exists('get_user_status_class'))
{
    /**
     * Get status badge class
     * 
     * @param int $status Status code
     * @return string CSS class
     */
    function get_user_status_class($status)
    {
        return $status == 1 ? 'badge-success' : 'badge-secondary';
    }
}

// --------------------------------------------------------------------
// Workshop Management Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_verification_status_label'))
{
    /**
     * Get workshop verification status label
     * 
     * @param int $status Verification status (0=pending, 1=verified, 2=rejected)
     * @return string Status label
     */
    function get_verification_status_label($status)
    {
        $labels = [
            0 => 'Menunggu Verifikasi',
            1 => 'Terverifikasi',
            2 => 'Ditolak'
        ];
        
        return isset($labels[$status]) ? $labels[$status] : 'Unknown';
    }
}

if ( ! function_exists('get_verification_status_class'))
{
    /**
     * Get verification status badge class
     * 
     * @param int $status Verification status
     * @return string CSS class
     */
    function get_verification_status_class($status)
    {
        $classes = [
            0 => 'badge-warning',
            1 => 'badge-success',
            2 => 'badge-danger'
        ];
        
        return isset($classes[$status]) ? $classes[$status] : 'badge-secondary';
    }
}

if ( ! function_exists('get_featured_status_label'))
{
    /**
     * Get featured status label
     * 
     * @param int $is_featured Featured status (0/1)
     * @return string Status label
     */
    function get_featured_status_label($is_featured)
    {
        return $is_featured == 1 ? 'Featured' : 'Regular';
    }
}

if ( ! function_exists('get_featured_status_class'))
{
    /**
     * Get featured status badge class
     * 
     * @param int $is_featured Featured status
     * @return string CSS class
     */
    function get_featured_status_class($is_featured)
    {
        return $is_featured == 1 ? 'badge-primary' : 'badge-secondary';
    }
}

// --------------------------------------------------------------------
// Review Moderation Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_review_status_label'))
{
    /**
     * Get review status label
     * 
     * @param string $status Review status (pending, approved, rejected)
     * @return string Status label
     */
    function get_review_status_label($status)
    {
        $labels = [
            'pending' => 'Menunggu Moderasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak'
        ];
        
        return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
    }
}

if ( ! function_exists('get_review_status_class'))
{
    /**
     * Get review status badge class
     * 
     * @param string $status Review status
     * @return string CSS class
     */
    function get_review_status_class($status)
    {
        $classes = [
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger'
        ];
        
        return isset($classes[$status]) ? $classes[$status] : 'badge-secondary';
    }
}

if ( ! function_exists('get_rating_stars'))
{
    /**
     * Generate star rating HTML
     * 
     * @param float $rating Rating value (0-5)
     * @param bool $show_number Whether to show number
     * @return string HTML stars
     */
    function get_rating_stars($rating, $show_number = TRUE)
    {
        $stars = '';
        $full_stars = floor($rating);
        $half_star = ($rating - $full_stars) >= 0.5;
        
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $full_stars) {
                $stars .= '<i class="fas fa-star text-warning"></i>';
            } elseif ($i == $full_stars + 1 && $half_star) {
                $stars .= '<i class="fas fa-star-half-alt text-warning"></i>';
            } else {
                $stars .= '<i class="fas fa-star text-muted"></i>';
            }
        }
        
        if ($show_number) {
            $stars .= ' <span class="text-muted">(' . number_format($rating, 1) . ')</span>';
        }
        
        return $stars;
    }
}

// --------------------------------------------------------------------
// Activity Log Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_action_type_label'))
{
    /**
     * Get action type label
     * 
     * @param string $action_type Action type code
     * @return string Action label
     */
    function get_action_type_label($action_type)
    {
        $labels = [
            'USER_CREATE' => 'User Dibuat',
            'USER_UPDATE' => 'User Diperbarui',
            'USER_DELETE' => 'User Dihapus',
            'USER_ACTIVATE' => 'User Diaktifkan',
            'USER_DEACTIVATE' => 'User Dinonaktifkan',
            'WORKSHOP_CREATE' => 'Bengkel Dibuat',
            'WORKSHOP_UPDATE' => 'Bengkel Diperbarui',
            'WORKSHOP_VERIFY' => 'Bengkel Diverifikasi',
            'WORKSHOP_REJECT' => 'Bengkel Ditolak',
            'REVIEW_APPROVE' => 'Review Disetujui',
            'REVIEW_REJECT' => 'Review Ditolak',
            'SYSTEM_SETTING_UPDATE' => 'Pengaturan Sistem Diperbarui',
            'LOGIN' => 'Login',
            'LOGOUT' => 'Logout'
        ];
        
        return isset($labels[$action_type]) ? $labels[$action_type] : $action_type;
    }
}

if ( ! function_exists('format_activity_date'))
{
    /**
     * Format activity log date
     * 
     * @param int $timestamp Unix timestamp
     * @return string Formatted date
     */
    function format_activity_date($timestamp)
    {
        return date('d M Y H:i:s', $timestamp);
    }
}

// --------------------------------------------------------------------
// Chart Data Helpers
// --------------------------------------------------------------------

if ( ! function_exists('prepare_chart_data'))
{
    /**
     * Prepare data for Chart.js
     * 
     * @param array $data Array of ['label' => x, 'value' => y]
     * @return array Formatted for Chart.js
     */
    function prepare_chart_data($data)
    {
        return [
            'labels' => array_column($data, 'label'),
            'values' => array_column($data, 'value')
        ];
    }
}

/* End of file admin_helper.php */
/* Location: ./application/helpers/admin_helper.php */
