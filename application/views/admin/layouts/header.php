<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?> - <?php echo isset($app_name) ? $app_name : 'Bengkel Terdekat'; ?></title>
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-bg: #1a1a1a;
            --sidebar-hover: #2d2d2d;
            --sidebar-active: #3d3d3d;
            --navbar-bg: #000000;
            --main-bg: #121212;
            --card-bg: #1e1e1e;
            --text-primary: #e0e0e0;
            --text-secondary: #a0a0a0;
            --border-color: #333333;
        }
        
        body { background-color: var(--main-bg); color: var(--text-primary); }
        .navbar { background-color: var(--navbar-bg) !important; border-bottom: 1px solid var(--border-color); }
        .sidebar { min-height: calc(100vh - 56px); background-color: var(--sidebar-bg); position: fixed; top: 56px; left: 0; width: 260px; overflow-y: auto; border-right: 1px solid var(--border-color); z-index: 100; }
        .sidebar a { color: var(--text-secondary); text-decoration: none; padding: 12px 20px; display: block; transition: all 0.3s ease; border-left: 3px solid transparent; }
        .sidebar a:hover { background-color: var(--sidebar-hover); color: var(--text-primary); }
        .sidebar a.active { background-color: var(--sidebar-active); color: #fff; border-left-color: #0d6efd; }
        .sidebar i { width: 25px; margin-right: 10px; text-align: center; }
        .sidebar .nav-header { color: #6c757d; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; padding: 15px 20px 10px; margin-top: 10px; font-weight: 600; }
        .sidebar-sub { padding-left: 45px !important; font-size: 0.9em; }
        .sidebar-dropdown .dropdown-toggle::after { display: none; }
        .main-content { margin-left: 260px; padding: 24px; min-height: calc(100vh - 56px); }
        .card { background-color: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); margin-bottom: 24px; }
        .card-header { background-color: rgba(255,255,255,0.03); border-bottom: 1px solid var(--border-color); padding: 16px 20px; font-weight: 600; }
        .table { color: var(--text-primary); }
        .table thead th { background-color: rgba(255,255,255,0.05); border-bottom: 2px solid var(--border-color); color: var(--text-primary); }
        .table tbody tr { border-bottom: 1px solid var(--border-color); }
        .table tbody tr:hover { background-color: rgba(255,255,255,0.02); }
        .form-control, .form-select { background-color: #2d2d2d; border: 1px solid var(--border-color); color: var(--text-primary); }
        .form-control:focus, .form-select:focus { background-color: #333333; border-color: #0d6efd; color: var(--text-primary); }
        .form-label { color: var(--text-secondary); font-weight: 500; }
        .breadcrumb-item a { color: var(--text-secondary); text-decoration: none; }
        .breadcrumb-item a:hover { color: #0d6efd; }
        .stat-card { border-radius: 10px; transition: transform 0.2s, box-shadow 0.2s; border: none; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
        .modal-content { background-color: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-primary); }
        .modal-header { border-bottom: 1px solid var(--border-color); }
        .modal-footer { border-top: 1px solid var(--border-color); }
        .btn-close { filter: invert(1); }
        .info-box { display: flex; align-items: stretch; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); margin-bottom: 1rem; min-height: 80px; }
        .info-box-icon { display: flex; align-items: center; justify-content: center; font-size: 2rem; padding: 0.5rem 1.5rem; min-width: 80px; }
        .info-box-content { display: flex; flex-direction: column; justify-content: center; line-height: 1.6; padding: 0.75rem 1rem; flex: 1; }
        .bg-dark-theme-primary { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white; }
        .bg-dark-theme-success { background: linear-gradient(135deg, #198754 0%, #146c43 100%); color: white; }
        .bg-dark-theme-warning { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #1a1a1a; }
        .bg-dark-theme-info { background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); color: white; }
        .bg-dark-theme-danger { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); color: white; }
        .bg-dark-theme-dark { background: linear-gradient(135deg, #343a40 0%, #23272b 100%); color: white; }
        .progress { background-color: #2d2d2d; }
        @media (max-width: 768px) { .sidebar { width: 100%; height: auto; position: relative; top: 0; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark fixed-top">
        <div class="container-fluid">
            <span class="navbar-brand"><i class="fas fa-shield-alt"></i> Admin Panel</span>
            <div class="d-flex align-items-center">
                <span class="text-light me-3"><?php echo isset($current_user->full_name) ? $current_user->full_name : (isset($user->full_name) ? $user->full_name : 'Admin'); ?></span>
                <a href="<?php echo site_url('auth/logout'); ?>" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <?php $this->load->view('admin/_sidebar'); ?>
            <div class="col-md-10 offset-md-2 main-content">
