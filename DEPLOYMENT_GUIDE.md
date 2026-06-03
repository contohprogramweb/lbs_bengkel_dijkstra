# DEPLOYMENT GUIDE & CRON JOB SETUP

## Aplikasi Bengkel Terdekat v4.1

### 1. INSTRUKSI INSTALASI

#### Prasyarat
- PHP 7.4 atau lebih tinggi
- MySQL 5.7+ atau MariaDB 10.3+
- Composer
- Web server (Apache/Nginx)
- Akses CLI untuk cron job

#### Langkah Instalasi

1. **Clone repository**
```bash
cd /var/www/html
git clone <repository_url> bengkel_terdekat
cd bengkel_terdekat
```

2. **Install dependencies**
```bash
composer install --no-dev --optimize-autoloader
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
    'username' => 'bengkel_user',
    'password' => 'your_secure_password',
    'database' => 'bengkel_terdekat',
    'dbdriver' => 'mysqli',
    // ... konfigurasi lainnya
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
$config['mail_from'] = 'noreply@bengkelterdekat.com';
$config['mail_from_name'] = 'Bengkel Terdekat';
```

6. **Set permissions**
```bash
chmod -R 755 uploads/
chmod -R 777 application/logs/
chmod -R 777 application/cache/
chown -R www-data:www-data uploads/ application/logs/ application/cache/
```

7. **Konfigurasi base URL**
Edit `application/config/config.php`:
```php
$config['base_url'] = 'https://bengkel.example.com/';
```

8. **Test email SMTP dari Admin Panel**
- Login sebagai admin: admin@bengkelterdekat.com / admin123
- Buka menu Admin > Notifikasi > Kirim Test Email
- Masukkan email tujuan dan kirim

9. **Akses aplikasi**
```
https://bengkel.example.com/index.php
```

---

### 2. CRON JOB SETUP

Cron job digunakan untuk otomatisasi tugas-tugas berikut:
- Reminder servis berkala harian
- Timeout emergency request
- Cleanup booking pending

#### A. Tambahkan Cron Job ke Crontab

1. **Buka crontab editor**
```bash
crontab -e
```

2. **Tambahkan baris berikut** (sesuaikan path dengan lokasi instalasi Anda):

```bash
# Reminder servis berkala - Setiap hari jam 6 pagi
0 6 * * * php /var/www/html/bengkel_terdekat/index.php cli reminder daily >> /var/log/bengkel_reminder.log 2>&1

# Emergency timeout check - Setiap 30 menit
*/30 * * * * php /var/www/html/bengkel_terdekat/index.php cli emergency close_timeout >> /var/log/bengkel_emergency.log 2>&1

# Booking cleanup - Setiap hari jam 2 pagi
0 2 * * * php /var/www/html/bengkel_terdekat/index.php cli booking cleanup >> /var/log/bengkel_cleanup.log 2>&1
```

#### B. Verifikasi Cron Job

1. **Lihat daftar cron job yang terpasang**
```bash
crontab -l
```

2. **Cek log cron**
```bash
tail -f /var/log/syslog | grep CRON
tail -f /var/log/bengkel_reminder.log
tail -f /var/log/bengkel_emergency.log
tail -f /var/log/bengkel_cleanup.log
```

3. **Test manual script CLI**
```bash
# Test reminder
php /var/www/html/bengkel_terdekat/index.php cli reminder daily

# Test emergency timeout
php /var/www/html/bengkel_terdekat/index.php cli emergency close_timeout

# Test booking cleanup
php /var/www/html/bengkel_terdekat/index.php cli booking cleanup

# Test email
php /var/www/html/bengkel_terdekat/index.php cli test_email admin@example.com
```

#### C. Troubleshooting Cron

**Jika cron tidak berjalan:**

1. Pastikan service cron aktif:
```bash
sudo systemctl status cron
sudo systemctl enable cron
```

2. Cek permission file:
```bash
ls -la /var/www/html/bengkel_terdekat/index.php
chmod +x /var/www/html/bengkel_terdekat/index.php
```

3. Cek PHP CLI path:
```bash
which php
# Gunakan path lengkap di crontab, misal: /usr/bin/php
```

4. Test dengan user yang benar:
```bash
# Jalankan sebagai user www-data
sudo -u www-data php /var/www/html/bengkel_terdekat/index.php cli reminder daily
```

---

### 3. INTEGRATION TESTING CHECKLIST

#### A. End-to-End Test Scenario

**Scenario 1: User Registration → Booking → Review**
- [ ] User register akun baru
- [ ] User login
- [ ] User tambah kendaraan (merk, model, tahun, plat nomor)
- [ ] User cari bengkel di peta
- [ ] User pilih bengkel dan buat booking dengan slot waktu
- [ ] Workshop terima booking
- [ ] Workshop assign mekanik
- [ ] Workshop tambah temuan inspeksi
- [ ] User approve temuan
- [ ] Workshop selesaikan pekerjaan
- [ ] User beri review dan rating

**Scenario 2: Emergency Flow**
- [ ] User klik tombol darurat
- [ ] User isi form emergency (lokasi, deskripsi masalah)
- [ ] Sistem notifikasi bengkel terdekat (radius 5km)
- [ ] Bengkel konfirmasi penerimaan
- [ ] User dapat info bengkel yang merespons

**Scenario 3: Cron Reminder**
- [ ] Setup data test: kendaraan dengan km mendekati threshold
- [ ] Jalankan script reminder manual: `php index.php cli reminder daily`
- [ ] Cek email log di tabel `notification_logs`
- [ ] Verifikasi email terkirim ke user

#### B. Race Condition Test

**Test Slot Booking Bersamaan:**
1. Buka 2 browser berbeda (atau incognito mode)
2. Login dengan 2 user berbeda
3. Pilih bengkel yang sama
4. Pilih tanggal dan slot waktu yang sama
5. Submit booking hampir bersamaan
6. **Expected Result**: Hanya 1 booking yang berhasil, user lain mendapat error "Slot sudah terisi"

#### C. PDF & Export Test

**Test Invoice PDF:**
- [ ] Selesaikan booking
- [ ] Generate invoice PDF dari panel workshop
- [ ] Download dan verifikasi isi PDF (data booking, item layanan, total)

**Test Export CSV:**
- [ ] Buka halaman laporan (Admin > Reports)
- [ ] Pilih periode tanggal
- [ ] Export data bookings ke CSV
- [ ] Buka file CSV dan verifikasi data

#### D. Security Test

**CSRF Protection:**
- [ ] Coba submit form tanpa CSRF token → harus ditolak
- [ ] Inspect element, hapus hidden input CSRF token → submit gagal

**SQL Injection:**
- [ ] Coba input `' OR '1'='1` di form login → harus gagal
- [ ] Coba input malicious SQL di search box → tidak error

**XSS Prevention:**
- [ ] Coba input `<script>alert('XSS')</script>` di form review → output diescape
- [ ] Verifikasi semua output menggunakan fungsi `e()` helper

**File Upload Security:**
- [ ] Coba upload file .php/.exe → ditolak
- [ ] Coba upload file gambar > 2MB → ditolak
- [ ] Upload file JPG/PNG valid → berhasil

---

### 4. DEFAULT LOGIN CREDENTIALS

| Role  | Email                     | Password   |
|-------|---------------------------|------------|
| Admin | admin@bengkelterdekat.com | admin123   |

**PENTING**: Segera ubah password default setelah instalasi!

---

### 5. MONITORING & MAINTENANCE

#### Log Files untuk Monitoring
```bash
# Application logs
tail -f /var/www/html/bengkel_terdekat/application/logs/log-$(date +%Y-%m-%d).php

# Cron job logs
tail -f /var/log/bengkel_reminder.log
tail -f /var/log/bengkel_emergency.log

# Web server logs
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log
```

#### Database Backup Harian
```bash
# Tambahkan ke crontab
0 3 * * * mysqldump -u root -pYOUR_PASSWORD bengkel_terdekat > /backups/bengkel_$(date +\%Y\%m\%d).sql
```

#### Health Check Script
```bash
#!/bin/bash
# health_check.sh

echo "=== Health Check ==="
echo "Date: $(date)"

# Check PHP
php -v | head -1

# Check database connection
mysql -u bengkel_user -pYOUR_PASSWORD -e "SELECT 'Database OK' as status;" bengkel_terdekat

# Check disk space
df -h /var/www

# Check recent errors
echo "Recent Errors:"
tail -5 /var/www/html/bengkel_terdekat/application/logs/log-$(date +%Y-%m-%d).php
```

---

### 6. TROUBLESHOOTING COMMON ISSUES

**Issue: Email tidak terkirim**
- Cek konfigurasi SMTP di `app.php`
- Untuk Gmail, gunakan App Password, bukan password biasa
- Cek firewall/port 587 (TLS) atau 465 (SSL)
- Test manual: `php index.php cli test_email your@email.com`

**Issue: Cron tidak jalan**
- Pastikan cron service running: `systemctl status cron`
- Cek log cron: `/var/log/syslog | grep CRON`
- Test manual script CLI dulu

**Issue: Upload file gagal**
- Cek permission folder `uploads/`: harus 755 atau 777
- Cek `upload_max_filesize` dan `post_max_size` di php.ini
- Verifikasi tipe file yang diizinkan di `security_helper.php`

**Issue: Session timeout terlalu cepat**
- Edit `application/config/config.php`:
```php
$config['sess_expiration'] = 7200; // 2 hours
```

---

### 7. SECURITY HARDENING CHECKLIST

- [ ] CSRF Protection enabled (global)
- [ ] Password hashing BCRYPT
- [ ] File upload validation (MIME type, size, extension)
- [ ] SQL injection prevention (query binding)
- [ ] XSS protection (htmlspecialchars via helper `e()`)
- [ ] Custom error pages 404/403
- [ ] Session security settings
- [ ] HTTPS recommended untuk production
- [ ] Disable error display in production
- [ ] Regular backup database

---

### 8. CONTACT & SUPPORT

Untuk pertanyaan atau dukungan teknis, hubungi tim development.

**Version**: 4.1  
**Last Updated**: June 2026
