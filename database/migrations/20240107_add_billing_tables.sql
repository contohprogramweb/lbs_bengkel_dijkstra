-- ============================================
-- Migration: Add Billing and Reporting Tables
-- Version: 4.1
-- Description: Tables for invoicing, billing, and reporting functionality
-- Scope: Modul Penagihan & Laporan
-- ============================================

-- Add billing-related columns to bookings table
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS service_cost DECIMAL(12,2) DEFAULT 0 COMMENT 'Biaya layanan' AFTER estimated_price,
ADD COLUMN IF NOT EXISTS sparepart_cost DECIMAL(12,2) DEFAULT 0 COMMENT 'Biaya sparepart' AFTER service_cost,
ADD COLUMN IF NOT EXISTS additional_cost DECIMAL(12,2) DEFAULT 0 COMMENT 'Biaya tambahan (jika approval disetujui)' AFTER sparepart_cost,
ADD COLUMN IF NOT EXISTS final_total DECIMAL(12,2) DEFAULT 0 COMMENT 'Total tagihan akhir' AFTER additional_cost,
ADD COLUMN IF NOT EXISTS payment_status ENUM('unpaid', 'paid', 'partial') DEFAULT 'unpaid' AFTER final_total,
ADD COLUMN IF NOT EXISTS invoice_number VARCHAR(50) NULL UNIQUE COMMENT 'Nomor invoice' AFTER payment_status,
ADD COLUMN IF NOT EXISTS invoiced_at DATETIME NULL COMMENT 'Tanggal invoice dibuat' AFTER invoice_number,
ADD COLUMN IF NOT EXISTS paid_at DATETIME NULL COMMENT 'Tanggal pembayaran' AFTER invoiced_at;

-- Create index for billing queries
CREATE INDEX IF NOT EXISTS idx_bookings_payment_status ON bookings(payment_status);
CREATE INDEX IF NOT EXISTS idx_bookings_invoiced_at ON bookings(invoiced_at);

-- Table for booking service items (detail layanan)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Detail layanan per booking';

-- Table for booking sparepart items (detail sparepart)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Detail sparepart per booking';

-- Table for booking additional charges (biaya tambahan)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Biaya tambahan per booking';

-- Table for invoices (tagihan formal)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Invoice formal untuk tagihan';

-- Table for invoice payments (pembayaran invoice - bisa multiple payments)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Pembayaran invoice (bisa multiple payments)';

-- Add report_settings table for customizable report parameters
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Pengaturan laporan per bengkel';

-- Insert default report settings
INSERT INTO report_settings (setting_key, setting_value, setting_type, description) VALUES
('default_tax_rate', '0', 'decimal', 'Default tax rate percentage'),
('invoice_prefix', 'INV', 'string', 'Prefix untuk nomor invoice'),
('report_currency', 'IDR', 'string', 'Mata uang untuk laporan'),
('invoice_due_days', '7', 'integer', 'Jatuh tempo invoice dalam hari')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ============================================
-- END OF MIGRATION
-- ============================================
