-- ============================================
-- Database Schema for Aplikasi Bengkel Terdekat
-- Version: 5.0 - Complete with Sample Data
-- Description: Full database schema with dummy data
-- Note: No charset specified for universal compatibility
-- ============================================

-- ============================================
-- 1. TABLE: system_settings (Must be first - no dependencies)
-- ============================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    setting_type ENUM('string', 'integer', 'float', 'boolean', 'json') DEFAULT 'string',
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- 2. TABLE: users (No FK dependencies)
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
) ENGINE=InnoDB;

-- ============================================
-- 3. TABLE: workshops (FK: users)
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
    is_featured TINYINT(1) DEFAULT 0 COMMENT 'Featured workshop for promotion',
    verified_at TIMESTAMP NULL COMMENT 'Timestamp when workshop was verified by admin',
    business_license VARCHAR(255) NULL COMMENT 'Business license document path',
    certification_doc VARCHAR(255) NULL COMMENT 'Certification document path',
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
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
    INDEX idx_rating (rating_avg),
    INDEX idx_is_featured (is_featured),
    INDEX idx_verified_at (verified_at)
) ENGINE=InnoDB;

-- ============================================
-- 4. TABLE: vehicles (FK: users)
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
    reminder_enabled TINYINT(1) DEFAULT 1 COMMENT 'Enable/disable service reminder for this vehicle',
    reminder_snoozed_until DATE NULL COMMENT 'Reminder snoozed until this date',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_number (vehicle_number),
    INDEX idx_vehicle_type (vehicle_type),
    INDEX idx_is_deleted (is_deleted),
    INDEX idx_reminder_enabled (reminder_enabled)
) ENGINE=InnoDB;

-- ============================================
-- 5. TABLE: workshop_services (FK: workshops)
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
) ENGINE=InnoDB;

-- ============================================
-- 6. TABLE: mechanics (FK: users, workshops)
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
) ENGINE=InnoDB;

-- ============================================
-- 7. TABLE: workshop_schedules (FK: workshops)
-- ============================================
CREATE TABLE IF NOT EXISTS workshop_schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workshop_id INT UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL COMMENT '0=Sunday, 1=Monday, ..., 6=Saturday',
    open_time TIME NOT NULL,
    close_time TIME NOT NULL,
    is_open TINYINT(1) DEFAULT 1,
    slot_interval INT DEFAULT 60 COMMENT 'Interval in minutes',
    capacity_per_slot INT DEFAULT 1 COMMENT 'Capacity per slot',
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
    INDEX idx_workshop_id (workshop_id),
    INDEX idx_day (day_of_week),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB;

-- ============================================
-- 8. TABLE: workshop_blocked_dates (FK: workshops, users)
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
) ENGINE=InnoDB;

-- ============================================
-- 9. TABLE: workshop_blocked_slots (FK: workshops, users)
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
) ENGINE=InnoDB;

-- ============================================
-- 10. TABLE: bookings (FK: users, workshops, vehicles)
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
    service_cost DECIMAL(12,2) DEFAULT 0 COMMENT 'Biaya layanan',
    sparepart_cost DECIMAL(12,2) DEFAULT 0 COMMENT 'Biaya sparepart',
    additional_cost DECIMAL(12,2) DEFAULT 0 COMMENT 'Biaya tambahan',
    final_total DECIMAL(12,2) DEFAULT 0 COMMENT 'Total tagihan akhir',
    payment_status ENUM('unpaid', 'paid', 'partial') DEFAULT 'unpaid',
    invoice_number VARCHAR(50) NULL UNIQUE COMMENT 'Nomor invoice',
    invoiced_at DATETIME NULL COMMENT 'Tanggal invoice dibuat',
    paid_at DATETIME NULL COMMENT 'Tanggal pembayaran',
    actual_price DECIMAL(10,2),
    status ENUM('pending', 'accepted', 'processed', 'waiting_approval', 'rejected', 'in_progress', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
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
    reschedule_count INT DEFAULT 0,
    last_rescheduled_at TIMESTAMP NULL,
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
    INDEX idx_workshop_status_created (workshop_id, status, created_at),
    INDEX idx_booking_number (booking_number),
    INDEX idx_payment_status (payment_status),
    INDEX idx_invoiced_at (invoiced_at)
) ENGINE=InnoDB;

-- ============================================
-- 11. TABLE: booking_mechanics (FK: bookings, mechanics, users)
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
) ENGINE=InnoDB;

-- ============================================
-- 12. TABLE: booking_approvals (FK: bookings, users)
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
) ENGINE=InnoDB;

-- ============================================
-- 13. TABLE: booking_service_items (FK: bookings)
-- ============================================
CREATE TABLE IF NOT EXISTS booking_service_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    service_name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id)
) ENGINE=InnoDB COMMENT='Detail layanan per booking';

-- ============================================
-- 14. TABLE: booking_sparepart_items (FK: bookings)
-- ============================================
CREATE TABLE IF NOT EXISTS booking_sparepart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    sparepart_name VARCHAR(200) NOT NULL,
    part_number VARCHAR(100) NULL,
    description TEXT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id)
) ENGINE=InnoDB COMMENT='Detail sparepart per booking';

-- ============================================
-- 15. TABLE: booking_additional_charges (FK: bookings, booking_approvals)
-- ============================================
CREATE TABLE IF NOT EXISTS booking_additional_charges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    charge_name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    amount DECIMAL(12,2) NOT NULL,
    is_approved TINYINT(1) DEFAULT 0 COMMENT 'Disetujui user atau tidak',
    approval_id INT NULL COMMENT 'Link ke booking_approvals jika ada',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (approval_id) REFERENCES booking_approvals(id) ON DELETE SET NULL,
    INDEX idx_booking (booking_id)
) ENGINE=InnoDB COMMENT='Biaya tambahan per booking';

-- ============================================
-- 16. TABLE: invoices (FK: bookings, workshops, users)
-- ============================================
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    booking_id INT NOT NULL,
    workshop_id INT NOT NULL,
    user_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NULL,
    service_cost DECIMAL(12,2) DEFAULT 0,
    sparepart_cost DECIMAL(12,2) DEFAULT 0,
    additional_cost DECIMAL(12,2) DEFAULT 0,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    tax_rate DECIMAL(5,2) DEFAULT 0 COMMENT 'Persentase pajak',
    total_amount DECIMAL(12,2) NOT NULL,
    payment_status ENUM('unpaid', 'paid', 'partial', 'cancelled') DEFAULT 'unpaid',
    paid_amount DECIMAL(12,2) DEFAULT 0,
    paid_at DATETIME NULL,
    payment_method VARCHAR(50) NULL,
    payment_note TEXT NULL,
    notes TEXT NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_workshop (workshop_id),
    INDEX idx_user (user_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_issue_date (issue_date)
) ENGINE=InnoDB COMMENT='Invoice formal untuk tagihan';

-- ============================================
-- 17. TABLE: invoice_payments (FK: invoices, users)
-- ============================================
CREATE TABLE IF NOT EXISTS invoice_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL COMMENT 'cash, transfer, debit, credit',
    reference_number VARCHAR(100) NULL COMMENT 'No referensi transfer/kwitansi',
    notes TEXT NULL,
    received_by INT NULL COMMENT 'User ID yang menerima pembayaran',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_invoice (invoice_id),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB COMMENT='Pembayaran invoice (bisa multiple payments)';

-- ============================================
-- 18. TABLE: report_settings (FK: workshops)
-- ============================================
CREATE TABLE IF NOT EXISTS report_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workshop_id INT NULL COMMENT 'NULL = global setting',
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    setting_type ENUM('string', 'integer', 'decimal', 'boolean', 'json') DEFAULT 'string',
    description VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    UNIQUE KEY uk_workshop_setting (workshop_id, setting_key),
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Pengaturan laporan per bengkel';

-- ============================================
-- 19. TABLE: reviews (FK: bookings, users, workshops, mechanics)
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
    moderation_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    moderation_notes TEXT NULL COMMENT 'Admin notes for moderation',
    moderated_by INT NULL COMMENT 'Admin who moderated this review',
    moderated_at TIMESTAMP NULL COMMENT 'When moderation was performed',
    report_count INT DEFAULT 0 COMMENT 'Number of times this review was reported',
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
    FOREIGN KEY (moderated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_booking_id (booking_id),
    INDEX idx_user_id (user_id),
    INDEX idx_workshop_id (workshop_id),
    INDEX idx_rating (rating),
    INDEX idx_visible (is_visible),
    INDEX idx_is_deleted (is_deleted),
    INDEX idx_moderation_status (moderation_status),
    INDEX idx_report_count (report_count)
) ENGINE=InnoDB;

-- ============================================
-- 20. TABLE: review_photos (FK: reviews)
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
) ENGINE=InnoDB;

-- ============================================
-- 21. TABLE: review_reports (FK: reviews, users)
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
) ENGINE=InnoDB;

-- ============================================
-- 22. TABLE: emergency_requests (FK: users, vehicles, workshops, mechanics)
-- ============================================
CREATE TABLE IF NOT EXISTS emergency_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    vehicle_id INT UNSIGNED,
    workshop_id INT UNSIGNED,
    ip_address VARCHAR(45) NULL COMMENT 'For rate limiting',
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
) ENGINE=InnoDB;

-- ============================================
-- 23. TABLE: notification_templates (No FK dependencies)
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
) ENGINE=InnoDB;

-- ============================================
-- 24. TABLE: notification_logs (No FK dependencies)
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
    read_at TIMESTAMP NULL COMMENT 'Timestamp when notification was read',
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
) ENGINE=InnoDB;

-- ============================================
-- 25. TABLE: activity_logs (FK: users, workshops)
-- ============================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL COMMENT 'User who performed the action',
    workshop_id INT NULL COMMENT 'Workshop involved (if applicable)',
    action_type VARCHAR(50) NOT NULL COMMENT 'Type of action',
    action_description TEXT NOT NULL,
    target_user_id INT NULL COMMENT 'Target user (if action affects another user)',
    target_workshop_id INT NULL COMMENT 'Target workshop (if action affects another workshop)',
    ip_address VARCHAR(45) NULL COMMENT 'IP address of the actor',
    user_agent VARCHAR(255) NULL COMMENT 'Browser/User agent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_workshop_id (workshop_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE SET NULL,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (target_workshop_id) REFERENCES workshops(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Audit trail for admin actions';

-- ============================================
-- 26. TABLE: road_nodes (No FK dependencies - for Dijkstra algorithm)
-- ============================================
CREATE TABLE IF NOT EXISTS road_nodes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'Nama simpul/persimpangan',
    latitude DECIMAL(10, 8) NOT NULL COMMENT 'Latitude koordinat',
    longitude DECIMAL(11, 8) NOT NULL COMMENT 'Longitude koordinat',
    node_type ENUM('intersection', 'landmark', 'custom') DEFAULT 'intersection' COMMENT 'Tipe simpul',
    description TEXT NULL COMMENT 'Deskripsi tambahan',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'Status aktif/nonaktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    INDEX idx_coordinates (latitude, longitude),
    INDEX idx_active (is_active),
    INDEX idx_node_type (node_type)
) ENGINE=InnoDB;

-- ============================================
-- 27. TABLE: road_edges (FK: road_nodes)
-- ============================================
CREATE TABLE IF NOT EXISTS road_edges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_node_id INT UNSIGNED NOT NULL COMMENT 'Simpul asal',
    to_node_id INT UNSIGNED NOT NULL COMMENT 'Simpul tujuan',
    road_name VARCHAR(150) NULL COMMENT 'Nama jalan',
    distance_km DECIMAL(10, 4) NOT NULL COMMENT 'Jarak dalam kilometer (bobot edge)',
    is_bidirectional TINYINT(1) DEFAULT 1 COMMENT 'Apakah dua arah',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'Status aktif/nonaktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    FOREIGN KEY (from_node_id) REFERENCES road_nodes(id) ON DELETE CASCADE,
    FOREIGN KEY (to_node_id) REFERENCES road_nodes(id) ON DELETE CASCADE,
    INDEX idx_from_node (from_node_id),
    INDEX idx_to_node (to_node_id),
    INDEX idx_active (is_active),
    CONSTRAINT chk_distance_positive CHECK (distance_km > 0),
    CONSTRAINT chk_different_nodes CHECK (from_node_id != to_node_id)
) ENGINE=InnoDB;

-- ============================================
-- SAMPLE DATA INSERTION
-- ============================================

-- ============================================
-- 1. System Settings
-- ============================================
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('radius_darurat', '5', 'integer', 'Emergency radius in kilometers for emergency request matching'),
('same_day_booking', '1', 'boolean', 'Enable/disable same-day booking feature'),
('moderasi_review_ketat', '0', 'boolean', 'Enable strict review moderation'),
('invoice_tax_rate', '11', 'decimal', 'Default tax rate percentage for invoices'),
('invoice_due_days', '7', 'integer', 'Default due days for invoice payment'),
('max_upload_size_mb', '5', 'integer', 'Maximum file upload size in MB'),
('allowed_file_types', 'jpg,jpeg,png,pdf', 'string', 'Comma-separated allowed file extensions'),
('featured_workshop_limit', '10', 'integer', 'Maximum number of featured workshops'),
('reminder_interval_km', '5000', 'integer', 'Default kilometer interval for service reminder'),
('reminder_interval_months', '6', 'integer', 'Default month interval for service reminder'),
('reminder_max_per_week', '1', 'integer', 'Maximum reminders per week per vehicle'),
('booking_default_capacity', '5', 'integer', 'Default booking capacity per slot');

-- ============================================
-- 2. Users (password hashed with bcrypt cost 10, default: password123)
-- ============================================
INSERT INTO users (email, password, full_name, phone, role, is_active, email_verified_at) VALUES
-- Admin
('admin@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '081234567890', 'admin', 1, NOW()),
-- Workshop Owners
('owner1@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', '081234567891', 'workshop_owner', 1, NOW()),
('owner2@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Aminah', '081234567892', 'workshop_owner', 1, NOW()),
-- Mechanics
('mechanic1@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Hidayat', '081234567893', 'mechanic', 1, NOW()),
('mechanic2@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rudi Hartono', '081234567894', 'mechanic', 1, NOW()),
-- Customers
('customer1@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Andi Wijaya', '081234567895', 'customer', 1, NOW()),
('customer2@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dewi Lestari', '081234567896', 'customer', 1, NOW()),
('customer3@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eko Prasetyo', '081234567897', 'customer', 1, NOW());

-- Add default_city for some users
UPDATE users SET default_city = 'Jakarta' WHERE role = 'customer';

-- ============================================
-- 3. Workshops
-- ============================================
INSERT INTO workshops (user_id, name, description, address, city, province, postal_code, latitude, longitude, phone, whatsapp, status, is_active, is_featured, verified_at) VALUES
(2, 'Bengkel Maju Jaya', 'Spesialis servis mobil Jepang dan Eropa', 'Jl. Raya Bogor No. 123', 'Jakarta Timur', 'DKI Jakarta', '13460', -6.229746, 106.822917, '021-12345678', '081234567891', 'active', 1, 1, NOW()),
(3, 'Bengkel Berkah Motor', 'Servis motor dan mobil semua merk', 'Jl. Sudirman No. 45', 'Bandung', 'Jawa Barat', '40123', -6.917464, 107.619123, '022-87654321', '081234567892', 'active', 1, 0, NOW());

-- ============================================
-- 4. Vehicles
-- ============================================
INSERT INTO vehicles (user_id, vehicle_number, vehicle_type, brand, model, year, color, transmission, fuel_type, current_km, last_service_date, last_service_km, is_primary) VALUES
-- Customer 1 vehicles
(6, 'B 1234 CD', 'car', 'Toyota', 'Avanza', 2020, 'Silver', 'manual', 'petrol', 45000, '2024-06-15', 40000, 1),
(6, 'B 5678 EF', 'motorcycle', 'Honda', 'Vario 150', 2021, 'Red', 'cvt', 'petrol', 25000, '2024-08-01', 20000, 0),
-- Customer 2 vehicles
(7, 'D 9876 AB', 'car', 'Honda', 'Brio', 2019, 'White', 'automatic', 'petrol', 60000, '2024-05-20', 55000, 1),
-- Customer 3 vehicles
(8, 'F 1111 GG', 'motorcycle', 'Yamaha', 'NMAX', 2022, 'Blue', 'cvt', 'petrol', 15000, '2024-09-01', 10000, 1),
(8, 'F 2222 HH', 'car', 'Mitsubishi', 'Xpander', 2021, 'Black', 'automatic', 'petrol', 35000, '2024-07-10', 30000, 0);

-- ============================================
-- 5. Workshop Services
-- ============================================
INSERT INTO workshop_services (workshop_id, service_name, service_category, description, price_min, price_max, duration_minutes, is_available) VALUES
-- Workshop 1 services
(1, 'Ganti Oli Mesin', 'servis', 'Ganti oli mesin termasuk filter', 150000, 300000, 30, 1),
(1, 'Servis Rutin', 'servis', 'Servis berkala 10.000 km', 250000, 400000, 60, 1),
(1, 'Overhaul Mesin', 'servis', 'Turun mesin lengkap', 3000000, 5000000, 480, 1),
(1, 'Ganti Kampas Rem', 'servis', 'Ganti kampas rem depan/belakang', 200000, 400000, 45, 1),
(1, 'Spooring & Balancing', 'servis', 'Penyetelan roda dan balancing', 150000, 250000, 60, 1),
-- Workshop 2 services
(2, 'Ganti Oli Motor', 'servis', 'Ganti oli motor matic/bebek', 50000, 100000, 20, 1),
(2, 'Servis Ringan Motor', 'servis', 'Tune up motor', 80000, 150000, 30, 1),
(2, 'Ganti Ban Motor', 'ban', 'Ganti ban motor termasuk pasang', 200000, 500000, 30, 1),
(2, 'Ganti Aki', 'aki', 'Ganti aki motor/mobil', 150000, 800000, 15, 1);

-- ============================================
-- 6. Mechanics
-- ============================================
INSERT INTO mechanics (user_id, workshop_id, specialization, experience_years, certification, is_available) VALUES
(4, 1, '["mesin", "transmisi", "rem"]', 5, 'ASTRA Certified', 1),
(5, 2, '["motor", "elektrikal"]', 3, 'Yamaha Certified', 1);

-- ============================================
-- 7. Workshop Schedules
-- ============================================
INSERT INTO workshop_schedules (workshop_id, day_of_week, open_time, close_time, is_open, slot_interval, capacity_per_slot) VALUES
-- Workshop 1 schedule (Monday-Saturday)
(1, 1, '08:00:00', '17:00:00', 1, 60, 3),
(1, 2, '08:00:00', '17:00:00', 1, 60, 3),
(1, 3, '08:00:00', '17:00:00', 1, 60, 3),
(1, 4, '08:00:00', '17:00:00', 1, 60, 3),
(1, 5, '08:00:00', '17:00:00', 1, 60, 3),
(1, 6, '08:00:00', '15:00:00', 1, 60, 2),
-- Workshop 2 schedule
(2, 1, '07:00:00', '18:00:00', 1, 30, 5),
(2, 2, '07:00:00', '18:00:00', 1, 30, 5),
(2, 3, '07:00:00', '18:00:00', 1, 30, 5),
(2, 4, '07:00:00', '18:00:00', 1, 30, 5),
(2, 5, '07:00:00', '18:00:00', 1, 30, 5),
(2, 6, '07:00:00', '16:00:00', 1, 30, 3);

-- ============================================
-- 8. Bookings
-- ============================================
INSERT INTO bookings (booking_number, user_id, workshop_id, vehicle_id, service_type, service_description, scheduled_date, scheduled_time, estimated_duration, estimated_price, status, approval_status, created_at) VALUES
-- Completed bookings
('B-20250101-0001', 6, 1, 1, 'regular', 'Servis rutin ganti oli', '2025-01-05', '09:00:00', 60, 250000, 'completed', 'approved', '2025-01-01 10:00:00'),
('B-20250102-0002', 7, 1, 3, 'regular', 'Ganti kampas rem', '2025-01-06', '10:00:00', 45, 300000, 'completed', 'approved', '2025-01-02 11:00:00'),
-- Pending/Accepted bookings
('B-20250115-0003', 8, 2, 4, 'regular', 'Servis ringan motor', '2025-01-20', '08:00:00', 30, 100000, 'pending', 'pending', '2025-01-15 09:00:00'),
('B-20250115-0004', 6, 1, 2, 'repair', 'Cek mesin berisik', '2025-01-22', '14:00:00', 90, 200000, 'accepted', 'pending', '2025-01-15 14:00:00');

-- Update completed bookings with actual prices and payment info
UPDATE bookings SET 
    actual_price = estimated_price,
    final_total = estimated_price,
    payment_status = 'paid',
    completed_at = DATE_ADD(scheduled_date, INTERVAL 1 HOUR),
    customer_rating = 5,
    customer_review = 'Pelayanan bagus dan cepat'
WHERE status = 'completed';

-- ============================================
-- 9. Booking Mechanics Assignment
-- ============================================
INSERT INTO booking_mechanics (booking_id, mechanic_id, assigned_by, notes) VALUES
(1, 1, 2, 'Servis rutin'),
(2, 1, 2, 'Ganti kampas rem');

-- ============================================
-- 10. Reviews
-- ============================================
INSERT INTO reviews (booking_id, user_id, workshop_id, rating, review_text, is_visible, moderation_status, created_at) VALUES
(1, 6, 1, 5, 'Pelayanan sangat baik, mekanik profesional dan ramah. Harga transparan.', 1, 'approved', '2025-01-05 12:00:00'),
(2, 7, 1, 4, 'Bagus, tapi agak lama menunggu. Overall puas.', 1, 'approved', '2025-01-06 14:00:00');

-- Update workshop ratings
UPDATE workshops SET rating_avg = 4.5, total_reviews = 2 WHERE id = 1;

-- ============================================
-- 11. Notification Templates
-- ============================================
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables, is_active, language) VALUES
('booking_accepted', 'Booking Diterima', 
 'Booking Anda Diterima - {{kode_booking}}', 
 '<p>Halo {{nama_pengguna}},</p><p>Booking Anda dengan kode <strong>{{kode_booking}}</strong> telah diterima oleh <strong>{{nama_bengkel}}</strong>.</p><p>Silakan datang sesuai jadwal yang telah ditentukan.</p>', 
 '["nama_pengguna", "kode_booking", "nama_bengkel"]', 1, 'id'),

('booking_processed', 'Booking Sedang Dikerjakan', 
 'Booking Sedang Dikerjakan - {{kode_booking}}', 
 '<p>Halo {{nama_pengguna}},</p><p>Booking Anda <strong>{{kode_booking}}</strong> sedang dikerjakan.</p>', 
 '["nama_pengguna", "kode_booking"]', 1, 'id'),

('booking_completed', 'Booking Selesai', 
 'Booking Selesai - {{kode_booking}}', 
 '<p>Halo {{nama_pengguna}},</p><p>Booking Anda <strong>{{kode_booking}}</strong> telah selesai.</p>', 
 '["nama_pengguna", "kode_booking"]', 1, 'id'),

('booking_cancelled', 'Booking Dibatalkan', 
 'Booking Dibatalkan - {{kode_booking}}', 
 '<p>Halo {{nama_pengguna}},</p><p>Booking Anda <strong>{{kode_booking}}</strong> telah dibatalkan.</p>', 
 '["nama_pengguna", "kode_booking"]', 1, 'id'),

('service_reminder', 'Pengingat Servis Berkala', 
 'Saatnya Servis Kendaraan Anda!', 
 '<p>Halo {{nama_pengguna}},</p><p>Kendaraan <strong>{{kendaraan}}</strong> sudah saatnya servis berkala.</p>', 
 '["nama_pengguna", "kendaraan"]', 1, 'id'),

('booking_rejected', 'Booking Ditolak', 
 'Booking Ditolak - {{kode_booking}}', 
 '<p>Halo {{nama_pengguna}},</p><p>Maaf, booking Anda <strong>{{kode_booking}}</strong> tidak dapat diterima.</p>', 
 '["nama_pengguna", "kode_booking"]', 1, 'id');

-- ============================================
-- 12. Emergency Requests (sample)
-- ============================================
INSERT INTO emergency_requests (request_number, user_id, emergency_type, description, latitude, longitude, location_address, status, created_at) VALUES
('EMG-20250115-0001', 6, 'flat_tire', 'Ban bocor di jalan raya', -6.208763, 106.845599, 'Jl. Sudirman, Jakarta Pusat', 'completed', '2025-01-15 08:00:00'),
('EMG-20250115-0002', 7, 'battery', 'Aki soak, mobil tidak bisa distarter', -6.917464, 107.619123, 'Jl. Asia Afrika, Bandung', 'pending', '2025-01-15 10:30:00');

-- ============================================
-- 13. Road Nodes (for Dijkstra algorithm - Jakarta area)
-- ============================================
INSERT INTO road_nodes (name, latitude, longitude, node_type, description) VALUES
('Simpang HI', -6.194732, 106.822917, 'intersection', 'Bundaran Hotel Indonesia'),
('Simpang Thamrin-Sudirman', -6.197444, 106.821667, 'intersection', 'Pertemuan Jl. Thamrin dan Sudirman'),
('Simpang Kuningan', -6.216667, 106.823889, 'intersection', 'Rasuna Said/Kuningan'),
('Simpang Semanggi', -6.212778, 106.805556, 'intersection', 'Simpang Susun Semanggi'),
('Simpang Blok M', -6.242222, 106.798333, 'intersection', 'Terminal Blok M'),
('Monas', -6.175392, 106.827153, 'landmark', 'Monumen Nasional'),
('Stasiun Gambir', -6.173889, 106.833889, 'landmark', 'Stasiun Kereta Gambir');

-- ============================================
-- 14. Road Edges
-- ============================================
INSERT INTO road_edges (from_node_id, to_node_id, road_name, distance_km, is_bidirectional) VALUES
(1, 2, 'Jl. M.H. Thamrin', 0.35, 1),
(2, 3, 'Jl. Jend. Sudirman', 2.15, 1),
(3, 4, 'Jl. Prof. Dr. Satrio', 2.50, 1),
(4, 5, 'Jl. Jend. Sudirman', 3.20, 1),
(2, 4, 'Jl. H.R. Rasuna Said', 1.80, 1),
(1, 6, 'Jl. Medan Merdeka', 2.10, 1),
(6, 7, 'Jl. Medan Merdeka Utara', 0.80, 1);

-- ============================================
-- 15. Activity Logs (sample audit trail)
-- ============================================
INSERT INTO activity_logs (user_id, action_type, action_description, ip_address, created_at) VALUES
(1, 'USER_CREATE', 'Admin membuat user baru', '127.0.0.1', '2025-01-01 08:00:00'),
(1, 'WORKSHOP_VERIFY', 'Admin memverifikasi bengkel ID 1', '127.0.0.1', '2025-01-02 09:00:00'),
(1, 'WORKSHOP_VERIFY', 'Admin memverifikasi bengkel ID 2', '127.0.0.1', '2025-01-02 09:30:00');

-- ============================================
-- 16. Report Settings
-- ============================================
INSERT INTO report_settings (setting_key, setting_value, setting_type, description) VALUES
('default_tax_rate', '0', 'decimal', 'Default tax rate percentage'),
('invoice_prefix', 'INV', 'string', 'Prefix untuk nomor invoice'),
('report_currency', 'IDR', 'string', 'Mata uang untuk laporan'),
('invoice_due_days', '7', 'integer', 'Jatuh tempo invoice dalam hari');

-- ============================================
-- 17. Invoice (sample for completed booking)
-- ============================================
INSERT INTO invoices (invoice_number, booking_id, workshop_id, user_id, issue_date, due_date, service_cost, total_amount, payment_status, paid_at, payment_method) VALUES
('INV-20250105-0001', 1, 1, 6, '2025-01-05', '2025-01-12', 250000, 250000, 'paid', '2025-01-05 12:00:00', 'cash'),
('INV-20250106-0001', 2, 1, 7, '2025-01-06', '2025-01-13', 300000, 300000, 'paid', '2025-01-06 14:30:00', 'transfer');

-- ============================================
-- Summary Statistics Query
-- ============================================
SELECT 'Database setup completed successfully!' AS status;
SELECT COUNT(*) AS total_users FROM users WHERE is_deleted = 0;
SELECT COUNT(*) AS total_workshops FROM workshops WHERE is_deleted = 0;
SELECT COUNT(*) AS total_vehicles FROM vehicles WHERE is_deleted = 0;
SELECT COUNT(*) AS total_bookings FROM bookings WHERE is_deleted = 0;
SELECT COUNT(*) AS total_reviews FROM reviews WHERE is_deleted = 0;
SELECT COUNT(*) AS total_mechanics FROM mechanics WHERE is_deleted = 0;
SELECT COUNT(*) AS total_road_nodes FROM road_nodes WHERE is_deleted = 0;
SELECT COUNT(*) AS total_road_edges FROM road_edges WHERE is_deleted = 0;

-- ============================================
-- END OF SCHEMA AND SAMPLE DATA
-- ============================================
