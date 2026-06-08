<?php $this->load->view('admin/layouts/header'); ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-file-alt"></i> <?php echo $page_title; ?></h2>
            <p class="text-muted">Laporan transaksi untuk bengkel: <?php echo $workshop->name; ?></p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="<?php echo site_url('report/workshop_detail/' . $workshop->id); ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Sudah Dibayar</option>
                        <option value="unpaid" <?php echo $status_filter === 'unpaid' ? 'selected' : ''; ?>>Belum Dibayar</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="<?php echo site_url('admin/report/global'); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Invoice</span>
                    <span class="info-box-number"><?php echo isset($summary['total_invoices']) ? $summary['total_invoices'] : 0; ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Omzet</span>
                    <span class="info-box-number">Rp <?php echo number_format(isset($summary['gross_revenue']) ? $summary['gross_revenue'] : 0, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Sudah Dibayar</span>
                    <span class="info-box-number">Rp <?php echo number_format(isset($summary['paid_revenue']) ? $summary['paid_revenue'] : 0, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Belum Dibayar</span>
                    <span class="info-box-number">Rp <?php echo number_format(isset($summary['unpaid_revenue']) ? $summary['unpaid_revenue'] : 0, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Detail Transaksi</h5>
        </div>
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Tidak ada transaksi untuk periode yang dipilih.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>No. Invoice</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Layanan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $trx): ?>
                                <tr>
                                    <td><?php echo $trx['invoice_number']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($trx['created_at'])); ?></td>
                                    <td><?php echo $trx['customer_name']; ?></td>
                                    <td><?php echo $trx['service_name']; ?></td>
                                    <td>Rp <?php echo number_format($trx['total_amount'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php if ($trx['payment_status'] === 'paid'): ?>
                                            <span class="badge bg-success">Sudah Dibayar</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Belum Dibayar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url('order/detail/' . $trx['booking_id']); ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $this->load->view('admin/layouts/footer'); ?>
