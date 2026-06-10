<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card stat-card-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Order</h6>
                            <h2 class="mb-0"><?php echo count($bookings); ?></h2>
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
                            <h2 class="mb-0">
                                <?php 
                                $pending = 0;
                                foreach ($bookings as $b) {
                                    if (in_array($b['status'], ['pending', 'accepted'])) $pending++;
                                }
                                echo $pending;
                                ?>
                            </h2>
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
                            <h2 class="mb-0">
                                <?php 
                                $in_progress = 0;
                                foreach ($bookings as $b) {
                                    if ($b['status'] == 'in_progress') $in_progress++;
                                }
                                echo $in_progress;
                                ?>
                            </h2>
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
                            <h2 class="mb-0">
                                <?php 
                                $completed = 0;
                                foreach ($bookings as $b) {
                                    if (in_array($b['status'], ['completed', 'waiting_approval'])) $completed++;
                                }
                                echo $completed;
                                ?>
                            </h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x text-success-opacity"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Order</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="status_filter" class="form-label">Filter Status:</label>
                    <select name="status" id="status_filter" class="form-select">
                        <option value="all" <?php echo ($status_filter == 'all') ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="accepted" <?php echo ($status_filter == 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                        <option value="in_progress" <?php echo ($status_filter == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo ($status_filter == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="waiting_approval" <?php echo ($status_filter == 'waiting_approval') ? 'selected' : ''; ?>>Waiting Approval</option>
                        <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="<?php echo site_url('mechanic/bookings'); ?>" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list"></i> Daftar Order Saya</h5>
            <span class="badge bg-primary"><?php echo count($bookings); ?> Order</span>
        </div>
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i> Belum ada order yang ditugaskan kepada Anda.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>No. Booking</th>
                                <th>Tanggal & Waktu</th>
                                <th>Pelanggan</th>
                                <th>Kendaraan</th>
                                <th>Layanan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><strong><?php echo e($booking['booking_number']); ?></strong></td>
                                    <td>
                                        <?php 
                                        if (!empty($booking['scheduled_date'])) {
                                            echo date('d M Y', strtotime($booking['scheduled_date']));
                                            if (!empty($booking['scheduled_time'])) {
                                                echo '<br><small class="text-muted">' . date('H:i', strtotime($booking['scheduled_time'])) . ' WIB</small>';
                                            }
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo e($booking['customer_name'] ?? 'N/A'); ?>
                                        <?php if (!empty($booking['customer_phone'])): ?>
                                            <br><small class="text-muted"><i class="fas fa-phone"></i> <?php echo e($booking['customer_phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo e($booking['vehicle_name'] ?? $booking['vehicle_type'] ?? '-'); ?>
                                        <?php if (!empty($booking['vehicle_plate'])): ?>
                                            <br><small class="text-muted"><?php echo e($booking['vehicle_plate']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo e($booking['service_type'] ?? $booking['service_name'] ?? '-'); ?>
                                        <?php if (!empty($booking['complaint'])): ?>
                                            <br><small class="text-muted"><i class="fas fa-comment"></i> <?php echo e(substr($booking['complaint'], 0, 30)); ?><?php echo strlen($booking['complaint']) > 30 ? '...' : ''; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badges = [
                                            'pending' => 'secondary',
                                            'accepted' => 'warning',
                                            'rejected' => 'danger',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'waiting_approval' => 'primary',
                                            'cancelled' => 'dark',
                                            'no_show' => 'secondary'
                                        ];
                                        $badge_class = $status_badges[$booking['status']] ?? 'secondary';
                                        $status_label = $booking['status_label'] ?? ucfirst($booking['status']);
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>"><?php echo e($status_label); ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo site_url('mechanic/booking_detail/' . $booking['id']); ?>" 
                                               class="btn btn-primary" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <?php if ($booking['status'] == 'accepted'): ?>
                                                <button onclick="updateStatus(<?php echo $booking['id']; ?>, 'in_progress')" 
                                                        class="btn btn-info" 
                                                        title="Mulai Kerjakan">
                                                    <i class="fas fa-play"></i> Mulai
                                                </button>
                                            <?php elseif ($booking['status'] == 'in_progress'): ?>
                                                <button onclick="updateStatus(<?php echo $booking['id']; ?>, 'completed')" 
                                                        class="btn btn-success" 
                                                        title="Selesaikan">
                                                    <i class="fas fa-check"></i> Selesai
                                                </button>
                                            <?php endif; ?>
                                        </div>
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
    // Initialize DataTable if available
    if ($.fn.DataTable) {
        $('#bookingsTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[1, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            columnDefs: [
                { orderable: false, targets: [6] }
            ]
        });
    }
});

function updateStatus(bookingId, newStatus) {
    let statusText = '';
    let confirmText = '';
    
    if (newStatus === 'in_progress') {
        statusText = 'Sedang Dikerjakan';
        confirmText = 'Apakah Anda yakin ingin memulai pengerjaan order ini?';
    } else if (newStatus === 'completed') {
        statusText = 'Selesai';
        confirmText = 'Apakah Anda yakin telah menyelesaikan pengerjaan order ini?';
    }
    
    Swal.fire({
        title: 'Konfirmasi',
        text: confirmText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0984e3',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Get CSRF token
            const csrfTokenName = $('meta[name="csrf_token_name"]').attr('content');
            const csrfTokenHash = $('meta[name="csrf_token_hash"]').attr('content');
            
            $.ajax({
                url: '<?php echo site_url('mechanic_dashboard/update_status'); ?>',
                type: 'POST',
                data: {
                    booking_id: bookingId,
                    status: newStatus,
                    [csrfTokenName]: csrfTokenHash
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success || response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Status berhasil diperbarui',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message || 'Gagal memperbarui status', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = 'Gagal memperbarui status';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', errorMsg, 'error');
                }
            });
        }
    });
}
</script>
