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
        
        .about-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .about-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .about-text {
            color: #666;
            line-height: 1.8;
            font-size: 1.1rem;
        }
        
        .value-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
            height: 100%;
        }
        
        .value-card:hover {
            transform: translateY(-5px);
        }
        
        .value-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .value-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        
        .value-text {
            color: #666;
            line-height: 1.6;
        }
        
        .team-member {
            text-align: center;
            padding: 20px;
        }
        
        .team-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--gradient-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 3rem;
        }
        
        .team-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }
        
        .team-role {
            color: #666;
            font-size: 0.9rem;
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
                        <a class="nav-link" href="<?php echo site_url('home/cara_pakai'); ?>">Cara Pakai</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo site_url('home/tentang'); ?>">Tentang</a>
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
            <h1><i class="fas fa-info-circle"></i> Tentang Kami</h1>
            <p>Mengenal lebih dekat <?php echo $app_name; ?> dan misi kami</p>
        </div>
    </section>

    <!-- Content -->
    <section class="content-section">
        <div class="container">
            <!-- About Section -->
            <div class="about-card mb-5">
                <h2 class="about-title">Tentang <?php echo $app_name; ?></h2>
                <p class="about-text">
                    <?php echo $app_name; ?> adalah platform digital yang menghubungkan pemilik kendaraan dengan bengkel terpercaya di Indonesia. 
                    Kami hadir untuk memudahkan masyarakat dalam menemukan bengkel yang sesuai dengan kebutuhan mereka, 
                    serta membantu pemilik bengkel untuk menjangkau lebih banyak pelanggan.
                </p>
                <p class="about-text">
                    Dengan sistem booking online yang terintegrasi, <?php echo $app_name; ?> memberikan pengalaman servis kendaraan 
                    yang lebih efisien, transparan, dan terpercaya. Pengguna dapat melihat informasi lengkap tentang bengkel, 
                    termasuk layanan yang ditawarkan, harga, lokasi, dan review dari pengguna lain.
                </p>
                <p class="about-text">
                    Platform ini dibangun dengan tujuan untuk meningkatkan kualitas industri otomotif di Indonesia, 
                    khususnya dalam hal pelayanan bengkel. Kami berkomitmen untuk terus berinovasi dan memberikan 
                    layanan terbaik bagi para pengguna setia kami.
                </p>
            </div>

            <!-- Vision & Mission -->
            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="about-card h-100">
                        <h2 class="about-title"><i class="fas fa-eye"></i> Visi</h2>
                        <p class="about-text">
                            Menjadi platform pencarian dan booking bengkel terdepan di Indonesia yang 
                            dipercaya oleh jutaan pengguna dan membantu ribuan bengkel untuk berkembang.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="about-card h-100">
                        <h2 class="about-title"><i class="fas fa-bullseye"></i> Misi</h2>
                        <ul class="about-text" style="padding-left: 20px;">
                            <li>Menyediakan platform yang mudah digunakan untuk mencari bengkel terdekat</li>
                            <li>Meningkatkan transparansi harga dan kualitas layanan bengkel</li>
                            <li>Membantu bengkel lokal untuk go digital dan menjangkau lebih banyak pelanggan</li>
                            <li>Menciptakan ekosistem otomotif yang saling menguntungkan antara pengguna dan bengkel</li>
                            <li>Terus berinovasi untuk memberikan pengalaman terbaik bagi pengguna</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Values -->
            <div class="mb-5">
                <h2 class="text-center fw-bold mb-4">Nilai-Nilai Kami</h2>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h3 class="value-title">Terpercaya</h3>
                            <p class="value-text">
                                Kami hanya bekerja sama dengan bengkel yang telah terverifikasi dan memiliki reputasi baik.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-search-dollar"></i>
                            </div>
                            <h3 class="value-title">Transparan</h3>
                            <p class="value-text">
                                Informasi harga dan layanan ditampilkan dengan jelas tanpa biaya tersembunyi.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="value-title">Berorientasi Pelanggan</h3>
                            <p class="value-text">
                                Kepuasan pengguna adalah prioritas utama kami dalam setiap pengembangan fitur.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="about-card mb-5">
                <h2 class="text-center fw-bold mb-4">Statistik Platform</h2>
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-4">
                        <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary-color);">
                            <i class="fas fa-store"></i>
                        </div>
                        <p class="text-muted">Bengkel Terdaftar</p>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary-color);">
                            <i class="fas fa-users"></i>
                        </div>
                        <p class="text-muted">Pengguna Aktif</p>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary-color);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <p class="text-muted">Booking Selesai</p>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary-color);">
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="text-muted">Rating Rata-rata</p>
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="about-card">
                <h2 class="about-title"><i class="fas fa-envelope"></i> Hubungi Kami</h2>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <p class="about-text">
                            Punya pertanyaan atau ingin bermitra dengan kami? Jangan ragu untuk menghubungi tim kami.
                        </p>
                        <ul class="list-unstyled mt-4">
                            <li class="mb-3">
                                <i class="fas fa-map-marker-alt" style="color: var(--primary-color); width: 25px;"></i>
                                Jakarta, Indonesia
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-envelope" style="color: var(--primary-color); width: 25px;"></i>
                                info@bengkelterdekat.com
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-phone" style="color: var(--primary-color); width: 25px;"></i>
                                +62 812 3456 7890
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-clock" style="color: var(--primary-color); width: 25px;"></i>
                                Senin - Jumat, 09:00 - 17:00 WIB
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div style="background: #f8f9fa; padding: 30px; border-radius: 10px;">
                            <h4 class="fw-bold mb-3">Ikuti Kami</h4>
                            <div class="d-flex gap-3">
                                <a href="#" style="font-size: 2rem; color: var(--primary-color);">
                                    <i class="fab fa-facebook"></i>
                                </a>
                                <a href="#" style="font-size: 2rem; color: var(--primary-color);">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="#" style="font-size: 2rem; color: var(--primary-color);">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" style="font-size: 2rem; color: var(--primary-color);">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5" style="background: var(--gradient-bg); color: white; text-align: center;">
        <div class="container">
            <h2 class="fw-bold mb-3">Siap Menggunakan Layanan Kami?</h2>
            <p class="mb-4">Bergabunglah dengan ribuan pengguna lainnya yang telah merasakan kemudahan <?php echo $app_name; ?></p>
            <a href="<?php echo site_url('auth/register'); ?>" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
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
