<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-car"></i> Daftar Kendaraan Saya</h4>
            <?php if ($can_add['can_add']): ?>
                <a href="<?php echo site_url('user/vehicle_add'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Kendaraan
                </a>
            <?php else: ?>
                <span class="badge bg-warning text-dark">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Maksimal <?php echo $can_add['max']; ?> kendaraan tercapai (<?php echo $can_add['count']; ?>/<?php echo $can_add['max']; ?>)
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (empty($vehicles)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-car-side fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Belum ada kendaraan terdaftar</h5>
            <p class="text-muted">Tambahkan kendaraan Anda untuk memudahkan pemesanan servis.</p>
            <?php if ($can_add['can_add']): ?>
                <a href="<?php echo site_url('user/vehicle_add'); ?>" class="btn btn-primary mt-2">
                    <i class="fas fa-plus"></i> Tambah Kendaraan Pertama
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100 vehicle-card" data-vehicle-id="<?php echo $vehicle->id; ?>">
                    <div class="card-header bg-white border-0 pt-3 pb-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-<?php echo $vehicle->fuel_type == 'electric' ? 'success' : 'primary'; ?>">
                                <?php 
                                    $fuel_labels = ['petrol' => 'Bensin', 'diesel' => 'Solar', 'electric' => 'Listrik', 'hybrid' => 'Hybrid'];
                                    echo $fuel_labels[$vehicle->fuel_type] ?? $vehicle->fuel_type;
                                ?>
                            </span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo site_url('user/vehicle_detail/' . $vehicle->id); ?>">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo site_url('user/vehicle_edit/' . $vehicle->id); ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger delete-vehicle-btn" href="#" data-vehicle-id="<?php echo $vehicle->id; ?>" data-vehicle-number="<?php echo htmlspecialchars($vehicle->vehicle_number); ?>">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <i class="fas fa-car fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title text-center mb-1"><?php echo htmlspecialchars($vehicle->brand . ' ' . $vehicle->model); ?></h5>
                        <p class="text-center text-muted mb-3">
                            <strong><?php echo htmlspecialchars($vehicle->vehicle_number); ?></strong>
                        </p>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Tahun</small>
                                <strong><?php echo $vehicle->year; ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Kilometer</small>
                                <strong id="km-<?php echo $vehicle->id; ?>"><?php echo number_format($vehicle->current_km); ?> km</strong>
                            </div>
                        </div>
                        
                        <?php if (!empty($vehicle->vin)): ?>
                            <div class="mb-2">
                                <small class="text-muted d-block">VIN</small>
                                <code><?php echo htmlspecialchars($vehicle->vin); ?></code>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-0 pb-3">
                        <div class="d-grid">
                            <a href="<?php echo site_url('user/vehicle_detail/' . $vehicle->id); ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-history"></i> Lihat Riwayat Servis
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteVehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus Kendaraan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus kendaraan <strong id="deleteVehicleNumber"></strong>?</p>
                <p class="text-muted small">Data historis booking akan tetap tersimpan, namun kendaraan tidak dapat digunakan untuk booking baru.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Delete vehicle functionality
    let vehicleToDelete = null;
    
    $('.delete-vehicle-btn').on('click', function(e) {
        e.preventDefault();
        vehicleToDelete = $(this).data('vehicle-id');
        const vehicleNumber = $(this).data('vehicle-number');
        
        $('#deleteVehicleNumber').text(vehicleNumber);
        $('#deleteVehicleModal').modal('show');
    });
    
    $('#confirmDeleteBtn').on('click', function() {
        if (!vehicleToDelete) return;
        
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghapus...');
        
        $.ajax({
            url: '<?php echo site_url("user/vehicle_delete"); ?>/' + vehicleToDelete,
            type: 'POST',
            dataType: 'json',
            data: {
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteVehicleModal').modal('hide');
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
                    btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Ya, Hapus');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat menghapus kendaraan.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message
                });
                btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Ya, Hapus');
            }
        });
    });
    
    $('#deleteVehicleModal').on('hidden.bs.modal', function() {
        vehicleToDelete = null;
        $('#confirmDeleteBtn').prop('disabled', false).html('<i class="fas fa-trash"></i> Ya, Hapus');
    });
});
</script>

<style>
.vehicle-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.vehicle-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}
</style>
