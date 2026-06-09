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
        
        /* Hero Section */
        .hero-section {
            background: var(--gradient-bg);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .hero-section h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .hero-section p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        
        /* Cards */
        .workshop-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
            height: 100%;
        }
        
        .workshop-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .workshop-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .workshop-card-body {
            padding: 20px;
        }
        
        .workshop-card-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .workshop-card-location {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .badge-category {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-color);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }
        
        /* Features Section */
        .features-section {
            padding: 60px 0;
            background: white;
        }
        
        .feature-box {
            text-align: center;
            padding: 30px;
            border-radius: 15px;
            background: #f8f9fa;
            transition: transform 0.3s;
        }
        
        .feature-box:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .feature-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        
        .feature-text {
            color: #666;
            line-height: 1.6;
        }
        
        /* CTA Section */
        .cta-section {
            background: var(--gradient-bg);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .btn-cta {
            background: white;
            color: var(--primary-color);
            padding: 15px 40px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1.1rem;
            border: none;
            transition: transform 0.3s;
        }
        
        .btn-cta:hover {
            transform: scale(1.05);
            color: var(--primary-color);
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .hero-section p {
                font-size: 1rem;
            }
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Temukan Bengkel Terpercaya</h1>
            <p>Platform pencarian bengkel terdekat dengan layanan lengkap dan terpercaya. Booking mudah, harga transparan.</p>
            <a href="#workshops" class="btn btn-cta">
                <i class="fas fa-search"></i> Cari Bengkel
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3 class="feature-title">Lokasi Terdekat</h3>
                        <p class="feature-text">Temukan bengkel terdekat dari lokasi Anda dengan mudah dan cepat.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3 class="feature-title">Booking Online</h3>
                        <p class="feature-text">Jadwalkan servis kendaraan Anda tanpa perlu antri di bengkel.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">Terpercaya</h3>
                        <p class="feature-text">Bengkel terverifikasi dengan rating dan review dari pengguna lain.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Workshops Section -->
    <section id="workshops" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Bengkel Tersedia</h2>
                <p class="text-muted">Pilih bengkel yang sesuai dengan kebutuhan Anda</p>
            </div>
            
            <?php if (empty($workshops)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> Belum ada bengkel terdaftar saat ini.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($workshops as $workshop): ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="workshop-card">
                                <?php if (!empty($workshop['logo'])): ?>
                                    <img src="<?php echo base_url($workshop['logo']); ?>" alt="<?php echo e($workshop['name']); ?>">
                                <?php else: ?>
                                    <div style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-wrench" style="font-size: 4rem; color: white;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="workshop-card-body">
                                    <h5 class="workshop-card-title"><?php echo e($workshop['name']); ?></h5>
                                    <p class="workshop-card-location">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo e($workshop['city'] . ', ' . $workshop['province']); ?>
                                    </p>
                                    
                                    <?php if (!empty($workshop['description'])): ?>
                                        <p class="text-muted small" style="height: 60px; overflow: hidden;">
                                            <?php echo e(substr($workshop['description'], 0, 100)); ?>...
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <?php 
                                        $services = $this->workshop_model->get_workshop_services($workshop['id']);
                                        $categories_shown = [];
                                        foreach ($services as $service) {
                                            if (!in_array($service['service_category'], $categories_shown) && count($categories_shown) < 3) {
                                                $categories_shown[] = $service['service_category'];
                                                echo '<span class="badge-category">' . e($categories[$service['service_category']] ?? $service['service_category']) . '</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    
                                    <a href="<?php echo site_url('home/workshop_detail/' . $workshop['id']); ?>" class="btn btn-primary w-100" style="background: var(--gradient-bg); border: none;">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <p class="text-muted">
                    <i class="fas fa-store"></i> Total <?php echo $workshop_count; ?> bengkel terdaftar
                </p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Siap Menggunakan Layanan Kami?</h2>
            <p class="mb-4">Daftar sekarang dan temukan bengkel terbaik untuk kendaraan Anda</p>
            <a href="<?php echo site_url('auth/register'); ?>" class="btn btn-cta">
                <i class="fas fa-user-plus"></i> Daftar Gratis
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
