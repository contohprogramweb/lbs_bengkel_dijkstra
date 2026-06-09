
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-road"></i> <?php echo $page_title; ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/road_graph'); ?>">Road Graph</a></li>
                    <li class="breadcrumb-item active">Edges</li>
                </ol>
            </nav>
        </div>
        <a href="<?php echo site_url('admin/road_graph/create_edge'); ?>" class="btn btn-primary-dark">
            <i class="fas fa-plus"></i> Tambah Edge
        </a>
    </div>

    <div class="card card-dark-theme">
        <div class="card-body">
            <table id="edgesTable" class="table table-striped table-hover table-dark-theme">
                <thead>
                    <tr>
                        <th width="5%">No</th>
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
                        <?php $no = 1; foreach ($edges as $edge): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($edge->road_name ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($edge->from_node_name ?: 'Node '.$edge->from_node_id); ?></td>
                                <td><?php echo htmlspecialchars($edge->to_node_name ?: 'Node '.$edge->to_node_id); ?></td>
                                <td><?php echo number_format($edge->distance_km, 2); ?></td>
                                <td>
                                    <?php if ($edge->is_bidirectional): ?>
                                        <span class="badge badge-dark-theme-info">Dua Arah</span>
                                    <?php else: ?>
                                        <span class="badge badge-dark-theme-warning">Satu Arah</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($edge->is_active): ?>
                                        <span class="badge badge-dark-theme-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-dark-theme-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo site_url('admin/road_graph/edit_edge/'.$edge->id); ?>" class="btn btn-sm btn-warning-dark">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button onclick="confirmDelete('<?php echo site_url('admin/road_graph/delete_edge/'.$edge->id); ?>', 'Hapus Edge', 'Hapus edge ini?')"
                                            class="btn btn-sm btn-danger-dark">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?php echo site_url('admin/road_graph'); ?>" class="btn btn-secondary-dark">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard Road Graph
        </a>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#edgesTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });
});
</script>

