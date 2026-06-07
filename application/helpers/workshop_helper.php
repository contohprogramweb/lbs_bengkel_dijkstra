<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Workshop Helper
 * 
 * Provides helper functions specific to workshop owner operations.
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */

// --------------------------------------------------------------------
// Workshop Dashboard Helpers
// --------------------------------------------------------------------

if ( ! function_exists('format_workshop_stats'))
{
    /**
     * Format statistics for workshop dashboard
     * 
     * @param int $number Raw number
     * @return string Formatted number
     */
    function format_workshop_stats($number)
    {
        if ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }
        return number_format($number);
    }
}

// --------------------------------------------------------------------
// Booking Status Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_booking_status_label'))
{
    /**
     * Get booking status label
     * 
     * @param string $status Booking status
     * @return string Status label
     */
    function get_booking_status_label($status)
    {
        $labels = [
            'pending' => 'Menunggu Konfirmasi',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];
        
        return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
    }
}

if ( ! function_exists('get_booking_status_class'))
{
    /**
     * Get booking status badge class
     * 
     * @param string $status Booking status
     * @return string CSS class
     */
    function get_booking_status_class($status)
    {
        $classes = [
            'pending' => 'badge-warning',
            'accepted' => 'badge-info',
            'rejected' => 'badge-danger',
            'in_progress' => 'badge-primary',
            'completed' => 'badge-success',
            'cancelled' => 'badge-secondary'
        ];
        
        return isset($classes[$status]) ? $classes[$status] : 'badge-secondary';
    }
}

// --------------------------------------------------------------------
// Service Category Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_service_category_label'))
{
    /**
     * Get service category label
     * 
     * @param string $category Category slug
     * @return string Category label
     */
    function get_service_category_label($category)
    {
        $labels = [
            'sparepart' => 'Sparepart',
            'servis' => 'Servis Berkala',
            'cat' => 'Pengecatan',
            'ban' => 'Ban & Velg',
            'aki' => 'Aki & Kelistrikan',
            'tuning' => 'Tuning & Modifikasi',
            'lainnya' => 'Lainnya'
        ];
        
        return isset($labels[$category]) ? $labels[$category] : ucfirst($category);
    }
}

if ( ! function_exists('get_service_category_icon'))
{
    /**
     * Get service category icon
     * 
     * @param string $category Category slug
     * @return string FontAwesome icon class
     */
    function get_service_category_icon($category)
    {
        $icons = [
            'sparepart' => 'fa-cogs',
            'servis' => 'fa-wrench',
            'cat' => 'fa-paint-roller',
            'ban' => 'fa-circle',
            'aki' => 'fa-car-battery',
            'tuning' => 'fa-tachometer-alt',
            'lainnya' => 'fa-tools'
        ];
        
        return isset($icons[$category]) ? $icons[$category] : 'fa-tools';
    }
}

// --------------------------------------------------------------------
// Mechanic Management Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_mechanic_status_label'))
{
    /**
     * Get mechanic status label
     * 
     * @param string $status Mechanic status
     * @return string Status label
     */
    function get_mechanic_status_label($status)
    {
        $labels = [
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'on_leave' => 'Cuti'
        ];
        
        return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
    }
}

if ( ! function_exists('get_mechanic_status_class'))
{
    /**
     * Get mechanic status badge class
     * 
     * @param string $status Mechanic status
     * @return string CSS class
     */
    function get_mechanic_status_class($status)
    {
        $classes = [
            'active' => 'badge-success',
            'inactive' => 'badge-secondary',
            'on_leave' => 'badge-warning'
        ];
        
        return isset($classes[$status]) ? $classes[$status] : 'badge-secondary';
    }
}

if ( ! function_exists('get_mechanic_availability_label'))
{
    /**
     * Get mechanic availability label
     * 
     * @param bool $is_available Availability status
     * @return string Status label
     */
    function get_mechanic_availability_label($is_available)
    {
        return $is_available ? 'Tersedia' : 'Tidak Tersedia';
    }
}

if ( ! function_exists('get_mechanic_availability_class'))
{
    /**
     * Get mechanic availability badge class
     * 
     * @param bool $is_available Availability status
     * @return string CSS class
     */
    function get_mechanic_availability_class($is_available)
    {
        return $is_available ? 'badge-success' : 'badge-danger';
    }
}

// --------------------------------------------------------------------
// Schedule Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_day_name'))
{
    /**
     * Get Indonesian day name
     * 
     * @param string $day Day slug (monday, tuesday, etc.)
     * @return string Day name in Indonesian
     */
    function get_day_name($day)
    {
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu'
        ];
        
        return isset($days[$day]) ? $days[$day] : ucfirst($day);
    }
}

if ( ! function_exists('format_time_range'))
{
    /**
     * Format time range
     * 
     * @param string $start Start time (HH:MM)
     * @param string $end End time (HH:MM)
     * @return string Formatted time range
     */
    function format_time_range($start, $end)
    {
        return date('H:i', strtotime($start)) . ' - ' . date('H:i', strtotime($end));
    }
}

if ( ! function_exists('get_schedule_status_label'))
{
    /**
     * Get schedule status label
     * 
     * @param bool $is_open Open status
     * @return string Status label
     */
    function get_schedule_status_label($is_open)
    {
        return $is_open ? 'Buka' : 'Tutup';
    }
}

if ( ! function_exists('get_schedule_status_class'))
{
    /**
     * Get schedule status badge class
     * 
     * @param bool $is_open Open status
     * @return string CSS class
     */
    function get_schedule_status_class($is_open)
    {
        return $is_open ? 'badge-success' : 'badge-danger';
    }
}

// --------------------------------------------------------------------
// Order/Job Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_job_priority_label'))
{
    /**
     * Get job priority label
     * 
     * @param string $priority Priority level
     * @return string Priority label
     */
    function get_job_priority_label($priority)
    {
        $labels = [
            'low' => 'Rendah',
            'normal' => 'Normal',
            'high' => 'Tinggi',
            'urgent' => 'Darurat'
        ];
        
        return isset($labels[$priority]) ? $labels[$priority] : ucfirst($priority);
    }
}

if ( ! function_exists('get_job_priority_class'))
{
    /**
     * Get job priority badge class
     * 
     * @param string $priority Priority level
     * @return string CSS class
     */
    function get_job_priority_class($priority)
    {
        $classes = [
            'low' => 'badge-secondary',
            'normal' => 'badge-info',
            'high' => 'badge-warning',
            'urgent' => 'badge-danger'
        ];
        
        return isset($classes[$priority]) ? $classes[$priority] : 'badge-secondary';
    }
}

// --------------------------------------------------------------------
// Invoice Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_payment_status_label'))
{
    /**
     * Get payment status label
     * 
     * @param string $status Payment status
     * @return string Status label
     */
    function get_payment_status_label($status)
    {
        $labels = [
            'unpaid' => 'Belum Dibayar',
            'partial' => 'Dibayar Sebagian',
            'paid' => 'Lunas',
            'overdue' => 'Jatuh Tempo'
        ];
        
        return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
    }
}

if ( ! function_exists('get_payment_status_class'))
{
    /**
     * Get payment status badge class
     * 
     * @param string $status Payment status
     * @return string CSS class
     */
    function get_payment_status_class($status)
    {
        $classes = [
            'unpaid' => 'badge-danger',
            'partial' => 'badge-warning',
            'paid' => 'badge-success',
            'overdue' => 'badge-dark'
        ];
        
        return isset($classes[$status]) ? $classes[$status] : 'badge-secondary';
    }
}

if ( ! function_exists('format_currency'))
{
    /**
     * Format currency (Rupiah)
     * 
     * @param float $amount Amount
     * @return string Formatted currency
     */
    function format_currency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

// --------------------------------------------------------------------
// Workshop Verification Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_workshop_verification_badge'))
{
    /**
     * Get workshop verification badge HTML
     * 
     * @param int $is_verified Verification status
     * @return string Badge HTML
     */
    function get_workshop_verification_badge($is_verified)
    {
        if ($is_verified) {
            return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Terverifikasi</span>';
        }
        return '<span class="badge badge-warning"><i class="fas fa-clock"></i> Menunggu Verifikasi</span>';
    }
}

/* End of file workshop_helper.php */
/* Location: ./application/helpers/workshop_helper.php */
