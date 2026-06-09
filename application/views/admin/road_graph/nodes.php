
<div class="container-fluid">
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
        <a href="<?php echo site_url('admin/road_graph/create_node'); ?>" class="btn btn-primary-dark">
            <i class="fas fa-plus"></i> Tambah Simpul
        </a>
    </div>

    <div class="card card-dark-theme">
        <div class="card-body">
            <table id="nodesTable" class="table table-striped table-hover table-dark-theme">
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
                                <td><span class="badge badge-dark-theme-info"><?php echo $node->node_type; ?></span></td>
                                <td><?php echo number_format($node->latitude, 6); ?></td>
                                <td><?php echo number_format($node->longitude, 6); ?></td>
                                <td>
                                    <?php if ($node->is_active): ?>
                                        <span class="badge badge-dark-theme-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-dark-theme-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo site_url('admin/road_graph/edit_node/'.$node->id); ?>" class="btn btn-sm btn-warning-dark">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button onclick="confirmDelete('<?php echo site_url('admin/road_graph/delete_node/'.$node->id); ?>', 'Hapus Simpul', 'Hapus simpul ini? Pastikan tidak ada edge yang menggunakan simpul ini.')"
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
    $('#nodesTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });
});
</script>

