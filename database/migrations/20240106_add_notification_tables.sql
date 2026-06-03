-- ============================================
-- Migration: Add reminder columns to vehicles table
-- Version: 4.0
-- Description: Add columns for service reminder functionality (UC-USR-11, BR-73, BR-74)
-- ============================================

-- Add reminder_enabled column (BR-74: user can disable reminder per vehicle)
ALTER TABLE vehicles 
ADD COLUMN IF NOT EXISTS reminder_enabled TINYINT(1) DEFAULT 1 COMMENT 'Enable/disable service reminder for this vehicle'
AFTER is_primary;

-- Add reminder_snoozed_until column (UC-USR-11: snooze feature)
ALTER TABLE vehicles 
ADD COLUMN IF NOT EXISTS reminder_snoozed_until DATE NULL COMMENT 'Reminder snoozed until this date'
AFTER reminder_enabled;

-- Add default_city column to users table for nearest workshop recommendation
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS default_city VARCHAR(100) NULL COMMENT 'Default city for workshop recommendations'
AFTER phone;

-- Add read_at column to notification_logs for inbox read status
ALTER TABLE notification_logs 
ADD COLUMN IF NOT EXISTS read_at TIMESTAMP NULL COMMENT 'Timestamp when notification was read'
AFTER opened_at;

-- Create index for reminder queries optimization
CREATE INDEX IF NOT EXISTS idx_reminder_enabled ON vehicles(reminder_enabled);
CREATE INDEX IF NOT EXISTS idx_last_service_date ON vehicles(last_service_date);
CREATE INDEX IF NOT EXISTS idx_last_service_km ON vehicles(last_service_km);

-- Insert default system settings for reminder thresholds (BR-75)
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('reminder_interval_km', '5000', 'integer', 'Default kilometer interval for service reminder'),
('reminder_interval_months', '6', 'integer', 'Default month interval for service reminder'),
('reminder_max_per_week', '1', 'integer', 'Maximum reminders per week per vehicle (BR-73)')
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    updated_at = CURRENT_TIMESTAMP;

-- Insert default notification templates
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables, is_active, language) VALUES
('booking_accepted', 'Booking Diterima', 
 'Booking Anda Diterima - {{kode_booking}}', 
 '<p>Halo {{nama_pengguna}},</p><p>Booking Anda dengan kode <strong>{{kode_booking}}</strong> telah diterima oleh <strong>{{nama_bengkel}}</strong>.</p><p>Silakan datang sesuai jadwal yang telah ditentukan.</p><p>Terima kasih.</p>', 
 '["nama_pengguna", "kode_booking", "nama_bengkel"]', 
 1, 'id'),

('booking_processed', 'Booking Sedang Dikerjakan', 
 'Booking Sedang Dikerjakan - {{kode_booking}}', 
 '<p>Halo {{nama_pengguna}},</p><p>Booking Anda <strong>{{kode_booking}}</strong> sedang dikerjakan di <strong>{{nama_bengkel}}</strong>.</p><p>Kami akan menginformasikan jika ada perubahan.</p>', 
 '["nama_pengguna", "kode_booking", "nama_bengkel"]', 
 1, 'id'),

('booking_completed', 'Booking Selesai', 
 'Booking Selesai - {{kode_booking}}', 
 '<p>Halo {{nama_pengguna}},</p><p>Booking Anda <strong>{{kode_booking}}</strong> telah selesai dikerjakan di <strong>{{nama_bengkel}}</strong>.</p><p>Silakan lakukan pembayaran dan ambil kendaraan Anda.</p><p>Terima kasih telah menggunakan layanan kami.</p>', 
 '["nama_pengguna", "kode_booking", "nama_bengkel"]', 
 1, 'id'),

('booking_cancelled', 'Booking Dibatalkan', 
 'Booking Dibatalkan - {{kode_booking}}', 
 '<p>Halo {{nama_pengguna}},</p><p>Booking Anda <strong>{{kode_booking}}</strong> telah dibatalkan.</p><p>Jika ada pertanyaan, silakan hubungi bengkel terkait.</p>', 
 '["nama_pengguna", "kode_booking"]', 
 1, 'id'),

('service_reminder', 'Pengingat Servis Berkala', 
 'Saatnya Servis Kendaraan Anda!', 
 '<p>Halo {{nama_pengguna}},</p><p>Kendaraan <strong>{{kendaraan}}</strong> Anda sudah saatnya untuk servis berkala.</p><p>Kilometer terakhir: {{km_terakhir}} km</p><p>Estimasi kilometer saat ini: {{km_estimasi}} km</p><p>Waktu servis terakhir: {{tanggal_servis}}</p><p>Berikut rekomendasi bengkel terdekat untuk Anda:</p><p>{{rekomendasi_bengkel}}</p><p>Segera jadwalkan servis untuk menjaga kondisi kendaraan Anda.</p><p>Terima kasih.</p>', 
 '["nama_pengguna", "kendaraan", "km_terakhir", "km_estimasi", "tanggal_servis", "rekomendasi_bengkel"]', 
 1, 'id'),

('booking_rejected', 'Booking Ditolak', 
 'Booking Ditolak - {{kode_booking}}', 
 '<p>Halo {{nama_pengguna}},</p><p>Maaf, booking Anda <strong>{{kode_booking}}</strong> tidak dapat diterima oleh <strong>{{nama_bengkel}}</strong>.</p><p>Silakan hubungi bengkel untuk informasi lebih lanjut atau buat booking di bengkel lain.</p>', 
 '["nama_pengguna", "kode_booking", "nama_bengkel"]', 
 1, 'id')
ON DUPLICATE KEY UPDATE 
    body_template = VALUES(body_template),
    updated_at = CURRENT_TIMESTAMP;

-- ============================================
-- END OF MIGRATION
-- ============================================
