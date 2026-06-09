
<div class="container-fluid">
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

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon"><i class="fas fa-circle"></i></div>
                <div class="stat-content">
                    <span class="stat-text">Total Simpul (Nodes)</span>
                    <span class="stat-number"><?php echo $stats['total_nodes']; ?></span>
                </div>
                <a href="<?php echo site_url('admin/road_graph/nodes'); ?>" class="btn btn-light-dark btn-sm mt-3">
                    Kelola Simpul
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-success">
                <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
                <div class="stat-content">
                    <span class="stat-text">Total Edge</span>
                    <span class="stat-number"><?php echo $stats['total_edges']; ?></span>
                </div>
                <a href="<?php echo site_url('admin/road_graph/edges'); ?>" class="btn btn-light-dark btn-sm mt-3">
                    Kelola Edge
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-info">
                <div class="stat-icon"><i class="fas fa-map"></i></div>
                <div class="stat-content">
                    <span class="stat-text">Peta Graf</span>
                    <span class="stat-text-small">Graf jalan untuk algoritma Dijkstra</span>
                </div>
                <small class="text-muted mt-2 d-block">Gunakan panel admin untuk mengelola simpul dan edge jalan.</small>
            </div>
        </div>
    </div>

    <div class="card card-dark-theme">
        <div class="card-header">
            <h5 class="mb-0">Panduan Penggunaan</h5>
        </div>
        <div class="card-body">
            <ol class="text-light">
                <li><strong>Nodes (Simpul):</strong> Merepresentasikan persimpangan atau titik penting di jalan.</li>
                <li><strong>Edges:</strong> Merepresentasikan segmen jalan yang menghubungkan dua simpul dengan bobot jarak.</li>
                <li><strong>Algoritma Dijkstra:</strong> Digunakan untuk menghitung rute terpendek dari lokasi user ke bengkel.</li>
                <li>Tambahkan nodes terlebih dahulu, kemudian hubungkan dengan edges untuk membangun graf jalan.</li>
            </ol>
        </div>
    </div>
</div>

