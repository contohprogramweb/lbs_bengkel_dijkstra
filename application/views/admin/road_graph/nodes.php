<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-project-diagram"></i> <?php echo $page_title; ?></h2>
            <a href="<?php echo site_url('admin/road_graph/create_node'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Simpul
            </a>
        </div>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped table-hover">
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
                                           onclick="return confirm('Hapus simpul ini?')">
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
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</body>
</html>
