<?php $this->load->view('layouts/user_header', ['page_title' => $page_title, 'user' => $user]); ?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Reminder Settings Card -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-bell"></i> Pengaturan Pengingat Servis Berkala</h5>
                </div>
                <div class="card-body">
                    <p>Kelola pengaturan pengingat servis untuk setiap kendaraan Anda. Sistem akan mengirim notifikasi email ketika kendaraan Anda memerlukan servis berkala berdasarkan:</p>
                    <ul>
                        <li><strong>Jarak Tempuh:</strong> Setiap 5.000 km sejak servis terakhir (dapat dikonfigurasi)</li>
                        <li><strong>Waktu:</strong> Setiap 6 bulan sejak servis terakhir</li>
                    </ul>
                    <p class="text-muted small">
                        <i class="fas fa-info-circle"></i> 
                        Sesuai dengan Business Rules BR-73: Maksimal 1 reminder per 7 hari per kendaraan.<br>
                        BR-74: Anda dapat menonaktifkan reminder untuk kendaraan tertentu.
                    </p>
                </div>
            </div>

            <!-- Vehicles List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-car"></i> Kendaraan Saya</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($vehicles)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-car fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada kendaraan terdaftar.</p>
                            <a href="<?= site_url('user/vehicle_form') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Kendaraan Pertama
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="15%">Nomor Polisi</th>
                                        <th width="20%">Kendaraan</th>
                                        <th width="15%">KM Terakhir</th>
                                        <th width="15%">Servis Terakhir</th>
                                        <th width="15%">Status Reminder</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehicles as $idx => $v): ?>
                                        <tr>
                                            <td><?= $idx + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($v['vehicle_number']) ?></strong></td>
                                            <td>
                                                <?= htmlspecialchars($v['brand']) ?> <?= htmlspecialchars($v['model']) ?><br>
                                                <small class="text-muted"><?= ucfirst($v['vehicle_type']) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($v['last_service_km']): ?>
                                                    <?= number_format($v['last_service_km']) ?> km
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($v['last_service_date']): ?>
                                                    <?= date('d/m/Y', strtotime($v['last_service_date'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Tidak tercatat</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($v['reminder_snoozed_until']) && strtotime($v['reminder_snoozed_until']) > time()): ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-pause"></i> Snooze s/d <?= date('d/m/Y', strtotime($v['reminder_snoozed_until'])) ?>
                                                    </span>
                                                <?php elseif ($v['reminder_enabled']): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-times-circle"></i> Nonaktif
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-sm <?= $v['reminder_enabled'] ? 'btn-outline-warning' : 'btn-outline-success' ?> toggle-reminder-btn"
                                                        data-vehicle-id="<?= $v['id'] ?>"
                                                        data-enabled="<?= $v['reminder_enabled'] ?>">
                                                    <i class="fas fa-<?= $v['reminder_enabled'] ? 'pause' : 'play' ?>"></i>
                                                    <?= $v['reminder_enabled'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                                </button>
                                                <?php if ($v['reminder_enabled']): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info snooze-reminder-btn"
                                                            data-vehicle-id="<?= $v['id'] ?>">
                                                        <i class="fas fa-clock"></i> Snooze
                                                    </button>
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
    </div>
</div>

<!-- Modal Snooze -->
<div class="modal fade" id="snoozeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tunda Pengingat (Snooze)</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Pilih durasi penundaan pengingat:</p>
                <input type="hidden" id="snooze_vehicle_id">
                <div class="form-group">
                    <select id="snooze_days" class="form-control">
                        <option value="7">7 Hari</option>
                        <option value="14">14 Hari</option>
                        <option value="30" selected>30 Hari (Rekomendasi)</option>
                        <option value="60">60 Hari</option>
                        <option value="90">90 Hari</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="confirmSnooze()">
                    <i class="fas fa-clock"></i> Tunda Pengingat
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle reminder enabled/disabled
    $('.toggle-reminder-btn').click(function() {
        var btn = $(this);
        var vehicleId = btn.data('vehicle-id');
        var enabled = btn.data('enabled') == 1;
        
        if (!confirm('Apakah Anda yakin ingin ' + (enabled ? 'menonaktifkan' : 'mengaktifkan') + ' pengingat untuk kendaraan ini?')) {
            return;
        }
        
        $.post('<?= site_url('notifications/toggle_reminder') ?>', { vehicle_id: vehicleId })
            .done(function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Gagal mengubah pengaturan.');
                }
            })
            .fail(function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
    });
    
    // Snooze reminder
    $('.snooze-reminder-btn').click(function() {
        $('#snooze_vehicle_id').val($(this).data('vehicle-id'));
        $('#snoozeModal').modal('show');
    });
});

function confirmSnooze() {
    var vehicleId = $('#snooze_vehicle_id').val();
    var days = $('#snooze_days').val();
    
    $.post('<?= site_url('notifications/snooze_reminder') ?>', { 
        vehicle_id: vehicleId, 
        days: days 
    })
    .done(function(response) {
        $('#snoozeModal').modal('hide');
        if (response.success) {
            location.reload();
        } else {
            alert(response.message || 'Gagal menunda pengingat.');
        }
    })
    .fail(function() {
        alert('Terjadi kesalahan. Silakan coba lagi.');
    });
}
</script>

<?php $this->load->view('layouts/user_footer'); ?>
