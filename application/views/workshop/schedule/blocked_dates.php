<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-calendar-times me-2"></i><?= $title ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <p class="text-muted mb-2">Klik pada tanggal di kalender untuk memblokir hari tersebut.</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="<?= site_url('workshop_schedule') ?>" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Konfigurasi
                            </a>
                        </div>
                    </div>

                    <!-- Kalender FullCalendar -->
                    <div id="calendar" class="mb-4"></div>

                    <!-- List Blocked Dates -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Tanggal Diblokir</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($blocked_dates)): ?>
                                <p class="text-muted text-center mb-0">Belum ada tanggal yang diblokir.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Hari</th>
                                                <th>Alasan</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($blocked_dates as $bd): ?>
                                            <tr>
                                                <td><?= date('d M Y', strtotime($bd['blocked_date'])) ?></td>
                                                <td><?= date('l', strtotime($bd['blocked_date'])) ?></td>
                                                <td><?= $bd['reason'] ?? '-' ?></td>
                                                <td>
                                                    <span class="badge <?= $bd['is_full_day'] ? 'bg-danger' : 'bg-warning' ?>">
                                                        <?= $bd['is_full_day'] ? 'Full Day' : 'Partial' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger unblock-btn" 
                                                            data-date="<?= $bd['blocked_date'] ?>">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
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
    </div>
</div>

<!-- Modal Blokir Tanggal -->
<div class="modal fade" id="blockDateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-calendar-times me-2"></i>Blokir Tanggal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="blockDateInput">
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="text" class="form-control" id="displayDate" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alasan</label>
                    <textarea class="form-control" id="blockReason" rows="3" placeholder="Contoh: Libur Nasional, Cuti Bersama, dll"></textarea>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="isFullDay" checked>
                    <label class="form-check-label" for="isFullDay">
                        Blokir seluruh hari (full day)
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" id="confirmBlockBtn">
                    <i class="fas fa-ban me-2"></i>Blokir Tanggal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Load FullCalendar CSS & JS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu'
        },
        events: '<?= site_url('workshop_schedule/ajax_get_events') ?>',
        selectable: true,
        select: function(info) {
            // User klik tanggal
            openBlockModal(info.startStr);
        },
        eventClick: function(info) {
            if (info.event.id.startsWith('blocked_')) {
                // Klik event blocked date
                Swal.fire({
                    title: 'Hapus Blokir?',
                    text: 'Tanggal ini akan dibuka kembali.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus Blokir',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('<?= site_url('workshop_schedule/ajax_unblock_date') ?>', {
                            date: info.event.startStr
                        }, function(response) {
                            if (response.success) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                calendar.refetchEvents();
                                location.reload();
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        }, 'json');
                    }
                });
            }
        }
    });
    
    calendar.render();
    
    // Open modal block date
    function openBlockModal(dateStr) {
        $('#blockDateInput').val(dateStr);
        $('#displayDate').val(new Date(dateStr).toLocaleDateString('id-ID', { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
        }));
        $('#blockReason').val('');
        $('#isFullDay').prop('checked', true);
        $('#blockDateModal').modal('show');
    }
    
    // Confirm block date
    $('#confirmBlockBtn').click(function() {
        var date = $('#blockDateInput').val();
        var reason = $('#blockReason').val();
        var isFullDay = $('#isFullDay').is(':checked') ? 1 : 0;
        
        $.post('<?= site_url('workshop_schedule/ajax_block_date') ?>', {
            date: date,
            reason: reason,
            is_full_day: isFullDay
        }, function(response) {
            $('#blockDateModal').modal('hide');
            if (response.success) {
                Swal.fire('Berhasil!', response.message, 'success');
                calendar.refetchEvents();
                location.reload();
            } else {
                Swal.fire('Gagal!', response.message, 'error');
            }
        }, 'json');
    });
    
    // Unblock from list
    $('.unblock-btn').click(function() {
        var date = $(this).data('date');
        Swal.fire({
            title: 'Hapus Blokir?',
            text: 'Tanggal ' + date + ' akan dibuka kembali.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= site_url('workshop_schedule/ajax_unblock_date') ?>', {
                    date: date
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        location.reload();
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                }, 'json');
            }
        });
    });
});
</script>
