<?php $this->load->view('admin/layouts/header'); ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-chart-line"></i> Laporan Global</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Laporan Global</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-chart-line"></i> Ringkasan Global (<?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?>)</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Invoice</span>
                                    <span class="info-box-number"><?= $summary['total_invoices'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sudah Dibayar</span>
                                    <span class="info-box-number">Rp <?= number_format($summary['paid_revenue'] ?? 0, 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Belum Dibayar</span>
                                    <span class="info-box-number">Rp <?= number_format($summary['unpaid_revenue'] ?? 0, 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Omzet</span>
                                    <span class="info-box-number">Rp <?= number_format($summary['gross_revenue'] ?? 0, 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <div class="info-box bg-dark">
                                <span class="info-box-icon"><i class="fas fa-store"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Bengkel Aktif</span>
                                    <span class="info-box-number"><?= $summary['active_workshops'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="get" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Filter Bengkel</label>
                            <select name="workshop_id" class="form-select">
                                <option value="">Semua Bengkel</option>
                                <?php foreach ($workshops as $ws): ?>
                                <option value="<?= $ws['id'] ?>" <?= $workshop_filter == $ws['id'] ? 'selected' : '' ?>>
                                    <?= esc($ws['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                            <a href="<?= site_url('admin/report/export_global_csv?start_date=' . $start_date . '&end_date=' . $end_date . '&workshop_id=' . $workshop_filter) ?>" class="btn btn-success">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Workshop Breakdown Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-store"></i> Rincian Per Bengkel</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Bengkel</th>
                                    <th class="text-center">Total Invoice</th>
                                    <th class="text-right">Total Omzet</th>
                                    <th class="text-right">Sudah Dibayar</th>
                                    <th class="text-right">% Terbayar</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($by_workshop)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Belum ada data transaksi</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php 
                                    $no = 1;
                                    $grand_total = 0;
                                    $grand_paid = 0;
                                    ?>
                                    <?php foreach ($by_workshop as $ws): ?>
                                    <?php 
                                    $grand_total += $ws['gross_revenue'];
                                    $grand_paid += $ws['paid_revenue'];
                                    $percent = $ws['gross_revenue'] > 0 ? ($ws['paid_revenue'] / $ws['gross_revenue']) * 100 : 0;
                                    ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= esc($ws['workshop_name']) ?></strong></td>
                                    <td class="text-center"><?= $ws['total_invoices'] ?></td>
                                    <td class="text-right">Rp <?= number_format($ws['gross_revenue'], 0, ',', '.') ?></td>
                                    <td class="text-right">Rp <?= number_format($ws['paid_revenue'], 0, ',', '.') ?></td>
                                    <td class="text-right">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: <?= $percent ?>%;" 
                                                 aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?= number_format($percent, 1) ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('admin/report/workshop_detail/' . $ws['workshop_id'] . '?start_date=' . $start_date . '&end_date=' . $end_date) ?>" 
                                           class="btn btn-sm btn-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                    <?php endforeach; ?>
                                <!-- Grand Total Row -->
                                <tr class="table-secondary">
                                    <td colspan="2" class="text-right"><strong>TOTAL SEMUA BENGKEL</strong></td>
                                    <td class="text-center"><strong><?= array_sum(array_column($by_workshop, 'total_invoices')) ?></strong></td>
                                    <td class="text-right"><strong>Rp <?= number_format($grand_total, 0, ',', '.') ?></strong></td>
                                    <td class="text-right"><strong>Rp <?= number_format($grand_paid, 0, ',', '.') ?></strong></td>
                                    <td class="text-right">
                                        <?php 
                                        $grand_percent = $grand_total > 0 ? ($grand_paid / $grand_total) * 100 : 0;
                                        ?>
                                        <strong><?= number_format($grand_percent, 1) ?>%</strong>
                                    </td>
                                    <td></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-box {
    border-radius: 0.25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    display: flex;
    margin-bottom: 1rem;
    min-height: 80px;
    padding: .5rem;
    position: relative;
}
.info-box-icon {
    align-items: center;
    background-color: rgba(255,255,255,0.2);
    display: flex;
    font-size: 1.875rem;
    justify-content: center;
    width: 70px;
    border-radius: 0.25rem;
}
.info-box-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.8;
    flex: 1;
    padding: 0 10px;
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
.bg-primary { background-color: #007bff !important; color: white; }
.bg-success { background-color: #28a745 !important; color: white; }
.bg-warning { background-color: #ffc107 !important; color: #1f2d3d; }
.bg-info { background-color: #17a2b8 !important; color: white; }
.bg-dark { background-color: #343a40 !important; color: white; }
.progress { background-color: #e9ecef; }
</style>

<?php $this->load->view('admin/layouts/footer'); ?>
