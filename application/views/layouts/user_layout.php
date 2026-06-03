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
        body { background-color: #f8f9fa; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { color: white; background: rgba(255,255,255,0.1); }
        .sidebar-brand { font-size: 1.5rem; font-weight: bold; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .nav-link { padding: 12px 20px; }
        .main-content { padding: 30px; }
        .card-stat { border-radius: 15px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .card-stat .icon { font-size: 2.5rem; opacity: 0.3; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="sidebar-brand">
                    <i class="fas fa-wrench"></i> Bengkel Terdekat
                </div>
                <nav class="nav flex-column mt-3">
                    <a class="nav-link <?php echo $this->uri->segment(2) == 'dashboard' ? 'active' : ''; ?>" href="<?php echo site_url('user/dashboard'); ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a class="nav-link <?php echo $this->uri->segment(2) == 'profile' ? 'active' : ''; ?>" href="<?php echo site_url('user/profile'); ?>">
                        <i class="fas fa-user"></i> Profil Saya
                    </a>
                    <a class="nav-link" href="#">
                        <i class="fas fa-car"></i> Kendaraan
                    </a>
                    <a class="nav-link" href="#">
                        <i class="fas fa-calendar-check"></i> Booking Saya
                    </a>
                    <a class="nav-link" href="#">
                        <i class="fas fa-bell"></i> Notifikasi
                    </a>
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                    <a class="nav-link" href="<?php echo site_url('auth/logout'); ?>">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><?php echo $page_title; ?></h2>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo $current_user->full_name; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo site_url('user/profile'); ?>"><i class="fas fa-user"></i> Profil</a></li>
                            <li><a class="dropdown-item" href="<?php echo site_url('user/change_password'); ?>"><i class="fas fa-key"></i> Ubah Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo site_url('auth/logout'); ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Flash Messages -->
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card card-stat bg-primary text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Total Booking</h6>
                                    <h3 class="mb-0"><?php echo $stats['total_bookings']; ?></h3>
                                </div>
                                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stat bg-warning text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Pending</h6>
                                    <h3 class="mb-0"><?php echo $stats['pending_bookings']; ?></h3>
                                </div>
                                <div class="icon"><i class="fas fa-clock"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stat bg-success text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Selesai</h6>
                                    <h3 class="mb-0"><?php echo $stats['completed_bookings']; ?></h3>
                                </div>
                                <div class="icon"><i class="fas fa-check"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stat bg-info text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Kendaraan</h6>
                                    <h3 class="mb-0"><?php echo $stats['total_vehicles']; ?></h3>
                                </div>
                                <div class="icon"><i class="fas fa-car"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content Area -->
                <?php echo $content_for_layout ?? ''; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
