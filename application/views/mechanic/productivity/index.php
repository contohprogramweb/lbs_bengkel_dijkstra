<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3"><i class="fas fa-chart-line"></i> Produktivitas Saya</h2>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo e($start_date); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo e($end_date); ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="<?php echo site_url('mechanic/productivity'); ?>" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
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
                            <h2 class="mb-0"><?php echo $stats['total_bookings']; ?></h2>
                        </div>
                        <i class="fas fa-clipboard-list fa-3x text-primary-opacity"></i>
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
                            <h2 class="mb-0"><?php echo $stats['completed_count']; ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x text-success-opacity"></i>
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
                            <h2 class="mb-0"><?php echo $stats['in_progress_count']; ?></h2>
                        </div>
                        <i class="fas fa-tools fa-3x text-info-opacity"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card stat-card-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Rating Rata-rata</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['avg_rating'], 1); ?> ★</h2>
                            <small class="text-muted">(<?php echo $stats['review_count']; ?> ulasan)</small>
                        </div>
                        <i class="fas fa-star fa-3x text-warning-opacity"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Bookings Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-history"></i> Riwayat Order Selesai</h5>
        </div>
        <div class="card-body">
            <?php if (empty($completed_bookings)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Belum ada order selesai pada periode ini.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No. Booking</th>
                                <th>Pelanggan</th>
                                <th>Layanan</th>
                                <th>Tanggal Selesai</th>
                                <th>Rating</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completed_bookings as $booking): ?>
                                <tr>
                                    <td><strong><?php echo e($booking['booking_number']); ?></strong></td>
                                    <td><?php echo e($booking['customer_name']); ?></td>
                                    <td><?php echo e($booking['service_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($booking['completed_at'])); ?></td>
                                    <td>
                                        <?php if ($booking['customer_rating']): ?>
                                            <span class="text-warning">
                                                <?php echo str_repeat('★', floor($booking['customer_rating'])); ?>
                                                <?php echo number_format($booking['customer_rating'], 1); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-success">Selesai</span></td>
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
// Auto-submit filter on date change
document.querySelectorAll('input[type="date"]').forEach(input => {
    input.addEventListener('change', function() {
        // Optional: auto submit on date change
        // this.form.submit();
    });
});
</script>
