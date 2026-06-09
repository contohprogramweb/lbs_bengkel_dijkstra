<?php
/**
 * User Dashboard View
 *
 * @var array $stats Statistics data
 */
?>

<div class="container-fluid py-3">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-2"><i class="fas fa-hand-wave me-2"></i>Selamat Datang, <?php echo e($user->full_name); ?>!</h4>
                    <p class="mb-0 opacity-75">Kelola booking dan kendaraan Anda dengan mudah.</p>
                </div>
            </div>
        </div>
    </div>


	  <!-- Stats Cards -->
                <?php if (isset($stats)): ?>
                <div class="row mb-4">
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card card-stat bg-primary text-white p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Total Booking</h6>
                                    <h3 class="mb-0"><?php echo $stats['total_bookings'] ?? 0; ?></h3>
                                </div>
                                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card card-stat bg-warning text-white p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Pending</h6>
                                    <h3 class="mb-0"><?php echo $stats['pending_bookings'] ?? 0; ?></h3>
                                </div>
                                <div class="icon"><i class="fas fa-clock"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card card-stat bg-success text-white p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Selesai</h6>
                                    <h3 class="mb-0"><?php echo $stats['completed_bookings'] ?? 0; ?></h3>
                                </div>
                                <div class="icon"><i class="fas fa-check"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card card-stat bg-info text-white p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Kendaraan</h6>
                                    <h3 class="mb-0"><?php echo $stats['total_vehicles'] ?? 0; ?></h3>
                                </div>
                                <div class="icon"><i class="fas fa-car"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <a href="<?php echo site_url('user/bookings/create'); ?>" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-plus-circle me-2"></i>Booking Baru
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="<?php echo site_url('user/vehicles'); ?>" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-car me-2"></i>Kendaraan
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="<?php echo site_url('user/bookings'); ?>" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-list me-2"></i>Riwayat Booking
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="<?php echo site_url('user/notifications'); ?>" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-bell me-2"></i>Notifikasi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Booking Terakhir</h6>
                    <a href="<?php echo site_url('user/bookings'); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No. Booking</th>
                                    <th>Workshop</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($recent_bookings) && !empty($recent_bookings)): ?>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td><strong><?php echo e($booking->booking_number); ?></strong></td>
                                        <td><?php echo e($booking->workshop_name); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($booking->booking_date)); ?></td>
                                        <td>
                                            <?php
                                            $status_badges = [
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'in_progress' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $badge = $status_badges[$booking->status] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($booking->status); ?></span>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('user/bookings/detail/' . $booking->id); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            Belum ada booking
                                        </td>
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