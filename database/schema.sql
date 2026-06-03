-- ============================================
-- Database Schema for Aplikasi Bengkel Terdekat
-- Version: 4.0
-- Charset: utf8mb4_unicode_ci
-- ============================================

-- Create database (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS bengkel_terdekat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE bengkel_terdekat;

-- ============================================
-- TABLE: system_settings
-- Global system configuration (Reviewer #3)
-- ============================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    setting_type ENUM('string', 'integer', 'float', 'boolean', 'json') DEFAULT 'string',
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: users
-- User accounts for all roles
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'workshop_owner', 'mechanic', 'customer') NOT NULL DEFAULT 'customer',
    avatar VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: workshops
-- Workshop/garage information
-- ============================================
CREATE TABLE IF NOT EXISTS workshops (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    postal_code VARCHAR(10),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    phone VARCHAR(20),
    whatsapp VARCHAR(20),
    logo VARCHAR(255),
    photos JSON,
    rating_avg DECIMAL(3,2) DEFAULT 0,
    total_reviews INT UNSIGNED DEFAULT 0,
    status ENUM('pending', 'active', 'inactive', 'suspended') DEFAULT 'pending',
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    verified_at TIMESTAMP NULL,
    approved_by INT UNSIGNED,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    operating_hours JSON,
    services_offered JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_is_deleted (is_deleted),
    INDEX idx_city (city),
    INDEX idx_province (province),
    INDEX idx_coordinates (latitude, longitude),
    INDEX idx_rating (rating_avg)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: workshop_services
-- Services offered by each workshop (Prompt #3)
-- ============================================
CREATE TABLE IF NOT EXISTS workshop_services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workshop_id INT UNSIGNED NOT NULL,
    service_name VARCHAR(150) NOT NULL,
    service_category ENUM('sparepart', 'servis', 'cat', 'ban', 'aki', 'tuning', 'lainnya') DEFAULT 'servis',
    description TEXT,
    price_min DECIMAL(12,2),
    price_max DECIMAL(12,2),
    unit ENUM('fixed', 'range', 'per_hour') DEFAULT 'fixed',
    duration_minutes INT DEFAULT 60,
    is_available TINYINT(1) DEFAULT 1,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
    INDEX idx_workshop_id (workshop_id),
    INDEX idx_category (service_category),
    INDEX idx_is_available (is_available),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: vehicles
-- Customer vehicle information
-- ============================================
CREATE TABLE IF NOT EXISTS vehicles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    vehicle_number VARCHAR(20) NOT NULL,
    vehicle_type ENUM('motorcycle', 'car', 'truck', 'bus', 'other') NOT NULL,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50),
    year YEAR,
    color VARCHAR(30),
    engine_capacity VARCHAR(20),
    transmission ENUM('manual', 'automatic', 'cvt') DEFAULT 'manual',
    fuel_type ENUM('petrol', 'diesel', 'electric', 'hybrid') DEFAULT 'petrol',
    last_service_date DATE,
    last_service_km INT UNSIGNED,
    current_km INT UNSIGNED DEFAULT 0,
    notes TEXT,
    photo VARCHAR(255),
    is_primary TINYINT(1) DEFAULT 0,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    reminder_enabled TINYINT(1) DEFAULT 1 COMMENT 'BR-74: User can disable reminder per vehicle',
    reminder_snoozed_until DATE NULL COMMENT 'BR-73: Snooze reminder until this date',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_number (vehicle_number),
    INDEX idx_vehicle_type (vehicle_type),
    INDEX idx_is_deleted (is_deleted),
    INDEX idx_reminder_enabled (reminder_enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: workshop_schedules
-- Workshop operating schedules
-- ============================================
CREATE TABLE IF NOT EXISTS workshop_schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workshop_id INT UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL COMMENT '0=Sunday, 1=Monday, ..., 6=Saturday',
    open_time TIME NOT NULL,
    close_time TIME NOT NULL,
    is_open TINYINT(1) DEFAULT 1,
    slot_interval INT DEFAULT 60 COMMENT 'BR-82: Interval 30-240 menit (kelipatan 30)',
    capacity_per_slot INT DEFAULT 1 COMMENT 'BR-83: Kapasitas 1-20 kendaraan per slot',
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
    INDEX idx_workshop_id (workshop_id),
    INDEX idx_day (day_of_week),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: workshop_blocked_dates
-- Blocked dates (full day) for workshops
-- ============================================
CREATE TABLE IF NOT EXISTS workshop_blocked_dates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workshop_id INT UNSIGNED NOT NULL,
    blocked_date DATE NOT NULL,
    reason VARCHAR(255),
    is_full_day TINYINT(1) DEFAULT 1,
    blocked_by INT UNSIGNED,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_workshop_date (workshop_id, blocked_date),
    INDEX idx_blocked_date (blocked_date),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: workshop_blocked_slots
-- Blocked time slots for workshops
-- ============================================
CREATE TABLE IF NOT EXISTS workshop_blocked_slots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workshop_id INT UNSIGNED NOT NULL,
    slot_date DATE NOT NULL,
    slot_time TIME NOT NULL,
    reason VARCHAR(255),
    blocked_by INT UNSIGNED,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_workshop_date (workshop_id, slot_date),
    INDEX idx_slot_date (slot_date),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: mechanics
-- Mechanic profiles linked to workshops
-- ============================================
CREATE TABLE IF NOT EXISTS mechanics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    workshop_id INT UNSIGNED,
    specialization JSON COMMENT 'Array of specializations',
    experience_years INT UNSIGNED DEFAULT 0,
    certification TEXT,
    rating_avg DECIMAL(3,2) DEFAULT 0,
    total_reviews INT UNSIGNED DEFAULT 0,
    is_available TINYINT(1) DEFAULT 1,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_workshop_id (workshop_id),
    INDEX idx_available (is_available),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: bookings
-- Service bookings (SRS v4.0 + Reviewer suggestions)
-- ============================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    workshop_id INT UNSIGNED NOT NULL,
    vehicle_id INT UNSIGNED,
    service_type ENUM('regular', 'repair', 'maintenance', 'emergency', 'custom') NOT NULL,
    service_description TEXT NOT NULL,
    scheduled_date DATE NOT NULL,
    scheduled_time TIME NOT NULL,
    estimated_duration INT UNSIGNED COMMENT 'in minutes',
    estimated_price DECIMAL(10,2),
    actual_price DECIMAL(10,2),
    status ENUM('pending', 'accepted', 'processed', 'waiting_approval', 'rejected', 'in_progress', 'completed', 'cancelled', 'no_show') DEFAULT 'pending' COMMENT 'SRS v4.0 state diagram: Pending→Accepted→Processed→waiting_approval→Completed/Cancelled',
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT UNSIGNED,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    completed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    cancelled_by INT UNSIGNED,
    cancellation_reason TEXT,
    notes TEXT,
    mechanic_notes TEXT,
    customer_rating TINYINT,
    customer_review TEXT,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_workshop_id (workshop_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_status (status),
    INDEX idx_scheduled_date (scheduled_date),
    INDEX idx_is_deleted (is_deleted),
    -- Composite index as per Reviewer #4
    INDEX idx_workshop_status_created (workshop_id, status, created_at),
    INDEX idx_booking_number (booking_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: booking_mechanics
-- Junction table for bookings and mechanics
-- ============================================
CREATE TABLE IF NOT EXISTS booking_mechanics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    mechanic_id INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT UNSIGNED,
    notes TEXT,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (mechanic_id) REFERENCES mechanics(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_booking_id (booking_id),
    INDEX idx_mechanic_id (mechanic_id),
    INDEX idx_is_deleted (is_deleted),
    UNIQUE KEY unique_booking_mechanic (booking_id, mechanic_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: reviews
-- Customer reviews for workshops
-- ============================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    workshop_id INT UNSIGNED NOT NULL,
    mechanic_id INT UNSIGNED,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    is_visible TINYINT(1) DEFAULT 1,
    admin_response TEXT,
    responded_at TIMESTAMP NULL,
    responded_by INT UNSIGNED,
    helpful_count INT UNSIGNED DEFAULT 0,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
    FOREIGN KEY (mechanic_id) REFERENCES mechanics(id) ON DELETE SET NULL,
    FOREIGN KEY (responded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_booking_id (booking_id),
    INDEX idx_user_id (user_id),
    INDEX idx_workshop_id (workshop_id),
    INDEX idx_rating (rating),
    INDEX idx_visible (is_visible),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: review_photos
-- Photos attached to reviews
-- ============================================
CREATE TABLE IF NOT EXISTS review_photos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id INT UNSIGNED NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    photo_original_name VARCHAR(255),
    photo_size INT UNSIGNED,
    photo_mime_type VARCHAR(50),
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    INDEX idx_review_id (review_id),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: emergency_requests
-- Emergency roadside assistance requests
-- ============================================
CREATE TABLE IF NOT EXISTS emergency_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    vehicle_id INT UNSIGNED,
    workshop_id INT UNSIGNED,
    emergency_type ENUM('breakdown', 'accident', 'flat_tire', 'battery', 'fuel', 'lockout', 'other') NOT NULL,
    description TEXT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    location_address TEXT,
    status ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    assigned_workshop_id INT UNSIGNED,
    assigned_mechanic_id INT UNSIGNED,
    assigned_at TIMESTAMP NULL,
    accepted_at TIMESTAMP NULL,
    arrived_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT,
    estimated_arrival_time INT COMMENT 'minutes',
    actual_arrival_time INT COMMENT 'minutes',
    service_cost DECIMAL(10,2),
    notes TEXT,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_workshop_id) REFERENCES workshops(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_mechanic_id) REFERENCES mechanics(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_emergency_type (emergency_type),
    INDEX idx_location (latitude, longitude),
    INDEX idx_created (created_at),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: booking_approvals
-- Approval workflow for bookings
-- ============================================
CREATE TABLE IF NOT EXISTS booking_approvals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    approver_id INT UNSIGNED NOT NULL,
    action ENUM('approve', 'reject') NOT NULL,
    reason TEXT,
    comments TEXT,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_approver_id (approver_id),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: notification_templates
-- Email/notification templates
-- ============================================
CREATE TABLE IF NOT EXISTS notification_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_key VARCHAR(100) NOT NULL UNIQUE,
    event_name VARCHAR(200) NOT NULL,
    subject_template VARCHAR(255) NOT NULL,
    body_template TEXT NOT NULL,
    variables JSON COMMENT 'Available template variables',
    is_active TINYINT(1) DEFAULT 1,
    language VARCHAR(10) DEFAULT 'id',
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_key (event_key),
    INDEX idx_active (is_active),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: notification_logs
-- Notification sending history
-- ============================================
CREATE TABLE IF NOT EXISTS notification_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipient_email VARCHAR(191) NOT NULL,
    recipient_name VARCHAR(100),
    event_key VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT,
    status ENUM('pending', 'sent', 'failed', 'bounced') DEFAULT 'pending',
    error_message TEXT,
    sent_at TIMESTAMP NULL,
    opened_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,
    metadata JSON,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipient (recipient_email),
    INDEX idx_event_key (event_key),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: review_reports
-- Reports for inappropriate reviews (BR-68)
-- ============================================
CREATE TABLE IF NOT EXISTS review_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    reason TEXT,
    status ENUM('pending', 'resolved', 'dismissed') DEFAULT 'pending',
    resolved_by INT UNSIGNED,
    resolved_at TIMESTAMP NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_review_id (review_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_is_deleted (is_deleted),
    UNIQUE KEY unique_review_user_report (review_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- END OF SCHEMA
-- ============================================
