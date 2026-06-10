<?php
/**
 * Workshop Orders Index View
 * Dashboard for workshop owner to manage bookings
 */
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><?= $page_title ?></h2>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-2 col-sm-6">
                    <div class="card stat-card stat-card-primary bg-primary text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['total'] ?? 0 ?></h3>
                            <small>Total Pesanan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="card stat-card stat-card-warning bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3><?= $stats['pending'] ?? 0 ?></h3>
                            <small>Pending</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="card stat-card stat-card-info bg-info text-dark">
                        <div class="card-body text-center">
                            <h3><?= $stats['accepted'] ?? 0 ?></h3>
                            <small>Diterima</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="card stat-card bg-purple text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['processed'] ?? 0 ?></h3>
                            <small>Diproses</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="card stat-card stat-card-success bg-success text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['completed'] ?? 0 ?></h3>
                            <small>Selesai</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="card stat-card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['pending_approval'] ?? 0 ?></h3>
                            <small>Approval Pending</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pesanan Terbaru</h5>
                    <a href="<?= site_url('order/bookings') ?>" class="btn btn-sm btn-outline-primary-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>No. Booking</th>
                                    <th>Pelanggan</th>
                                    <th>Kendaraan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Approval</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_bookings)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">Belum ada pesanan</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                <tr>
                                    <td><strong><?= esc($booking['booking_number']) ?></strong></td>
                                    <td><?= esc($booking['user_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if (!empty($booking['license_plate'])): ?>
                                            <?= esc($booking['brand']) ?> <?= esc($booking['model']) ?>
                                            <br><small class="text-muted"><?= esc($booking['license_plate']) ?></small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($booking['scheduled_date'] . ' ' . $booking['scheduled_time'])) ?></td>
                                    <td>
                                        <?php
                                        $status_badges = [
                                            'pending' => 'warning',
                                            'accepted' => 'info',
                                            'processed' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $badge = $status_badges[$booking['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $badge ?>"><?= ucfirst($booking['status']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($booking['approval_status'] === 'pending'): ?>
                                            <span class="badge bg-danger">Menunggu Approval</span>
                                        <?php elseif ($booking['approval_status'] === 'approved'): ?>
                                            <span class="badge bg-success">Disetujui</span>
                                        <?php elseif ($booking['approval_status'] === 'rejected'): ?>
                                            <span class="badge bg-secondary">Ditolak</span>
                                        <?php else: ?>
                                            <span class="badge bg-light">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('order/detail/' . $booking['id']) ?>" class="btn btn-sm btn-outline-primary-primary">Detail</a>
                                    </td>
                                </tr>
                                    <?php endforeach; ?>
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
.bg-purple { background-color: #6f42c1 !important; color: #fff; }
</style>
