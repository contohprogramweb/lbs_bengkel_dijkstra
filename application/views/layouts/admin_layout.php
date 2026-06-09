<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? e($page_title) . ' - ' : ''; ?><?php echo e($app_name); ?></title>
    
    <!-- CSRF Token -->
    <meta name="csrf_token_name" content="<?php echo $this->security->get_csrf_token_name(); ?>">
    <meta name="csrf_token_hash" content="<?php echo $this->security->get_csrf_hash(); ?>">
    <meta name="csrf-token" content="<?php echo $this->security->get_csrf_hash(); ?>">
    
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
            --sidebar-dark-bg: #1a1a2e;
            --sidebar-dark-hover: #16213e;
            --sidebar-dark-active: #0f3460;
            --sidebar-text: #e0e0e0;
            --sidebar-text-muted: #a0a0a0;
            --header-dark-bg: #121212;
            --content-light-bg: #f8f9fa;
            --border-color: #dee2e6;
            /* Admin color scheme */
            --admin-primary: #0d6efd;
            --admin-success: #198754;
            --admin-info: #0dcaf0;
            --admin-warning: #ffc107;
            --admin-danger: #dc3545;
            --admin-secondary: #6c757d;
            --admin-dark: #212529;
        }
        * {
            box-sizing: border-box;
        }
        body {
            background-color: var(--content-light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        .sidebar {
            min-height: 100vh;
            background-color: var(--sidebar-dark-bg);
            color: var(--sidebar-text);
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
        }
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        .sidebar a { 
            color: var(--sidebar-text); 
            text-decoration: none; 
            transition: all 0.2s; 
            border-left: 3px solid transparent;
        }
        .sidebar a:hover, .sidebar a.active { 
            color: #fff; 
            background-color: var(--sidebar-dark-hover);
            border-left-color: #e94560;
        }
        .sidebar-brand { 
            font-size: 1.3rem; 
            font-weight: bold; 
            padding: 20px; 
            background-color: var(--header-dark-bg);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: #fff;
        }
        .nav-link { padding: 12px 20px; }
        .main-content {
            padding: 20px;
            margin-left: 250px;
            transition: margin-left 0.3s ease;
            background-color: var(--content-light-bg);
            min-height: 100vh;
            width: calc(100% - 250px);
            overflow-x: hidden;
            position: relative;
            left: 0;
        }
        .main-content.expanded {
            margin-left: 0;
            width: 100%;
        }
        .card-stat { 
            border-radius: 10px; 
            border: none; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.08); 
            transition: transform 0.2s;
            background: #fff;
            overflow: hidden;
        }
        .card-stat:hover { transform: translateY(-3px); }
        .card-stat .icon { font-size: 2.5rem; opacity: 0.3; }
        
        /* Card styling for light content */
        .card {
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
        }

        /* Stat card colors */
        .stat-card.bg-admin-primary { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white; }
        .stat-card.bg-admin-success { background: linear-gradient(135deg, #198754 0%, #146c43 100%); color: white; }
        .stat-card.bg-admin-info { background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); color: white; }
        .stat-card.bg-admin-warning { background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%); color: #212529; }
        .stat-card.bg-admin-danger { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); color: white; }
        .stat-card.bg-admin-secondary { background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; }
        .stat-card.bg-admin-dark { background: linear-gradient(135deg, #212529 0%, #1a1e21 100%); color: white; }

        /* Button styling for admin */
        .btn-admin-primary { background-color: var(--admin-primary); border-color: var(--admin-primary); color: white; }
        .btn-admin-primary:hover { background-color: #0b5ed7; border-color: #0a58ca; color: white; }
        .btn-admin-success { background-color: var(--admin-success); border-color: var(--admin-success); color: white; }
        .btn-admin-success:hover { background-color: #157347; border-color: #146c43; color: white; }
        .btn-admin-info { background-color: var(--admin-info); border-color: var(--admin-info); color: #212529; }
        .btn-admin-info:hover { background-color: #0aacd6; border-color: #0aa2c0; color: #212529; }
        .btn-admin-warning { background-color: var(--admin-warning); border-color: var(--admin-warning); color: #212529; }
        .btn-admin-warning:hover { background-color: #e0a800; border-color: #d39e00; color: #212529; }
        .btn-admin-danger { background-color: var(--admin-danger); border-color: var(--admin-danger); color: white; }
        .btn-admin-danger:hover { background-color: #bb2d3b; border-color: #b02a37; color: white; }
        .btn-admin-secondary { background-color: var(--admin-secondary); border-color: var(--admin-secondary); color: white; }
        .btn-admin-secondary:hover { background-color: #5c636a; border-color: #565e64; color: white; }

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
                padding: 15px;
            }
            .sidebar-toggle {
                display: block !important;
            }
            .card-stat { margin-bottom: 15px; }
            .row > [class*="col-"] {
                padding-left: 10px;
                padding-right: 10px;
            }
        }

        /* Prevent horizontal scroll */
        body, html {
            overflow-x: hidden;
            max-width: 100vw;
            margin: 0;
            padding: 0;
        }
        .container-fluid {
            max-width: 100%;
            padding-left: 0;
            padding-right: 0;
            margin-left: 0;
            margin-right: 0;
            width: 100%;
        }
        .row {
            margin-left: 0;
            margin-right: 0;
        }
        .row > [class*="col-"] {
            padding-left: 15px;
            padding-right: 15px;
        }
        
        .sidebar-toggle {
            display: none;
            background-color: var(--header-dark-bg);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            margin-right: 15px;
        }
        .sidebar-toggle:hover {
            background-color: var(--sidebar-dark-hover);
        }
        
        /* Table styling */
        .table {
            background-color: #fff;
            width: 100%;
            max-width: 100%;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid var(--border-color);
            white-space: nowrap;
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Badge colors for admin */
        .bg-admin-primary { background-color: var(--admin-primary) !important; color: white; }
        .bg-admin-success { background-color: var(--admin-success) !important; color: white; }
        .bg-admin-info { background-color: var(--admin-info) !important; color: #212529; }
        .bg-admin-warning { background-color: var(--admin-warning) !important; color: #212529; }
        .bg-admin-danger { background-color: var(--admin-danger) !important; color: white; }
        .bg-admin-secondary { background-color: var(--admin-secondary) !important; color: white; }
        .bg-admin-dark { background-color: var(--admin-dark) !important; color: white; }
        
        /* Badge dark theme variants */
        .badge-dark-theme-info { background-color: var(--admin-info); color: #212529; padding: 0.35em 0.65em; border-radius: 0.25rem; font-weight: 500; }
        .badge-dark-theme-success { background-color: var(--admin-success); color: white; padding: 0.35em 0.65em; border-radius: 0.25rem; font-weight: 500; }
        .badge-dark-theme-danger { background-color: var(--admin-danger); color: white; padding: 0.35em 0.65em; border-radius: 0.25rem; font-weight: 500; }
        .badge-dark-theme-warning { background-color: var(--admin-warning); color: #212529; padding: 0.35em 0.65em; border-radius: 0.25rem; font-weight: 500; }
        .badge-dark-theme-primary { background-color: var(--admin-primary); color: white; padding: 0.35em 0.65em; border-radius: 0.25rem; font-weight: 500; }
        .badge-dark-theme-secondary { background-color: var(--admin-secondary); color: white; padding: 0.35em 0.65em; border-radius: 0.25rem; font-weight: 500; }
        .badge-dark-theme-dark { background-color: var(--admin-dark); color: white; padding: 0.35em 0.65em; border-radius: 0.25rem; font-weight: 500; }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="sidebar-toggle d-md-none" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar p-0" id="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-shield-alt"></i> Admin Panel
        </div>
        <nav class="nav flex-column mt-3">
            <a class="nav-link <?php echo $this->uri->segment(2) == 'dashboard' ? 'active' : ''; ?>" href="<?php echo site_url('admin/dashboard'); ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
            <div class="px-3 py-2" style="color: var(--sidebar-text-muted); font-size: 0.85rem; text-transform: uppercase;">Management</div>
            <a class="nav-link <?php echo $this->uri->segment(2) == 'users' ? 'active' : ''; ?>" href="<?php echo site_url('admin/users'); ?>">
                <i class="fas fa-users"></i> Pengguna
            </a>
            <a class="nav-link <?php echo $this->uri->segment(2) == 'workshops' ? 'active' : ''; ?>" href="<?php echo site_url('admin/workshops'); ?>">
                <i class="fas fa-store"></i> Bengkel
            </a>
            <a class="nav-link <?php echo $this->uri->segment(2) == 'pending_verification' ? 'active' : ''; ?>" href="<?php echo site_url('admin/pending_verification'); ?>">
                <i class="fas fa-clock"></i> Verifikasi Bengkel
            </a>
            <a class="nav-link <?php echo $this->uri->segment(2) == 'review_moderation' ? 'active' : ''; ?>" href="<?php echo site_url('admin/review_moderation'); ?>">
                <i class="fas fa-star"></i> Moderasi Review
            </a>
            <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
            <div class="px-3 py-2" style="color: var(--sidebar-text-muted); font-size: 0.85rem; text-transform: uppercase;">System</div>
            <a class="nav-link <?php echo $this->uri->segment(2) == 'activity_logs' ? 'active' : ''; ?>" href="<?php echo site_url('admin/activity_logs'); ?>">
                <i class="fas fa-history"></i> Activity Logs
            </a>
            <a class="nav-link <?php echo $this->uri->segment(2) == 'settings' ? 'active' : ''; ?>" href="<?php echo site_url('admin/settings'); ?>">
                <i class="fas fa-cog"></i> Pengaturan
            </a>
            <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
            <div class="px-3 py-2" style="color: var(--sidebar-text-muted); font-size: 0.85rem; text-transform: uppercase;">Road Graph</div>
            <a class="nav-link <?php echo ($this->uri->segment(2) == 'road_graph' && $this->uri->segment(3) == '') ? 'active' : ''; ?>" href="<?php echo site_url('admin/road_graph'); ?>">
                <i class="fas fa-road"></i> Dashboard
            </a>
            <a class="nav-link <?php echo $this->uri->segment(3) == 'nodes' ? 'active' : ''; ?>" href="<?php echo site_url('admin/road_graph/nodes'); ?>">
                <i class="fas fa-map-marker-alt"></i> Nodes
            </a>
            <a class="nav-link <?php echo $this->uri->segment(3) == 'edges' ? 'active' : ''; ?>" href="<?php echo site_url('admin/road_graph/edges'); ?>">
                <i class="fas fa-exchange-alt"></i> Edges
            </a>
            <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
            <div class="px-3 py-2" style="color: var(--sidebar-text-muted); font-size: 0.85rem; text-transform: uppercase;">Reports</div>
            <a class="nav-link <?php echo $this->uri->segment(2) == 'report' ? 'active' : ''; ?>" href="<?php echo site_url('admin/report'); ?>">
                <i class="fas fa-chart-bar"></i> Laporan
            </a>
            <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
            <a class="nav-link text-danger" href="<?php echo site_url('auth/logout'); ?>">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="container-fluid">
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
                            <i class="fas fa-user-shield"></i> <?php echo e($current_user->full_name ?? 'Admin'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo site_url('admin/settings'); ?>"><i class="fas fa-cog"></i> Pengaturan</a></li>
                            <li><a class="dropdown-item" href="<?php echo site_url('user/change_password'); ?>"><i class="fas fa-key"></i> Ubah Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo site_url('auth/logout'); ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Flash Messages with SweetAlert -->
                <?php if ($this->session->flashdata('success')): ?>
                    <script>
                        $(document).ready(function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: '<?php echo addslashes($this->session->flashdata('success')); ?>',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        });
                    </script>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <script>
                        $(document).ready(function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: '<?php echo addslashes($this->session->flashdata('error')); ?>',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000,
                                timerProgressBar: true
                            });
                        });
                    </script>
                <?php endif; ?>
                <?php if ($this->session->flashdata('warning')): ?>
                    <script>
                        $(document).ready(function() {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Peringatan!',
                                text: '<?php echo addslashes($this->session->flashdata('warning')); ?>',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                        });
                    </script>
                <?php endif; ?>
                <?php if ($this->session->flashdata('info')): ?>
                    <script>
                        $(document).ready(function() {
                            Swal.fire({
                                icon: 'info',
                                title: 'Info',
                                text: '<?php echo addslashes($this->session->flashdata('info')); ?>',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                        });
                    </script>
                <?php endif; ?>

                <!-- Main Content Area -->
                <?php echo $content_for_layout ?? ''; ?>
            </div><!-- /.container-fluid -->
        </div><!-- /#mainContent -->
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

        // Confirmation dialog for delete action
        function confirmDelete(url, title, message) {
            Swal.fire({
                title: title || 'Konfirmasi Hapus',
                text: message || 'Apakah Anda yakin ingin menghapus data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    
                    // Add CSRF token
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = csrfTokenName;
                    csrfInput.value = csrfTokenHash;
                    form.appendChild(csrfInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Confirmation dialog for general actions
        function confirmAction(url, title, message, confirmText, confirmColor) {
            Swal.fire({
                title: title || 'Konfirmasi Aksi',
                text: message || 'Apakah Anda yakin ingin melanjutkan?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: confirmColor || '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText || 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>
</body>
</html>
