<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Left Column - Vehicle Info -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="fas fa-car"></i> Informasi Kendaraan</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="fas fa-car fa-4x text-primary"></i>
                </div>
                
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Nomor Polisi</th>
                        <td><strong><?php echo htmlspecialchars($vehicle->vehicle_number); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Merk</th>
                        <td><?php echo htmlspecialchars($vehicle->brand); ?></td>
                    </tr>
                    <tr>
                        <th>Model</th>
                        <td><?php echo htmlspecialchars($vehicle->model ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Tahun</th>
                        <td><?php echo $vehicle->year; ?></td>
                    </tr>
                    <tr>
                        <th>Tipe</th>
                        <td>
                            <?php 
                                $type_labels = ['motorcycle' => 'Motor', 'car' => 'Mobil', 'truck' => 'Truk', 'bus' => 'Bus', 'other' => 'Lainnya'];
                                echo $type_labels[$vehicle->vehicle_type] ?? $vehicle->vehicle_type;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Bahan Bakar</th>
                        <td>
                            <?php 
                                $fuel_labels = ['petrol' => 'Bensin', 'diesel' => 'Solar', 'electric' => 'Listrik', 'hybrid' => 'Hybrid'];
                                echo '<span class="badge bg-' . ($vehicle->fuel_type == 'electric' ? 'success' : 'primary') . '">';
                                echo $fuel_labels[$vehicle->fuel_type] ?? $vehicle->fuel_type;
                                echo '</span>';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Transmisi</th>
                        <td>
                            <?php 
                                $trans_labels = ['manual' => 'Manual', 'automatic' => 'Otomatis', 'cvt' => 'CVT'];
                                echo $trans_labels[$vehicle->transmission] ?? $vehicle->transmission;
                            ?>
                        </td>
                    </tr>
                    <?php if (!empty($vehicle->color)): ?>
                    <tr>
                        <th>Warna</th>
                        <td><?php echo htmlspecialchars($vehicle->color); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($vehicle->vin)): ?>
                    <tr>
                        <th>VIN</th>
                        <td><code><?php echo htmlspecialchars($vehicle->vin); ?></code></td>
                    </tr>
                    <?php endif; ?>
                </table>
                
                <div class="d-grid gap-2 mt-3">
                    <a href="<?php echo site_url('user/vehicle_edit/' . $vehicle->id); ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit Kendaraan
                    </a>
                </div>
            </div>
        </div>

        <!-- Service Recommendation Card -->
        <?php if ($recommendation): ?>
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Rekomendasi Servis</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <?php if ($recommendation['overdue']): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                            <p class="mb-0 mt-2"><strong>Servis Sudah Lewat!</strong></p>
                        </div>
                    <?php elseif ($recommendation['needs_service_soon']): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-clock fa-2x"></i>
                            <p class="mb-0 mt-2"><strong>Segera Servis</strong></p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                            <p class="mb-0 mt-2"><strong>Kondisi Baik</strong></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small text-muted">Progress Interval Servis</label>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar <?php echo $recommendation['percentage_used'] >= 80 ? 'bg-danger' : ($recommendation['percentage_used'] >= 60 ? 'bg-warning' : 'bg-success'); ?>" 
                             role="progressbar" 
                             style="width: <?php echo min(100, $recommendation['percentage_used']); ?>%"
                             aria-valuenow="<?php echo $recommendation['percentage_used']; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <?php echo round($recommendation['percentage_used']); ?>%
                        </div>
                    </div>
                </div>
                
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <small class="text-muted d-block">Kilometer Saat Ini</small>
                            <strong id="current_km_display"><?php echo number_format($recommendation['current_km']); ?></strong> km
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <small class="text-muted d-block">Servis Berikutnya</small>
                            <strong><?php echo number_format($recommendation['next_service_km']); ?></strong> km
                        </div>
                    </div>
                </div>
                
                <?php if ($recommendation['km_until_service'] > 0 && !$recommendation['overdue']): ?>
                <div class="text-center mt-3">
                    <small class="text-muted">Sisa <?php echo number_format($recommendation['km_until_service']); ?> km lagi</small>
                </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-primary w-100" id="updateKmBtn" data-vehicle-id="<?php echo $vehicle->id; ?>">
                        <i class="fas fa-tachometer-alt"></i> Update Kilometer
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column - Service History -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <ul class="nav nav-tabs card-header-tabs" id="vehicleTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                            <i class="fas fa-history"></i> Riwayat Servis
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="vehicleTabsContent">
                    <div class="tab-pane fade show active" id="history" role="tabpanel">
                        <?php if (empty($service_history)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada riwayat servis</h5>
                                <p class="text-muted">Riwayat servis akan muncul setelah Anda melakukan pemesanan layanan.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No. Booking</th>
                                            <th>Bengkel</th>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($service_history as $booking): ?>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-decoration-none"><?php echo htmlspecialchars($booking['booking_number']); ?></a>
                                                </td>
                                                <td><?php echo htmlspecialchars($booking['workshop_name'] ?: '-'); ?></td>
                                                <td>
                                                    <?php 
                                                        $date = strtotime($booking['scheduled_date']);
                                                        echo date('d/m/Y', $date);
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $status_badges = [
                                                            'completed' => 'success',
                                                            'cancelled' => 'secondary',
                                                            'pending' => 'warning',
                                                            'accepted' => 'info',
                                                            'in_progress' => 'primary'
                                                        ];
                                                        $status_labels = [
                                                            'completed' => 'Selesai',
                                                            'cancelled' => 'Dibatalkan',
                                                            'pending' => 'Pending',
                                                            'accepted' => 'Diterima',
                                                            'in_progress' => 'Diproses'
                                                        ];
                                                        $status = $booking['status'];
                                                        echo '<span class="badge bg-' . ($status_badges[$status] ?? 'secondary') . '">';
                                                        echo $status_labels[$status] ?? $status;
                                                        echo '</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars(substr($booking['service_description'], 0, 50)); ?>
                                                    <?php echo strlen($booking['service_description']) > 50 ? '...' : ''; ?>
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

<!-- Update Kilometer Modal -->
<div class="modal fade" id="updateKmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-tachometer-alt"></i> Update Kilometer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="updateVehicleId" value="<?php echo $vehicle->id; ?>">
                <div class="mb-3">
                    <label class="form-label">Kilometer Saat Ini</label>
                    <input type="text" class="form-control" id="currentKmDisplay" value="<?php echo number_format($vehicle->current_km); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="newKm" class="form-label">Kilometer Baru <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="newKm" name="current_km" 
                               min="<?php echo $vehicle->current_km; ?>" step="1" required>
                        <span class="input-group-text">km</span>
                    </div>
                    <div class="form-text">Kilometer baru harus lebih besar atau sama dengan kilometer sebelumnya.</div>
                    <div id="kmValidationMessage" class="text-danger small mt-1"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveKmBtn">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Update Kilometer Modal
    $('#updateKmBtn').on('click', function() {
        const vehicleId = $(this).data('vehicle-id');
        $('#updateVehicleId').val(vehicleId);
        $('#newKm').val('').attr('min', '<?php echo $vehicle->current_km; ?>');
        $('#kmValidationMessage').html('');
        $('#updateKmModal').modal('show');
    });

    // Validate new KM input
    $('#newKm').on('input', function() {
        const currentKm = <?php echo $vehicle->current_km; ?>;
        const newKm = parseInt($(this).val()) || 0;
        
        if (newKm < currentKm) {
            $('#kmValidationMessage').html('<i class="fas fa-exclamation-circle"></i> Kilometer baru harus ≥ ' + currentKm.toLocaleString() + ' km');
        } else {
            $('#kmValidationMessage').html('');
        }
    });

    // Save new KM
    $('#saveKmBtn').on('click', function() {
        const vehicleId = $('#updateVehicleId').val();
        const newKm = parseInt($('#newKm').val()) || 0;
        const currentKm = <?php echo $vehicle->current_km; ?>;
        
        if (newKm < currentKm) {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: 'Kilometer baru harus lebih besar atau sama dengan kilometer sebelumnya.'
            });
            return;
        }
        
        if (!newKm) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Kosong',
                text: 'Silakan masukkan kilometer baru.'
            });
            return;
        }
        
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        
        $.ajax({
            url: '<?php echo site_url("user/update_odometer"); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                vehicle_id: vehicleId,
                current_km: newKm,
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#updateKmModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat menyimpan kilometer.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message
                });
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
            }
        });
    });

    $('#updateKmModal').on('hidden.bs.modal', function() {
        $('#saveKmBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
    });
});
</script>
