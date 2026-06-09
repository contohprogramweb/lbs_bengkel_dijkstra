<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo $app_name; ?></title>
    
    <!-- CSRF Token -->
    <meta name="csrf_token_name" content="<?php echo $this->security->get_csrf_token_name(); ?>">
    <meta name="csrf_token_hash" content="<?php echo $this->security->get_csrf_hash(); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --gradient-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Navbar */
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: #333 !important;
            font-weight: 500;
            margin: 0 10px;
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .btn-login {
            background: var(--gradient-bg);
            color: white;
            border: none;
            padding: 8px 25px;
            border-radius: 25px;
            font-weight: 500;
        }
        
        .btn-login:hover {
            background: var(--gradient-bg);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        /* Page Header */
        .page-header {
            background: var(--gradient-bg);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        /* Content */
        .content-section {
            padding: 60px 0;
        }
        
        .step-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        
        .step-card:hover {
            transform: translateY(-5px);
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            background: var(--gradient-bg);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .step-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        
        .step-text {
            color: #666;
            line-height: 1.8;
        }
        
        .faq-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .faq-question {
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .faq-answer {
            color: #666;
            line-height: 1.6;
        }
        
        /* Footer */
        footer {
            background: #2d3748;
            color: white;
            padding: 40px 0 20px;
        }
        
        footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
        }
        
        footer a:hover {
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo site_url('home'); ?>">
                <i class="fas fa-wrench"></i> <?php echo $app_name; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo site_url('home'); ?>">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo site_url('home/cara_pakai'); ?>">Cara Pakai</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo site_url('home/tentang'); ?>">Tentang</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-login" href="<?php echo site_url('auth/login'); ?>">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-primary" href="<?php echo site_url('auth/register'); ?>" style="border-radius: 25px; padding: 8px 25px;">
                            Daftar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1><i class="fas fa-book-open"></i> Cara Pakai</h1>
            <p>Panduan lengkap menggunakan layanan <?php echo $app_name; ?></p>
        </div>
    </section>

    <!-- Content -->
    <section class="content-section">
        <div class="container">
            <!-- For Customers -->
            <div class="mb-5">
                <h2 class="text-center fw-bold mb-4">Untuk Pelanggan</h2>
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <h3 class="step-title">Daftar Akun</h3>
                            <p class="step-text">
                                Klik tombol "Daftar" di pojok kanan atas, lalu isi formulir pendaftaran dengan data diri Anda. 
                                Pilih role sebagai "Customer" untuk mencari bengkel.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <h3 class="step-title">Cari Bengkel</h3>
                            <p class="step-text">
                                Browse daftar bengkel yang tersedia di halaman beranda. 
                                Anda bisa melihat lokasi, layanan, dan rating setiap bengkel.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <h3 class="step-title">Lihat Detail Bengkel</h3>
                            <p class="step-text">
                                Klik "Lihat Detail" pada bengkel yang diminati untuk melihat informasi lengkap, 
                                layanan yang ditawarkan, dan harga.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="step-card">
                            <div class="step-number">4</div>
                            <h3 class="step-title">Tambah Kendaraan</h3>
                            <p class="step-text">
                                Setelah login, tambahkan data kendaraan Anda di menu "Kendaraan". 
                                Ini akan memudahkan proses booking.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="step-card">
                            <div class="step-number">5</div>
                            <h3 class="step-title">Booking Servis</h3>
                            <p class="step-text">
                                Pilih bengkel, pilih layanan yang dibutuhkan, tentukan jadwal, 
                                dan konfirmasi booking Anda.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="step-card">
                            <div class="step-number">6</div>
                            <h3 class="step-title">Datang ke Bengkel</h3>
                            <p class="step-text">
                                Datang ke bengkel sesuai jadwal yang telah ditentukan. 
                                Setelah servis selesai, Anda bisa memberikan review.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- For Workshop Owners -->
            <div class="mb-5">
                <h2 class="text-center fw-bold mb-4">Untuk Pemilik Bengkel</h2>
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <h3 class="step-title">Daftar sebagai Workshop Owner</h3>
                            <p class="step-text">
                                Klik tombol "Daftar" dan pilih role sebagai "Workshop Owner". 
                                Isi data diri Anda dengan benar.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <h3 class="step-title">Buat Profil Bengkel</h3>
                            <p class="step-text">
                                Setelah login, buat profil bengkel Anda dengan mengisi nama, alamat, 
                                deskripsi, dan upload logo bengkel.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <h3 class="step-title">Tambah Layanan</h3>
                            <p class="step-text">
                                Tambahkan layanan yang disediakan bengkel Anda beserta kategori, 
                                deskripsi, dan rentang harga.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="step-card">
                            <div class="step-number">4</div>
                            <h3 class="step-title">Verifikasi Admin</h3>
                            <p class="step-text">
                                Profil bengkel Anda akan diverifikasi oleh admin. 
                                Setelah diverifikasi, bengkel Anda akan tampil di pencarian.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="step-card">
                            <div class="step-number">5</div>
                            <h3 class="step-title">Kelola Booking</h3>
                            <p class="step-text">
                                Terima dan kelola booking dari pelanggan melalui dashboard. 
                                Anda bisa menyetujui atau menolak booking.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="step-card">
                            <div class="step-number">6</div>
                            <h3 class="step-title">Terima Pelanggan</h3>
                            <p class="step-text">
                                Layani pelanggan sesuai jadwal booking. 
                                Setelah selesai, status booking akan berubah menjadi completed.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div>
                <h2 class="text-center fw-bold mb-4">Pertanyaan Umum (FAQ)</h2>
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="faq-item">
                            <div class="faq-question"><i class="fas fa-question-circle"></i> Apakah layanan ini gratis?</div>
                            <div class="faq-answer">
                                Ya, penggunaan platform ini gratis untuk pelanggan. Anda hanya membayar layanan bengkel sesuai tarif yang ditetapkan.
                            </div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question"><i class="fas fa-question-circle"></i> Bagaimana cara membatalkan booking?</div>
                            <div class="faq-answer">
                                Anda dapat membatalkan booking melalui menu "Booking Saya" di dashboard. Pembatalan dapat dilakukan sebelum jadwal servis dimulai.
                            </div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question"><i class="fas fa-question-circle"></i> Apakah saya bisa reschedule booking?</div>
                            <div class="faq-answer">
                                Ya, Anda dapat melakukan reschedule booking melalui dashboard dengan memilih jadwal baru yang tersedia.
                            </div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question"><i class="fas fa-question-circle"></i> Berapa lama proses verifikasi bengkel?</div>
                            <div class="faq-answer">
                                Proses verifikasi biasanya memakan waktu 1-3 hari kerja. Admin akan memeriksa kelengkapan dan keabsahan data bengkel Anda.
                            </div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question"><i class="fas fa-question-circle"></i> Bagaimana jika bengkel tidak muncul di pencarian?</div>
                            <div class="faq-answer">
                                Pastikan bengkel sudah diverifikasi admin dan statusnya aktif. Jika masih bermasalah, hubungi tim support kami.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5" style="background: var(--gradient-bg); color: white; text-align: center;">
        <div class="container">
            <h2 class="fw-bold mb-3">Masih Ada Pertanyaan?</h2>
            <p class="mb-4">Tim support kami siap membantu Anda</p>
            <a href="mailto:info@bengkelterdekat.com" class="btn btn-light btn-lg">
                <i class="fas fa-envelope"></i> Hubungi Kami
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-wrench"></i> <?php echo $app_name; ?></h5>
                    <p class="text-muted">Platform pencarian dan booking bengkel terpercaya di Indonesia.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Tautan Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo site_url('home'); ?>">Beranda</a></li>
                        <li><a href="<?php echo site_url('home/cara_pakai'); ?>">Cara Pakai</a></li>
                        <li><a href="<?php echo site_url('home/tentang'); ?>">Tentang Kami</a></li>
                        <li><a href="<?php echo site_url('auth/login'); ?>">Login</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Hubungi Kami</h5>
                    <p class="text-muted">
                        <i class="fas fa-envelope"></i> info@bengkelterdekat.com<br>
                        <i class="fas fa-phone"></i> +62 812 3456 7890
                    </p>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.2);">
            <div class="text-center text-muted">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo $app_name; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
