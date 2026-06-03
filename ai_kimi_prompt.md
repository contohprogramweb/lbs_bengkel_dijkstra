Berikut adalah urutan prompt sistematis untuk AI Coder agar dapat mengimplementasikan SRS Bengkel Terdekat v4.0 secara modular dan terstruktur. Urutan ini didesain agar setiap prompt memiliki dependency yang jelas dan deliverable yang dapat diuji secara independen.
FASE 1: FOUNDATION & DATABASE
Prompt #1: Project Setup, Database Schema v4.0 Lengkap, dan Konfigurasi Dasar CodeIgniter

    Scope: SRS Bagian 6 (Skema DB), Deployment Checklist Database, dan Saran Reviewer #2, #3, #4

plain

Kamu adalah senior PHP developer. Buatkan project CodeIgniter 3 (atau CI4 jika lebih disukai, tetapi sesuaikan dengan struktur SRS yang menggunakan application/config/app.php) untuk "Aplikasi Bengkel Terdekat" berdasarkan SRS v4.0.

Tugas spesifik:
1. Inisialisasi struktur folder CI dengan Composer, integrasikan PHPMailer (saran reviewer #2), dan buat konfigurasi SMTP di application/config/app.php.
2. Buat DDL lengkap untuk SEMUA tabel SRS v4.0: users, workshops, bookings, vehicles, workshop_schedules, workshop_blocked_slots, mechanics, booking_mechanics, reviews, review_photos, emergency_requests, booking_approvals, notification_templates, notification_logs. JANGAN lupa ALTER TABLE bookings (tambah vehicle_id, scheduled_date, scheduled_time, approval_status).
3. Tambahkan kolom rating_avg DECIMAL(3,2) DEFAULT 0 di tabel workshops (saran reviewer #1) dan tabel system_settings (saran reviewer #3) untuk konfigurasi global.
4. Buat seed data: admin default, template notifikasi default untuk semua event_key (booking_accepted, booking_completed, reminder_service, emergency_alert), dan seed system_settings (radius_darurat=5km, reminder_interval_km=5000, reminder_interval_months=6).
5. Tambahkan composite index pada bookings(workshop_id, status, created_at) (saran reviewer #4).
6. Setup base controller dengan autentikasi session, CSRF protection, dan BCRYPT untuk password.
7. Buat konfigurasi upload folder: uploads/reviews/ dengan permission logic di .htaccess atau config.

Deliverable: Zip project dasar + file SQL lengkap. Pastikan foreign key dan charset utf8mb4_unicode_ci sesuai SRS.

Prompt #2: Autentikasi, RBAC, dan Middleware Akses

    Scope: SRS Bagian 1.3 (RBAC), UC dasar autentikasi

plain

Lanjutkan dari project Prompt #1. Implementasikan sistem autentikasi dan Role-Based Access Control (RBAC) dengan 3 role: admin, workshop_owner, user.

Tugas spesifik:
1. Register/Login untuk User dan Workshop Owner (terpisah form atau unified dengan role selection saat register workshop).
2. Admin account pre-seeded (tidak bisa register publik).
3. Middleware/filter di setiap controller: is_logged_in(), is_admin(), is_workshop_owner(), is_user().
4. Dashboard routing: /admin/dashboard, /workshop/dashboard, /user/dashboard.
5. Profil management: edit nama, telepon, alamat (alamat digunakan untuk rekomendasi bengkel di fitur reminder).
6. Soft delete mechanism untuk semua entity utama (flag is_deleted).

Deliverable: Controller Auth, User, Admin + View login/register/dashboard dasar. Pastikan session security dan CSRF token di setiap form.

FASE 2: MASTER DATA & MANAJEMEN ENTITAS
Prompt #3: Modul Manajemen Bengkel (Workshop Management)

    Scope: SRS Modul Manajemen Bengkel, FR dasar

plain

Lanjutkan project. Implementasikan Modul Manajemen Bengkel untuk role workshop_owner dan admin.

Tugas spesifik:
1. CRUD Profil Bengkel: nama, alamat lengkap (untuk geocoding lat/long), jam operasional dasar, deskripsi, layanan (sparepart, servis, cat, dll), harga estimasi per layanan.
2. Geocoding: saat simpan alamat bengkel, generate latitude dan longitude (gunakan API geocoding gratis seperti Nominatim OSM atau input manual jika API terbatas).
3. Manajemen Unit Layanan: CRUD layanan & harga per bengkel (tabel terpisah atau JSON, pilih yang optimal).
4. Status bengkel: aktif/non-aktif (hanya bengkel aktif yang muncul di pencarian).
5. Admin dapat melihat semua bengkel; workshop_owner hanya bengkel miliknya.
6. Tampilkan rating_avg di dashboard bengkel (kolom sudah dibuat di Prompt #1, isi default 0).

Deliverable: Controller Workshop + View CRUD lengkap. Validasi form server-side.

Prompt #4: Modul Manajemen Kendaraan Pengguna

    Scope: SRS Modul 6 (FR-VEH-01~03), UC-USR-07, BR-58~61

plain

Lanjutkan project. Implementasikan Modul Manajemen Kendaraan untuk role user.

Tugas spesifik:
1. Halaman "Kendaraan Saya": list kendaraan dengan kartu UI.
2. CRUD Kendaraan: merk (Select2 dari master merk), model, tahun (dropdown 1980-saat ini+1), nomor polisi (unik per user, case-insensitive, normalize spasi), VIN opsional (17 karakter), kilometer terakhir, jenis bahan bakar (enum: bensin, solar, listrik, hybrid).
3. Business Rules: Maksimal 5 kendaraan per user (BR-58). Validasi tahun (BR-59, BR-60). Kilometer hanya bisa diupdate ke nilai >= sebelumnya.
4. Soft delete kendaraan (BR-61) dengan cek: tidak boleh dihapus jika masih ada booking aktif (pending/accepted/processed).
5. Tab "Riwayat Servis" di detail kendaraan: tampilkan list booking yang terhubung ke kendaraan tersebut.
6. Rekomendasi servis berkala: tampilkan estimasi servis berikutnya berdasarkan last_odometer + 5000km (ambil dari system_settings).

Deliverable: Controller Vehicle + View lengkap. AJAX untuk validasi real-time jika memungkinkan.

Prompt #5: Modul Penjadwalan & Slot Waktu

    Scope: SRS Modul 7 (FR-SCH-01~04), UC-WRK-09, BR-62~64, BR-81~83

plain

Lanjutkan project. Implementasikan Modul Penjadwalan & Slot Waktu untuk workshop_owner.

Tugas spesifik:
1. Konfigurasi Jadwal Harian: Senin-Minggu. Setiap hari: jam buka, jam tutup, interval slot (30/60/120 menit), kapasitas per slot (1-20 kendaraan). Validasi min 30 menit, max 4 jam (BR-82, BR-83).
2. Auto-generate slot: berdasarkan konfigurasi di atas, sistem generate slot tersedia per tanggal (tabel workshop_slots atau logika perhitungan real-time, pilih yang efisien).
3. Blokir Slot/Hari Libur: kalender interaktif untuk blokir tanggal spesifik atau slot tertentu dengan alasan (tabel workshop_blocked_slots).
4. Kalender Manajemen Mekanik: tampilan kalender mingguan yang menunjukkan slot terisi dan nama pengguna yang booking (alternatif UC-WRK-09).
5. Business Rules: Perubahan konfigurasi tidak mempengaruhi booking yang sudah ada (BR-81). Slot di-reserve saat booking dibuat, dikembalikan jika dibatalkan (BR-64).

Deliverable: Controller Workshop_schedule + View kalender interaktif (bisa menggunakan FullCalendar.js atau kalender custom).

FASE 3: CORE FEATURES (PETA, BOOKING, MEKANIK)
Prompt #6: Modul Pencarian Bengkel, Dijkstra, dan OpenStreetMap/Leaflet.js

    Scope: SRS Modul Pencarian & Pemetaan, Algoritma Dijkstra & Graf Jalan

plain

Lanjutkan project. Implementasikan Modul Pencarian Bengkel Terdekat dengan Dijkstra dan visualisasi OSM/Leaflet.js v1.9+.

Tugas spesifik:
1. Halaman utama peta: tampilkan peta Leaflet.js dengan tile OpenStreetMap.
2. Deteksi lokasi pengguna: HTML5 Geolocation. Jika ditolak,允许 manual pin di peta.
3. Tampilkan marker bengkel aktif dalam radius tertentu (default 10km atau Euclidean jika graf jalan belum lengkap).
4. Algoritma Dijkstra: Admin dapat memelihara graf jalan (simpul=jalan/intersection, edge=jarak antar simpul). Implementasikan perhitungan rute terpendek dari lokasi user ke bengkel terpilih.
5. Visualisasi rute: gambar polyline rute terpendek di Leaflet dari lokasi user ke bengkel.
6. Panel sidebar hasil pencarian: urutkan bengkel berdasarkan jarak rute (bukan Euclidean), tampilkan estimasi waktu tempuh.
7. Admin panel untuk kelola graf jalan: CRUD node dan edge (sederhana, untuk MVP).

Deliverable: Controller Map, Dijkstra (library/helper), Admin/Graf + View peta lengkap. Pastikan Dijkstra menggunakan bobot non-negatif.

Prompt #7: Modul Pemesanan Layanan (Booking Flow)

    Scope: SRS Modul Pemesanan Layanan, UC-USR-08, UC dasar booking v3.0

plain

Lanjutkan project. Implementasikan flow Pemesanan Layanan lengkap dengan integrasi kendaraan dan slot waktu.

Tugas spesifik:
1. Multi-step booking form:
   Step 1: Pilih Kendaraan dari dropdown (data dari tabel vehicles milik user).
   Step 2: Pilih Jadwal & Slot Waktu (UC-USR-08): tampilkan kalender bulan berjalan. Tanggal tersedia= hijau, penuh=abu, libur=merah. Saat klik tanggal, tampilkan slot (08:00-09:00, dll) dengan sisa kapasitas. Validasi real-time via AJAX (slot masih tersedia?).
   Step 3: Pilih Layanan & Estimasi Biaya (dari master layanan bengkel).
   Step 4: Konfirmasi & Generate Booking Code (format: B-YYYYMMDD-XXXX).
2. Business Rules: Booking minimal H+1 kecuali same-day diizinkan admin (BR-62). User bisa ubah jadwal selama status Pending (BR-63).
3. Simpan booking dengan vehicle_id, scheduled_date, scheduled_time. Kurangi kapasitas slot.
4. Race condition handling: jika slot terambil saat submit, berikan error "Slot baru saja terisi" dan redirect ke pemilihan ulang.

Deliverable: Controller Booking (user side) + View multi-step. Integrasikan dengan modul kendaraan dan jadwal.

Prompt #8: Modul Manajemen Pesanan & Approval Estimasi

    Scope: SRS State Diagram Booking Lifecycle, UC-WRK-08, BR-78~80

plain

Lanjutkan project. Implementasikan Manajemen Pesanan untuk workshop_owner dan user, termasuk approval estimasi tambahan.

Tugas spesifik:
1. State Lifecycle Booking: Pending → Accepted → Processed → [waiting_approval] → Processed/Completed/Cancelled. Implementasikan semua transisi status sesuai SRS State Diagram v4.0.
2. Panel Workshop Owner:
   - Terima/Tolak pesanan (Pending→Accepted/Cancelled).
   - Update status ke Processed.
   - Tombol "Tambah Temuan & Minta Approval": input deskripsi temuan, estimasi biaya tambahan, sparepart. Simpan ke booking_approvals, ubah approval_status di bookings menjadi 'pending'.
   - Selesaikan pesanan (Processed→Completed).
3. Panel User:
   - Lihat detail pesanan dengan status tracking.
   - Notifikasi approval: tampilkan ringkasan temuan, biaya tambahan, total baru. Tombol "Setuju & Lanjutkan" atau "Tolak".
   - Jika setuju: approval_status→approved, total diupdate. Jika tolak: approval_status→rejected, lanjutkan hanya pekerjaan awal.
   - Timeout handling: jika user tidak merespons dalam 48 jam, berikan tombol ke workshop owner untuk "Lanjutkan" atau "Batalkan Tambahan" (sesuai SRS).
4. Log audit: setiap perubahan status dan approval tercatat di activity_logs (atau tabel terpisah jika perlu).

Deliverable: Controller Order/Booking_management + View panel workshop dan user. Pastikan state transition aman (cek role).

FASE 4: MODUL BARU v4.0 (MEKANIK, REVIEW, DARURAT, NOTIFIKASI)
Prompt #9: Modul Manajemen Mekanik & Penugasan

    Scope: SRS Modul 9 (FR-MEC-01~03), UC-WRK-07, BR-76~77

plain

Lanjutkan project. Implementasikan Modul Manajemen Mekanik untuk workshop_owner.

Tugas spesifik:
1. CRUD Mekanik: nama, spesialisasi (mesin/kelistrikan/body), shift (pagi/siang/malam/flexible), telepon, status aktif/non-aktif.
2. Penugasan Mekanik per Pesanan: di modal detail pesanan (status accepted/processed), workshop owner pilih 1-3 mekanik aktif yang tersedia pada slot jadwal pesanan. Cek overlapping jadwal (BR-76): satu mekanik tidak bisa ditugaskan ke 2 pesanan di slot yang sama.
3. Simpan ke booking_mechanics.
4. Laporan Produktivitas: dashboard workshop owner melihat jumlah pesanan selesai per mekanik dalam rentang tanggal (filter date range). Tampilkan juga rata-rata rating dari review pesanan yang mereka tangani (join ke reviews via bookings).
5. Penugasan bersifat opsional (BR-77): pesanan bisa diproses tanpa mekanik.

Deliverable: Controller Mechanic + View CRUD dan laporan produktivitas.

Prompt #10: Modul Review & Rating Bengkel

    Scope: SRS Modul 8 (FR-RVW-01~03), UC-USR-09, BR-65~69, State Diagram Review

plain

Lanjutkan project. Implementasikan Modul Review & Rating.

Tugas spesifik:
1. User dapat memberikan review setelah pesanan Completed: rating 1-5 bintang (komponen star selector), teks ulasan (min 10 karakter, max 500), upload foto opsional (maks 3 foto, max 2MB each, resize ke max 800x800px sebelum simpan, simpan di uploads/reviews/).
2. Validasi: satu review per booking (BR-65), hanya untuk booking completed (BR-66), user tidak bisa review bengkel yang belum pernah dipesan (BR-69).
3. Status review: 'active' (langsung tampil) atau 'pending' jika admin aktifkan moderasi ketat.
4. Tampilkan di halaman detail bengkel: rata-rata rating, jumlah ulasan, daftar ulasan (paging). Update rating_avg di tabel workshops setelah review submitted (BR-67).
5. Laporkan review: tombol "Laporkan" di setiap review. Jika report_count >= 3, otomatis ubah status ke 'pending' (BR-68).
6. Admin panel: moderasi review (approve→active, reject→hidden dengan moderation_note).

Deliverable: Controller Review + View form review dan detail bengkel dengan review list. Admin moderation panel.

Prompt #11: Modul Layanan Darurat (Emergency)

    Scope: SRS Modul 11 (FR-EMG-01~03), UC-USR-10, BR-70~72

plain

Lanjutkan project. Implementasikan Modul Layanan Darurat.

Tugas spesifik:
1. Tombol "DARURAT" besar berwarna merah di halaman utama (fixed position), bisa diakses guest maupun logged-in user.
2. Form darurat: nama (pre-fill jika login), nomor telepon, deskripsi masalah (dropdown: ban bocor, mesin mati, kecelakaan, dll + teks opsional).
3. Akses lokasi GPS. Jika ditolak, pilih lokasi manual di peta Leaflet.
4. Sistem cari bengkel aktif yang memiliki layanan panggilan aktif dan jarak < 5km (ambil dari system_settings). Gunakan Euclidean distance jika Dijkstra/graf jalan tidak tersedia untuk darurat (sesuai SRS).
5. Simpan ke emergency_requests. Kirim notifikasi email ke semua bengkel dalam radius (gunakan PHPMailer dan template dari notification_templates).
6. Tampilkan ke user: daftar bengkel yang dihubungi (nama, telepon, jarak, status "Menunggu respons").
7. Panel Workshop: notifikasi darurat masuk, tombol Terima/Tolak. Bengkel pertama yang konfirmasi menjadi responden aktif. Update responded_by_workshop.
8. Business Rules: 1 permintaan aktif per pengguna (BR-70). Auto-close setelah 2 jam tanpa respons (BR-71). Tidak perlu pembayaran di muka (BR-72).
9. Rate limiting: maks 3 request/jam per IP di endpoint emergency/create (saran reviewer #5).

Deliverable: Controller Emergency + View form darurat, panel notifikasi bengkel, tracking status.

Prompt #12: Modul Notifikasi, Template, dan Reminder Servis Berkala

    Scope: SRS Modul 10 (FR-NOT-02~04), UC-USR-11, UC-ADM-06, BR-73~75, BR-84~85

plain

Lanjutkan project. Implementasikan sistem Notifikasi multi-channel dan Reminder Otomatis.

Tugas spesifik:
1. Template Management (Admin): CRUD template notifikasi dengan rich text editor (CKEditor lite atau textarea). Variabel dinamis format {{nama_pengguna}}, {{kode_booking}}, {{nama_bengkel}} (BR-84). Template tidak bisa dihapus, hanya dinonaktifkan (BR-85). Fallback ke default hardcoded jika nonaktif.
2. Notifikasi Email Status Pesanan: trigger email saat booking berubah status (accepted, processed, completed, cancelled). Gunakan PHPMailer + SMTP.
3. Inbox Notifikasi User: tabel notification_logs, tampilkan di panel user (badge unread), tandai dibaca (read_at).
4. Reminder Servis Berkala (UC-USR-11):
   - Cron job logic: evaluasi harian (00:00) semua kendaraan. Hitung: jika km terakhir + estimasi km saat ini > threshold (default 5000km dari system_settings) ATAU waktu terakhir servis > 6 bulan.
   - Kirim email template "Saatnya Servis Kendaraan Anda!" dengan rekomendasi bengkel terdekat (berdasarkan alamat default user).
   - Snooze: user bisa tunda 30 hari dari email/notifikasi.
   - BR-73: maks 1 reminder per 7 hari per kendaraan. BR-74: user bisa nonaktifkan reminder per kendaraan.
5. Admin bisa kirim "Test Notifikasi" ke email sendiri.

Deliverable: Controller Notification (admin + user), Model reminder logic, View inbox. Sertakan pseudocode cron job.

FASE 5: LAPORAN, ADMIN, DAN POLISH
Prompt #13: Modul Penagihan, Laporan, dan Export

    Scope: SRS Modul Penagihan & Laporan

plain

Lanjutkan project. Implementasikan Modul Penagihan dan Laporan untuk workshop_owner dan admin.

Tugas spesifik:
1. Kalkulasi Tagihan: otomatis generate tagihan dari booking yang completed. Total = harga layanan + sparepart + biaya tambahan (jika approval disetujui).
2. Cetak PDF: invoice/tagihan dalam format PDF (gunakan library seperti mPDF atau DomPDF). Include logo bengkel, detail layanan, sparepart, total, tanggal.
3. Laporan Transaksi: workshop owner lihat laporan pemasukan per rentang tanggal, filter by status. Tampilkan total omzet.
4. Export: tombol Export Excel/CSV untuk laporan transaksi dan daftar booking.
5. Admin: laporan global semua bengkel (aggregate).

Deliverable: Controller Billing/Report + View laporan dan tombol cetak PDF.

Prompt #14: Admin Dashboard, Back Office, dan System Settings

    Scope: SRS UC-ADM-06, Saran Reviewer #3, #6

plain

Lanjutkan project. Implementasikan Admin Dashboard dan Back Office lengkap.

Tugas spesifik:
1. Dashboard Admin: statistik jumlah user, bengkel, booking hari ini, emergency request aktif, review pending moderasi. Gunakan chart sederhana (Chart.js).
2. Manajemen User: DataTables server-side (SS) list user, reset password, nonaktifkan akun.
3. Manajemen Bengkel: verifikasi bengkel baru, nonaktifkan bengkel, set featured (saran reviewer #6: kolom is_featured untuk promosi).
4. Moderasi Review: panel khusus untuk review dengan status 'pending' (dari report_count >=3 atau moderasi ketat).
5. Manajemen Template Notifikasi: lihat Prompt #12, pastikan admin bisa edit semua template.
6. System Settings: konfigurasi global editable admin: radius_darurat, reminder_interval_km, reminder_interval_months, same_day_booking (on/off), moderasi_review_ketat (on/off).
7. Log Audit: tampilkan activity_logs (filter by user, workshop, action).

Deliverable: Controller Admin + View dashboard dan back office. Pastikan DataTables SS untuk performa.

Prompt #15: Frontend Responsive, Security Hardening, dan UX Polish

    Scope: SRS NFR/Security, validasi, file upload

plain

Lanjutkan project. Lakukan hardening dan polish pada seluruh aplikasi.

Tugas spesifik:
1. Responsive Design: pastikan semua halaman (user, workshop, admin) responsive menggunakan Bootstrap 5. Mobile-first untuk fitur peta dan booking.
2. Form Validation: jValidate (client-side) + server-side validation untuk semua form. SweetAlert untuk konfirmasi dan sukses.
3. File Upload Security: validasi tipe file (jpg/png only), max size, rename file random, cek MIME type.
4. CSRF Protection: token di semua form POST/AJAX.
5. SQL Injection Prevention: gunakan query binding CI di semua model.
6. XSS Protection: escape output di view (htmlspecialchars).
7. Select2 untuk dropdown merk kendaraan, sparepart, dll.
8. DataTables Server-Side untuk semua tabel besar (bookings, mechanics, reviews) agar tidak load semua data sekaligus.
9. Error handling: custom 404, 403 page. Flashdata untuk notifikasi session.

Deliverable: Update semua view dan controller. Pastikan konsistensi UI/UX.

Prompt #16: Integration Testing, Cron Job Setup, dan Final Bug Fix

    Scope: SRS Deployment Checklist

plain

Lanjutkan project. Lakukan integration testing dan setup final.

Tugas spesifik:
1. End-to-end test scenario:
   - User register → login → tambah kendaraan → cari bengkel di peta → booking dengan slot → workshop terima → assign mekanik → tambah temuan → user approve → selesai → user review.
   - Emergency flow: klik darurat → isi form → bengkel terdekat menerima notifikasi → bengkel konfirmasi.
   - Cron reminder: simulasi data kendaraan dengan km mendekati threshold, jalankan script reminder, cek email log.
2. Race condition test: buka 2 browser, pilih slot sama secara bersamaan, pastikan hanya 1 yang berhasil.
3. Setup instruksi cron job:
   - 0 6 * * * php /path/to/index.php cli reminder daily
   - */30 * * * * php /path/to/index.php cli emergency close_timeout
4. Test email SMTP: kirim test email dari panel admin.
5. Test PDF cetak invoice dan export CSV.
6. Perbaiki bug minor yang ditemukan selama testing.

Deliverable: Aplikasi siap deploy + file README dengan instruksi install dan cron job.

FASE 6: VERIFIKASI
Prompt #17: Verifikasi Kepatuhan Aplikasi terhadap SRS v4.0

    Scope: Seluruh SRS v4.0, Traceability Matrix, Business Rules

plain

Kamu adalah QA Lead dan System Analyst. Lakukan audit komprehensif terhadap aplikasi yang sudah dibangun untuk memastikan 100% kepatuhan terhadap SRS Bengkel Terdekat v4.0.

Tugas spesifik:
1. Buatkan dokumen checklist verifikasi berformat markdown dengan struktur:
   A. Kebutuhan Fungsional (per Modul):
      - [ ] Modul Pencarian & Pemetaan: Dijkstra, OSM/Leaflet, rute terpendek, geolocation.
      - [ ] Modul Manajemen Bengkel: CRUD profil, layanan, harga, status aktif.
      - [ ] Modul Pemesanan: multi-step, booking code format B-YYYYMMDD-XXXX, integrasi slot & kendaraan.
      - [ ] Modul Manajemen Pesanan: state diagram v4.0 lengkap (Pending→Accepted→Processed→waiting_approval→Completed/Cancelled).
      - [ ] Modul Kendaraan: max 5, soft delete, riwayat servis, rekomendasi interval.
      - [ ] Modul Penjadwalan: slot interval 30-240 menit, kapasitas 1-20, blokir slot, H+1 rule.
      - [ ] Modul Review: 1-5 bintang, foto max 3, report≥3 auto pending, weighted average (cek: SRS sebut arithmetic average BR-67, pastikan sesuai).
      - [ ] Modul Mekanik: CRUD, penugasan 1-3 mekanik, cek overlapping, produktivitas.
      - [ ] Modul Notifikasi: email SMTP (bukan mail()), template {{variabel}}, in-app inbox, test send.
      - [ ] Modul Darurat: radius 5km, auto-close 2 jam, 1 request aktif per user, rate limit 3/jam per IP.
      - [ ] Modul Reminder: cron daily, 5000km/6 bulan, snooze 30 hari, max 1 per 7 hari.
      - [ ] Modul Approval: estimasi tambahan, timeout 48 jam, log permanen.
      - [ ] Modul Penagihan: PDF invoice, laporan, export.
   
   B. Business Rules (BR-58 s/d BR-85):
      - [ ] Cek satu per satu apakah BR sudah diimplementasikan. Jika tidak, catat bug.
   
   C. Use Case Baru v4.0:
      - [ ] UC-USR-07 s/d UC-USR-11
      - [ ] UC-WRK-07 s/d UC-WRK-09
      - [ ] UC-ADM-06
   
   D. Database Schema:
      - [ ] Semua 9 tabel baru v4.0 ada (vehicles, workshop_schedules, workshop_blocked_slots, mechanics, booking_mechanics, reviews, review_photos, emergency_requests, booking_approvals, notification_templates, notification_logs).
      - [ ] ALTER TABLE bookings (vehicle_id, scheduled_date, scheduled_time, approval_status).
      - [ ] Kolom rating_avg dan system_settings ada.
   
   E. API Endpoint:
      - [ ] /api/vehicle/*, /api/schedule/*, /api/review/*, /api/emergency/*, /api/booking/approval/*, /api/user/notifications/*.
   
   F. Keamanan & Performa:
      - [ ] CSRF token, BCRYPT, SQL binding, XSS escape.
      - [ ] Composite index bookings(workshop_id, status, created_at).
      - [ ] PHPMailer (bukan mail()).
      - [ ] Upload folder permission.

2. Untuk setiap item yang BELUM sesuai, berikan:
   - Lokasi file yang seharusnya diubah
   - Rekomendasi perbaikan kode spesifik
   - Prioritas (Critical/High/Medium/Low)

3. Buat ringkasan: "Fitur Lengkap", "Fitur Partial (perlu perbaikan)", "Fitur Missing".

Deliverable: Dokumen QA Audit Report dalam format markdown. Jangan buat perubahan kode, hanya analisis dan rekomendasi.

Tips Eksekusi untuk User:

    Jalankan secara berurutan — setiap prompt dirancang agar modular tetapi membutuhkan hasil prompt sebelumnya.
    Simpan state project — setelah setiap prompt selesai, simpan versi ZIP/Git commit agar bisa rollback jika AI coder melakukan kesalahan.
    Prompt #17 bisa dijalankan berulang — setelah perbaikan dari temuan QA, jalankan ulang Prompt #17 untuk regresi testing.
    Sesuaikan tech stack — jika AI coder lebih nyaman dengan Laravel/CodeIgniter 4, instruksikan di Prompt #1 agar disesuaikan, tetapi pastikan semua konsep (Dijkstra helper, PHPMailer, CI3-style config) tetap terwakili.
