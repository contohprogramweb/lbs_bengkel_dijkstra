<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i><?= $title ?></h5>
                    <a href="<?= site_url('workshop_schedule/calendar') ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!$booking): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>Booking tidak ditemukan.
                        </div>
                    <?php else: ?>
                        <!-- Status Badge -->
                        <div class="text-center mb-4">
                            <?php
                            $status_class = [
                                'pending' => 'bg-warning',
                                'accepted' => 'bg-success',
                                'in_progress' => 'bg-info',
                                'completed' => 'bg-secondary',
                                'cancelled' => 'bg-danger'
                            ];
                            $status_labels = [
                                'pending' => 'Menunggu Konfirmasi',
                                'accepted' => 'Diterima',
                                'in_progress' => 'Sedang Dikerjakan',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan'
                            ];
                            ?>
                            <span class="badge <?= $status_class[$booking['status']] ?? 'bg-secondary' ?> fs-6 p-3">
                                <?= $status_labels[$booking['status']] ?? ucfirst($booking['status']) ?>
                            </span>
                        </div>

                        <!-- Booking Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted border-bottom pb-2"><i class="fas fa-clock me-2"></i>Informasi Jadwal</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="40%">Tanggal</th>
                                        <td>: <?= date('d F Y', strtotime($booking['appointment_date'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Waktu</th>
                                        <td>: <?= date('H:i', strtotime($booking['appointment_time'])) ?> WIB</td>
                                    </tr>
                                    <tr>
                                        <th>Layanan</th>
                                        <td>: <?= ucfirst($booking['service_type']) ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted border-bottom pb-2"><i class="fas fa-user me-2"></i>Informasi Pelanggan</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="40%">Nama</th>
                                        <td>: <?= $booking['user_name'] ?? 'N/A' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Telepon</th>
                                        <td>: <?= $booking['phone'] ?? 'N/A' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>: <?= $booking['email'] ?? 'N/A' ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Vehicle Info -->
                        <div class="mb-4">
                            <h6 class="text-muted border-bottom pb-2"><i class="fas fa-car me-2"></i>Informasi Kendaraan</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="20%">Merk</th>
                                    <td>: <?= $booking['brand'] ?? 'N/A' ?></td>
                                    <th width="20%">Model</th>
                                    <td>: <?= $booking['model'] ?? 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <th>Nomor Polisi</th>
                                    <td>: <?= strtoupper($booking['vehicle_number'] ?? 'N/A') ?></td>
                                    <th>Tahun</th>
                                    <td>: <?= $booking['year'] ?? 'N/A' ?></td>
                                </tr>
                                <?php if (!empty($booking['vin'])): ?>
                                <tr>
                                    <th>VIN</th>
                                    <td colspan="3">: <?= $booking['vin'] ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>

                        <!-- Complaint/Notes -->
                        <?php if (!empty($booking['complaint_notes'])): ?>
                        <div class="mb-4">
                            <h6 class="text-muted border-bottom pb-2"><i class="fas fa-sticky-note me-2"></i>Catatan Keluhan</h6>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(htmlspecialchars($booking['complaint_notes'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Actions -->
                        <div class="border-top pt-3 mt-4">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Dibuat pada: <?= date('d M Y H:i', strtotime($booking['created_at'])) ?>
                                </small>
                                <div class="btn-group">
                                    <?php if ($booking['status'] == 'pending'): ?>
                                    <a href="<?= site_url('workshop/bookings/approve/' . $booking['id']) ?>" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-check me-1"></i>Terima Booking
                                    </a>
                                    <a href="<?= site_url('workshop/bookings/reject/' . $booking['id']) ?>" 
                                       class="btn btn-danger btn-sm">
                                        <i class="fas fa-times me-1"></i>Tolak
                                    </a>
                                    <?php elseif ($booking['status'] == 'accepted'): ?>
                                    <a href="<?= site_url('workshop/bookings/start/' . $booking['id']) ?>" 
                                       class="btn btn-info btn-sm text-white">
                                        <i class="fas fa-play me-1"></i>Mulai Pengerjaan
                                    </a>
                                    <?php elseif ($booking['status'] == 'in_progress'): ?>
                                    <a href="<?= site_url('workshop/bookings/complete/' . $booking['id']) ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-check-double me-1"></i>Selesaikan
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
