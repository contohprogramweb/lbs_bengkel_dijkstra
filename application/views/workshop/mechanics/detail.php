<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="mb-0"><?= $page_title; ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= site_url('mechanic/productivity'); ?>" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> Laporan Produktivitas
                </a>
            </div>
        </div>
    </div>

    <!-- Mechanic Info Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    <div class="avatar avatar-xl mb-3">
                        <i class="fas fa-user-cog fa-5x text-primary"></i>
                    </div>
                </div>
                <div class="col-md-9">
                    <h3><?= esc($mechanic['name']); ?></h3>
                    <p class="text-muted mb-2">
                        <i class="fas fa-envelope"></i> <?= esc($mechanic['email']); ?> | 
                        <i class="fas fa-phone"></i> <?= esc($mechanic['phone'] ?? '-'); ?>
                    </p>
                    <div class="mb-2">
                        <strong>Spesialisasi:</strong>
                        <?php 
                        $specs = json_decode($mechanic['specialization'] ?? '[]', TRUE);
                        if (!empty($specs)):
                            foreach ($specs as $spec):
                                echo '<span class="badge badge-info mr-1">' . ucfirst($spec) . '</span>';
                            endforeach;
                        else:
                            echo '<span class="text-muted">-</span>';
                        endif;
                        ?>
                    </div>
                    <div class="mb-2">
                        <strong>Pengalaman:</strong> <?= $mechanic['experience_years']; ?> tahun
                    </div>
                    <div class="mb-2">
                        <strong>Status:</strong>
                        <?php if ($mechanic['is_available']): ?>
                            <span class="badge badge-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Non-Aktif</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($mechanic['certification'])): ?>
                    <div>
                        <strong>Sertifikasi:</strong><br>
                        <small><?= nl2br(esc($mechanic['certification'])); ?></small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h2><?= $stats['total_bookings']; ?></h2>
                    <p class="mb-0">Total Pesanan Ditangani</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h2><?= $stats['completed']; ?></h2>
                    <p class="mb-0">Pesanan Selesai</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking History -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Riwayat Penugasan</h5>
            <div class="btn-group">
                <a href="<?= site_url('mechanic/detail/' . $mechanic['id'] . '?status=completed'); ?>" 
                   class="btn btn-sm btn-outline-success">Selesai</a>
                <a href="<?= site_url('mechanic/detail/' . $mechanic['id'] . '?status=processed'); ?>" 
                   class="btn btn-sm btn-outline-warning">Diproses</a>
                <a href="<?= site_url('mechanic/detail/' . $mechanic['id']); ?>" 
                   class="btn btn-sm btn-outline-secondary">Semua</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <div class="alert alert-info">
                    Belum ada riwayat penugasan untuk mekanik ini.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>No. Booking</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Kendaraan</th>
                                <th>Layanan</th>
                                <th>Status</th>
                                <th>Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <a href="<?= site_url('order/detail/' . $booking['id']); ?>">
                                            <?= esc($booking['booking_number']); ?>
                                        </a>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($booking['scheduled_date'])); ?> 
                                        <br><small><?= $booking['scheduled_time']; ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                        $this->db->select('name');
                                        $user = $this->db->get_where('users', ['id' => $booking['user_id']])->row();
                                        echo esc($user->name ?? '-');
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($booking['vehicle_id'])) {
                                            $this->db->select('vehicle_number, vehicle_type');
                                            $vehicle = $this->db->get_where('vehicles', ['id' => $booking['vehicle_id']])->row();
                                            echo esc($vehicle->vehicle_number ?? '-');
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td><?= esc(ucfirst($booking['service_type'])); ?></td>
                                    <td>
                                        <?php 
                                        $status_badges = [
                                            'pending' => 'secondary',
                                            'accepted' => 'info',
                                            'processed' => 'warning',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $badge = $status_badges[$booking['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $badge; ?>">
                                            <?= ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($booking['customer_rating'])): ?>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star text-<?= $i <= $booking['customer_rating'] ? 'warning' : 'light'; ?>"></i>
                                            <?php endfor; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
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

<script>
$(document).ready(function() {
    $('#bookingsTable').DataTable({
        order: [[1, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json'
        }
    });
});
</script>
