<?php
/**
 * User Bookings List View
 * Shows all user bookings with filtering options
 */
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= $page_title ?></h2>
                <a href="<?= site_url('user/bookings/create') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Buat Booking Baru
                </a>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" action="<?= site_url('booking_management/bookings') ?>">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option value="pending" <?= isset($filters['status']) && $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="accepted" <?= isset($filters['status']) && $filters['status'] === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                                    <option value="in_progress" <?= isset($filters['status']) && $filters['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= isset($filters['status']) && $filters['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= isset($filters['status']) && $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="approval">Approval Status</label>
                                <select name="approval" id="approval" class="form-control">
                                    <option value="">Semua Approval</option>
                                    <option value="pending" <?= isset($filters['approval_status']) && $filters['approval_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="approved" <?= isset($filters['approval_status']) && $filters['approval_status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                    <option value="rejected" <?= isset($filters['approval_status']) && $filters['approval_status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="month">Bulan</label>
                                <select name="month" id="month" class="form-control">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= isset($filters['month']) && $filters['month'] == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="year">Tahun</label>
                                <select name="year" id="year" class="form-control">
                                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                    <option value="<?= $y ?>" <?= isset($filters['year']) && $filters['year'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="q">Cari (No. Booking / Bengkel)</label>
                                <input type="text" name="q" id="q" class="form-control" placeholder="Masukkan pencarian..." value="<?= isset($filters['search']) ? esc($filters['search']) : '' ?>">
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="<?= site_url('booking_management/bookings') ?>" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($bookings)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h5>Tidak Ada Booking Ditemukan</h5>
                        <p class="text-muted">Tidak ada booking yang sesuai dengan filter yang dipilih.</p>
                        <a href="<?= site_url('booking_management/bookings') ?>" class="btn btn-primary">
                            <i class="fas fa-redo"></i> Reset Filter
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No. Booking</th>
                                    <th>Bengkel</th>
                                    <th>Kendaraan</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Status</th>
                                    <th>Approval</th>
                                    <th>Estimasi Biaya</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><strong><?= esc($booking['booking_number']) ?></strong></td>
                                    <td><?= esc($booking['workshop_name']) ?></td>
                                    <td>
                                        <?php if (!empty($booking['vehicle_model'])): ?>
                                        <?= esc($booking['vehicle_model'] . ' ' . $booking['vehicle_year']) ?>
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
                                            'in_progress' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $badge = $status_badges[$booking['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $badge ?>"><?= ucfirst($booking['status']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($booking['approval_status'] === 'pending'): ?>
                                        <span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i> Perlu Approval</span>
                                        <?php elseif ($booking['approval_status'] === 'approved'): ?>
                                        <span class="badge badge-success">Disetujui</span>
                                        <?php elseif ($booking['approval_status'] === 'rejected'): ?>
                                        <span class="badge badge-secondary">Ditolak</span>
                                        <?php else: ?>
                                        <span class="badge badge-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>Rp <?= number_format($booking['estimated_price'], 0, ',', '.') ?></td>
                                    <td>
                                        <a href="<?= site_url('booking_management/detail/' . $booking['id']) ?>" class="btn btn-sm btn-info">
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
    </div>
</div>