-- ============================================
-- MIGRATION: Add category column to system_settings
-- Purpose: Support categorization of settings in admin panel
-- Date: 2024-01-09
-- ============================================

-- Add category column to system_settings table
ALTER TABLE system_settings 
ADD COLUMN category VARCHAR(50) DEFAULT 'general' AFTER description;

-- Add index for category filtering
CREATE INDEX IF NOT EXISTS idx_category ON system_settings(category);

-- Update existing settings with appropriate categories
UPDATE system_settings SET category = 'general' WHERE setting_key IN ('app_name', 'site_url', 'timezone');

UPDATE system_settings SET category = 'booking' WHERE setting_key IN (
    'same_day_booking',
    'max_advance_booking_days',
    'min_booking_lead_time_hours'
);

UPDATE system_settings SET category = 'emergency' WHERE setting_key IN (
    'radius_darurat',
    'emergency_contact_number'
);

UPDATE system_settings SET category = 'notification' WHERE setting_key IN (
    'reminder_interval_km',
    'reminder_interval_months',
    'reminder_max_per_week',
    'email_notifications_enabled',
    'sms_notifications_enabled',
    'whatsapp_notifications_enabled'
);

UPDATE system_settings SET category = 'review' WHERE setting_key IN (
    'moderasi_review_ketat',
    'min_review_length',
    'allow_review_photos'
);

UPDATE system_settings SET category = 'billing' WHERE setting_key IN (
    'invoice_tax_rate',
    'invoice_due_days',
    'payment_methods_enabled'
);

UPDATE system_settings SET category = 'system' WHERE setting_key IN (
    'max_upload_size_mb',
    'allowed_file_types',
    'featured_workshop_limit',
    'cache_ttl_minutes',
    'maintenance_mode'
);

-- Insert additional useful settings if they don't exist
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, category) VALUES
('app_name', 'Bengkel Terdekat', 'string', 'Application name displayed in UI', 'general'),
('timezone', 'Asia/Jakarta', 'string', 'System timezone', 'general'),
('email_notifications_enabled', '1', 'boolean', 'Enable email notifications', 'notification'),
('sms_notifications_enabled', '0', 'boolean', 'Enable SMS notifications', 'notification'),
('whatsapp_notifications_enabled', '0', 'boolean', 'Enable WhatsApp notifications', 'notification'),
('min_review_length', '20', 'integer', 'Minimum character length for reviews', 'review'),
('allow_review_photos', '1', 'boolean', 'Allow users to upload photos with reviews', 'review'),
('max_advance_booking_days', '30', 'integer', 'Maximum days in advance for booking', 'booking'),
('min_booking_lead_time_hours', '2', 'integer', 'Minimum hours before booking time', 'booking'),
('cache_ttl_minutes', '60', 'integer', 'Cache time-to-live in minutes', 'system'),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode', 'system')
ON DUPLICATE KEY UPDATE
    updated_at = CURRENT_TIMESTAMP;

-- ============================================
-- END OF MIGRATION
-- ============================================
