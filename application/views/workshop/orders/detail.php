<?php
/**
 * Workshop Order Detail View
 * Shows booking details with state transition controls for workshop owner
 */
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= $page_title ?></h2>
                <a href="<?= site_url('order/bookings') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
            <?php endif; ?>
            
            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
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
                                            <span class="badge badge-danger ml-2">Menunggu Approval Pelanggan</span>
                                        <?php endif; ?>
                                    </td>
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
                                <?php if (!empty($booking['final_cost'])): ?>
                                <tr>
                                    <th>Biaya Final</th>
                                    <td><strong>Rp <?= number_format($booking['final_cost'], 0, ',', '.') ?></strong></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Pelanggan</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Nama</th>
                                    <td><?= esc($user['name'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Telepon</th>
                                    <td><?= esc($user['phone'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?= esc($user['email'] ?? '-') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Vehicle Info -->
                    <?php if (!empty($vehicle)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Kendaraan</h5>
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
                            <h5 class="mb-0">Riwayat Approval Tambahan</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($approvals as $approval): ?>
                            <div class="border rounded p-3 mb-3 <?= $approval['status'] === 'pending' ? 'bg-warning-light' : '' ?>">
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
                                <div class="mt-2 small text-muted">
                                    Dibuat: <?= date('d/m/Y H:i', strtotime($approval['created_at'])) ?>
                                    <?php if ($approval['expires_at']): ?>
                                    | Expired: <?= date('d/m/Y H:i', strtotime($approval['expires_at'])) ?>
                                    <?php endif; ?>
                                    <?php if ($approval['responded_at']): ?>
                                    | Dijawab: <?= date('d/m/Y H:i', strtotime($approval['responded_at'])) ?>
                                    <?php endif; ?>
                                </div>
                                <?php if ($approval['response_note']): ?>
                                <div class="mt-1 small">Catatan: <?= esc($approval['response_note']) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
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
                            <!-- State-based actions -->
                            <?php if ($can_accept): ?>
                            <button type="button" class="btn btn-success btn-block mb-2" onclick="acceptBooking(<?= $booking['id'] ?>)">
                                <i class="fas fa-check"></i> Terima Pesanan
                            </button>
                            <button type="button" class="btn btn-danger btn-block mb-2" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times"></i> Tolak Pesanan
                            </button>
                            <?php endif; ?>

                            <?php if ($can_process): ?>
                            <button type="button" class="btn btn-primary btn-block mb-2" onclick="startProcessing(<?= $booking['id'] ?>)">
                                <i class="fas fa-tools"></i> Mulai Pengerjaan
                            </button>
                            <button type="button" class="btn btn-info btn-block mb-2" data-toggle="modal" data-target="#assignMechanicModal">
                                <i class="fas fa-user-cog"></i> Tugaskan Mekanik
                            </button>
                            <?php endif; ?>

                            <?php if ($can_add_finding): ?>
                            <button type="button" class="btn btn-warning btn-block mb-2" data-toggle="modal" data-target="#addFindingModal">
                                <i class="fas fa-search-plus"></i> Tambah Temuan & Minta Approval
                            </button>
                            <?php endif; ?>

                            <?php if ($can_complete): ?>
                            <button type="button" class="btn btn-success btn-block mb-2" data-toggle="modal" data-target="#completeModal">
                                <i class="fas fa-check-circle"></i> Selesaikan Pesanan
                            </button>
                            <?php endif; ?>

                            <?php if ($approval_timeout_expired): ?>
                            <hr>
                            <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Approval Timeout</h6>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-block mb-2" onclick="handleTimeout(<?= $booking['id'] ?>, 'continue')">
                                Lanjutkan (Tanpa Tambahan)
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-block" onclick="handleTimeout(<?= $booking['id'] ?>, 'cancel_additional')">
                                Batalkan Tambahan
                            </button>
                            <?php endif; ?>

                            <?php if (!$can_accept && !$can_process && !$can_add_finding && !$can_complete && !$approval_timeout_expired): ?>
                            <p class="text-muted text-center mb-0">Tidak ada aksi yang tersedia untuk status ini</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Activity Log -->
                    <?php if (!empty($activity_logs)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Aktivitas</h5>
                        </div>
                        <div class="card-body">
                            <ul class="timeline">
                                <?php foreach ($activity_logs as $log): ?>
                                <li class="timeline-item mb-3">
                                    <div class="timeline-badge bg-<?= $log['action'] === 'completed' ? 'success' : 'primary' ?>"></div>
                                    <div class="timeline-panel">
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></small>
                                        <p class="mb-0"><?= esc($log['description']) ?></p>
                                        <?php if ($log['user_name']): ?>
                                        <small class="text-muted">oleh: <?= esc($log['user_name']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">

<!-- Assign Mechanic Modal -->
<div class="modal fade" id="assignMechanicModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-user-cog"></i> Tugaskan Mekanik</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="assignMechanicForm" method="POST" action="<?= site_url('mechanic/assign_to_booking') ?>">
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="booking_id" value="<?= $booking['id']; ?>">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Pilih 1-3 mekanik yang tersedia. Sistem akan mengecek jadwal bentrok otomatis.
                    </div>
                    
                    <div class="form-group">
                        <label>Pilih Mekanik</label>
                        <div id="mechanicsList" class="list-group">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted" id="selectedCount">0 dari 3 mekanik dipilih</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan untuk Mekanik</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Instruksi khusus (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnAssignMechanic" disabled>
                        <i class="fas fa-save"></i> Simpan Penugasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Load mechanics when modal opens
$('#assignMechanicModal').on('show.bs.modal', function() {
    $.ajax({
        url: '<?= site_url('mechanic/get_available_for_booking/' . $booking['id']); ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var html = '';
            if (response.mechanics && response.mechanics.length > 0) {
                response.mechanics.forEach(function(mech) {
                    var disabledClass = mech.has_conflict ? 'disabled opacity-50' : '';
                    var conflictInfo = mech.has_conflict ? 
                        '<br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Bentrok dengan ' + 
                        mech.conflicts[0]?.booking_number + '</small>' : '';
                    
                    var specs = JSON.parse(mech.specialization || '[]');
                    var specBadges = specs.map(s => '<span class="badge badge-sm badge-info mr-1">' + s + '</span>').join('');
                    
                    html += '<label class="list-group-item list-group-item-action ' + disabledClass + '">' +
                        '<input type="checkbox" name="mechanic_ids[]" value="' + mech.id + '" ' +
                        (mech.has_conflict ? 'disabled' : '') +
                        ' class="mr-2 mechanic-checkbox" onchange="updateSelectedCount()">' +
                        '<strong>' + mech.name + '</strong>' + conflictInfo +
                        '<br><small class="text-muted">' + mech.email + '</small><br>' +
                        specBadges +
                        '</label>';
                });
            } else {
                html = '<div class="alert alert-warning mb-0">Tidak ada mekanik tersedia</div>';
            }
            $('#mechanicsList').html(html);
            updateSelectedCount();
        },
        error: function() {
            $('#mechanicsList').html('<div class="alert alert-danger mb-0">Gagal memuat data mekanik</div>');
        }
    });
});

function updateSelectedCount() {
    var count = $('input[name="mechanic_ids[]"]:checked').length;
    $('#selectedCount').text(count + ' dari 3 mekanik dipilih');
    $('#btnAssignMechanic').prop('disabled', count === 0);
}

// Validate max 3 mechanics
$(document).on('change', '.mechanic-checkbox', function() {
    var checked = $('input[name="mechanic_ids[]"]:checked');
    if (checked.length > 3) {
        $(this).prop('checked', false);
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Maksimal 3 mekanik per pesanan',
            timer: 2000,
            showConfirmButton: false
        });
    }
    updateSelectedCount();
});
</script>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Pesanan</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="rejectForm" method="POST" action="<?= site_url('order/reject/' . $booking['id']) ?>">
                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="form-group">
                        <label>Alasan Penolakan</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Finding Modal -->
<div class="modal fade" id="addFindingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Temuan & Minta Approval</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="addFindingForm" method="POST" action="<?= site_url('order/add_finding/' . $booking['id']) ?>">
                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Pelanggan memiliki waktu 48 jam untuk merespons permintaan approval ini.
                    </div>
                    <div class="form-group">
                        <label>Deskripsi Temuan *</label>
                        <textarea name="description" class="form-control" rows="3" required placeholder="Jelaskan temuan tambahan yang ditemukan"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Sparepart yang Diperlukan</label>
                        <input type="text" name="spareparts" class="form-control" placeholder="Contoh: Oli mesin 4L, Filter oli">
                    </div>
                    <div class="form-group">
                        <label>Estimasi Biaya Tambahan (Rp) *</label>
                        <input type="number" name="additional_amount" class="form-control" required min="0" placeholder="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Kirim Permintaan Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Selesaikan Pesanan</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="completeForm" method="POST" action="<?= site_url('order/complete/' . $booking['id']) ?>">
                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="form-group">
                        <label>Biaya Final (Rp)</label>
                        <input type="number" name="final_cost" class="form-control" value="<?= $booking['estimated_price'] ?>" min="0">
                        <small class="text-muted">Total termasuk approval yang disetujui</small>
                    </div>
                    <div class="form-group">
                        <label>Catatan Tambahan</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan untuk pelanggan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Selesaikan Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function acceptBooking(bookingId) {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Terima pesanan ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Terima',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= site_url('order/accept/') ?>' + bookingId, {
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            }, function(response) {
                if (response.success) {
                    window.location.href = response.redirect || window.location.href;
                } else {
                    Swal.fire('Error!', response.message || 'Gagal menerima pesanan', 'error');
                }
            }, 'json');
        }
    });
}

function startProcessing(bookingId) {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Mulai pengerjaan pesanan ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Mulai',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= site_url('order/start_processing/') ?>' + bookingId, {
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            }, function(response) {
                if (response.success) {
                    window.location.href = response.redirect || window.location.href;
                } else {
                    Swal.fire('Error!', response.message || 'Gagal mengubah status', 'error');
                }
            }, 'json');
        }
    });
}

function handleTimeout(bookingId, action) {
    var confirmMsg = action === 'continue' 
        ? 'Lanjutkan pekerjaan tanpa tambahan?' 
        : 'Batalkan temuan tambahan?';
    
    Swal.fire({
        title: 'Konfirmasi',
        text: confirmMsg,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: action === 'continue' ? '#0d6efd' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= site_url('order/handle_timeout/') ?>' + bookingId + '/' + action, {
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            }, function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    Swal.fire('Error!', response.message || 'Gagal memproses', 'error');
                }
            }, 'json');
        }
    });
}
</script>

<style>
.timeline { list-style: none; padding: 0; margin: 0; }
.timeline-item { position: relative; padding-left: 30px; }
.timeline-badge { position: absolute; left: 0; top: 0; width: 12px; height: 12px; border-radius: 50%; background: #007bff; }
.bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
.badge-lg { padding: 0.5em 1em; font-size: 0.9rem; }
</style>
