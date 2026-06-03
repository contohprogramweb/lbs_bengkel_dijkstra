-- ============================================
-- Migration: Admin Dashboard, Back Office, and System Settings
-- Version: 4.1
-- Description: Add tables for audit logs, workshop verification, review moderation, 
--              featured workshops, and enhanced system settings
-- Scope: SRS UC-ADM-06, Saran Reviewer #3, #6, BR-84~85
-- ============================================

-- Create activity_logs table for audit trail (UC-ADM-06)
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL COMMENT 'User who performed the action',
    workshop_id INT NULL COMMENT 'Workshop involved (if applicable)',
    action_type VARCHAR(50) NOT NULL COMMENT 'Type of action (e.g., USER_DEACTIVATE, WORKSHOP_VERIFY)',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add is_featured column to workshops table (Saran Reviewer #6: promotional featured workshops)
ALTER TABLE workshops
ADD COLUMN IF NOT EXISTS is_featured TINYINT(1) DEFAULT 0 COMMENT 'Featured workshop for promotion'
AFTER is_active;

-- Add verified_at column to workshops table for verification tracking
ALTER TABLE workshops
ADD COLUMN IF NOT EXISTS verified_at TIMESTAMP NULL COMMENT 'Timestamp when workshop was verified by admin'
AFTER is_active;

-- Add verification documents fields
ALTER TABLE workshops
ADD COLUMN IF NOT EXISTS business_license VARCHAR(255) NULL COMMENT 'Business license document path'
AFTER description,
ADD COLUMN IF NOT EXISTS certification_doc VARCHAR(255) NULL COMMENT 'Certification document path'
AFTER business_license;

-- Add moderation status to reviews table
ALTER TABLE reviews
ADD COLUMN IF NOT EXISTS moderation_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved'
AFTER is_deleted;

-- Add moderation notes
ALTER TABLE reviews
ADD COLUMN IF NOT EXISTS moderation_notes TEXT NULL COMMENT 'Admin notes for moderation'
AFTER moderation_status;

-- Add moderated_by (admin user id)
ALTER TABLE reviews
ADD COLUMN IF NOT EXISTS moderated_by INT NULL COMMENT 'Admin who moderated this review'
AFTER moderation_notes;

-- Add moderated_at timestamp
ALTER TABLE reviews
ADD COLUMN IF NOT EXISTS moderated_at TIMESTAMP NULL COMMENT 'When moderation was performed'
AFTER moderated_by;

-- Add report_count for auto-flagging reviews
ALTER TABLE reviews
ADD COLUMN IF NOT EXISTS report_count INT DEFAULT 0 COMMENT 'Number of times this review was reported'
AFTER is_deleted;

-- Index for pending reviews moderation
CREATE INDEX IF NOT EXISTS idx_moderation_status ON reviews(moderation_status);
CREATE INDEX IF NOT EXISTS idx_report_count ON reviews(report_count);

-- Add more system settings for admin configuration
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('radius_darurat', '5', 'decimal', 'Emergency radius in kilometers for emergency request matching'),
('same_day_booking', '1', 'boolean', 'Enable/disable same-day booking feature'),
('moderasi_review_ketat', '0', 'boolean', 'Enable strict review moderation (all reviews require approval)'),
('invoice_tax_rate', '11', 'decimal', 'Default tax rate percentage for invoices'),
('invoice_due_days', '7', 'integer', 'Default due days for invoice payment'),
('max_upload_size_mb', '5', 'integer', 'Maximum file upload size in MB'),
('allowed_file_types', 'jpg,jpeg,png,pdf', 'string', 'Comma-separated allowed file extensions'),
('featured_workshop_limit', '10', 'integer', 'Maximum number of featured workshops displayed on homepage')
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value),
    updated_at = CURRENT_TIMESTAMP;

-- Create index for featured workshops
CREATE INDEX IF NOT EXISTS idx_is_featured ON workshops(is_featured);
CREATE INDEX IF NOT EXISTS idx_verified_at ON workshops(verified_at);

-- Insert sample activity log types reference (documentation only)
-- Common action_type values:
-- USER_CREATE, USER_UPDATE, USER_ACTIVATE, USER_DEACTIVATE, USER_DELETE
-- WORKSHOP_CREATE, WORKSHOP_UPDATE, WORKSHOP_VERIFY, WORKSHOP_REJECT, WORKSHOP_FEATURE, WORKSHOP_UNFEATURE
-- REVIEW_APPROVE, REVIEW_REJECT, REVIEW_FLAG
-- BOOKING_CANCEL, BOOKING_REFUND
-- SYSTEM_SETTING_UPDATE, TEMPLATE_UPDATE
-- EMERGENCY_DISPATCH, EMERGENCY_COMPLETE

-- ============================================
-- END OF MIGRATION
-- ============================================
