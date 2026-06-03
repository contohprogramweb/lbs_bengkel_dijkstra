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
            <h2><i class="fas fa-road"></i> <?php echo $page_title; ?></h2>
            <a href="<?php echo site_url('admin/road_graph/create_edge'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Edge
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
                            <th>Nama Jalan</th>
                            <th>Dari Simpul</th>
                            <th>Ke Simpul</th>
                            <th>Jarak (km)</th>
                            <th>Arah</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($edges)): ?>
                            <tr><td colspan="8" class="text-center">Belum ada data edge.</td></tr>
                        <?php else: ?>
                            <?php foreach ($edges as $edge): ?>
                                <tr>
                                    <td><?php echo $edge->id; ?></td>
                                    <td><?php echo htmlspecialchars($edge->road_name ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($edge->from_node_name ?: 'Node '.$edge->from_node_id); ?></td>
                                    <td><?php echo htmlspecialchars($edge->to_node_name ?: 'Node '.$edge->to_node_id); ?></td>
                                    <td><?php echo number_format($edge->distance_km, 2); ?></td>
                                    <td>
                                        <?php if ($edge->is_bidirectional): ?>
                                            <span class="badge bg-info">Dua Arah</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Satu Arah</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($edge->is_active): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url('admin/road_graph/edit_edge/'.$edge->id); ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="<?php echo site_url('admin/road_graph/delete_edge/'.$edge->id); ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Hapus edge ini?')">
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
