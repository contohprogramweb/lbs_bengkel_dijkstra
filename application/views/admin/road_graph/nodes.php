<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-project-diagram"></i> <?php echo $page_title; ?></h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/road_graph'); ?>">Road Graph</a></li>
                                <li class="breadcrumb-item active">Nodes</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="<?php echo site_url('admin/road_graph/create_node'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Simpul
                    </a>
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

                <div class="card">
                    <div class="card-body">
                        <table id="nodesTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($nodes)): ?>
                                    <tr><td colspan="7" class="text-center">Belum ada data simpul.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($nodes as $node): ?>
                                        <tr>
                                            <td><?php echo $node->id; ?></td>
                                            <td><?php echo htmlspecialchars($node->name); ?></td>
                                            <td><span class="badge bg-info"><?php echo $node->node_type; ?></span></td>
                                            <td><?php echo number_format($node->latitude, 6); ?></td>
                                            <td><?php echo number_format($node->longitude, 6); ?></td>
                                            <td>
                                                <?php if ($node->is_active): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo site_url('admin/road_graph/edit_node/'.$node->id); ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="<?php echo site_url('admin/road_graph/delete_node/'.$node->id); ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Hapus simpul ini? Pastikan tidak ada edge yang menggunakan simpul ini.')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="<?php echo site_url('admin/road_graph'); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard Road Graph
                    </a>
                </div>

            </div><!-- /.col-md-10 -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#nodesTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });
        });
    </script>
</body>
</html>
