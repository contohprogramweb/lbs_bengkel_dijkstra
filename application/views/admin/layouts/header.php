<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?> - <?php echo isset($app_name) ? $app_name : 'Bengkel Terdekat'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .sidebar { 
            min-height: calc(100vh - 56px); 
            background-color: #343a40; 
            position: fixed;
            top: 56px;
            left: 0;
            width: 250px;
            overflow-y: auto;
        }
        .sidebar a { 
            color: #fff; 
            text-decoration: none; 
            padding: 10px 15px; 
            display: block; 
            border-left: 3px solid transparent;
        }
        .sidebar a:hover, .sidebar a.active { 
            background-color: #495057; 
            border-left-color: #007bff;
        }
        .sidebar i { 
            width: 25px; 
            margin-right: 10px;
        }
        .sidebar .nav-header {
            color: #adb5bd;
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 10px 15px;
            margin-top: 15px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card { 
            border-radius: 10px; 
            transition: transform 0.2s; 
        }
        .stat-card:hover { 
            transform: translateY(-5px); 
        }
        .chart-container { 
            position: relative; 
            height: 300px; 
        }
        .info-box {
            display: flex;
            align-items: stretch;
            border-radius: 0.25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            margin-bottom: 1rem;
            min-height: 80px;
            position: relative;
        }
        .info-box-icon {
            border-radius: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            padding: 0.5rem 1rem;
        }
        .info-box-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.8;
            padding: 0.5rem 1rem;
            overflow: hidden;
        }
        .info-box-text {
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .info-box-number {
            display: block;
            font-weight: 700;
            font-size: 1.25rem;
        }
        .bg-primary { background-color: #007bff !important; color: white !important; }
        .bg-success { background-color: #28a745 !important; color: white !important; }
        .bg-warning { background-color: #ffc107 !important; color: #1f2d3d !important; }
        .bg-info { background-color: #17a2b8 !important; color: white !important; }
        .bg-dark { background-color: #343a40 !important; color: white !important; }
        .bg-danger { background-color: #dc3545 !important; color: white !important; }
        .progress { background-color: #e9ecef; }
        .navbar-brand { font-weight: bold; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <span class="navbar-brand"><i class="fas fa-shield-alt"></i> Admin Panel</span>
            <div class="d-flex align-items-center">
                <span class="text-light me-3"><?php echo isset($current_user->full_name) ? $current_user->full_name : 'Admin'; ?></span>
                <a href="<?php echo site_url('auth/logout'); ?>" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <?php $this->load->view('admin/_sidebar'); ?>

            <!-- Main Content -->
            <div class="col-md-10 offset-md-2 main-content">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $this->session->flashdata('warning'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('info')): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle"></i> <?php echo $this->session->flashdata('info'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
