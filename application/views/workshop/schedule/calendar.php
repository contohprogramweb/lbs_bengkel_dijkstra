<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-week me-2"></i><?= $title ?></h5>
                    <div>
                        <a href="<?= site_url('workshop') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Statistik Mingguan -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-3"><i class="fas fa-chart-bar me-2"></i>Statistik Minggu Ini</h6>
                        </div>
                        <?php foreach ($statistics as $stat): ?>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted small"><?= date('d M', strtotime($stat['date'])) ?></h6>
                                    <p class="mb-1 fw-bold"><?= date('l', strtotime($stat['date'])) ?></p>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Slot:</span>
                                        <span class="fw-bold"><?= $stat['total_slots'] ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Terisi:</span>
                                        <span class="text-primary fw-bold"><?= $stat['total_booked'] ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Utilisasi:</span>
                                        <span class="<?= $stat['utilization'] > 80 ? 'text-danger' : ($stat['utilization'] > 50 ? 'text-warning' : 'text-success') ?> fw-bold">
                                            <?= $stat['utilization'] ?>%
                                        </span>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar <?= $stat['utilization'] > 80 ? 'bg-danger' : ($stat['utilization'] > 50 ? 'bg-warning' : 'bg-success') ?>" 
                                             role="progressbar" 
                                             style="width: <?= $stat['utilization'] ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Kalender FullCalendar -->
                    <div id="calendar"></div>

                    <!-- Legend -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Legenda Status Booking</h6>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning me-2" style="width: 20px; height: 20px;"></span>
                                <span>Pending</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2" style="width: 20px; height: 20px;"></span>
                                <span>Accepted</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info me-2" style="width: 20px; height: 20px;"></span>
                                <span>In Progress</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-secondary me-2" style="width: 20px; height: 20px;"></span>
                                <span>Completed</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2" style="width: 20px; height: 20px;"></span>
                                <span>Hari Libur</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Event -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-calendar-check me-2"></i>Detail Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="#" class="btn btn-primary" id="viewBookingBtn">Lihat Detail</a>
            </div>
        </div>
    </div>
</div>

<!-- Load FullCalendar -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'id',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            day: 'Hari',
            list: 'Daftar'
        },
        allDaySlot: true,
        slotMinTime: '07:00:00',
        slotMaxTime: '20:00:00',
        firstDay: 1, // Senin
        events: '<?= site_url('workshop_schedule/ajax_get_events') ?>',
        eventClick: function(info) {
            if (!info.event.id.startsWith('blocked_')) {
                // Show booking detail modal
                var props = info.event.extendedProps;
                var statusBadge = '';
                
                switch(props.status) {
                    case 'pending': statusBadge = '<span class="badge bg-warning">Pending</span>'; break;
                    case 'accepted': statusBadge = '<span class="badge bg-success">Accepted</span>'; break;
                    case 'in_progress': statusBadge = '<span class="badge bg-info">In Progress</span>'; break;
                    case 'completed': statusBadge = '<span class="badge bg-secondary">Completed</span>'; break;
                    default: statusBadge = '<span class="badge bg-secondary">' + props.status + '</span>';
                }
                
                var html = `
                    <table class="table table-sm">
                        <tr>
                            <th width="30%">Pelanggan</th>
                            <td>${info.event.title.split(' - ')[0]}</td>
                        </tr>
                        <tr>
                            <th>Kendaraan</th>
                            <td>${props.vehicle || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <td>${info.event.start.toLocaleDateString('id-ID')} ${info.event.start.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})}</td>
                        </tr>
                        <tr>
                            <th>Layanan</th>
                            <td>${props.service_type || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>${statusBadge}</td>
                        </tr>
                        <tr>
                            <th>Kontak</th>
                            <td>${props.phone || 'N/A'}</td>
                        </tr>
                    </table>
                `;
                
                $('#eventModalBody').html(html);
                $('#viewBookingBtn').attr('href', info.event.url);
                $('#eventModal').modal('show');
            }
        },
        nowIndicator: true,
        height: 'auto',
        navLinks: true
    });
    
    calendar.render();
});
</script>
