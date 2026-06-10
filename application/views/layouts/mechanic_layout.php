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
            --sidebar-dark-bg: #2d3436;
            --sidebar-dark-hover: #636e72;
            --sidebar-dark-active: #0984e3;
            --sidebar-text: #dfe6e9;
            --sidebar-text-muted: #b2bec3;
            --header-dark-bg: #2d3436;
            --content-light-bg: #f5f6fa;
            --border-color: #dfe6e9;
            --primary-color: #0984e3;
            --primary-hover: #74b9ff;
            --success-color: #00b894;
            --warning-color: #fdcb6e;
            --info-color: #00cec9;
            --danger-color: #d63031;
        }
        body {
            background-color: var(--content-light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background-color: var(--sidebar-dark-bg);
            color: var(--sidebar-text);
            position: fixed;
            width: 250px;
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
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
            border-left-color: var(--primary-color);
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
            max-width: calc(100% - 250px);
        }
        .main-content.expanded {
            margin-left: 0;
            width: 100%;
            max-width: 100%;
        }
        
        /* Stat Cards Styling */
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.2s;
            background: #fff;
            overflow: hidden;
            position: relative;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card-primary { border-left: 4px solid var(--primary-color); }
        .stat-card-warning { border-left: 4px solid var(--warning-color); }
        .stat-card-success { border-left: 4px solid var(--success-color); }
        .stat-card-info { border-left: 4px solid var(--info-color); }
        .text-primary-opacity { color: rgba(9, 132, 227, 0.3); }
        .text-warning-opacity { color: rgba(253, 203, 110, 0.3); }
        .text-success-opacity { color: rgba(0, 184, 148, 0.3); }
        .text-info-opacity { color: rgba(0, 206, 201, 0.3); }
        
        /* Card styling */
        .card {
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            background-color: #fff;
            border-radius: 8px;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
        }

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
                width: 100%;
                max-width: 100%;
            }
            .sidebar-toggle {
                display: block !important;
            }
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
            <i class="fas fa-wrench"></i> Mekanik
        </div>
        <nav class="nav flex-column mt-3">
            <a class="nav-link <?php echo $this->uri->segment(2) == 'dashboard' ? 'active' : ''; ?>" href="<?php echo site_url('mechanic/dashboard'); ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-link <?php echo $this->uri->segment(2) == 'bookings' ? 'active' : ''; ?>" href="<?php echo site_url('mechanic/bookings'); ?>">
                <i class="fas fa-clipboard-list"></i> Order Saya
            </a>
            <a class="nav-link <?php echo $this->uri->segment(2) == 'productivity' ? 'active' : ''; ?>" href="<?php echo site_url('mechanic/productivity'); ?>">
                <i class="fas fa-chart-bar"></i> Produktivitas
            </a>
            <a class="nav-link <?php echo $this->uri->segment(2) == 'profile' ? 'active' : ''; ?>" href="<?php echo site_url('mechanic/profile'); ?>">
                <i class="fas fa-user"></i> Profil Saya
            </a>
            <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
            <a class="nav-link" href="<?php echo site_url('auth/logout'); ?>">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
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
                    <li><a class="dropdown-item" href="<?php echo site_url('mechanic/profile'); ?>"><i class="fas fa-user"></i> Profil Saya</a></li>
                    <li><a class="dropdown-item" href="<?php echo site_url('mechanic/change_password'); ?>"><i class="fas fa-key"></i> Ubah Password</a></li>
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

        <!-- Content -->
        <?php echo $content_for_layout; ?>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('show');
            mainContent.classList.toggle('expanded');
        }

        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>

    <?php if (isset($scripts)) echo $scripts; ?>
</body>
</html>
