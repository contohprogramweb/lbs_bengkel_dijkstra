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
        <h2 class="mb-4"><i class="fas fa-project-diagram"></i> <?php echo $page_title; ?></h2>

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

        <div class="mt-3">
            <a href="<?php echo site_url('admin/dashboard'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard Admin
            </a>
        </div>
    </div>
</body>
</html>
