<?php
/**
 * User Booking Dashboard/Index View
 * Shows overview of user's bookings with statistics and quick actions
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

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Total Booking</h6>
                                    <h2 class="mb-0"><?= $stats['total'] ?? 0 ?></h2>
                                </div>
                                <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Pending</h6>
                                    <h2 class="mb-0"><?= $stats['pending'] ?? 0 ?></h2>
                                </div>
                                <i class="fas fa-clock fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Selesai</h6>
                                    <h2 class="mb-0"><?= $stats['completed'] ?? 0 ?></h2>
                                </div>
                                <i class="fas fa-check-circle fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Dalam Proses</h6>
                                    <h2 class="mb-0"><?= $stats['in_progress'] ?? 0 ?></h2>
                                </div>
                                <i class="fas fa-tools fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals Alert -->
            <?php if (!empty($pending_approvals)): ?>
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle"></i> Perlu Persetujuan Anda
                </h5>
                <p class="mb-2">Anda memiliki <?= count($pending_approvals) ?> permintaan approval dari bengkel yang menunggu respons.</p>
                <hr>
                <?php foreach ($pending_approvals as $approval): ?>
                <div class="mb-2">
                    <strong>Booking #<?= esc($approval['booking_number']) ?></strong>:
                    <?= esc($approval['description']) ?> -
                    <span class="text-danger">Rp <?= number_format($approval['additional_amount'], 0, ',', '.') ?></span>
                    <a href="<?= site_url('booking_management/detail/' . $approval['booking_id']) ?>" class="btn btn-sm btn-primary ml-2">
                        Lihat Detail
                    </a>
                </div>
                <?php endforeach; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <!-- Recent Bookings Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Booking Terbaru</h5>
                    <a href="<?= site_url('booking_management/bookings') ?>" class="btn btn-sm btn-outline-primary">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_bookings)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h5>Belum Ada Booking</h5>
                        <p class="text-muted">Anda belum memiliki riwayat booking.</p>
                        <a href="<?= site_url('user/bookings/create') ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Buat Booking Pertama
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No. Booking</th>
                                    <th>Bengkel</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Approval</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_bookings as $booking): ?>
                                <tr>
                                    <td><strong><?= esc($booking['booking_number']) ?></strong></td>
                                    <td><?= esc($booking['workshop_name']) ?></td>
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
                                        <?php else: ?>
                                        <span class="badge badge-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
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