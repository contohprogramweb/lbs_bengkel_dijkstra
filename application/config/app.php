<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Application Configuration File
 * 
 * This file contains application-specific configurations including:
 * - SMTP settings for PHPMailer
 * - Upload directories
 * - System settings defaults
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */

// --------------------------------------------------------------------
// SMTP Configuration (PHPMailer)
// --------------------------------------------------------------------

$config['smtp_host'] = 'smtp.gmail.com';
$config['smtp_port'] = 587;
$config['smtp_user'] = ''; // Set in environment or database
$config['smtp_pass'] = ''; // Set in environment or database
$config['smtp_crypto'] = 'tls'; // tls or ssl
$config['smtp_debug'] = false; // Enable for debugging
$config['mail_from_email'] = 'noreply@bengkelterdekat.com';
$config['mail_from_name'] = 'Bengkel Terdekat';

// --------------------------------------------------------------------
// Upload Configuration
// --------------------------------------------------------------------

$config['upload_path_reviews'] = FCPATH . 'uploads/reviews/';
$config['upload_path_profiles'] = FCPATH . 'uploads/profiles/';
$config['upload_path_workshops'] = FCPATH . 'uploads/workshops/';
$config['upload_path_vehicles'] = FCPATH . 'uploads/vehicles/';

$config['allowed_types_reviews'] = 'jpg|jpeg|png|gif';
$config['allowed_types_profiles'] = 'jpg|jpeg|png';
$config['allowed_types_workshops'] = 'jpg|jpeg|png|pdf';
$config['allowed_types_vehicles'] = 'jpg|jpeg|png';

$config['max_size_reviews'] = 2048; // 2MB
$config['max_size_profiles'] = 1024; // 1MB
$config['max_size_workshops'] = 5120; // 5MB
$config['max_size_vehicles'] = 2048; // 2MB

// --------------------------------------------------------------------
// System Settings Defaults
// --------------------------------------------------------------------

$config['default_radius_darurat'] = 5; // km
$config['default_reminder_interval_km'] = 5000; // km
$config['default_reminder_interval_months'] = 6; // months

// --------------------------------------------------------------------
// Application Settings
// --------------------------------------------------------------------

$config['app_name'] = 'Bengkel Terdekat';
$config['app_version'] = '4.0';
$config['app_timezone'] = 'Asia/Jakarta';
$config['app_language'] = 'indonesian';

// Session settings
$config['session_timeout'] = 7200; // 2 hours in seconds

// Password settings
$config['password_algo'] = PASSWORD_BCRYPT;
$config['password_cost'] = 10;

// Pagination
$config['per_page'] = 10;

// Date formats
$config['date_format'] = 'Y-m-d';
$config['datetime_format'] = 'Y-m-d H:i:s';
$config['display_date_format'] = 'd/m/Y';
$config['display_datetime_format'] = 'd/m/Y H:i';

// --------------------------------------------------------------------
// Notification Event Keys
// --------------------------------------------------------------------

$config['notification_events'] = [
    'booking_accepted' => 'Booking Diterima',
    'booking_completed' => 'Booking Selesai',
    'reminder_service' => 'Pengingat Servis',
    'emergency_alert' => 'Alert Darurat',
    'booking_rejected' => 'Booking Ditolak',
    'booking_cancelled' => 'Booking Dibatalkan',
    'workshop_approved' => 'Workshop Disetujui',
    'workshop_rejected' => 'Workshop Ditolak',
    'password_reset' => 'Reset Password',
    'welcome_user' => 'Selamat Datang',
    'review_submitted' => 'Review Dikirim',
];

// --------------------------------------------------------------------
// Booking Status
// --------------------------------------------------------------------

$config['booking_status'] = [
    'pending' => 'Menunggu Persetujuan',
    'accepted' => 'Diterima',
    'rejected' => 'Ditolak',
    'in_progress' => 'Sedang Dikerjakan',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan',
    'no_show' => 'Tidak Hadir',
];

// --------------------------------------------------------------------
// Approval Status
// --------------------------------------------------------------------

$config['approval_status'] = [
    'pending' => 'Menunggu',
    'approved' => 'Disetujui',
    'rejected' => 'Ditolak',
];

// --------------------------------------------------------------------
// User Roles
// --------------------------------------------------------------------

$config['user_roles'] = [
    'admin' => 'Administrator',
    'workshop_owner' => 'Pemilik Workshop',
    'mechanic' => 'Mekanik',
    'customer' => 'Pelanggan',
];

// --------------------------------------------------------------------
// Emergency Request Status
// --------------------------------------------------------------------

$config['emergency_status'] = [
    'pending' => 'Menunggu',
    'assigned' => 'Ditugaskan',
    'in_progress' => 'Dalam Penanganan',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan',
];

// --------------------------------------------------------------------
// Workshop Status
// --------------------------------------------------------------------

$config['workshop_status'] = [
    'pending' => 'Menunggu Persetujuan',
    'active' => 'Aktif',
    'inactive' => 'Tidak Aktif',
    'suspended' => 'Ditangguhkan',
];

/* End of file app.php */
/* Location: ./application/config/app.php */
