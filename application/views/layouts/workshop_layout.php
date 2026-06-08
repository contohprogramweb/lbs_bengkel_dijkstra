<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? e($page_title) . ' - ' : ''; ?><?php echo e($app_name); ?></title>
    
    <!-- CSRF Token -->
    <meta name="csrf_token_name" content="<?php echo $this->security->get_csrf_token_name(); ?>">
    <meta name="csrf_token_hash" content="<?php echo $this->security->get_csrf_hash(); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-bg-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        body { 
            background-color: #f8f9fa; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background: var(--sidebar-bg-gradient);
            color: white;
            position: fixed;
            width: 250px;
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { color: white; background: rgba(255,255,255,0.1); }
        .sidebar-brand { font-size: 1.3rem; font-weight: bold; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .nav-link { padding: 12px 20px; }
        .main-content { 
            padding: 20px; 
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }
        .main-content.expanded {
            margin-left: 0;
        }
        .card-stat { border-radius: 15px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .card-stat:hover { transform: translateY(-5px); }
        .card-stat .icon { font-size: 2.5rem; opacity: 0.3; }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar-toggle {
                display: block !important;
            }
            .card-stat { margin-bottom: 15px; }
        }
        
        .sidebar-toggle {
            display: none;
            background: var(--sidebar-bg-gradient);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="sidebar-toggle d-md-none" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0" id="sidebar">
                <div class="sidebar-brand">
                    <i class="fas fa-tools"></i> Workshop Owner
                </div>
                <nav class="nav flex-column mt-3">
                    <a class="nav-link <?php echo $this->uri->segment(2) == 'dashboard' ? 'active' : ''; ?>" href="<?php echo site_url('workshop/dashboard'); ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a class="nav-link <?php echo $this->uri->segment(2) == 'profile' ? 'active' : ''; ?>" href="<?php echo site_url('workshop/profile'); ?>">
                        <i class="fas fa-store"></i> Profil Bengkel
                    </a>
                    <a class="nav-link <?php echo $this->uri->segment(2) == 'services' ? 'active' : ''; ?>" href="<?php echo site_url('workshop/services'); ?>">
                        <i class="fas fa-tools"></i> Kelola Layanan
                    </a>
                    <a class="nav-link <?php echo $this->uri->segment(2) == 'schedule' || strpos($this->uri->segment(2), 'booking') !== false ? 'active' : ''; ?>" href="<?php echo site_url('workshop/schedule'); ?>">
                        <i class="fas fa-calendar-alt"></i> Jadwal Booking
                    </a>
                    <a class="nav-link <?php echo $this->uri->segment(2) == 'orders' ? 'active' : ''; ?>" href="<?php echo site_url('workshop/orders'); ?>">
                        <i class="fas fa-clipboard-list"></i> Order
                    </a>
                    <a class="nav-link <?php echo $this->uri->segment(2) == 'mechanics' ? 'active' : ''; ?>" href="<?php echo site_url('workshop/mechanics'); ?>">
                        <i class="fas fa-user-cog"></i> Mekanik
                    </a>
                    <a class="nav-link <?php echo $this->uri->segment(2) == 'billing' ? 'active' : ''; ?>" href="<?php echo site_url('workshop/billing'); ?>">
                        <i class="fas fa-file-invoice-dollar"></i> Billing
                    </a>
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                    <a class="nav-link" href="<?php echo site_url('auth/logout'); ?>">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content" id="mainContent">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <button class="sidebar-toggle d-md-none me-2" onclick="toggleSidebar()">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h2 class="mb-0"><?php echo e($page_title); ?></h2>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo e($current_user->full_name ?? 'User'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo site_url('workshop/profile'); ?>"><i class="fas fa-store"></i> Profil Bengkel</a></li>
                            <li><a class="dropdown-item" href="<?php echo site_url('workshop/edit_profile'); ?>"><i class="fas fa-user-edit"></i> Edit Profil Pribadi</a></li>
                            <li><a class="dropdown-item" href="<?php echo site_url('user/change_password'); ?>"><i class="fas fa-key"></i> Ubah Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo site_url('auth/logout'); ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Flash Messages with SweetAlert -->
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo e($this->session->flashdata('success')); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo e($this->session->flashdata('error')); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo e($this->session->flashdata('warning')); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('info')): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle"></i> <?php echo e($this->session->flashdata('info')); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Main Content Area -->
                <?php echo $content_for_layout ?? ''; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        // Sidebar toggle for mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.sidebar-toggle');
            if (window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                !toggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });

        // Initialize Select2
        $(document).ready(function() {
            $('.select2').each(function() {
                $(this).select2({
                    theme: 'bootstrap-5',
                    placeholder: $(this).attr('placeholder') || 'Pilih...',
                    allowClear: true
                });
            });
        });

        // CSRF Token for AJAX
        const csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        const csrfTokenHash = '<?php echo $this->security->get_csrf_hash(); ?>';

        // Setup AJAX defaults
        $.ajaxSetup({
            data: {
                [csrfTokenName]: csrfTokenHash
            }
        });

        // Update CSRF token after each request
        $(document).ajaxComplete(function(event, xhr, settings) {
            const newToken = xhr.getResponseHeader('X-CSRF-TOKEN');
            if (newToken) {
                window[csrfTokenName] = newToken;
            }
        });

        // Confirmation dialog helper
        function confirmAction(message, callback) {
            Swal.fire({
                title: 'Konfirmasi',
                text: message || 'Apakah Anda yakin?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f093fb',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed && callback) {
                    callback();
                }
            });
        }

        // Success notification helper
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        }

        // Error notification helper
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: message
            });
        }
    </script>
</body>
</html>
