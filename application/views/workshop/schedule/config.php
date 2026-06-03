<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i><?= $title ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= site_url('workshop_schedule/save_schedule') ?>" method="POST" id="scheduleForm">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">Hari</th>
                                        <th width="10%">Status</th>
                                        <th width="15%">Jam Buka</th>
                                        <th width="15%">Jam Tutup</th>
                                        <th width="20%">Interval Slot</th>
                                        <th width="15%">Kapasitas/Slot</th>
                                        <th width="10%">Info</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $day_names = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                    for ($i = 0; $i <= 6; $i++): 
                                        $sch = isset($schedules[$i]) ? $schedules[$i] : null;
                                        $is_open = $sch ? $sch['is_open'] : ($i == 0 ? 0 : 1);
                                    ?>
                                    <tr class="<?= $is_open ? '' : 'table-secondary' ?>">
                                        <td><strong><?= $day_names[$i] ?></strong></td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input day-toggle" type="checkbox" 
                                                       name="day[<?= $i ?>]" value="1" 
                                                       id="day_<?= $i ?>" 
                                                       <?= $is_open ? 'checked' : '' ?>
                                                       onchange="toggleDayFields(<?= $i ?>)">
                                                <label class="form-check-label" for="day_<?= $i ?>">
                                                    <?= $is_open ? 'Buka' : 'Tutup' ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="time" class="form-control form-control-sm day-field" 
                                                   name="open_time_<?= $i ?>" id="open_time_<?= $i ?>"
                                                   value="<?= $sch && $sch['open_time'] ? substr($sch['open_time'], 0, 5) : '08:00' ?>"
                                                   <?= !$is_open ? 'disabled' : '' ?>>
                                        </td>
                                        <td>
                                            <input type="time" class="form-control form-control-sm day-field" 
                                                   name="close_time_<?= $i ?>" id="close_time_<?= $i ?>"
                                                   value="<?= $sch && $sch['close_time'] ? substr($sch['close_time'], 0, 5) : '17:00' ?>"
                                                   <?= !$is_open ? 'disabled' : '' ?>>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm day-field" 
                                                    name="slot_interval_<?= $i ?>" id="slot_interval_<?= $i ?>"
                                                    <?= !$is_open ? 'disabled' : '' ?>>
                                                <?php foreach ($interval_options as $val => $label): ?>
                                                <option value="<?= $val ?>" 
                                                        <?= $sch && $sch['slot_interval'] == $val ? 'selected' : ($val == 60 ? 'selected' : '') ?>>
                                                    <?= $label ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">BR-82, BR-83: 30-240 menit</small>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm day-field" 
                                                   name="capacity_<?= $i ?>" id="capacity_<?= $i ?>"
                                                   value="<?= $sch && $sch['capacity_per_slot'] ? $sch['capacity_per_slot'] : 1 ?>"
                                                   min="1" max="20"
                                                   <?= !$is_open ? 'disabled' : '' ?>>
                                            <small class="text-muted">1-20 kendaraan</small>
                                        </td>
                                        <td>
                                            <span class="badge <?= $is_open ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $is_open ? 'Aktif' : 'Nonaktif' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Catatan:</strong> Perubahan konfigurasi tidak akan mempengaruhi booking yang sudah ada (BR-81).
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="<?= site_url('workshop_schedule/blocked_dates') ?>" class="btn btn-warning">
                                <i class="fas fa-calendar-times me-2"></i>Kelola Hari Libur
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Konfigurasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDayFields(dayIndex) {
    const checkbox = document.getElementById('day_' + dayIndex);
    const isOpen = checkbox.checked;
    
    // Update label
    checkbox.nextElementSibling.textContent = isOpen ? 'Buka' : 'Tutup';
    
    // Toggle row style
    const row = checkbox.closest('tr');
    if (isOpen) {
        row.classList.remove('table-secondary');
    } else {
        row.classList.add('table-secondary');
    }
    
    // Toggle all fields in this row
    const fields = row.querySelectorAll('.day-field');
    fields.forEach(field => {
        field.disabled = !isOpen;
    });
}
</script>
