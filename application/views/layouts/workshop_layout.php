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
            --sidebar-dark-bg: #1a1a2e;
            --sidebar-dark-hover: #16213e;
            --sidebar-dark-active: #0f3460;
            --sidebar-text: #e0e0e0;
            --sidebar-text-muted: #a0a0a0;
            --header-dark-bg: #121212;
            --content-light-bg: #f8f9fa;
            --border-color: #dee2e6;
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
        }
        .main-content.expanded {
            margin-left: 0;
        }
        .card-stat { 
            border-radius: 10px; 
            border: none; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.08); 
            transition: transform 0.2s;
            background: #fff;
        }
        .card-stat:hover { transform: translateY(-3px); }
        .card-stat .icon { font-size: 2.5rem; opacity: 0.3; }
        
        /* Card styling for light content */
        .card {
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
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
            }
            .sidebar-toggle {
                display: block !important;
            }
            .card-stat { margin-bottom: 15px; }
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
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid var(--border-color);
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

        // Confirmation dialog for general action
        function confirmAction(url, title, message, confirmText, confirmColor) {
            Swal.fire({
                title: title || 'Konfirmasi',
                text: message || 'Apakah Anda yakin?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: confirmColor || '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText || 'Ya, Lanjutkan',
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