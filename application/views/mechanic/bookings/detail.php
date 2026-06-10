<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-file-alt"></i> <?= $page_title ?></h2>
                <a href="<?= site_url('mechanic/bookings') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>
            
            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- Main Info -->
                <div class="col-lg-8">
                    <!-- Booking Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Informasi Order</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">No. Booking</th>
                                    <td><strong><?= esc($booking['booking_number']) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
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
                                        $status_label = ucfirst($booking['status']);
                                        ?>
                                        <span class="badge bg-<?= $badge_class ?> badge-lg"><?= $status_label ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tanggal & Waktu Jadwal</th>
                                    <td>
                                        <?php 
                                        if (!empty($booking['scheduled_date'])) {
                                            echo date('d M Y', strtotime($booking['scheduled_date']));
                                            if (!empty($booking['scheduled_time'])) {
                                                echo ' - ' . date('H:i', strtotime($booking['scheduled_time'])) . ' WIB';
                                            }
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php if (!empty($booking['complaint'])): ?>
                                <tr>
                                    <th>Keluhan</th>
                                    <td><?= nl2br(esc($booking['complaint'])) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($booking['notes'])): ?>
                                <tr>
                                    <th>Catatan Tambahan</th>
                                    <td><?= nl2br(esc($booking['notes'])) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <!-- Workshop Information -->
                    <?php if (!empty($workshop)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-wrench"></i> Bengkel</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Nama Bengkel</th>
                                    <td><strong><?= esc($workshop['name']) ?></strong></td>
                                </tr>
                                <?php if (!empty($workshop['address'])): ?>
                                <tr>
                                    <th>Alamat</th>
                                    <td><?= nl2br(esc($workshop['address'])) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($workshop['phone'])): ?>
                                <tr>
                                    <th>Telepon</th>
                                    <td><i class="fas fa-phone"></i> <?= esc($workshop['phone']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Vehicle Information -->
                    <?php if (!empty($booking['vehicle_name']) || !empty($booking['vehicle_type']) || !empty($booking['vehicle_plate'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-car"></i> Kendaraan</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <?php if (!empty($booking['vehicle_name'])): ?>
                                <tr>
                                    <th width="30%">Nama Kendaraan</th>
                                    <td><?= esc($booking['vehicle_name']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($booking['vehicle_type'])): ?>
                                <tr>
                                    <th>Tipe</th>
                                    <td><?= esc($booking['vehicle_type']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($booking['vehicle_plate'])): ?>
                                <tr>
                                    <th>Plat Nomor</th>
                                    <td><strong><?= esc($booking['vehicle_plate']) ?></strong></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($booking['vehicle_year'])): ?>
                                <tr>
                                    <th>Tahun</th>
                                    <td><?= esc($booking['vehicle_year']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Service Information -->
                    <?php if (!empty($booking['service_type']) || !empty($booking['service_name'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-tools"></i> Layanan</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <?php if (!empty($booking['service_name'])): ?>
                                <tr>
                                    <th width="30%">Nama Layanan</th>
                                    <td><?= esc($booking['service_name']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($booking['service_type'])): ?>
                                <tr>
                                    <th>Tipe Layanan</th>
                                    <td><?= esc($booking['service_type']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($booking['estimated_price'])): ?>
                                <tr>
                                    <th>Estimasi Biaya</th>
                                    <td>Rp <?= number_format($booking['estimated_price'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Customer Information -->
                    <?php if (!empty($booking['customer_name']) || !empty($booking['customer_phone'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user"></i> Pelanggan</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <?php if (!empty($booking['customer_name'])): ?>
                                <tr>
                                    <th width="30%">Nama</th>
                                    <td><?= esc($booking['customer_name']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($booking['customer_phone'])): ?>
                                <tr>
                                    <th>Telepon</th>
                                    <td><i class="fas fa-phone"></i> <?= esc($booking['customer_phone']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($booking['customer_email'])): ?>
                                <tr>
                                    <th>Email</th>
                                    <td><i class="fas fa-envelope"></i> <?= esc($booking['customer_email']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Assigned Mechanics -->
                    <?php if (!empty($assigned_mechanics)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-users"></i> Tim Mekanik</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($assigned_mechanics as $assigned): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary text-white rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <strong><?= esc($assigned['mechanic_name']) ?></strong>
                                            <?php if ($assigned['is_primary'] ?? false): ?>
                                            <br><small class="text-primary"><i class="fas fa-star"></i> Mekanik Utama</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Action Panel -->
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 20px;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-bolt"></i> Aksi Cepat</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($booking['status'] == 'accepted'): ?>
                            <button onclick="updateStatus('<?= $booking['id'] ?>', 'in_progress')" 
                                    class="btn btn-info btn-block mb-3">
                                <i class="fas fa-play"></i> Mulai Pengerjaan
                            </button>
                            <?php endif; ?>

                            <?php if ($booking['status'] == 'in_progress'): ?>
                            <button onclick="updateStatus('<?= $booking['id'] ?>', 'completed')" 
                                    class="btn btn-success btn-block mb-3">
                                <i class="fas fa-check"></i> Selesaikan Pengerjaan
                            </button>
                            <?php endif; ?>

                            <?php if ($booking['status'] == 'completed'): ?>
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle"></i> Pengerjaan Selesai<br>
                                <small>Menunggu approval pelanggan</small>
                            </div>
                            <?php endif; ?>

                            <?php if ($booking['status'] == 'pending'): ?>
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-clock"></i> Menunggu Acceptance<br>
                                <small>Silakan tunggu bengkel menugaskan Anda</small>
                            </div>
                            <?php endif; ?>

                            <hr>
                            
                            <div class="text-muted small">
                                <p class="mb-2"><strong>Status Saat Ini:</strong></p>
                                <span class="badge bg-<?= $badge_class ?> badge-lg w-100 text-center py-2"><?= $status_label ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add any initialization scripts here
});

function updateStatus(bookingId, newStatus) {
    let statusText = '';
    let confirmText = '';
    
    if (newStatus === 'in_progress') {
        statusText = 'Sedang Dikerjakan';
        confirmText = 'Apakah Anda yakin ingin memulai pengerjaan order ini?';
    } else if (newStatus === 'completed') {
        statusText = 'Selesai';
        confirmText = 'Apakah Anda yakin telah menyelesaikan pengerjaan order ini? Pastikan semua pekerjaan telah selesai dengan baik.';
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

<style>
.badge-lg {
    padding: 0.5em 1em;
    font-size: 1rem;
}

.avatar {
    flex-shrink: 0;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}

.timeline-item {
    position: relative;
}

.timeline-badge {
    position: absolute;
    left: -34px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #e0e0e0;
}

.sticky-top {
    z-index: 100;
}
</style>
