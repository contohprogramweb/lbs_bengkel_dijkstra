<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #212529; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 15px; display: block; transition: all 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #343a40; color: #fff; border-left: 3px solid #0d6efd; }
        .sidebar-sub { padding-left: 30px !important; font-size: 0.9em; }
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

            <div class="col-md-10 p-4">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2><i class="fas fa-circle"></i> <?php echo $page_title; ?></h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/road_graph'); ?>">Road Graph</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/road_graph/nodes'); ?>">Nodes</a></li>
                                <li class="breadcrumb-item active"><?php echo isset($node) ? 'Edit' : 'Tambah'; ?> Simpul</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo isset($node) ? 'Edit Simpul' : 'Tambah Simpul Baru'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Nama Simpul <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required 
                                       value="<?php echo isset($node) ? htmlspecialchars($node->name) : set_value('name'); ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Latitude <span class="text-danger">*</span></label>
                                    <input type="number" step="0.000001" name="latitude" class="form-control" required
                                           value="<?php echo isset($node) ? $node->latitude : set_value('latitude'); ?>">
                                    <small class="text-muted">Contoh: -6.200000</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Longitude <span class="text-danger">*</span></label>
                                    <input type="number" step="0.000001" name="longitude" class="form-control" required
                                           value="<?php echo isset($node) ? $node->longitude : set_value('longitude'); ?>">
                                    <small class="text-muted">Contoh: 106.816666</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipe Simpul</label>
                                <select name="node_type" class="form-select">
                                    <option value="intersection" <?php echo (isset($node) && $node->node_type == 'intersection') ? 'selected' : ''; ?>>Persimpangan</option>
                                    <option value="landmark" <?php echo (isset($node) && $node->node_type == 'landmark') ? 'selected' : ''; ?>>Landmark</option>
                                    <option value="custom" <?php echo (isset($node) && $node->node_type == 'custom') ? 'selected' : ''; ?>>Custom</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="3"><?php echo isset($node) ? htmlspecialchars($node->description) : ''; ?></textarea>
                            </div>
                            <?php if (isset($node)): ?>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1"
                                       <?php echo $node->is_active ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                                <a href="<?php echo site_url('admin/road_graph/nodes'); ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div><!-- /.col-md-10 -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
