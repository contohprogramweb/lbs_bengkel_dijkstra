<?php
/**
 * User Booking Detail View
 * Shows booking details with approval response controls for customer
 */
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= $page_title ?></h2>
                <a href="<?= site_url('booking_management/bookings') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
            <?php endif; ?>
            
            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
            <?php endif; ?>

            <!-- Pending Approval Alert -->
            <?php if ($needs_approval_response): ?>
            <div class="alert <?= $approval_urgent ? 'alert-danger' : ($approval_expired ? 'alert-warning' : 'alert-info') ?> alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <?php if ($approval_expired): ?>
                        Approval Telah Kadaluarsa
                    <?php elseif ($approval_urgent): ?>
                        Segera Respons Permintaan Bengkel!
                    <?php else: ?>
                        Ada Permintaan Approval dari Bengkel
                    <?php endif; ?>
                </h5>
                <hr>
                <p class="mb-2"><strong>Temuan:</strong> <?= esc($pending_approval['description']) ?></p>
                <p class="mb-2"><strong>Sparepart:</strong> <?= esc($pending_approval['spareparts'] ?? '-') ?></p>
                <p class="mb-2"><strong>Biaya Tambahan:</strong> <span class="text-danger font-weight-bold">Rp <?= number_format($pending_approval['additional_amount'], 0, ',', '.') ?></span></p>
                <p class="mb-3">
                    <strong>Total Baru:</strong> 
                    <span class="font-weight-bold">Rp <?= number_format($total_cost + $pending_approval['additional_amount'], 0, ',', '.') ?></span>
                </p>
                
                <?php if (!$approval_expired): ?>
                <p class="mb-3">
                    <small>
                        <?php if ($approval_hours_remaining <= 6): ?>
                            <span class="text-danger font-weight-bold">⏰ Waktu tersisa: <?= $approval_hours_remaining ?> jam lagi!</span>
                        <?php else: ?>
                            ⏰ Waktu tersisa: <?= $approval_hours_remaining ?> jam
                        <?php endif; ?>
                    </small>
                </p>
                
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success btn-lg" onclick="approveAdditional(<?= $booking['id'] ?>)">
                        <i class="fas fa-check"></i> Setuju & Lanjutkan
                    </button>
                    <button type="button" class="btn btn-danger btn-lg" data-toggle="modal" data-target="#rejectApprovalModal">
                        <i class="fas fa-times"></i> Tolak
                    </button>
                </div>
                <?php else: ?>
                <p class="text-muted">
                    <small>Permintaan approval telah kadaluarsa. Bengkel akan melanjutkan pekerjaan awal saja.</small>
                </p>
                <?php endif; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- Main Info -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Pesanan</h5>
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
                                            'pending' => 'warning',
                                            'accepted' => 'info',
                                            'processed' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $badge = $status_badges[$booking['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $badge ?> badge-lg"><?= ucfirst($booking['status']) ?></span>
                                        
                                        <?php if ($booking['approval_status'] === 'pending'): ?>
                                            <span class="badge badge-danger ml-2">Menunggu Respons Anda</span>
                                        <?php elseif ($booking['approval_status'] === 'approved'): ?>
                                            <span class="badge badge-success ml-2">Approval Disetujui</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Bengkel</th>
                                    <td><?= esc($workshop['name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <td><?= date('d/m/Y H:i', strtotime($booking['scheduled_date'] . ' ' . $booking['scheduled_time'])) ?> WIB</td>
                                </tr>
                                <tr>
                                    <th>Layanan</th>
                                    <td><?= esc($booking['service_description']) ?></td>
                                </tr>
                                <tr>
                                    <th>Estimasi Biaya Awal</th>
                                    <td>Rp <?= number_format($booking['estimated_price'], 0, ',', '.') ?></td>
                                </tr>
                                <?php if ($total_cost != $booking['estimated_price']): ?>
                                <tr>
                                    <th>Total Biaya (termasuk approval)</th>
                                    <td><strong class="text-primary">Rp <?= number_format($total_cost, 0, ',', '.') ?></strong></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($booking['final_cost'])): ?>
                                <tr>
                                    <th>Biaya Final</th>
                                    <td><strong>Rp <?= number_format($booking['final_cost'], 0, ',', '.') ?></strong></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <!-- Vehicle Info -->
                    <?php if (!empty($vehicle)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Kendaraan</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Merk/Model</th>
                                    <td><?= esc($vehicle['brand']) ?> <?= esc($vehicle['model']) ?></td>
                                </tr>
                                <tr>
                                    <th>Tahun</th>
                                    <td><?= esc($vehicle['year']) ?></td>
                                </tr>
                                <tr>
                                    <th>Plat Nomor</th>
                                    <td><strong><?= esc($vehicle['license_plate']) ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Approval History -->
                    <?php if (!empty($approvals)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Riwayat Approval</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($approvals as $approval): ?>
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between">
                                    <strong><?= esc($approval['description']) ?></strong>
                                    <span class="badge badge-<?= $approval['status'] === 'approved' ? 'success' : ($approval['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($approval['status']) ?>
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <small>Sparepart: <?= esc($approval['spareparts'] ?? '-') ?></small><br>
                                    <strong class="text-danger">+ Rp <?= number_format($approval['additional_amount'], 0, ',', '.') ?></strong>
                                </div>
                                <?php if ($approval['response_note']): ?>
                                <div class="mt-2 small text-muted">Catatan: <?= esc($approval['response_note']) ?></div>
                                <?php endif; ?>
                                <div class="mt-2 small text-muted">
                                    <?= date('d/m/Y H:i', strtotime($approval['created_at'])) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Status Timeline -->
                    <?php if (!empty($activity_logs)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Tracking Status</h5>
                        </div>
                        <div class="card-body">
                            <ul class="timeline">
                                <?php 
                                // Reverse to show oldest first for timeline
                                $logs_reversed = array_reverse($activity_logs);
                                foreach ($logs_reversed as $log): 
                                ?>
                                <li class="timeline-item mb-3">
                                    <div class="timeline-badge bg-<?= in_array($log['action'], ['completed', 'approved']) ? 'success' : 'primary' ?>"></div>
                                    <div class="timeline-panel">
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></small>
                                        <p class="mb-0"><?= esc($log['description']) ?></p>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Action Panel -->
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 20px;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Aksi</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($can_cancel): ?>
                            <button type="button" class="btn btn-danger btn-block mb-2" data-toggle="modal" data-target="#cancelModal">
                                <i class="fas fa-times"></i> Batalkan Pesanan
                            </button>
                            <?php endif; ?>

                            <?php if ($can_reschedule): ?>
                            <button type="button" class="btn btn-warning btn-block mb-2" data-toggle="modal" data-target="#rescheduleModal">
                                <i class="fas fa-calendar-alt"></i> Ubah Jadwal
                            </button>
                            <?php endif; ?>

                            <?php if (!$can_cancel && !$can_reschedule && !$needs_approval_response): ?>
                            <p class="text-muted text-center mb-0">Tidak ada aksi yang tersedia untuk status ini</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Approval Modal -->
<div class="modal fade" id="rejectApprovalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Permintaan Tambahan</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="rejectApprovalForm" method="POST" action="<?= site_url('booking_management/reject_additional/' . $booking['id']) ?>">
                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> 
                        Jika ditolak, bengkel akan melanjutkan pekerjaan sesuai estimasi awal saja.
                    </div>
                    <div class="form-group">
                        <label>Alasan Penolakan (Opsional)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Berikan alasan penolakan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Permintaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Batalkan Pesanan</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="cancelForm" method="POST" action="<?= site_url('booking_management/cancel/' . $booking['id']) ?>">
                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Apakah Anda yakin ingin membatalkan pesanan ini?
                    </div>
                    <div class="form-group">
                        <label>Alasan Pembatalan *</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Jelaskan alasan pembatalan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Batalkan Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Jadwal</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="rescheduleForm" method="POST" action="<?= site_url('booking_management/reschedule/' . $booking['id']) ?>">
                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="form-group">
                        <label>Tanggal Baru *</label>
                        <input type="date" name="new_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Waktu Baru *</label>
                        <input type="time" name="new_time" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Ubah Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveAdditional(bookingId) {
    if (confirm('Setujui permintaan tambahan ini? Total biaya akan diperbarui.')) {
        $.post('<?= site_url('booking_management/approve_additional/') ?>' + bookingId, {
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        }, function(response) {
            if (response.success) {
                window.location.href = response.redirect || window.location.href;
            } else {
                alert(response.message || 'Gagal memproses approval');
            }
        }, 'json');
    }
}
</script>

<style>
.timeline { list-style: none; padding: 0; margin: 0; }
.timeline-item { position: relative; padding-left: 30px; }
.timeline-badge { position: absolute; left: 0; top: 5px; width: 12px; height: 12px; border-radius: 50%; background: #007bff; }
.badge-lg { padding: 0.5em 1em; font-size: 0.9rem; }
</style>
