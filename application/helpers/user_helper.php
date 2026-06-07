<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User/Customer Helper
 * 
 * Provides helper functions specific to customer/user operations.
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */

// --------------------------------------------------------------------
// User Dashboard Helpers
// --------------------------------------------------------------------

if ( ! function_exists('format_user_stats'))
{
    /**
     * Format statistics for user dashboard
     * 
     * @param int $number Raw number
     * @return string Formatted number
     */
    function format_user_stats($number)
    {
        return number_format($number);
    }
}

// --------------------------------------------------------------------
// Vehicle Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_vehicle_type_label'))
{
    /**
     * Get vehicle type label
     * 
     * @param string $type Vehicle type
     * @return string Type label
     */
    function get_vehicle_type_label($type)
    {
        $labels = [
            'car' => 'Mobil',
            'motorcycle' => 'Motor',
            'truck' => 'Truk',
            'bus' => 'Bus',
            'van' => 'Van'
        ];
        
        return isset($labels[$type]) ? $labels[$type] : ucfirst($type);
    }
}

if ( ! function_exists('get_vehicle_type_icon'))
{
    /**
     * Get vehicle type icon
     * 
     * @param string $type Vehicle type
     * @return string FontAwesome icon class
     */
    function get_vehicle_type_icon($type)
    {
        $icons = [
            'car' => 'fa-car',
            'motorcycle' => 'fa-motorcycle',
            'truck' => 'fa-truck',
            'bus' => 'fa-bus',
            'van' => 'fa-shuttle-van'
        ];
        
        return isset($icons[$type]) ? $icons[$type] : 'fa-car';
    }
}

if ( ! function_exists('format_vehicle_info'))
{
    /**
     * Format vehicle information display
     * 
     * @param object $vehicle Vehicle object
     * @return string Formatted vehicle info
     */
    function format_vehicle_info($vehicle)
    {
        if (!$vehicle) return '-';
        
        $info = get_vehicle_type_label($vehicle->vehicle_type);
        $info .= ' - ' . $vehicle->brand;
        $info .= ' ' . $vehicle->model;
        
        if (!empty($vehicle->year)) {
            $info .= ' (' . $vehicle->year . ')';
        }
        
        if (!empty($vehicle->license_plate)) {
            $info .= ' - BPN: ' . $vehicle->license_plate;
        }
        
        return $info;
    }
}

// --------------------------------------------------------------------
// Booking Status Helpers (Customer View)
// --------------------------------------------------------------------

if ( ! function_exists('get_customer_booking_status_label'))
{
    /**
     * Get booking status label for customer view
     * 
     * @param string $status Booking status
     * @return string Status label
     */
    function get_customer_booking_status_label($status)
    {
        $labels = [
            'pending' => 'Menunggu Konfirmasi Bengkel',
            'accepted' => 'Diterima oleh Bengkel',
            'rejected' => 'Ditolak oleh Bengkel',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai Dikerjakan',
            'cancelled' => 'Dibatalkan'
        ];
        
        return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
    }
}

if ( ! function_exists('get_customer_booking_status_class'))
{
    /**
     * Get booking status badge class for customer view
     * 
     * @param string $status Booking status
     * @return string CSS class
     */
    function get_customer_booking_status_class($status)
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

if ( ! function_exists('can_cancel_booking'))
{
    /**
     * Check if booking can be cancelled by customer
     * 
     * @param string $status Booking status
     * @return bool
     */
    function can_cancel_booking($status)
    {
        $cancellable_statuses = ['pending', 'accepted'];
        return in_array($status, $cancellable_statuses);
    }
}

// --------------------------------------------------------------------
// Review Helpers
// --------------------------------------------------------------------

if ( ! function_exists('can_write_review'))
{
    /**
     * Check if customer can write a review for booking
     * 
     * @param string $status Booking status
     * @param bool $has_review Whether review already exists
     * @return bool
     */
    function can_write_review($status, $has_review = FALSE)
    {
        // Can only review completed bookings and haven't reviewed yet
        return $status === 'completed' && !$has_review;
    }
}

if ( ! function_exists('get_review_rating_label'))
{
    /**
     * Get rating label
     * 
     * @param float $rating Rating value
     * @return string Rating label
     */
    function get_review_rating_label($rating)
    {
        if ($rating >= 4.5) {
            return 'Sangat Baik';
        } elseif ($rating >= 3.5) {
            return 'Baik';
        } elseif ($rating >= 2.5) {
            return 'Cukup';
        } elseif ($rating >= 1.5) {
            return 'Kurang';
        }
        return 'Buruk';
    }
}

if ( ! function_exists('generate_star_rating_html'))
{
    /**
     * Generate star rating HTML for display
     * 
     * @param float $rating Rating value (0-5)
     * @param bool $interactive Whether stars are interactive
     * @return string HTML
     */
    function generate_star_rating_html($rating, $interactive = FALSE)
    {
        $html = '<div class="star-rating">';
        
        for ($i = 1; $i <= 5; $i++) {
            $class = $i <= $rating ? 'active' : '';
            
            if ($interactive) {
                $html .= '<span class="star" data-value="' . $i . '"><i class="fas fa-star ' . $class . '"></i></span>';
            } else {
                $html .= '<i class="fas fa-star ' . $class . '"></i>';
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }
}

// --------------------------------------------------------------------
// Notification Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_notification_type_label'))
{
    /**
     * Get notification type label
     * 
     * @param string $type Notification type
     * @return string Type label
     */
    function get_notification_type_label($type)
    {
        $labels = [
            'booking_status' => 'Status Booking',
            'booking_reminder' => 'Pengingat Booking',
            'review_request' => 'Minta Review',
            'payment_due' => 'Tagihan Pembayaran',
            'system' => 'Sistem',
            'promotion' => 'Promosi'
        ];
        
        return isset($labels[$type]) ? $labels[$type] : ucfirst($type);
    }
}

if ( ! function_exists('get_notification_type_icon'))
{
    /**
     * Get notification type icon
     * 
     * @param string $type Notification type
     * @return string FontAwesome icon class
     */
    function get_notification_type_icon($type)
    {
        $icons = [
            'booking_status' => 'fa-calendar-check',
            'booking_reminder' => 'fa-bell',
            'review_request' => 'fa-star',
            'payment_due' => 'fa-file-invoice-dollar',
            'system' => 'fa-cog',
            'promotion' => 'fa-tag'
        ];
        
        return isset($icons[$type]) ? $icons[$type] : 'fa-bell';
    }
}

if ( ! function_exists('is_notification_unread'))
{
    /**
     * Check if notification is unread
     * 
     * @param int $is_read Read status (0/1)
     * @return bool
     */
    function is_notification_unread($is_read)
    {
        return $is_read == 0;
    }
}

// --------------------------------------------------------------------
// Billing/Payment Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_invoice_status_label'))
{
    /**
     * Get invoice status label
     * 
     * @param string $status Invoice status
     * @return string Status label
     */
    function get_invoice_status_label($status)
    {
        $labels = [
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'paid' => 'Lunas',
            'overdue' => 'Jatuh Tempo',
            'cancelled' => 'Dibatalkan'
        ];
        
        return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
    }
}

if ( ! function_exists('get_invoice_status_class'))
{
    /**
     * Get invoice status badge class
     * 
     * @param string $status Invoice status
     * @return string CSS class
     */
    function get_invoice_status_class($status)
    {
        $classes = [
            'draft' => 'badge-secondary',
            'sent' => 'badge-info',
            'paid' => 'badge-success',
            'overdue' => 'badge-danger',
            'cancelled' => 'badge-dark'
        ];
        
        return isset($classes[$status]) ? $classes[$status] : 'badge-secondary';
    }
}

if ( ! function_exists('format_rupiah'))
{
    /**
     * Format amount as Rupiah
     * 
     * @param float $amount Amount
     * @param bool $show_symbol Whether to show Rp symbol
     * @return string Formatted amount
     */
    function format_rupiah($amount, $show_symbol = TRUE)
    {
        $formatted = number_format($amount, 0, ',', '.');
        
        if ($show_symbol) {
            return 'Rp ' . $formatted;
        }
        
        return $formatted;
    }
}

if ( ! function_exists('get_payment_method_label'))
{
    /**
     * Get payment method label
     * 
     * @param string $method Payment method
     * @return string Method label
     */
    function get_payment_method_label($method)
    {
        $labels = [
            'cash' => 'Tunai',
            'transfer' => 'Transfer Bank',
            'credit_card' => 'Kartu Kredit',
            'debit_card' => 'Kartu Debit',
            'e_wallet' => 'E-Wallet',
            'qris' => 'QRIS'
        ];
        
        return isset($labels[$method]) ? $labels[$method] : ucfirst($method);
    }
}

if ( ! function_exists('get_payment_method_icon'))
{
    /**
     * Get payment method icon
     * 
     * @param string $method Payment method
     * @return string FontAwesome icon class
     */
    function get_payment_method_icon($method)
    {
        $icons = [
            'cash' => 'fa-money-bill-wave',
            'transfer' => 'fa-university',
            'credit_card' => 'fa-credit-card',
            'debit_card' => 'fa-credit-card',
            'e_wallet' => 'fa-wallet',
            'qris' => 'fa-qrcode'
        ];
        
        return isset($icons[$method]) ? $icons[$method] : 'fa-money-bill';
    }
}

// --------------------------------------------------------------------
// Emergency Request Helpers
// --------------------------------------------------------------------

if ( ! function_exists('get_emergency_status_label'))
{
    /**
     * Get emergency request status label
     * 
     * @param string $status Emergency status
     * @return string Status label
     */
    function get_emergency_status_label($status)
    {
        $labels = [
            'pending' => 'Menunggu Respon',
            'accepted' => 'Diterima',
            'on_the_way' => 'Menuju Lokasi',
            'handling' => 'Sedang Ditangani',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];
        
        return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
    }
}

if ( ! function_exists('get_emergency_status_class'))
{
    /**
     * Get emergency status badge class
     * 
     * @param string $status Emergency status
     * @return string CSS class
     */
    function get_emergency_status_class($status)
    {
        $classes = [
            'pending' => 'badge-warning',
            'accepted' => 'badge-info',
            'on_the_way' => 'badge-primary',
            'handling' => 'badge-danger',
            'completed' => 'badge-success',
            'cancelled' => 'badge-secondary'
        ];
        
        return isset($classes[$status]) ? $classes[$status] : 'badge-secondary';
    }
}

// --------------------------------------------------------------------
// Workshop Display Helpers
// --------------------------------------------------------------------

if ( ! function_exists('calculate_distance_label'))
{
    /**
     * Format distance with appropriate unit
     * 
     * @param float $distance_km Distance in kilometers
     * @return string Formatted distance
     */
    function calculate_distance_label($distance_km)
    {
        if ($distance_km < 1) {
            return round($distance_km * 1000) . ' m';
        }
        return round($distance_km, 1) . ' km';
    }
}

if ( ! function_exists('get_open_now_status'))
{
    /**
     * Get workshop open status label
     * 
     * @param bool $is_open Whether workshop is currently open
     * @return string Status HTML
     */
    function get_open_now_status($is_open)
    {
        if ($is_open) {
            return '<span class="text-success"><i class="fas fa-circle small"></i> Buka Sekarang</span>';
        }
        return '<span class="text-danger"><i class="fas fa-circle small"></i> Tutup</span>';
    }
}

/* End of file user_helper.php */
/* Location: ./application/helpers/user_helper.php */
