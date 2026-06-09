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
        
        /* Workshop Detail */
        .workshop-detail-section {
            padding: 60px 0;
        }
        
        .workshop-info-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .workshop-logo {
            width: 150px;
            height: 150px;
            border-radius: 15px;
            object-fit: cover;
            margin-bottom: 20px;
        }
        
        .workshop-name {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .workshop-location {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        .workshop-description {
            color: #666;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        
        .service-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: transform 0.3s;
        }
        
        .service-card:hover {
            transform: translateX(5px);
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .service-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .service-category {
            display: inline-block;
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-color);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }
        
        .service-price {
            font-weight: bold;
            color: #333;
        }
        
        .service-duration {
            color: #666;
            font-size: 0.9rem;
        }
        
        .badge-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-pending {
            background: #fff3cd;
            color: #856404;
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
            <h1><i class="fas fa-store"></i> Detail Bengkel</h1>
            <p>Informasi lengkap tentang bengkel</p>
        </div>
    </section>

    <!-- Workshop Detail Section -->
    <section class="workshop-detail-section">
        <div class="container">
            <div class="row">
                <!-- Left Column - Workshop Info -->
                <div class="col-lg-8 mb-4">
                    <div class="workshop-info-card">
                        <?php if (!empty($workshop['logo'])): ?>
                            <img src="<?php echo base_url($workshop['logo']); ?>" alt="<?php echo e($workshop['name']); ?>" class="workshop-logo">
                        <?php endif; ?>
                        
                        <h2 class="workshop-name"><?php echo e($workshop['name']); ?></h2>
                        
                        <div class="mb-3">
                            <span class="badge badge-<?php echo $workshop['status'] == 'active' ? 'active' : 'pending'; ?>">
                                <i class="fas fa-circle"></i> 
                                <?php 
                                if ($workshop['status'] == 'active') {
                                    echo 'Aktif';
                                } elseif ($workshop['status'] == 'pending') {
                                    echo 'Menunggu Verifikasi';
                                } else {
                                    echo ucfirst($workshop['status']);
                                }
                                ?>
                            </span>
                        </div>
                        
                        <p class="workshop-location">
                            <i class="fas fa-map-marker-alt"></i> 
                            <?php echo e($workshop['address'] . ', ' . $workshop['city'] . ', ' . $workshop['province']); ?>
                        </p>
                        
                        <?php if (!empty($workshop['phone'])): ?>
                            <p class="workshop-location">
                                <i class="fas fa-phone"></i> 
                                <?php echo e($workshop['phone']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($workshop['description'])): ?>
                            <h4 class="fw-bold mb-3">Tentang Bengkel</h4>
                            <p class="workshop-description"><?php echo nl2br(e($workshop['description'])); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($workshop['opening_hours'])): ?>
                            <h4 class="fw-bold mb-3">Jam Operasional</h4>
                            <p class="workshop-description"><?php echo e($workshop['opening_hours']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Services -->
                    <div class="workshop-info-card">
                        <h3 class="fw-bold mb-4"><i class="fas fa-tools"></i> Layanan Tersedia</h3>
                        
                        <?php if (empty($services)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Belum ada layanan yang terdaftar.
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($services as $service): ?>
                                    <div class="col-md-6">
                                        <div class="service-card">
                                            <span class="service-category">
                                                <?php echo e($categories[$service->service_category] ?? $service->service_category); ?>
                                            </span>
                                            <h5 class="service-name"><?php echo e($service->service_name); ?></h5>
                                            
                                            <?php if (!empty($service->description)): ?>
                                                <p class="text-muted small mb-2"><?php echo e(substr($service->description, 0, 80)); ?><?php echo strlen($service->description) > 80 ? '...' : ''; ?></p>
                                            <?php endif; ?>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="service-price">
                                                    <?php 
                                                    if ($service->price_min == $service->price_max) {
                                                        echo 'Rp ' . number_format($service->price_min, 0, ',', '.');
                                                    } else {
                                                        echo 'Rp ' . number_format($service->price_min, 0, ',', '.') . ' - Rp ' . number_format($service->price_max, 0, ',', '.');
                                                    }
                                                    ?>
                                                </span>
                                                <?php if (!empty($service->duration_minutes)): ?>
                                                    <span class="service-duration">
                                                        <i class="fas fa-clock"></i> <?php echo $service->duration_minutes; ?> menit
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Right Column - Action & Map -->
                <div class="col-lg-4 mb-4">
                    <div class="workshop-info-card text-center">
                        <h4 class="fw-bold mb-3">Tertarik dengan bengkel ini?</h4>
                        <p class="text-muted mb-4">Login atau daftar untuk melakukan booking</p>
                        <a href="<?php echo site_url('auth/login'); ?>" class="btn btn-primary w-100 mb-3" style="background: var(--gradient-bg); border: none;">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="<?php echo site_url('auth/register'); ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-user-plus"></i> Daftar Sekarang
                        </a>
                    </div>
                    
                    <div class="workshop-info-card">
                        <h4 class="fw-bold mb-3"><i class="fas fa-map"></i> Lokasi</h4>
                        <div style="background: #e9ecef; border-radius: 10px; height: 250px; display: flex; align-items: center; justify-content: center;">
                            <div class="text-center text-muted">
                                <i class="fas fa-map-marked-alt" style="font-size: 3rem; margin-bottom: 10px;"></i>
                                <p>Map akan ditampilkan di sini</p>
                                <small><?php echo e($workshop['city'] . ', ' . $workshop['province']); ?></small>
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
            <h2 class="fw-bold mb-3">Ingin Menemukan Bengkel Lain?</h2>
            <p class="mb-4">Lihat daftar bengkel lainnya yang tersedia di platform kami</p>
            <a href="<?php echo site_url('home#workshops'); ?>" class="btn btn-light btn-lg">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Bengkel
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
