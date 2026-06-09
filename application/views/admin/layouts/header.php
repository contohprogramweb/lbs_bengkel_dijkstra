<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Admin Panel'; ?> - BengkelKU</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png'); ?>">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            /* Dark Theme Colors (Header & Sidebar) */
            --dark-bg-primary: #1a1a2e;
            --dark-bg-secondary: #16213e;
            --dark-bg-tertiary: #0f3460;
            --dark-text-primary: #ffffff;
            --dark-text-secondary: #b8b8d1;
            --dark-border: #2a2a4a;
            
            /* Light Theme Colors (Content) */
            --light-bg: #f5f7fa;
            --light-card-bg: #ffffff;
            --light-text-primary: #2d3748;
            --light-text-secondary: #718096;
            --light-border: #e2e8f0;
            
            /* Accent Colors */
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            color: var(--light-text-primary);
            overflow-x: hidden;
        }
        
        .wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--dark-bg-primary) 0%, var(--dark-bg-secondary) 100%);
            color: var(--dark-text-primary);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid var(--dark-border);
            background-color: rgba(0, 0, 0, 0.2);
        }
        
        .sidebar-brand h3 {
            color: var(--dark-text-primary);
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
        }
        
        .sidebar-brand h3 i {
            color: var(--primary);
            margin-right: 8px;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-menu .menu-header {
            padding: 0.75rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--dark-text-secondary);
            margin-top: 1rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: var(--dark-text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--dark-text-primary);
            border-left-color: var(--primary);
        }
        
        .sidebar-menu a i {
            width: 24px;
            margin-right: 12px;
            font-size: 1.1rem;
        }
        
        .main-content {
            flex: 1;
            margin-left: 260px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .header {
            background: linear-gradient(135deg, var(--dark-bg-primary) 0%, var(--dark-bg-secondary) 100%);
            color: var(--dark-text-primary);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .header-toggle {
            background: none;
            border: none;
            color: var(--dark-text-primary);
            font-size: 1.5rem;
            cursor: pointer;
            margin-right: 1rem;
        }
        
        .header-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--info));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--dark-text-primary);
        }
        
        .user-role {
            font-size: 0.8rem;
            color: var(--dark-text-secondary);
        }
        
        .content-area {
            flex: 1;
            padding: 2rem;
            background-color: var(--light-bg);
        }
        
        .card {
            background-color: var(--light-card-bg);
            border: 1px solid var(--light-border);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--light-border);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: var(--light-text-primary);
        }
        
        .stat-card {
            background: var(--light-card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--light-border);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        
        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-card-icon.primary { background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(79, 70, 229, 0.2)); color: var(--primary); }
        .stat-card-icon.success { background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.2)); color: var(--success); }
        .stat-card-icon.warning { background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.2)); color: var(--warning); }
        .stat-card-icon.danger { background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.2)); color: var(--danger); }
        
        .stat-card-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--light-text-primary);
        }
        
        .stat-card-label {
            font-size: 0.9rem;
            color: var(--light-text-secondary);
        }
        
        .table thead th {
            background-color: #f8fafc;
            color: var(--light-text-primary);
            font-weight: 600;
            text-transform: uppercase;
            border-bottom: 2px solid var(--light-border);
        }
        
        .table tbody td {
            color: var(--light-text-primary);
            border-bottom: 1px solid var(--light-border);
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .badge.bg-primary { background-color: rgba(79, 70, 229, 0.1) !important; color: var(--primary); }
        .badge.bg-success { background-color: rgba(16, 185, 129, 0.1) !important; color: var(--success); }
        .badge.bg-warning { background-color: rgba(245, 158, 11, 0.1) !important; color: var(--warning); }
        .badge.bg-danger { background-color: rgba(239, 68, 68, 0.1) !important; color: var(--danger); }
        
        .form-control, .form-select {
            border: 1px solid var(--light-border);
            border-radius: 8px;
            color: var(--light-text-primary);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .footer {
            background-color: var(--light-card-bg);
            border-top: 1px solid var(--light-border);
            padding: 1.5rem;
            text-align: center;
            color: var(--light-text-secondary);
        }
        
        @media (max-width: 992px) {
            .sidebar { margin-left: -260px; }
            .sidebar.active { margin-left: 0; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <h3><i class="fas fa-wrench"></i> BengkelKU Admin</h3>
            </div>
            <div class="sidebar-menu">
                <div class="menu-header">Main Menu</div>
                <a href="<?= site_url('admin/dashboard'); ?>" class="<?= ($this->uri->segment(2) == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i><span>Dashboard</span>
                </a>
                
                <div class="menu-header">Management</div>
                <a href="<?= site_url('admin/users'); ?>" class="<?= ($this->uri->segment(2) == 'users') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i><span>Pengguna</span>
                </a>
                <a href="<?= site_url('admin/workshops'); ?>" class="<?= ($this->uri->segment(2) == 'workshops') ? 'active' : ''; ?>">
                    <i class="fas fa-store"></i><span>Bengkel</span>
                </a>
                <a href="<?= site_url('admin/pending_verification'); ?>" class="<?= ($this->uri->segment(2) == 'pending_verification') ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i><span>Verifikasi Bengkel</span>
                </a>
                <a href="<?= site_url('admin/review_moderation'); ?>" class="<?= ($this->uri->segment(2) == 'review_moderation') ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i><span>Moderasi Review</span>
                </a>
                
                <div class="menu-header">System</div>
                <a href="<?= site_url('admin/activity_logs'); ?>" class="<?= ($this->uri->segment(2) == 'activity_logs') ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i><span>Activity Logs</span>
                </a>
                <a href="<?= site_url('admin/settings'); ?>" class="<?= ($this->uri->segment(2) == 'settings') ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i><span>Pengaturan</span>
                </a>
                
                <div class="menu-header">Road Graph</div>
                <a href="<?= site_url('admin/road_graph'); ?>" class="<?= ($this->uri->segment(2) == 'road_graph') ? 'active' : ''; ?>">
                    <i class="fas fa-road"></i><span>Dashboard</span>
                </a>
                <a href="<?= site_url('admin/road_graph/nodes'); ?>" class="<?= ($this->uri->segment(3) == 'nodes') ? 'active' : ''; ?>">
                    <i class="fas fa-map-marker-alt"></i><span>Nodes</span>
                </a>
                <a href="<?= site_url('admin/road_graph/edges'); ?>" class="<?= ($this->uri->segment(3) == 'edges') ? 'active' : ''; ?>">
                    <i class="fas fa-exchange-alt"></i><span>Edges</span>
                </a>
                
                <div class="menu-header">Reports</div>
                <a href="<?= site_url('admin/report'); ?>" class="<?= ($this->uri->segment(2) == 'report') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i><span>Laporan</span>
                </a>
                
                <div class="menu-header">Account</div>
                <a href="<?= site_url('auth/logout'); ?>">
                    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                </a>
            </div>
        </nav>

        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <button class="header-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                    <h1 class="header-title"><?= $page_title ?? 'Admin Panel'; ?></h1>
                </div>
                <div class="header-right">
                    <div class="user-profile">
                        <div class="user-avatar"><?= strtoupper(substr($this->session->userdata('username') ?? 'A', 0, 1)); ?></div>
                        <div>
                            <div class="user-name"><?= $this->session->userdata('username') ?? 'Admin'; ?></div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-area">
