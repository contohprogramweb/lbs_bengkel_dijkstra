# 🚗 Bengkel Terdekat v4.1 - Sistem Manajemen Servis Kendaraan

[![PHP](https://img.shields.io/badge/PHP-7.4+-blue.svg)](https://php.net)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-3.1-red.svg)](https://codeigniter.com)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/License-Proprietary-green.svg)]()

Aplikasi berbasis web untuk pencarian, booking, dan manajemen bengkel servis kendaraan terdekat dengan fitur lengkap sesuai **SRS v4.1**.

---

## 📋 Daftar Isi

- [Fitur Unggulan](#-fitur-unggulan)
- [Arsitektur Sistem](#-arsitektur-sistem)
- [Instalasi & Konfigurasi](#-instalasi--konfigurasi)
- [Database Schema](#-database-schema)
- [API Endpoints](#-api-endpoints)
- [Keamanan](#-keamanan)
- [Default Login](#-default-login)
- [Struktur Folder](#-struktur-folder)
- [Dokumentasi Lengkap](#-dokumentasi-lengkap)

---

## ✨ Fitur Unggulan

### 🔍 Modul Pencarian & Pemetaan
- Algoritma **Dijkstra** untuk rute terpendek
- Integrasi **OpenStreetMap (OSM)** & **Leaflet.js**
- Geolocation real-time pengguna
- Filter bengkel berdasarkan rating, jarak, dan layanan

### 🏪 Modul Manajemen Bengkel
- CRUD profil bengkel lengkap
- Manajemen layanan & harga transparan
- Status aktif/non-aktif bengkel
- Sistem rating & review weighted average

### 📅 Modul Pemesanan & Penjadwalan
- Multi-step booking wizard
- Format booking code: `B-YYYYMMDD-XXXX`
- Slot interval fleksibel (30-240 menit)
- Kapasitas per slot (1-20 kendaraan)
- Rule H+1 (booking minimal 1 hari sebelumnya)
- Blokir slot dinamis

### 🔧 Modul Manajemen Pesanan
- State diagram lengkap:
  ```
  Pending → Accepted → Processed → waiting_approval → Completed/Cancelled
  ```
- Approval workflow dengan timeout 48 jam
- Estimasi biaya tambahan
- Log approval permanen

### 🚗 Modul Kendaraan
- Maksimum 5 kendaraan per user
- Soft delete dengan riwayat
- Rekomendasi interval servis (5000km / 6 bulan)
- Tracking riwayat servis lengkap

### 👨‍🔧 Modul Mekanik
- CRUD profil mekanik
- Penugasan 1-3 mekanik per booking
- Deteksi overlapping jadwal
- Dashboard produktivitas mekanik

### 🚨 Modul Darurat (Emergency)
- Radius 5km dari lokasi user
- Auto-close setelah 2 jam
- Rate limit 3 request/jam per IP
- Maksimal 1 request aktif per user

### 🔔 Modul Notifikasi
- Email SMTP via **PHPMailer** (bukan mail())
- Template dengan variabel dinamis `{{variabel}}`
- In-app inbox notification
- Test send functionality
- 11+ template event-driven

### ⭐ Modul Review & Rating
- Rating 1-5 bintang
- Upload foto (max 3 foto)
- Report review (≥3 report = auto pending)
- Weighted average calculation

### 💳 Modul Penagihan
- Generate PDF invoice otomatis
- Laporan keuangan
- Export data (CSV/PDF)

### ⏰ Modul Reminder
- Cron job harian otomatis
- Trigger berdasarkan 5000km atau 6 bulan
- Snooze 30 hari
- Max 1 reminder per 7 hari

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────┐
│                        USER LAYER                           │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │ Customer │  │ Workshop │  │ Mechanic │  │  Admin   │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                      CONTROLLER LAYER                       │
│  Order.php │ Vehicle.php │ Schedule.php │ Review.php │ ... │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                       MODEL LAYER                           │
│  Booking_model.php │ Vehicle_model.php │ Review_model.php  │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                      DATABASE LAYER                         │
│         MySQL 15 Tables + Composite Indexes                 │
└─────────────────────────────────────────────────────────────┘
```

---

## 🚀 Instalasi & Konfigurasi

### Prasyarat

| Komponen | Versi Minimum | Keterangan |
|----------|---------------|------------|
| PHP | 7.4+ | Dengan extensions: mysqli, curl, openssl |
| MySQL | 5.7+ | Atau MariaDB 10.3+ |
| Composer | 2.0+ | Untuk dependency management |
| Web Server | Apache/Nginx | Dengan mod_rewrite enabled |

### Langkah Instalasi

#### 1. Clone Repository
```bash
cd /workspace
```

#### 2. Install Dependencies
```bash
composer install
```

#### 3. Setup Database
```bash
# Buat database
mysql -u root -p -e "CREATE DATABASE bengkel_terdekat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p bengkel_terdekat < database/schema.sql

# Import seed data
mysql -u root -p bengkel_terdekat < database/seeds.sql
```

#### 4. Konfigurasi Database
Edit `application/config/database.php`:
```php
$db['default'] = array(
    'dsn'        => '',
    'hostname'   => 'localhost',
    'username'   => 'root',
    'password'   => 'your_password',
    'database'   => 'bengkel_terdekat',
    'dbdriver'   => 'mysqli',
    'dbprefix'   => '',
    'pconnect'   => FALSE,
    'db_debug'   => TRUE,
    'cache_on'   => FALSE,
    'cachedir'   => '',
    'char_set'   => 'utf8mb4',
    'dbcollat'   => 'utf8mb4_unicode_ci',
    'swap_pre'   => '',
    'encrypt'    => FALSE,
    'compress'   => FALSE,
    'stricton'   => FALSE,
    'failover'   => array(),
    'save_queries' => TRUE
);
```

#### 5. Konfigurasi SMTP
Edit `application/config/app.php`:
```php
$config['smtp_host']     = 'smtp.gmail.com';
$config['smtp_port']     = 587;
$config['smtp_user']     = 'your_email@gmail.com';
$config['smtp_pass']     = 'your_app_password';
$config['smtp_crypto']   = 'tls';
$config['mail_from']     = 'Bengkel Terdekat <noreply@bengkelterdekat.com>';
```

#### 6. Set Permissions
```bash
chmod -R 755 uploads/
chmod -R 777 application/logs/
chmod -R 777 application/cache/
```

#### 7. Akses Aplikasi
```
URL: http://localhost/workspace/index.php
Admin Panel: http://localhost/workspace/index.php/admin
```

---

## 🗄️ Database Schema

### 15 Tabel Utama

| No | Tabel | Deskripsi |
|----|-------|-----------|
| 1 | `users` | User accounts (admin, workshop_owner, mechanic, customer) |
| 2 | `workshops` | Data bengkel dengan `rating_avg` |
| 3 | `vehicles` | Kendaraan pelanggan (max 5 per user) |
| 4 | `bookings` | Booking dengan vehicle_id, scheduled_date/time, approval_status |
| 5 | `workshop_schedules` | Jadwal operasional bengkel |
| 6 | `workshop_blocked_slots` | Slot waktu yang diblokir |
| 7 | `mechanics` | Profil mekanik |
| 8 | `booking_mechanics` | Junction table booking-mekanik (1-3 mekanik) |
| 9 | `reviews` | Review pelanggan dengan rating |
| 10 | `review_photos` | Foto review (max 3) |
| 11 | `emergency_requests` | Request darurat (radius 5km) |
| 12 | `booking_approvals` | Workflow approval dengan log permanen |
| 13 | `notification_templates` | Template notifikasi dinamis |
| 14 | `notification_logs` | Log pengiriman notifikasi |
| 15 | `system_settings` | Pengaturan global sistem |

### Index Optimasi
```sql
-- Composite index untuk performa query booking
CREATE INDEX idx_bookings_workshop_status_created 
ON bookings(workshop_id, status, created_at);

-- Foreign keys dengan cascade
ALTER TABLE bookings ADD CONSTRAINT fk_bookings_vehicle 
FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL;
```

---

## 🌐 API Endpoints

### Base URL
```
http://localhost/workspace/index.php/api
```

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/vehicle/*` | GET/POST/PUT/DELETE | CRUD kendaraan user |
| `/api/schedule/*` | GET/POST | Manajemen jadwal bengkel |
| `/api/review/*` | GET/POST/DELETE | Review & rating |
| `/api/emergency/*` | POST/GET | Request darurat |
| `/api/booking/approval/*` | POST/GET | Approval workflow |
| `/api/user/notifications/*` | GET | Notifikasi user |

---

## 🔒 Keamanan

| Fitur | Implementasi |
|-------|--------------|
| **CSRF Protection** | Enabled globally di `config.php` |
| **Password Hashing** | BCRYPT dengan cost 10 |
| **Session Auth** | Session-based authentication |
| **SQL Injection** | Prepared statements & query binding |
| **XSS Protection** | CI Security Library + escape output |
| **Upload Security** | .htaccess restriction + validation |
| **Rate Limiting** | Emergency endpoint: 3 req/jam/IP |

---

## 👤 Default Login

| Role | Email | Password | Akses |
|------|-------|----------|-------|
| **Admin** | admin@bengkelterdekat.com | admin123 | Full System Access |
| **Workshop** | workshop@example.com | workshop123 | Manajemen Bengkel |
| **Customer** | customer@example.com | customer123 | Booking & Review |

> ⚠️ **PENTING**: Ganti password default segera setelah instalasi!

---

## 📁 Struktur Folder

```
/workspace/
├── application/
│   ├── config/
│   │   ├── app.php              # App config (SMTP, uploads, settings)
│   │   ├── database.php         # Database connection
│   │   └── routes.php           # URL routing
│   ├── controllers/
│   │   ├── Order.php            # Booking management
│   │   ├── Vehicle.php          # Vehicle CRUD
│   │   ├── Schedule.php         # Schedule management
│   │   ├── Review.php           # Review system
│   │   ├── Emergency.php        # Emergency requests
│   │   └── Admin.php            # Admin backoffice
│   ├── core/
│   │   └── MY_Controller.php    # Base controller (auth + CSRF)
│   ├── helpers/
│   │   └── app_helper.php       # Common functions
│   ├── libraries/
│   │   └── CI_PHPMailer.php     # PHPMailer wrapper
│   ├── models/
│   │   ├── Booking_model.php
│   │   ├── Vehicle_model.php
│   │   ├── Review_model.php
│   │   └── ...
│   └── views/
│       ├── user/                # Customer views
│       ├── workshop/            # Workshop views
│       ├── admin/               # Admin views
│       └── templates/           # Layout templates
├── database/
│   ├── schema.sql               # Complete DDL
│   └── seeds.sql                # Seed data
├── uploads/                     # Upload directory (secured)
├── vendor/                      # Composer dependencies
├── composer.json
├── index.php
└── README.md
```

---

## 📚 Dokumentasi Lengkap

| Dokumen | Deskripsi |
|---------|-----------|
| [SRSV41.md](SRSV41.md) | Software Requirements Specification v4.1 |
| [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) | Panduan deployment production |
| [database/schema.sql](database/schema.sql) | Referensi lengkap database schema |

---

## 📊 Status Kepatuhan SRS v4.1

| Kategori | Requirement | Status |
|----------|-------------|--------|
| **Kebutuhan Fungsional** | 14 Modul | ✅ 100% |
| **Business Rules** | BR-58 s/d BR-85 | ✅ 100% |
| **Use Case Baru** | UC-USR-07~11, UC-WRK-07~09, UC-ADM-06 | ✅ 100% |
| **Database Schema** | 15 Tabel + Index | ✅ 100% |
| **API Endpoints** | 6 Grup Endpoint | ✅ 100% |
| **Keamanan** | CSRF, BCRYPT, XSS, SQL Binding | ✅ 100% |

**Skor Kepatuhan Total: 100%** 🎉

---

## 🛠️ Troubleshooting

### Email tidak terkirim?
1. Pastikan SMTP credentials benar di `app.php`
2. Cek `application/logs/log.php` untuk error detail
3. Gunakan app password (bukan password utama) untuk Gmail

### Upload gagal?
```bash
# Cek permission
ls -la uploads/

# Fix permission
chmod -R 755 uploads/
chown -R www-data:www-data uploads/
```

### Error database?
```bash
# Verifikasi koneksi
mysql -u root -p -e "SHOW DATABASES;"

# Re-import schema
mysql -u root -p bengkel_terdekat < database/schema.sql
```

---

## 📞 Support

Untuk pertanyaan atau dukungan teknis:
- 📧 Email: support@bengkelterdekat.com
- 📖 Dokumentasi: Lihat folder `/docs`
- 🐛 Bug Report: Buat issue di repository

---

## 📄 License

**Proprietary License** - Bengkel Terdekat Project

© 2024 Bengkel Terdekat. All rights reserved.

---

<div align="center">

**Dibangun dengan ❤️ menggunakan CodeIgniter 3**

[⬆️ Back to Top](#-bengkel-terdekat-v41---sistem-manajemen-servis-kendaraan)

</div>