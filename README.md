# Aplikasi Bengkel Terdekat

Project CodeIgniter 3 untuk aplikasi pencarian dan booking bengkel terdekat berdasarkan SRS v4.0.

## Struktur Folder

| Directory     | Description                                                         |
| ------------- | ------------------------------------------------------------------- |
| `application/`| CodeIgniter application directory                                   |
| `application/config/app.php` | Application configuration (SMTP, uploads, settings)   |
| `application/core/MY_Controller.php` | Base controller with auth & CSRF protection  |
| `application/helpers/app_helper.php` | Common helper functions                       |
| `application/libraries/CI_PHPMailer.php` | PHPMailer integration library            |
| `database/`   | Database schema and seeds                                           |
| `database/schema.sql` | Complete DDL for all tables (SRS v4.0)                      |
| `database/seeds.sql` | Seed data (admin, notification templates, system settings)   |
| `uploads/`    | Upload directory with security .htaccess                            |
| `vendor/`     | Composer dependencies (CodeIgniter 3, PHPMailer)                    |

## Fitur Utama

- ✅ CodeIgniter 3 dengan Composer integration
- ✅ PHPMailer untuk pengiriman email SMTP
- ✅ Konfigurasi lengkap di `application/config/app.php`
- ✅ Base controller dengan autentikasi session & CSRF protection
- ✅ Password hashing menggunakan BCRYPT
- ✅ Upload folder dengan permission logic (.htaccess)

## Database Schema

### Tabel yang dibuat:
1. `users` - User accounts (admin, workshop_owner, mechanic, customer)
2. `workshops` - Workshop/bengkel informasi (dengan `rating_avg`)
3. `vehicles` - Data kendaraan pelanggan
4. `bookings` - Booking servis (dengan `vehicle_id`, `scheduled_date`, `scheduled_time`, `approval_status`)
5. `workshop_schedules` - Jadwal operasional workshop
6. `workshop_blocked_slots` - Slot waktu yang diblokir
7. `mechanics` - Profil mekanik
8. `booking_mechanics` - Junction table booking-mekanik
9. `reviews` - Review pelanggan
10. `review_photos` - Foto review
11. `emergency_requests` - Request darurat
12. `booking_approvals` - Approval workflow
13. `notification_templates` - Template notifikasi
14. `notification_logs` - Log pengiriman notifikasi
15. `system_settings` - Pengaturan global sistem

### Index Khusus:
- Composite index pada `bookings(workshop_id, status, created_at)` (Reviewer #4)
- Foreign keys dengan `ON DELETE CASCADE/SET NULL`
- Charset `utf8mb4_unicode_ci`

## Seed Data

File `database/seeds.sql` berisi:
- Admin default (email: admin@bengkelterdekat.com, password: admin123)
- Template notifikasi untuk semua event_key:
  - booking_accepted
  - booking_completed
  - reminder_service
  - emergency_alert
  - booking_rejected
  - booking_cancelled
  - workshop_approved
  - workshop_rejected
  - password_reset
  - welcome_user
  - review_submitted
- System settings default:
  - radius_darurat = 5 km
  - reminder_interval_km = 5000
  - reminder_interval_months = 6

## Instalasi

### Prasyarat
- PHP 7.4 atau lebih tinggi
- MySQL 5.7+ atau MariaDB
- Composer
- Web server (Apache/Nginx)

### Langkah Instalasi

1. **Clone repository**
```bash
cd /workspace
```

2. **Install dependencies (sudah terinstall)**
```bash
composer install
```

3. **Setup database**
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeds.sql
```

4. **Konfigurasi database**
Edit `application/config/database.php`:
```php
$db['default'] = array(
    'dsn'   => '',
    'hostname' => 'localhost',
    'username' => 'your_username',
    'password' => 'your_password',
    'database' => 'bengkel_terdekat',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => TRUE,
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);
```

5. **Konfigurasi SMTP**
Edit `application/config/app.php`:
```php
$config['smtp_host'] = 'smtp.gmail.com';
$config['smtp_port'] = 587;
$config['smtp_user'] = 'your_email@gmail.com';
$config['smtp_pass'] = 'your_app_password';
$config['smtp_crypto'] = 'tls';
```

6. **Set permissions**
```bash
chmod -R 755 uploads/
chmod -R 777 application/logs/
chmod -R 777 application/cache/
```

7. **Akses aplikasi**
```
http://localhost/workspace/index.php
```

## Default Login

| Role  | Email                     | Password   |
|-------|---------------------------|------------|
| Admin | admin@bengkelterdekat.com | admin123   |

## Security Features

- CSRF Protection enabled globally
- Password hashing dengan BCRYPT (cost 10)
- Session-based authentication
- Upload file validation dengan .htaccess security
- SQL injection prevention (prepared statements recommended)
- XSS protection via CI security library

## Reviewer Suggestions Implemented

| Reviewer | Suggestion | Status |
|----------|-----------|--------|
| #1 | Tambah `rating_avg` di tabel workshops | ✅ Done |
| #2 | Integrasi PHPMailer | ✅ Done |
| #3 | Tambah tabel `system_settings` | ✅ Done |
| #4 | Composite index pada bookings | ✅ Done |

## License

Proprietary - Bengkel Terdekat Project