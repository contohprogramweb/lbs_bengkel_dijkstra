-- ============================================
-- Seed Data for Aplikasi Bengkel Terdekat
-- Version: 4.0
-- ============================================

-- ============================================
-- 1. SYSTEM SETTINGS (Reviewer #3)
-- ============================================
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('radius_darurat', '5', 'integer', 'Radius darurat dalam kilometer'),
('reminder_interval_km', '5000', 'integer', 'Interval pengingat servis berdasarkan KM'),
('reminder_interval_months', '6', 'integer', 'Interval pengingat servis berdasarkan bulan'),
('max_booking_advance_days', '30', 'integer', 'Maksimal hari untuk booking ke depan'),
('min_booking_hours', '2', 'integer', 'Minimal jam sebelum waktu booking'),
('emergency_auto_assign', 'true', 'boolean', 'Auto assign untuk emergency request'),
('maintenance_mode', 'false', 'boolean', 'Mode maintenance aplikasi');

-- ============================================
-- 2. DEFAULT ADMIN USER
-- Password: admin123 (hashed with BCRYPT cost 10)
-- ============================================
INSERT INTO users (email, password, full_name, phone, role, is_active, email_verified_at) VALUES
('admin@bengkelterdekat.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '081234567890', 'admin', 1, NOW());

-- ============================================
-- 3. NOTIFICATION TEMPLATES
-- ============================================

-- Template: booking_accepted
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables) VALUES
('booking_accepted', 'Booking Diterima', 
'Booking Anda Diterima - {booking_number}', 
'Dear {customer_name},\n\nBooking Anda dengan nomor {booking_number} telah diterima oleh {workshop_name}.\n\nDetail Booking:\nTanggal: {scheduled_date}\nWaktu: {scheduled_time}\nLayanan: {service_type}\n\nSilakan datang tepat waktu. Terima kasih!', 
'["booking_number", "customer_name", "workshop_name", "scheduled_date", "scheduled_time", "service_type"]');

-- Template: booking_completed
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables) VALUES
('booking_completed', 'Booking Selesai', 
'Servis Kendaraan Anda Telah Selesai - {booking_number}', 
'Dear {customer_name},\n\nServis kendaraan Anda dengan booking nomor {booking_number} telah selesai dikerjakan.\n\nTotal Biaya: Rp {actual_price}\n\nAnda dapat mengambil kendaraan Anda di {workshop_name}.\n\nTerima kasih atas kepercayaan Anda!', 
'["booking_number", "customer_name", "workshop_name", "actual_price"]');

-- Template: reminder_service
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables) VALUES
('reminder_service', 'Pengingat Servis Berkala', 
'Pengingat Servis Berkala Kendaraan Anda', 
'Dear {customer_name},\n\nSudah saatnya untuk melakukan servis berkala pada kendaraan Anda ({vehicle_number}).\n\nRekomendasi servis setiap {reminder_interval_km} km atau {reminder_interval_months} bulan.\n\nSegera buat booking untuk menjaga performa kendaraan Anda.\n\nAkses aplikasi Bengkel Terdekat untuk booking sekarang!', 
'["customer_name", "vehicle_number", "reminder_interval_km", "reminder_interval_months"]');

-- Template: emergency_alert
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables) VALUES
('emergency_alert', 'Alert Darurat', 
'DARURAT: Permintaan Bantuan Jalan - {request_number}', 
'DARURAT!\n\nPermintaan bantuan jalan darurat baru:\nNomor: {request_number}\nJenis: {emergency_type}\nLokasi: {location_address}\nDeskripsi: {description}\n\nSegera tanggap jika Anda dapat membantu.', 
'["request_number", "emergency_type", "location_address", "description"]');

-- Template: booking_rejected
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables) VALUES
('booking_rejected', 'Booking Ditolak', 
'Booking Anda Ditolak - {booking_number}', 
'Dear {customer_name},\n\nMaaf, booking Anda dengan nomor {booking_number} tidak dapat diterima.\n\nAlasan: {rejection_reason}\n\nSilakan buat booking baru atau pilih workshop lain.\n\nTerima kasih atas pengertian Anda.', 
'["booking_number", "customer_name", "rejection_reason"]');

-- Template: booking_cancelled
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables) VALUES
('booking_cancelled', 'Booking Dibatalkan', 
'Booking Dibatalkan - {booking_number}', 
'Dear {customer_name},\n\nBooking Anda dengan nomor {booking_number} telah dibatalkan.\n\nAlasan: {cancellation_reason}\n\nJika Anda memiliki pertanyaan, silakan hubungi kami.\n\nTerima kasih.', 
'["booking_number", "customer_name", "cancellation_reason"]');

-- Template: workshop_approved
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables) VALUES
('workshop_approved', 'Workshop Disetujui', 
'Workshop Anda Telah Disetujui', 
'Dear {owner_name},\n\nSelamat! Workshop "{workshop_name}" Anda telah disetujui dan aktif di platform Bengkel Terdekat.\n\nAnda sekarang dapat menerima booking dari pelanggan.\n\nTerima kasih telah bergabung dengan kami!', 
'["owner_name", "workshop_name"]');

-- Template: workshop_rejected
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables) VALUES
('workshop_rejected', 'Workshop Ditolak', 
'Pendaftaran Workshop Ditolak', 
'Dear {owner_name},\n\nMaaf, pendaftaran workshop "{workshop_name}" Anda tidak dapat disetujui.\n\nAlasan: {rejection_reason}\n\nSilakan perbaiki data dan ajukan kembali.\n\nTerima kasih.', 
'["owner_name", "workshop_name", "rejection_reason"]');

-- Template: password_reset
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables) VALUES
('password_reset', 'Reset Password', 
'Reset Password Akun Anda', 
'Dear {user_name},\n\nKami menerima permintaan reset password untuk akun Anda.\n\nKlik link berikut untuk reset password:\n{reset_link}\n\nLink ini akan kadaluarsa dalam 1 jam.\n\nJika Anda tidak meminta reset ini, abaikan email ini.', 
'["user_name", "reset_link"]');

-- Template: welcome_user
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables) VALUES
('welcome_user', 'Selamat Datang', 
'Selamat Datang di Bengkel Terdekat!', 
'Dear {user_name},\n\nSelamat datang di Bengkel Terdekat!\n\nTerima kasih telah mendaftar. Platform kami akan membantu Anda menemukan bengkel terdekat dan terpercaya untuk kebutuhan servis kendaraan Anda.\n\nMulai jelajahi workshop dan buat booking pertama Anda sekarang!\n\nSalam,\nTim Bengkel Terdekat', 
'["user_name"]');

-- Template: review_submitted
INSERT INTO notification_templates (event_key, event_name, subject_template, body_template, variables) VALUES
('review_submitted', 'Review Dikirim', 
'Terima Kasih Atas Review Anda', 
'Dear {user_name},\n\nTerima kasih telah meluangkan waktu untuk memberikan review pada {workshop_name}.\n\nFeedback Anda sangat berharga untuk meningkatkan kualitas layanan bengkel di platform kami.\n\nSalam,\nTim Bengkel Terdekat', 
'["user_name", "workshop_name"]');

-- ============================================
-- END OF SEED DATA
-- ============================================
