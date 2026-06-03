<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-chart-line"></i> Ringkasan Pemasukan</h5>
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
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Daftar Invoice</h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="get" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control" value="<?= $filters['start_date'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control" value="<?= $filters['end_date'] ?>">
                            </div>
                            <div class="col-md-2">
                                <label>Status</label>
                                <select name="payment_status" class="form-control">
                                    <option value="">Semua</option>
                                    <option value="unpaid" <?= $filters['payment_status'] == 'unpaid' ? 'selected' : '' ?>>Belum Dibayar</option>
                                    <option value="paid" <?= $filters['payment_status'] == 'paid' ? 'selected' : '' ?>>Lunas</option>
                                    <option value="partial" <?= $filters['payment_status'] == 'partial' ? 'selected' : '' ?>>Sebagian</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Cari</label>
                                <input type="text" name="search" class="form-control" placeholder="No. Invoice / Booking / Customer" value="<?= $filters['search'] ?>">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>

                    <!-- Export Button -->
                    <div class="mb-3 text-right">
                        <a href="<?= site_url('workshop/billing/export_csv?start_date=' . $filters['start_date'] . '&end_date=' . $filters['end_date']) ?>" class="btn btn-success">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </a>
                        <a href="<?= site_url('workshop/billing/report?start_date=' . $filters['start_date'] . '&end_date=' . $filters['end_date']) ?>" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> Lihat Laporan Detail
                        </a>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Tanggal</th>
                                    <th>No. Booking</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Dibayar</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($invoices)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Belum ada invoice</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td><strong><?= esc($invoice['invoice_number']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($invoice['issue_date'])) ?></td>
                                    <td><?= esc($invoice['booking_number']) ?></td>
                                    <td><?= esc($invoice['customer_name']) ?></td>
                                    <td>Rp <?= number_format($invoice['total_amount'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($invoice['paid_amount'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php
                                        $badge_class = [
                                            'unpaid' => 'danger',
                                            'paid' => 'success',
                                            'partial' => 'warning'
                                        ];
                                        $status_label = [
                                            'unpaid' => 'Belum Dibayar',
                                            'paid' => 'Lunas',
                                            'partial' => 'Sebagian'
                                        ];
                                        ?>
                                        <span class="badge badge-<?= $badge_class[$invoice['payment_status']] ?>">
                                            <?= $status_label[$invoice['payment_status']] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('workshop/billing/view/' . $invoice['id']) ?>" class="btn btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= site_url('workshop/billing/print_invoice/' . $invoice['id']) ?>" class="btn btn-secondary" title="Cetak PDF" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <?php if ($invoice['payment_status'] !== 'paid'): ?>
                                            <a href="<?= site_url('workshop/billing/record_payment/' . $invoice['id']) ?>" class="btn btn-success" title="Catat Pembayaran">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1])) ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1])) ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
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
</style>
