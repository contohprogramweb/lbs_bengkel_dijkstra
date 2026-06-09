<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { 
            min-height: 100vh; 
            background: #212529; 
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar a { 
            color: #adb5bd; 
            text-decoration: none; 
            padding: 10px 15px; 
            display: block; 
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active { 
            background: #343a40; 
            color: #fff; 
            border-left: 3px solid #0d6efd;
        }
        .sidebar-sub { 
            padding-left: 30px !important; 
            font-size: 0.9em;
        }
        .sidebar-dropdown .dropdown-toggle::after { display: none; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand"><i class="fas fa-shield-alt"></i> Admin Panel</span>
            <div class="d-flex align-items-center">
                <span class="text-light me-3"><?php echo $current_user->full_name ?? ''; ?></span>
                <a href="<?php echo site_url('auth/logout'); ?>" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <?php $this->load->view('admin/_sidebar'); ?>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2><i class="fas fa-project-diagram"></i> <?php echo $page_title; ?></h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Road Graph</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5>Total Simpul (Nodes)</h5>
                                <h2><?php echo $stats['total_nodes']; ?></h2>
                                <a href="<?php echo site_url('admin/road_graph/nodes'); ?>" class="btn btn-light btn-sm mt-2">
                                    Kelola Simpul
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5>Total Edge</h5>
                                <h2><?php echo $stats['total_edges']; ?></h2>
                                <a href="<?php echo site_url('admin/road_graph/edges'); ?>" class="btn btn-light btn-sm mt-2">
                                    Kelola Edge
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5>Peta Graf</h5>
                                <p>Graf jalan untuk algoritma Dijkstra</p>
                                <small>Gunakan panel admin untuk mengelola simpul dan edge jalan.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Panduan Penggunaan</h5>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li><strong>Nodes (Simpul):</strong> Merepresentasikan persimpangan atau titik penting di jalan.</li>
                            <li><strong>Edges:</strong> Merepresentasikan segmen jalan yang menghubungkan dua simpul dengan bobot jarak.</li>
                            <li><strong>Algoritma Dijkstra:</strong> Digunakan untuk menghitung rute terpendek dari lokasi user ke bengkel.</li>
                            <li>Tambahkan nodes terlebih dahulu, kemudian hubungkan dengan edges untuk membangun graf jalan.</li>
                        </ol>
                    </div>
                </div>

            </div><!-- /.col-md-10 -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
