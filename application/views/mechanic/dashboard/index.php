<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-fluid">
    <!-- Welcome Banner -->
    <div class="card bg-primary text-white mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-2">Selamat Datang, <?php echo e($mechanic['name'] ?? $current_user->full_name); ?>!</h3>
                    <p class="mb-0"><i class="fas fa-store"></i> <?php echo e($workshop->name ?? 'Bengkel'); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <button onclick="toggleAvailability()" class="btn btn-light btn-sm">
                        <i class="fas fa-toggle-<?php echo $mechanic['is_available'] ? 'on' : 'off'; ?>"></i>
                        Status: <?php echo $mechanic['is_available'] ? 'Tersedia' : 'Tidak Tersedia'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card stat-card-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Order</h6>
                            <h2 class="mb-0"><?php echo $stats['total_assigned']; ?></h2>
                        </div>
                        <i class="fas fa-clipboard-list fa-3x text-primary-opacity"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card stat-card-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Menunggu</h6>
                            <h2 class="mb-0"><?php echo $stats['pending']; ?></h2>
                        </div>
                        <i class="fas fa-clock fa-3x text-warning-opacity"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card stat-card-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Sedang Dikerjakan</h6>
                            <h2 class="mb-0"><?php echo $stats['in_progress']; ?></h2>
                        </div>
                        <i class="fas fa-tools fa-3x text-info-opacity"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card stat-card-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Selesai</h6>
                            <h2 class="mb-0"><?php echo $stats['completed']; ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x text-success-opacity"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Bookings -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-day"></i> Order Hari Ini</h5>
                    <a href="<?php echo site_url('mechanic_dashboard/my_bookings'); ?>" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <?php if (empty($today_bookings)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> Tidak ada order yang dijadwalkan hari ini.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No. Booking</th>
                                        <th>Pelanggan</th>
                                        <th>Layanan</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($today_bookings as $booking): ?>
                                        <tr>
                                            <td><strong><?php echo e($booking['booking_number']); ?></strong></td>
                                            <td><?php echo e($booking['customer_name']); ?></td>
                                            <td><?php echo e($booking['service_type']); ?></td>
                                            <td><?php echo date('H:i', strtotime($booking['scheduled_time'])); ?></td>
                                            <td>
                                                <?php
                                                $status_badges = [
                                                    'pending' => 'secondary',
                                                    'accepted' => 'warning',
                                                    'in_progress' => 'info',
                                                    'completed' => 'success',
                                                    'waiting_approval' => 'primary'
                                                ];
                                                $badge = $status_badges[$booking['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $badge; ?>"><?php echo e($booking['status_label']); ?></span>
                                            </td>
                                            <td>
                                                <a href="<?php echo site_url('mechanic_dashboard/booking_detail/' . $booking['id']); ?>" 
                                                   class="btn btn-sm btn-primary">
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

        <!-- Recent Activity -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Aktivitas Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (empty($recent_bookings)): ?>
                            <div class="p-3 text-muted">Belum ada aktivitas</div>
                        <?php else: ?>
                            <?php foreach ($recent_bookings as $booking): ?>
                                <a href="<?php echo site_url('mechanic_dashboard/booking_detail/' . $booking['id']); ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo e($booking['booking_number']); ?></h6>
                                        <small class="text-muted"><?php echo time_ago($booking['updated_at']); ?></small>
                                    </div>
                                    <p class="mb-1 small"><?php echo e($booking['service_description']); ?></p>
                                    <small class="text-muted">Status: <?php echo e($booking['status_label']); ?></small>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mechanic Info -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-cog"></i> Informasi Mekanik</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Nama:</strong></td>
                            <td><?php echo e($mechanic['name'] ?? $current_user->full_name); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Spesialisasi:</strong></td>
                            <td>
                                <?php 
                                $specs = json_decode($mechanic['specialization'] ?? '[]', TRUE);
                                if (!empty($specs)):
                                    foreach ($specs as $spec): 
                                ?>
                                    <span class="badge bg-info me-1"><?php echo ucfirst($spec); ?></span>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Pengalaman:</strong></td>
                            <td><?php echo $mechanic['experience_years']; ?> tahun</td>
                        </tr>
                        <tr>
                            <td><strong>Sertifikasi:</strong></td>
                            <td><?php echo e($mechanic['certification'] ?? '-'); ?></td>
                        </tr>
                    </table>
                    <a href="<?php echo site_url('mechanic_dashboard/profile'); ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit"></i> Edit Profil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAvailability() {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin mengubah status ketersediaan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0984e3',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Ubah',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo site_url('mechanic_dashboard/toggle_availability'); ?>',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Gagal mengubah status', 'error');
                }
            });
        }
    });
}

function time_ago(time) {
    // Simple time ago function
    var time_difference = new Date() - new Date(time);
    var seconds = Math.floor(time_difference / 1000);
    var minutes = Math.floor(seconds / 60);
    var hours = Math.floor(minutes / 60);
    var days = Math.floor(hours / 24);

    if (days > 0) return days + ' hari lalu';
    if (hours > 0) return hours + ' jam lalu';
    if (minutes > 0) return minutes + ' menit lalu';
    return 'Baru saja';
}
</script>
