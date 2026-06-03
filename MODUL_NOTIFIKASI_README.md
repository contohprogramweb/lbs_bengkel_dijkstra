# Modul Notifikasi, Template, dan Reminder Servis Berkala

## Ringkasan Implementasi

Modul ini mengimplementasikan sistem notifikasi multi-channel dan reminder otomatis sesuai dengan SRS v4.1:
- **SRS Modul 10** (FR-NOT-02~04)
- **UC-USR-11**: Reminder Servis Berkala
- **UC-ADM-06**: Kelola Notifikasi & Template
- **Business Rules**: BR-73~75, BR-84~85

---

## File yang Dibuat/Dimodifikasi

### 1. Model
- `application/models/Notification_model.php` - Model utama untuk semua operasi notifikasi

### 2. Controllers
- `application/controllers/Notification.php` - Admin controller untuk template management
- `application/controllers/Notifications.php` - User controller untuk inbox dan reminder settings

### 3. Views
- `application/views/admin/notification/templates.php` - Daftar template (Admin)
- `application/views/admin/notification/template_form.php` - Form CRUD template (Admin)
- `application/views/user/notifications/inbox.php` - Inbox notifikasi user
- `application/views/user/notifications/reminder_settings.php` - Pengaturan reminder per kendaraan

### 4. Database Migration
- `database/migrations/20240106_add_notification_tables.sql` - Migration untuk kolom baru dan default templates

---

## Fitur Utama

### 1. Template Management (UC-ADM-06, BR-84, BR-85)

**CRUD Template:**
- Admin dapat membuat, mengedit template notifikasi
- Rich text editor support (textarea HTML)
- Variabel dinamis format `{{nama_variabel}}` (BR-84)
- Template tidak bisa dihapus, hanya dinonaktifkan (BR-85)
- Fallback ke default hardcoded jika nonaktif

**Variabel yang Tersedia:**
```
{{nama_pengguna}}     - Nama lengkap pengguna
{{kode_booking}}      - Kode unik booking
{{nama_bengkel}}      - Nama bengkel
{{tanggal_booking}}   - Tanggal booking
{{waktu_booking}}     - Slot waktu booking
{{kendaraan}}         - Informasi kendaraan
{{km_terakhir}}       - Kilometer terakhir servis
{{km_estimasi}}       - Estimasi kilometer saat ini
{{tanggal_servis}}    - Tanggal servis terakhir
{{rekomendasi_bengkel}} - Daftar rekomendasi bengkel
```

**Test Notifikasi:**
- Admin dapat mengirim test notifikasi ke email sendiri

---

### 2. Notifikasi Email Status Pesanan (FR-NOT-02)

**Trigger Otomatis:**
Email dikirim saat booking berubah status:
- `accepted` → Booking Diterima
- `in_progress` → Booking Sedang Dikerjakan
- `completed` → Booking Selesai
- `cancelled` → Booking Dibatalkan
- `rejected` → Booking Ditolak

**Implementasi:**
```php
// Di Booking_model atau controller yang mengubah status
$this->load->model('Notification_model');
$this->notification_model->send_booking_status_notification($booking_id, $new_status);
```

---

### 3. Inbox Notifikasi User

**Tabel `notification_logs`:**
- Menyimpan semua notifikasi yang dikirim
- Field: `recipient_email`, `subject`, `body`, `status`, `opened_at`, `metadata`

**Fitur Inbox:**
- Tampilan daftar notifikasi dengan badge unread
- Filter: Semua / Belum Dibaca
- Tandaidibaca (read_at/opened_at)
- Mark all as read
- Pagination

**API Endpoint:**
- `GET /notifications/unread_count` - Untuk badge display
- `GET /notifications/recent` - Recent notifications dropdown

---

### 4. Reminder Servis Berkala (UC-USR-11, FR-NOT-03)

**Cron Job Logic (Evaluasi Harian 00:00):**

```php
/**
 * PSEUDOCODE CRON JOB - reminder_cron.php
 * Jalankan via: php cli/reminder_cron.php
 * Atau setup crontab: 0 0 * * * cd /path/to/app && php cli/reminder_cron.php
 */

// 1. Load vehicles needing reminder
$vehicles = $this->notification_model->get_vehicles_needing_reminder();

// 2. For each vehicle
foreach ($vehicles as $vehicle) {
    // a. Get user data
    $user = get_user_by_id($vehicle['user_id']);
    
    // b. Check snooze status
    if (!empty($vehicle['reminder_snoozed_until']) && 
        strtotime($vehicle['reminder_snoozed_until']) > time()) {
        continue; // Skip snoozed vehicles
    }
    
    // c. Find nearest workshops
    $workshops = $this->notification_model->find_nearest_workshops(
        $user['default_city'], 
        3
    );
    
    // d. Send email reminder
    $this->notification_model->send_service_reminder(
        $vehicle, 
        $user, 
        $workshops
    );
    
    // e. Log notification
    log_notification('service_reminder', $vehicle['id']);
}
```

**Kondisi Trigger Reminder:**
1. **KM Threshold (BR-75):** `(current_km - last_service_km) >= 5000 km`
2. **Time Threshold (BR-75):** `last_service_date <= 6 bulan yang lalu`

**Business Rules Implementation:**
- **BR-73:** Maksimal 1 reminder per 7 hari per kendaraan
  ```php
  private function has_recent_reminder($vehicle_id, $days = 7)
  ```
  
- **BR-74:** User dapat menonaktifkan reminder per kendaraan
  ```php
  public function set_reminder_enabled($vehicle_id, $enabled)
  ```
  
- **BR-75:** Default interval configurable via system_settings
  ```sql
  INSERT INTO system_settings VALUES 
    ('reminder_interval_km', '5000', 'integer'),
    ('reminder_interval_months', '6', 'integer')
  ```

**Snooze Feature (UC-USR-11 Alternative Flow A2):**
- User dapat menunda reminder 30 hari (configurable: 7, 14, 30, 60, 90 hari)
- Via tombol "Snooze" di halaman reminder settings

---

## Integrasi dengan Sistem Existing

### 1. Trigger Email saat Status Booking Berubah

Di controller yang mengubah status booking (misal: `Booking_management.php`):

```php
// Setelah update status berhasil
if ($this->booking_model->update_status($booking_id, $new_status)) {
    // Trigger notification
    $this->load->model('Notification_model');
    $this->notification_model->send_booking_status_notification(
        $booking_id, 
        $new_status
    );
}
```

### 2. Badge Unread Count di Header

Di layout header user (`layouts/user_header.php`):

```php
<!-- Notification Bell -->
<li class="nav-item dropdown">
    <a class="nav-link" href="<?= site_url('notifications/inbox') ?>">
        <i class="fas fa-bell"></i>
        <?php 
        $this->load->model('Notification_model');
        $unread = $this->Notification_model->count_unread_notifications($current_user->email);
        if ($unread > 0):
        ?>
            <span class="badge badge-danger notification-badge"><?= $unread ?></span>
        <?php endif; ?>
    </a>
</li>
```

### 3. Menu Link di Sidebar User

```php
<li class="nav-item">
    <a href="<?= site_url('notifications/inbox') ?>" class="nav-link">
        <i class="fas fa-envelope"></i>
        <span>Notifikasi</span>
        <?php if ($unread_count > 0): ?>
            <span class="badge badge-danger right"><?= $unread_count ?></span>
        <?php endif; ?>
    </a>
</li>
<li class="nav-item">
    <a href="<?= site_url('notifications/reminder_settings') ?>" class="nav-link">
        <i class="fas fa-car"></i>
        <span>Pengingat Servis</span>
    </a>
</li>
```

---

## Konfigurasi SMTP

File: `application/config/app.php`

```php
$config['smtp_host'] = 'smtp.gmail.com';
$config['smtp_port'] = 587;
$config['smtp_user'] = 'your_email@gmail.com';  // Set di environment
$config['smtp_pass'] = 'your_app_password';     // Set di environment
$config['smtp_crypto'] = 'tls';
$config['mail_from_email'] = 'noreply@bengkelterdekat.com';
$config['mail_from_name'] = 'Bengkel Terdekat';
```

---

## Setup Cron Job

### Option 1: Crontab (Linux)

```bash
# Edit crontab
crontab -e

# Add line for daily reminder at 00:00
0 0 * * * cd /workspace && php -f cli/reminder_cron.php >> /var/log/reminder_cron.log 2>&1
```

### Option 2: Manual CLI Script

Buat file `cli/reminder_cron.php`:

```php
#!/usr/bin/php
<?php
defined('STDIN') or die('CLI only');

// Bootstrap CodeIgniter
require_once '../index.php';

// Load models
$CI =& get_instance();
$CI->load->model('Notification_model');

echo "[" . date('Y-m-d H:i:s') . "] Starting reminder cron job...\n";

// Get vehicles needing reminder
$vehicles = $CI->Notification_model->get_vehicles_needing_reminder();

echo "Found " . count($vehicles) . " vehicles needing reminder.\n";

$sent = 0;
foreach ($vehicles as $vehicle) {
    // Get user
    $user = $CI->db->where('id', $vehicle['user_id'])->get('users')->row_array();
    
    if (!$user) continue;
    
    // Get recommended workshops
    $workshops = $CI->Notification_model->find_nearest_workshops(
        $user['default_city'] ?? '', 
        3
    );
    
    // Send reminder
    if ($CI->Notification_model->send_service_reminder($vehicle, $user, $workshops)) {
        echo "✓ Sent to {$user['email']} for vehicle {$vehicle['vehicle_number']}\n";
        $sent++;
    } else {
        echo "✗ Failed to send to {$user['email']}\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Completed. Sent: {$sent}/" . count($vehicles) . "\n";
```

---

## Testing

### 1. Test Template Management
1. Login sebagai admin
2. Akses menu Notifikasi → Template
3. Klik "Tambah Template"
4. Isi form dengan variabel dinamis
5. Simpan dan uji dengan "Test Notifikasi"

### 2. Test Booking Status Notification
1. Buat booking baru
2. Ubah status booking (accept, process, complete)
3. Cek email user dan tabel `notification_logs`

### 3. Test User Inbox
1. Login sebagai user
2. Akses menu Notifikasi → Inbox
3. Verifikasi notifikasi muncul
4. Klik notifikasi untuk mark as read
5. Verifikasi badge count berkurang

### 4. Test Reminder Settings
1. Login sebagai user dengan kendaraan
2. Akses menu Notifikasi → Pengingat Servis
3. Toggle reminder on/off
4. Test snooze feature

### 5. Test Cron Job (Manual)
```bash
cd /workspace
php cli/reminder_cron.php
```

---

## Business Rules Compliance

| BR ID | Deskripsi | Status | Implementasi |
|-------|-----------|--------|--------------|
| BR-73 | Reminder tidak dikirim lebih dari 1x dalam 7 hari | ✅ | `has_recent_reminder()` di model |
| BR-74 | User dapat menonaktifkan reminder per kendaraan | ✅ | Kolom `reminder_enabled`, toggle button |
| BR-75 | Default interval: 5000km atau 6 bulan | ✅ | `system_settings` table, configurable |
| BR-84 | Variabel template format {{nama_variabel}} | ✅ | `extract_variables()`, `replace_variables()` |
| BR-85 | Template tidak dapat dihapus, hanya dinonaktifkan | ✅ | `deactivate_template()`, fallback default |

---

## Catatan Penting

1. **Database Changes:** Jalankan migration sebelum menggunakan modul:
   ```sql
   source database/migrations/20240106_add_notification_tables.sql
   ```

2. **Kolom Baru di Tabel `vehicles`:**
   - `reminder_enabled` TINYINT(1) DEFAULT 1
   - `reminder_snoozed_until` DATE NULL

3. **Kolom Baru di Tabel `users`:**
   - `default_city` VARCHAR(100) NULL

4. **Dependencies:**
   - PHPMailer (sudah ada di `vendor/`)
   - jQuery (untuk AJAX calls)
   - DataTables (untuk admin template list)

5. **Security:**
   - CSRF protection enabled pada semua form
   - Ownership verification pada user actions
   - Input sanitization pada template variables

---

## Deliverable Checklist

- [x] Controller Notification (admin) - `Notification.php`
- [x] Controller Notifications (user) - `Notifications.php`
- [x] Model reminder logic - `Notification_model.php`
- [x] View inbox user - `inbox.php`
- [x] View template management admin - `templates.php`, `template_form.php`
- [x] View reminder settings - `reminder_settings.php`
- [x] Database migration - `20240106_add_notification_tables.sql`
- [x] Pseudocode cron job - (dalam dokumentasi ini)
- [x] Test notification feature - Implemented in admin controller

---

**Dibuat oleh:** AI Assistant  
**Tanggal:** 2026-06-03  
**Versi Modul:** 4.0
