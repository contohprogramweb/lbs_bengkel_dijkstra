-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table dijkstrabengkelbaru.activity_logs
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL COMMENT 'User who performed the action',
  `workshop_id` int unsigned DEFAULT NULL COMMENT 'Workshop involved (if applicable)',
  `action_type` varchar(50) NOT NULL COMMENT 'Type of action',
  `action_description` text NOT NULL,
  `target_user_id` int unsigned DEFAULT NULL COMMENT 'Target user (if action affects another user)',
  `target_workshop_id` int unsigned DEFAULT NULL COMMENT 'Target workshop (if action affects another workshop)',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address of the actor',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Browser/User agent',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_workshop_id` (`workshop_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `target_user_id` (`target_user_id`),
  KEY `target_workshop_id` (`target_workshop_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activity_logs_ibfk_3` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activity_logs_ibfk_4` FOREIGN KEY (`target_workshop_id`) REFERENCES `workshops` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii COMMENT='Audit trail for admin actions';

-- Dumping data for table dijkstrabengkelbaru.activity_logs: ~19 rows (approximately)
INSERT INTO `activity_logs` (`id`, `user_id`, `workshop_id`, `action_type`, `action_description`, `target_user_id`, `target_workshop_id`, `ip_address`, `user_agent`, `created_at`) VALUES
	(1, 1, NULL, 'USER_CREATE', 'Admin membuat user baru', NULL, NULL, '127.0.0.1', NULL, '2025-01-01 01:00:00'),
	(2, 1, NULL, 'WORKSHOP_VERIFY', 'Admin memverifikasi bengkel ID 1', NULL, NULL, '127.0.0.1', NULL, '2025-01-02 02:00:00'),
	(3, 1, NULL, 'WORKSHOP_VERIFY', 'Admin memverifikasi bengkel ID 2', NULL, NULL, '127.0.0.1', NULL, '2025-01-02 02:30:00'),
	(4, 1, NULL, 'SYSTEM_SETTING_UPDATE', 'Admin memperbarui pengaturan sistem', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-07 21:12:06'),
	(5, 1, NULL, 'SYSTEM_SETTING_UPDATE', 'Admin memperbarui pengaturan sistem', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-07 21:12:24'),
	(6, 1, NULL, 'USER_DEACTIVATE', 'Admin menonaktifkan user ID 7', 7, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-08 03:01:58'),
	(7, 1, NULL, 'WORKSHOP_VERIFY', 'Admin memverifikasi bengkel ID 1', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-08 10:50:54'),
	(8, 1, NULL, 'SYSTEM_SETTING_UPDATE', 'Admin memperbarui pengaturan sistem', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-08 10:52:23'),
	(9, 1, NULL, 'USER_ACTIVATE', 'Admin mengaktifkan user ID 7', 7, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-08 15:33:25'),
	(10, 1, NULL, 'SYSTEM_SETTING_UPDATE', 'Admin memperbarui pengaturan sistem', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-09 04:18:48'),
	(11, 1, NULL, 'SYSTEM_SETTING_UPDATE', 'Admin memperbarui pengaturan sistem', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-09 05:02:19'),
	(12, 1, NULL, 'SYSTEM_SETTING_UPDATE', 'Admin memperbarui pengaturan sistem', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-09 05:02:27'),
	(13, 1, NULL, 'USER_DEACTIVATE', 'Admin menonaktifkan user ID 9', 9, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-09 13:17:29'),
	(14, 1, NULL, 'USER_ACTIVATE', 'Admin mengaktifkan user ID 9', 9, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-09 13:17:40'),
	(15, 1, NULL, 'WORKSHOP_VERIFY', 'Admin memverifikasi bengkel ID 2', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-09 13:43:09'),
	(16, 1, NULL, 'WORKSHOP_FEATURE', 'Menjadikan bengkel featured bengkel ID 2', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-09 14:04:34'),
	(17, 1, NULL, 'WORKSHOP_UNFEATURE', 'Menghapus status featured bengkel ID 2', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-09 14:04:52'),
	(18, 1, NULL, 'REVIEW_APPROVE', 'Admin menyetujui review ID 2', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-09 14:12:58'),
	(19, 1, NULL, 'REVIEW_REJECT', 'Admin menolak review ID 1: xxx', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:150.0) Gecko/20100101 Firefox/150.0', '2026-06-09 14:13:08');

-- Dumping structure for table dijkstrabengkelbaru.bookings
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `booking_number` varchar(50) NOT NULL,
  `user_id` int unsigned NOT NULL,
  `workshop_id` int unsigned NOT NULL,
  `vehicle_id` int unsigned DEFAULT NULL,
  `service_type` enum('regular','repair','maintenance','emergency','custom') NOT NULL,
  `service_description` text NOT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time NOT NULL,
  `estimated_duration` int unsigned DEFAULT NULL COMMENT 'in minutes',
  `estimated_price` decimal(10,2) DEFAULT NULL,
  `service_cost` decimal(12,2) DEFAULT '0.00' COMMENT 'Biaya layanan',
  `sparepart_cost` decimal(12,2) DEFAULT '0.00' COMMENT 'Biaya sparepart',
  `additional_cost` decimal(12,2) DEFAULT '0.00' COMMENT 'Biaya tambahan',
  `final_total` decimal(12,2) DEFAULT '0.00' COMMENT 'Total tagihan akhir',
  `payment_status` enum('unpaid','paid','partial') DEFAULT 'unpaid',
  `invoice_number` varchar(50) DEFAULT NULL COMMENT 'Nomor invoice',
  `invoiced_at` datetime DEFAULT NULL COMMENT 'Tanggal invoice dibuat',
  `paid_at` datetime DEFAULT NULL COMMENT 'Tanggal pembayaran',
  `actual_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','accepted','processed','waiting_approval','rejected','in_progress','completed','cancelled','no_show') DEFAULT 'pending',
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` int unsigned DEFAULT NULL,
  `cancellation_reason` text,
  `notes` text,
  `mechanic_notes` text,
  `customer_rating` tinyint DEFAULT NULL,
  `customer_review` text,
  `reschedule_count` int DEFAULT '0',
  `last_rescheduled_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_number` (`booking_number`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `approved_by` (`approved_by`),
  KEY `cancelled_by` (`cancelled_by`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_workshop_id` (`workshop_id`),
  KEY `idx_vehicle_id` (`vehicle_id`),
  KEY `idx_status` (`status`),
  KEY `idx_scheduled_date` (`scheduled_date`),
  KEY `idx_is_deleted` (`is_deleted`),
  KEY `idx_workshop_status_created` (`workshop_id`,`status`,`created_at`),
  KEY `idx_booking_number` (`booking_number`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_invoiced_at` (`invoiced_at`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_ibfk_5` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.bookings: ~4 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.booking_additional_charges
CREATE TABLE IF NOT EXISTS `booking_additional_charges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int unsigned NOT NULL,
  `charge_name` varchar(200) NOT NULL,
  `description` text,
  `amount` decimal(12,2) NOT NULL,
  `is_approved` tinyint(1) DEFAULT '0' COMMENT 'Disetujui user atau tidak',
  `approval_id` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `approval_id` (`approval_id`),
  KEY `idx_booking` (`booking_id`),
  CONSTRAINT `booking_additional_charges_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_additional_charges_ibfk_2` FOREIGN KEY (`approval_id`) REFERENCES `booking_approvals` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii COMMENT='Biaya tambahan per booking';

-- Dumping data for table dijkstrabengkelbaru.booking_additional_charges: ~0 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.booking_approvals
CREATE TABLE IF NOT EXISTS `booking_approvals` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int unsigned NOT NULL,
  `approver_id` int unsigned NOT NULL,
  `action` enum('approve','reject') NOT NULL,
  `reason` text,
  `comments` text,
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_approver_id` (`approver_id`),
  KEY `idx_is_deleted` (`is_deleted`),
  CONSTRAINT `booking_approvals_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_approvals_ibfk_2` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.booking_approvals: ~0 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.booking_mechanics
CREATE TABLE IF NOT EXISTS `booking_mechanics` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int unsigned NOT NULL,
  `mechanic_id` int unsigned NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` int unsigned DEFAULT NULL,
  `notes` text,
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_booking_mechanic` (`booking_id`,`mechanic_id`),
  KEY `assigned_by` (`assigned_by`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_mechanic_id` (`mechanic_id`),
  KEY `idx_is_deleted` (`is_deleted`),
  CONSTRAINT `booking_mechanics_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_mechanics_ibfk_2` FOREIGN KEY (`mechanic_id`) REFERENCES `mechanics` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_mechanics_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.booking_mechanics: ~2 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.booking_service_items
CREATE TABLE IF NOT EXISTS `booking_service_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int unsigned NOT NULL,
  `service_name` varchar(200) NOT NULL,
  `description` text,
  `quantity` int DEFAULT '1',
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking` (`booking_id`),
  CONSTRAINT `booking_service_items_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=ascii COMMENT='Detail layanan per booking';

-- Dumping data for table dijkstrabengkelbaru.booking_service_items: ~0 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.booking_sparepart_items
CREATE TABLE IF NOT EXISTS `booking_sparepart_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int unsigned NOT NULL,
  `sparepart_name` varchar(200) NOT NULL,
  `part_number` varchar(100) DEFAULT NULL,
  `description` text,
  `quantity` int DEFAULT '1',
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking` (`booking_id`),
  CONSTRAINT `booking_sparepart_items_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=ascii COMMENT='Detail sparepart per booking';

-- Dumping data for table dijkstrabengkelbaru.booking_sparepart_items: ~0 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.emergency_requests
CREATE TABLE IF NOT EXISTS `emergency_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `request_number` varchar(50) NOT NULL,
  `user_id` int unsigned NOT NULL,
  `vehicle_id` int unsigned DEFAULT NULL,
  `workshop_id` int unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'For rate limiting',
  `emergency_type` enum('breakdown','accident','flat_tire','battery','fuel','lockout','other') NOT NULL,
  `description` text NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `location_address` text,
  `status` enum('pending','assigned','in_progress','completed','cancelled') DEFAULT 'pending',
  `assigned_workshop_id` int unsigned DEFAULT NULL,
  `assigned_mechanic_id` int unsigned DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `arrived_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text,
  `estimated_arrival_time` int DEFAULT NULL COMMENT 'minutes',
  `actual_arrival_time` int DEFAULT NULL COMMENT 'minutes',
  `service_cost` decimal(10,2) DEFAULT NULL,
  `notes` text,
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_number` (`request_number`),
  KEY `vehicle_id` (`vehicle_id`),
  KEY `workshop_id` (`workshop_id`),
  KEY `assigned_workshop_id` (`assigned_workshop_id`),
  KEY `assigned_mechanic_id` (`assigned_mechanic_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_emergency_type` (`emergency_type`),
  KEY `idx_location` (`latitude`,`longitude`),
  KEY `idx_created` (`created_at`),
  KEY `idx_is_deleted` (`is_deleted`),
  CONSTRAINT `emergency_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `emergency_requests_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `emergency_requests_ibfk_3` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE SET NULL,
  CONSTRAINT `emergency_requests_ibfk_4` FOREIGN KEY (`assigned_workshop_id`) REFERENCES `workshops` (`id`) ON DELETE SET NULL,
  CONSTRAINT `emergency_requests_ibfk_5` FOREIGN KEY (`assigned_mechanic_id`) REFERENCES `mechanics` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.emergency_requests: ~2 rows (approximately)
INSERT INTO `emergency_requests` (`id`, `request_number`, `user_id`, `vehicle_id`, `workshop_id`, `ip_address`, `emergency_type`, `description`, `latitude`, `longitude`, `location_address`, `status`, `assigned_workshop_id`, `assigned_mechanic_id`, `assigned_at`, `accepted_at`, `arrived_at`, `completed_at`, `cancelled_at`, `cancellation_reason`, `estimated_arrival_time`, `actual_arrival_time`, `service_cost`, `notes`, `is_deleted`, `deleted_at`, `created_at`, `updated_at`) VALUES
	(1, 'EMG-20250115-0001', 6, NULL, NULL, NULL, 'flat_tire', 'Ban bocor di jalan raya', -6.20876300, 106.84559900, 'Jl. Sudirman, Jakarta Pusat', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-01-15 01:00:00', '2026-06-07 19:22:35'),
	(2, 'EMG-20250115-0002', 7, NULL, NULL, NULL, 'battery', 'Aki soak, mobil tidak bisa distarter', -6.91746400, 107.61912300, 'Jl. Asia Afrika, Bandung', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-01-15 03:30:00', '2026-06-07 19:22:35');

-- Dumping structure for table dijkstrabengkelbaru.invoices
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `booking_id` int unsigned NOT NULL,
  `workshop_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `service_cost` decimal(12,2) DEFAULT '0.00',
  `sparepart_cost` decimal(12,2) DEFAULT '0.00',
  `additional_cost` decimal(12,2) DEFAULT '0.00',
  `discount_amount` decimal(12,2) DEFAULT '0.00',
  `tax_amount` decimal(12,2) DEFAULT '0.00',
  `tax_rate` decimal(5,2) DEFAULT '0.00' COMMENT 'Persentase pajak',
  `total_amount` decimal(12,2) NOT NULL,
  `payment_status` enum('unpaid','paid','partial','cancelled') DEFAULT 'unpaid',
  `paid_amount` decimal(12,2) DEFAULT '0.00',
  `paid_at` datetime DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_note` text,
  `notes` text,
  `is_deleted` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `booking_id` (`booking_id`),
  KEY `idx_workshop` (`workshop_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_issue_date` (`issue_date`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=ascii COMMENT='Invoice formal untuk tagihan';

-- Dumping data for table dijkstrabengkelbaru.invoices: ~2 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.invoice_payments
CREATE TABLE IF NOT EXISTS `invoice_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL COMMENT 'cash, transfer, debit, credit',
  `reference_number` varchar(100) DEFAULT NULL COMMENT 'No referensi transfer/kwitansi',
  `notes` text,
  `received_by` int unsigned DEFAULT NULL COMMENT 'User ID yang menerima pembayaran',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `received_by` (`received_by`),
  KEY `idx_invoice` (`invoice_id`),
  KEY `idx_payment_date` (`payment_date`),
  CONSTRAINT `invoice_payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_payments_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii COMMENT='Pembayaran invoice (bisa multiple payments)';

-- Dumping data for table dijkstrabengkelbaru.invoice_payments: ~0 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.mechanics
CREATE TABLE IF NOT EXISTS `mechanics` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `workshop_id` int unsigned DEFAULT NULL,
  `specialization` json DEFAULT NULL COMMENT 'Array of specializations',
  `experience_years` int unsigned DEFAULT '0',
  `certification` text,
  `rating_avg` decimal(3,2) DEFAULT '0.00',
  `total_reviews` int unsigned DEFAULT '0',
  `is_available` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_workshop_id` (`workshop_id`),
  KEY `idx_available` (`is_available`),
  KEY `idx_is_deleted` (`is_deleted`),
  CONSTRAINT `mechanics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mechanics_ibfk_2` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.mechanics: ~2 rows (approximately)
INSERT INTO `mechanics` (`id`, `user_id`, `workshop_id`, `specialization`, `experience_years`, `certification`, `rating_avg`, `total_reviews`, `is_available`, `is_deleted`, `deleted_at`, `created_at`, `updated_at`) VALUES
	(1, 4, NULL, '["kelistrikan"]', 5, 'ASTRA Certified', 0.00, 0, 1, 0, NULL, '2026-06-07 19:22:35', '2026-06-10 04:46:52'),
	(2, 5, NULL, '["motor", "elektrikal"]', 3, 'Yamaha Certified', 0.00, 0, 1, 0, NULL, '2026-06-07 19:22:35', '2026-06-07 19:22:35');

-- Dumping structure for table dijkstrabengkelbaru.notification_logs
CREATE TABLE IF NOT EXISTS `notification_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `recipient_email` varchar(191) NOT NULL,
  `recipient_name` varchar(100) DEFAULT NULL,
  `event_key` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text,
  `status` enum('pending','sent','failed','bounced') DEFAULT 'pending',
  `error_message` text,
  `sent_at` timestamp NULL DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp when notification was read',
  `clicked_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recipient` (`recipient_email`),
  KEY `idx_event_key` (`event_key`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `idx_is_deleted` (`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.notification_logs: ~0 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.notification_templates
CREATE TABLE IF NOT EXISTS `notification_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_key` varchar(100) NOT NULL,
  `event_name` varchar(200) NOT NULL,
  `subject_template` varchar(255) NOT NULL,
  `body_template` text NOT NULL,
  `variables` json DEFAULT NULL COMMENT 'Available template variables',
  `is_active` tinyint(1) DEFAULT '1',
  `language` varchar(10) DEFAULT 'id',
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_key` (`event_key`),
  KEY `idx_event_key` (`event_key`),
  KEY `idx_active` (`is_active`),
  KEY `idx_is_deleted` (`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.notification_templates: ~6 rows (approximately)
INSERT INTO `notification_templates` (`id`, `event_key`, `event_name`, `subject_template`, `body_template`, `variables`, `is_active`, `language`, `is_deleted`, `deleted_at`, `created_at`, `updated_at`) VALUES
	(1, 'booking_accepted', 'Booking Diterima', 'Booking Anda Diterima - {{kode_booking}}', '<p>Halo {{nama_pengguna}},</p><p>Booking Anda dengan kode <strong>{{kode_booking}}</strong> telah diterima oleh <strong>{{nama_bengkel}}</strong>.</p><p>Silakan datang sesuai jadwal yang telah ditentukan.</p>', '["nama_pengguna", "kode_booking", "nama_bengkel"]', 1, 'id', 0, NULL, '2026-06-07 19:22:35', '2026-06-07 19:22:35'),
	(2, 'booking_processed', 'Booking Sedang Dikerjakan', 'Booking Sedang Dikerjakan - {{kode_booking}}', '<p>Halo {{nama_pengguna}},</p><p>Booking Anda <strong>{{kode_booking}}</strong> sedang dikerjakan.</p>', '["nama_pengguna", "kode_booking"]', 1, 'id', 0, NULL, '2026-06-07 19:22:35', '2026-06-07 19:22:35'),
	(3, 'booking_completed', 'Booking Selesai', 'Booking Selesai - {{kode_booking}}', '<p>Halo {{nama_pengguna}},</p><p>Booking Anda <strong>{{kode_booking}}</strong> telah selesai.</p>', '["nama_pengguna", "kode_booking"]', 1, 'id', 0, NULL, '2026-06-07 19:22:35', '2026-06-07 19:22:35'),
	(4, 'booking_cancelled', 'Booking Dibatalkan', 'Booking Dibatalkan - {{kode_booking}}', '<p>Halo {{nama_pengguna}},</p><p>Booking Anda <strong>{{kode_booking}}</strong> telah dibatalkan.</p>', '["nama_pengguna", "kode_booking"]', 1, 'id', 0, NULL, '2026-06-07 19:22:35', '2026-06-07 19:22:35'),
	(5, 'service_reminder', 'Pengingat Servis Berkala', 'Saatnya Servis Kendaraan Anda!', '<p>Halo {{nama_pengguna}},</p><p>Kendaraan <strong>{{kendaraan}}</strong> sudah saatnya servis berkala.</p>', '["nama_pengguna", "kendaraan"]', 1, 'id', 0, NULL, '2026-06-07 19:22:35', '2026-06-07 19:22:35'),
	(6, 'booking_rejected', 'Booking Ditolak', 'Booking Ditolak - {{kode_booking}}', '<p>Halo {{nama_pengguna}},</p><p>Maaf, booking Anda <strong>{{kode_booking}}</strong> tidak dapat diterima.</p>', '["nama_pengguna", "kode_booking"]', 1, 'id', 0, NULL, '2026-06-07 19:22:35', '2026-06-07 19:22:35');

-- Dumping structure for table dijkstrabengkelbaru.report_settings
CREATE TABLE IF NOT EXISTS `report_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `workshop_id` int unsigned DEFAULT NULL COMMENT 'NULL = global setting',
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` enum('string','integer','decimal','boolean','json') DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_workshop_setting` (`workshop_id`,`setting_key`),
  CONSTRAINT `report_settings_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=ascii COMMENT='Pengaturan laporan per bengkel';

-- Dumping data for table dijkstrabengkelbaru.report_settings: ~4 rows (approximately)
INSERT INTO `report_settings` (`id`, `workshop_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES
	(1, NULL, 'default_tax_rate', '0', 'decimal', 'Default tax rate percentage', '2026-06-08 02:22:36', NULL),
	(2, NULL, 'invoice_prefix', 'INV', 'string', 'Prefix untuk nomor invoice', '2026-06-08 02:22:36', NULL),
	(3, NULL, 'report_currency', 'IDR', 'string', 'Mata uang untuk laporan', '2026-06-08 02:22:36', NULL),
	(4, NULL, 'invoice_due_days', '7', 'integer', 'Jatuh tempo invoice dalam hari', '2026-06-08 02:22:36', NULL);

-- Dumping structure for table dijkstrabengkelbaru.reviews
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `workshop_id` int unsigned NOT NULL,
  `mechanic_id` int unsigned DEFAULT NULL,
  `rating` tinyint NOT NULL,
  `review_text` text,
  `is_visible` tinyint(1) DEFAULT '1',
  `moderation_status` enum('pending','approved','rejected') DEFAULT 'approved',
  `moderation_notes` text COMMENT 'Admin notes for moderation',
  `moderated_by` int unsigned DEFAULT NULL COMMENT 'Admin who moderated this review',
  `moderated_at` timestamp NULL DEFAULT NULL COMMENT 'When moderation was performed',
  `report_count` int DEFAULT '0' COMMENT 'Number of times this review was reported',
  `admin_response` text,
  `responded_at` timestamp NULL DEFAULT NULL,
  `responded_by` int unsigned DEFAULT NULL,
  `helpful_count` int unsigned DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `mechanic_id` (`mechanic_id`),
  KEY `responded_by` (`responded_by`),
  KEY `moderated_by` (`moderated_by`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_workshop_id` (`workshop_id`),
  KEY `idx_rating` (`rating`),
  KEY `idx_visible` (`is_visible`),
  KEY `idx_is_deleted` (`is_deleted`),
  KEY `idx_moderation_status` (`moderation_status`),
  KEY `idx_report_count` (`report_count`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_4` FOREIGN KEY (`mechanic_id`) REFERENCES `mechanics` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_ibfk_5` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_ibfk_6` FOREIGN KEY (`moderated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_chk_1` CHECK ((`rating` between 1 and 5))
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.reviews: ~2 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.review_photos
CREATE TABLE IF NOT EXISTS `review_photos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `review_id` int unsigned NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `photo_original_name` varchar(255) DEFAULT NULL,
  `photo_size` int unsigned DEFAULT NULL,
  `photo_mime_type` varchar(50) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_review_id` (`review_id`),
  KEY `idx_is_deleted` (`is_deleted`),
  CONSTRAINT `review_photos_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.review_photos: ~0 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.review_reports
CREATE TABLE IF NOT EXISTS `review_reports` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `review_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `reason` text,
  `status` enum('pending','resolved','dismissed') DEFAULT 'pending',
  `resolved_by` int unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_review_user_report` (`review_id`,`user_id`),
  KEY `resolved_by` (`resolved_by`),
  KEY `idx_review_id` (`review_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_is_deleted` (`is_deleted`),
  CONSTRAINT `review_reports_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `review_reports_ibfk_3` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.review_reports: ~0 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.road_edges
CREATE TABLE IF NOT EXISTS `road_edges` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `from_node_id` int unsigned NOT NULL COMMENT 'Simpul asal',
  `to_node_id` int unsigned NOT NULL COMMENT 'Simpul tujuan',
  `road_name` varchar(150) DEFAULT NULL COMMENT 'Nama jalan',
  `distance_km` decimal(10,4) NOT NULL COMMENT 'Jarak dalam kilometer (bobot edge)',
  `is_bidirectional` tinyint(1) DEFAULT '1' COMMENT 'Apakah dua arah',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Status aktif/nonaktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
  PRIMARY KEY (`id`),
  KEY `idx_from_node` (`from_node_id`),
  KEY `idx_to_node` (`to_node_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `road_edges_ibfk_1` FOREIGN KEY (`from_node_id`) REFERENCES `road_nodes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `road_edges_ibfk_2` FOREIGN KEY (`to_node_id`) REFERENCES `road_nodes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_different_nodes` CHECK ((`from_node_id` <> `to_node_id`)),
  CONSTRAINT `chk_distance_positive` CHECK ((`distance_km` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.road_edges: ~7 rows (approximately)
INSERT INTO `road_edges` (`id`, `from_node_id`, `to_node_id`, `road_name`, `distance_km`, `is_bidirectional`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 2, 'Jl. M.H. Thamrin', 0.3500, 1, 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL),
	(2, 2, 3, 'Jl. Jend. Sudirman', 2.1500, 1, 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL),
	(3, 3, 4, 'Jl. Prof. Dr. Satrio', 2.5000, 1, 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL),
	(4, 4, 5, 'Jl. Jend. Sudirman', 3.2000, 1, 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL),
	(5, 2, 4, 'Jl. H.R. Rasuna Said', 1.8000, 1, 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL),
	(6, 1, 6, 'Jl. Medan Merdeka', 2.1000, 1, 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL),
	(7, 6, 7, 'Jl. Medan Merdeka Utara', 0.8000, 1, 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL);

-- Dumping structure for table dijkstrabengkelbaru.road_nodes
CREATE TABLE IF NOT EXISTS `road_nodes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Nama simpul/persimpangan',
  `latitude` decimal(10,8) NOT NULL COMMENT 'Latitude koordinat',
  `longitude` decimal(11,8) NOT NULL COMMENT 'Longitude koordinat',
  `node_type` enum('intersection','landmark','custom') DEFAULT 'intersection' COMMENT 'Tipe simpul',
  `description` text COMMENT 'Deskripsi tambahan',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Status aktif/nonaktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
  PRIMARY KEY (`id`),
  KEY `idx_coordinates` (`latitude`,`longitude`),
  KEY `idx_active` (`is_active`),
  KEY `idx_node_type` (`node_type`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.road_nodes: ~7 rows (approximately)
INSERT INTO `road_nodes` (`id`, `name`, `latitude`, `longitude`, `node_type`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'Simpang HI', -6.19473200, 106.82291700, 'intersection', 'Bundaran Hotel Indonesia', 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL),
	(2, 'Simpang Thamrin-Sudirman', -6.19744400, 106.82166700, 'intersection', 'Pertemuan Jl. Thamrin dan Sudirman', 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL),
	(3, 'Simpang Kuningan', -6.21666700, 106.82388900, 'intersection', 'Rasuna Said/Kuningan', 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL),
	(4, 'Simpang Semanggi', -6.21277800, 106.80555600, 'intersection', 'Simpang Susun Semanggi', 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL),
	(5, 'Simpang Blok M', -6.24222200, 106.79833300, 'intersection', 'Terminal Blok M', 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL),
	(6, 'Monas', -6.17539200, 106.82715300, 'landmark', 'Monumen Nasional', 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL),
	(7, 'Stasiun Gambir', -6.17388900, 106.83388900, 'landmark', 'Stasiun Kereta Gambir', 1, '2026-06-07 19:22:36', '2026-06-07 19:22:36', NULL);

-- Dumping structure for table dijkstrabengkelbaru.system_settings
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','integer','float','boolean','json') DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.system_settings: ~11 rows (approximately)
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `category`, `created_at`, `updated_at`) VALUES
	(13, 'app_name', 'Bengkel Terdekat', 'string', 'Application name displayed in UI', 'general', '2026-06-07 21:11:12', '2026-06-09 05:02:26'),
	(14, 'timezone', 'Asia/Jakarta', 'string', 'System timezone', 'general', '2026-06-07 21:11:12', '2026-06-09 05:02:27'),
	(15, 'email_notifications_enabled', '1', 'boolean', 'Enable email notifications', 'notification', '2026-06-07 21:11:12', '2026-06-09 05:02:27'),
	(16, 'sms_notifications_enabled', '1', 'boolean', 'Enable SMS notifications', 'notification', '2026-06-07 21:11:12', '2026-06-09 05:02:27'),
	(17, 'whatsapp_notifications_enabled', '1', 'boolean', 'Enable WhatsApp notifications', 'notification', '2026-06-07 21:11:12', '2026-06-09 05:02:27'),
	(18, 'min_review_length', '20', 'integer', 'Minimum character length for reviews', 'review', '2026-06-07 21:11:12', '2026-06-09 05:02:27'),
	(19, 'allow_review_photos', '1', 'boolean', 'Allow users to upload photos with reviews', 'review', '2026-06-07 21:11:12', '2026-06-09 05:02:27'),
	(20, 'max_advance_booking_days', '30', 'integer', 'Maximum days in advance for booking', 'booking', '2026-06-07 21:11:12', '2026-06-09 05:02:27'),
	(21, 'min_booking_lead_time_hours', '2', 'integer', 'Minimum hours before booking time', 'booking', '2026-06-07 21:11:12', '2026-06-09 05:02:27'),
	(22, 'cache_ttl_minutes', '60', 'integer', 'Cache time-to-live in minutes', 'system', '2026-06-07 21:11:12', '2026-06-09 05:02:27'),
	(23, 'maintenance_mode', '1', 'boolean', 'Enable maintenance mode', 'system', '2026-06-07 21:11:12', '2026-06-09 05:02:27');

-- Dumping structure for table dijkstrabengkelbaru.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `address` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','workshop_owner','mechanic','customer') NOT NULL DEFAULT 'customer',
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_is_deleted` (`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.users: ~9 rows (approximately)
INSERT INTO `users` (`id`, `email`, `password`, `full_name`, `address`, `phone`, `role`, `avatar`, `is_active`, `is_deleted`, `deleted_at`, `email_verified_at`, `last_login_at`, `created_at`, `updated_at`) VALUES
	(1, 'admin@bengkel.com', '$2y$10$Z1RLuFUAqgRsqfgMWpir7.QgYmvic8XDM7pvG.FmDgpAzbUreBqJq', 'Administrator', NULL, '081234567890', 'admin', NULL, 1, 0, NULL, '2026-06-07 19:22:34', '2026-06-09 07:49:03', '2026-06-07 19:22:34', '2026-06-09 14:49:03'),
	(2, 'owner1@bengkel.com', '$2y$10$Z1RLuFUAqgRsqfgMWpir7.QgYmvic8XDM7pvG.FmDgpAzbUreBqJq', 'Budi Santoso', NULL, '081234567891', 'workshop_owner', NULL, 1, 0, NULL, '2026-06-07 19:22:34', '2026-06-09 18:53:10', '2026-06-07 19:22:34', '2026-06-10 01:53:10'),
	(3, 'owner2@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Aminah', NULL, '081234567892', 'workshop_owner', NULL, 1, 0, NULL, '2026-06-07 19:22:34', NULL, '2026-06-07 19:22:34', '2026-06-07 19:22:34'),
	(4, 'mechanic1@bengkel.com', '$2y$10$00bFQ7Yac3W/SYTV49j2Z.guXTRhGSc6WoZbns5.tdsR49ADC5Z8m', 'Ahmad Hidayat', NULL, '081234567893', 'mechanic', NULL, 1, 0, NULL, '2026-06-07 19:22:34', '2026-06-09 22:40:37', '2026-06-07 19:22:34', '2026-06-10 05:40:37'),
	(5, 'mechanic2@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rudi Hartono', NULL, '081234567894', 'mechanic', NULL, 1, 0, NULL, '2026-06-07 19:22:34', NULL, '2026-06-07 19:22:34', '2026-06-07 19:22:34'),
	(6, 'customer1@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Andi Wijaya', NULL, '081234567895', 'customer', NULL, 1, 0, NULL, '2026-06-07 19:22:34', NULL, '2026-06-07 19:22:34', '2026-06-07 19:22:34'),
	(7, 'customer2@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dewi Lestari', NULL, '081234567896', 'customer', NULL, 1, 0, NULL, '2026-06-07 19:22:34', NULL, '2026-06-07 19:22:34', '2026-06-08 15:33:25'),
	(8, 'customer3@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eko Prasetyo', NULL, '081234567897', 'customer', NULL, 1, 0, NULL, '2026-06-07 19:22:34', NULL, '2026-06-07 19:22:34', '2026-06-07 19:22:34'),
	(9, 'dedhy2001@yahoo.com', '$2y$10$Z1RLuFUAqgRsqfgMWpir7.QgYmvic8XDM7pvG.FmDgpAzbUreBqJq', 'dedhy', 'Semarang', '-', 'customer', 'uploads/profiles/avatar_9_1780961244.png', 1, 0, NULL, NULL, '2026-06-10 01:08:37', '2026-06-07 19:31:31', '2026-06-10 08:08:37');

-- Dumping structure for table dijkstrabengkelbaru.vehicles
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `vehicle_number` varchar(20) NOT NULL,
  `vehicle_type` enum('motorcycle','car','truck','bus','other') NOT NULL,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) DEFAULT NULL,
  `year` year DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `engine_capacity` varchar(20) DEFAULT NULL,
  `transmission` enum('manual','automatic','cvt') DEFAULT 'manual',
  `fuel_type` enum('petrol','diesel','electric','hybrid') DEFAULT 'petrol',
  `last_service_date` date DEFAULT NULL,
  `last_service_km` int unsigned DEFAULT NULL,
  `current_km` int unsigned DEFAULT '0',
  `notes` text,
  `photo` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `reminder_enabled` tinyint(1) DEFAULT '1' COMMENT 'Enable/disable service reminder for this vehicle',
  `reminder_snoozed_until` date DEFAULT NULL COMMENT 'Reminder snoozed until this date',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_vehicle_number` (`vehicle_number`),
  KEY `idx_vehicle_type` (`vehicle_type`),
  KEY `idx_is_deleted` (`is_deleted`),
  KEY `idx_reminder_enabled` (`reminder_enabled`),
  CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.vehicles: ~5 rows (approximately)
INSERT INTO `vehicles` (`id`, `user_id`, `vehicle_number`, `vehicle_type`, `brand`, `model`, `year`, `color`, `engine_capacity`, `transmission`, `fuel_type`, `last_service_date`, `last_service_km`, `current_km`, `notes`, `photo`, `is_primary`, `is_deleted`, `deleted_at`, `reminder_enabled`, `reminder_snoozed_until`, `created_at`, `updated_at`) VALUES
	(1, 6, 'B 1234 CD', 'car', 'Toyota', 'Avanza', '2020', 'Silver', NULL, 'manual', 'petrol', '2024-06-15', 40000, 45000, NULL, NULL, 1, 0, NULL, 1, NULL, '2026-06-07 19:22:35', '2026-06-07 19:22:35'),
	(2, 6, 'B 5678 EF', 'motorcycle', 'Honda', 'Vario 150', '2021', 'Red', NULL, 'cvt', 'petrol', '2024-08-01', 20000, 25000, NULL, NULL, 0, 0, NULL, 1, NULL, '2026-06-07 19:22:35', '2026-06-07 19:22:35'),
	(3, 7, 'D 9876 AB', 'car', 'Honda', 'Brio', '2019', 'White', NULL, 'automatic', 'petrol', '2024-05-20', 55000, 60000, NULL, NULL, 1, 0, NULL, 1, NULL, '2026-06-07 19:22:35', '2026-06-07 19:22:35'),
	(4, 8, 'F 1111 GG', 'motorcycle', 'Yamaha', 'NMAX', '2022', 'Blue', NULL, 'cvt', 'petrol', '2024-09-01', 10000, 15000, NULL, NULL, 1, 0, NULL, 1, NULL, '2026-06-07 19:22:35', '2026-06-07 19:22:35'),
	(5, 8, 'F 2222 HH', 'car', 'Mitsubishi', 'Xpander', '2021', 'Black', NULL, 'automatic', 'petrol', '2024-07-10', 30000, 35000, NULL, NULL, 0, 0, NULL, 1, NULL, '2026-06-07 19:22:35', '2026-06-07 19:22:35'),
	(6, 9, 'H3982MM', 'motorcycle', 'Yamaha', 'Vixion', '2012', 'Hitam', NULL, 'manual', 'petrol', NULL, NULL, 36000, '', NULL, 0, 0, NULL, 1, NULL, '2026-06-10 06:28:38', '2026-06-10 06:30:23');

-- Dumping structure for table dijkstrabengkelbaru.workshops
CREATE TABLE IF NOT EXISTS `workshops` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `photos` json DEFAULT NULL,
  `rating_avg` decimal(3,2) DEFAULT '0.00',
  `total_reviews` int unsigned DEFAULT '0',
  `status` enum('pending','active','inactive','suspended') DEFAULT 'pending',
  `is_featured` tinyint(1) DEFAULT '0' COMMENT 'Featured workshop for promotion',
  `verified_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp when workshop was verified by admin',
  `business_license` varchar(255) DEFAULT NULL COMMENT 'Business license document path',
  `certification_doc` varchar(255) DEFAULT NULL COMMENT 'Certification document path',
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `approved_by` int unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text,
  `operating_hours` json DEFAULT NULL,
  `services_offered` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_is_deleted` (`is_deleted`),
  KEY `idx_city` (`city`),
  KEY `idx_province` (`province`),
  KEY `idx_coordinates` (`latitude`,`longitude`),
  KEY `idx_rating` (`rating_avg`),
  KEY `idx_is_featured` (`is_featured`),
  KEY `idx_verified_at` (`verified_at`),
  CONSTRAINT `workshops_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workshops_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.workshops: ~2 rows (approximately)
INSERT INTO `workshops` (`id`, `user_id`, `name`, `description`, `address`, `city`, `province`, `postal_code`, `latitude`, `longitude`, `phone`, `whatsapp`, `logo`, `photos`, `rating_avg`, `total_reviews`, `status`, `is_featured`, `verified_at`, `business_license`, `certification_doc`, `is_active`, `is_deleted`, `deleted_at`, `approved_by`, `approved_at`, `rejection_reason`, `operating_hours`, `services_offered`, `created_at`, `updated_at`) VALUES
	(1, 2, 'Bengkel Maju Jaya', 'Spesialis servis mobil Jepang dan Eropa', 'Jl. Raya Bogor No. 123', 'Jakarta Timur', 'DKI Jakarta', '13460', -6.22974600, 106.82291700, '021-12345678', '081234567891', NULL, NULL, 4.50, 2, 'active', 1, '2026-06-08 03:50:53', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '[{"day": "Senin", "open": "08:00", "close": "17:00"}, {"day": "Selasa", "open": "08:00", "close": "17:00"}, {"day": "Rabu", "open": "08:00", "close": "17:00"}, {"day": "Kamis", "open": "08:00", "close": "17:00"}, {"day": "Jumat", "open": "08:00", "close": "17:00"}, {"day": "Sabtu", "open": "09:00", "close": "15:00"}, {"day": "Minggu", "open": null, "close": null}]', NULL, '2026-06-07 12:22:35', '2026-06-10 08:04:22'),
	(2, 3, 'Bengkel Berkah Motor', 'Servis motor dan mobil semua merk', 'Jl. Sudirman No. 45', 'Bandung', 'Jawa Barat', '40123', -6.91746400, 107.61912300, '022-87654321', '081234567892', NULL, NULL, 0.00, 0, 'active', 0, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '[{"day": "Senin", "open": "08:00", "close": "17:00"}, {"day": "Selasa", "open": "08:00", "close": "17:00"}, {"day": "Rabu", "open": "08:00", "close": "17:00"}, {"day": "Kamis", "open": "08:00", "close": "17:00"}, {"day": "Jumat", "open": "08:00", "close": "17:00"}, {"day": "Sabtu", "open": "09:00", "close": "15:00"}, {"day": "Minggu", "open": null, "close": null}]', NULL, '2026-06-07 12:22:35', '2026-06-10 08:04:29');

-- Dumping structure for table dijkstrabengkelbaru.workshop_blocked_dates
CREATE TABLE IF NOT EXISTS `workshop_blocked_dates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `workshop_id` int unsigned NOT NULL,
  `blocked_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `is_full_day` tinyint(1) DEFAULT '1',
  `blocked_by` int unsigned DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `blocked_by` (`blocked_by`),
  KEY `idx_workshop_date` (`workshop_id`,`blocked_date`),
  KEY `idx_blocked_date` (`blocked_date`),
  KEY `idx_is_deleted` (`is_deleted`),
  CONSTRAINT `workshop_blocked_dates_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workshop_blocked_dates_ibfk_2` FOREIGN KEY (`blocked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.workshop_blocked_dates: ~0 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.workshop_blocked_slots
CREATE TABLE IF NOT EXISTS `workshop_blocked_slots` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `workshop_id` int unsigned NOT NULL,
  `slot_date` date NOT NULL,
  `slot_time` time NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `blocked_by` int unsigned DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `blocked_by` (`blocked_by`),
  KEY `idx_workshop_date` (`workshop_id`,`slot_date`),
  KEY `idx_slot_date` (`slot_date`),
  KEY `idx_is_deleted` (`is_deleted`),
  CONSTRAINT `workshop_blocked_slots_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workshop_blocked_slots_ibfk_2` FOREIGN KEY (`blocked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.workshop_blocked_slots: ~0 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.workshop_schedules
CREATE TABLE IF NOT EXISTS `workshop_schedules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `workshop_id` int unsigned NOT NULL,
  `day_of_week` tinyint NOT NULL COMMENT '0=Sunday, 1=Monday, ..., 6=Saturday',
  `open_time` time NOT NULL,
  `close_time` time NOT NULL,
  `is_open` tinyint(1) DEFAULT '1',
  `slot_interval` int DEFAULT '60' COMMENT 'Interval in minutes',
  `capacity_per_slot` int DEFAULT '1' COMMENT 'Capacity per slot',
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_workshop_id` (`workshop_id`),
  KEY `idx_day` (`day_of_week`),
  KEY `idx_is_deleted` (`is_deleted`),
  CONSTRAINT `workshop_schedules_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.workshop_schedules: ~12 rows (approximately)

-- Dumping structure for table dijkstrabengkelbaru.workshop_services
CREATE TABLE IF NOT EXISTS `workshop_services` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `workshop_id` int unsigned NOT NULL,
  `service_name` varchar(150) NOT NULL,
  `service_category` enum('sparepart','servis','cat','ban','aki','tuning','lainnya') DEFAULT 'servis',
  `description` text,
  `price_min` decimal(12,2) DEFAULT NULL,
  `price_max` decimal(12,2) DEFAULT NULL,
  `unit` enum('fixed','range','per_hour') DEFAULT 'fixed',
  `duration_minutes` int DEFAULT '60',
  `is_available` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_workshop_id` (`workshop_id`),
  KEY `idx_category` (`service_category`),
  KEY `idx_is_available` (`is_available`),
  KEY `idx_is_deleted` (`is_deleted`),
  CONSTRAINT `workshop_services_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Dumping data for table dijkstrabengkelbaru.workshop_services: ~9 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
