<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0">
            <i class="fas fa-<?php echo isset($vehicle) ? 'edit' : 'plus'; ?>"></i> 
            <?php echo isset($vehicle) ? 'Edit' : 'Tambah'; ?> Kendaraan
        </h5>
    </div>
    <div class="card-body">
        <form method="post" id="vehicleForm" novalidate>
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <!-- Nomor Polisi -->
                    <div class="mb-3">
                        <label for="vehicle_number" class="form-label">Nomor Polisi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo form_error('vehicle_number') ? 'is-invalid' : ''; ?>" 
                               id="vehicle_number" name="vehicle_number" 
                               value="<?php echo isset($vehicle) ? htmlspecialchars($vehicle->vehicle_number) : set_value('vehicle_number'); ?>"
                               placeholder="Contoh: B 1234 ABC" required>
                        <div class="invalid-feedback"><?php echo form_error('vehicle_number'); ?></div>
                        <div id="plate_check_result" class="form-text"></div>
                    </div>

                    <!-- Merk (Select2) -->
                    <div class="mb-3">
                        <label for="brand" class="form-label">Merk <span class="text-danger">*</span></label>
                        <select class="form-select <?php echo form_error('brand') ? 'is-invalid' : ''; ?>" 
                                id="brand" name="brand" required>
                            <option value="">-- Pilih Merk --</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo htmlspecialchars($brand); ?>" 
                                    <?php echo (isset($vehicle) && $vehicle->brand == $brand) || set_value('brand') == $brand ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?php echo form_error('brand'); ?></div>
                    </div>

                    <!-- Model -->
                    <div class="mb-3">
                        <label for="model" class="form-label">Model</label>
                        <input type="text" class="form-control <?php echo form_error('model') ? 'is-invalid' : ''; ?>" 
                               id="model" name="model" 
                               value="<?php echo isset($vehicle) ? htmlspecialchars($vehicle->model) : set_value('model'); ?>"
                               placeholder="Contoh: Avanza, Jazz, etc.">
                        <div class="invalid-feedback"><?php echo form_error('model'); ?></div>
                    </div>

                    <!-- Tahun -->
                    <div class="mb-3">
                        <label for="year" class="form-label">Tahun <span class="text-danger">*</span></label>
                        <select class="form-select <?php echo form_error('year') ? 'is-invalid' : ''; ?>" 
                                id="year" name="year" required>
                            <option value="">-- Pilih Tahun --</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?php echo $y; ?>" 
                                    <?php echo (isset($vehicle) && $vehicle->year == $y) || set_value('year') == $y ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?php echo form_error('year'); ?></div>
                        <div id="year_check_result" class="form-text"></div>
                    </div>

                    <!-- Jenis Bahan Bakar -->
                    <div class="mb-3">
                        <label for="fuel_type" class="form-label">Jenis Bahan Bakar <span class="text-danger">*</span></label>
                        <select class="form-select <?php echo form_error('fuel_type') ? 'is-invalid' : ''; ?>" 
                                id="fuel_type" name="fuel_type" required>
                            <option value="">-- Pilih Bahan Bakar --</option>
                            <?php foreach ($fuel_types as $key => $label): ?>
                                <option value="<?php echo $key; ?>" 
                                    <?php echo (isset($vehicle) && $vehicle->fuel_type == $key) || set_value('fuel_type') == $key ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?php echo form_error('fuel_type'); ?></div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <!-- Tipe Kendaraan -->
                    <div class="mb-3">
                        <label for="vehicle_type" class="form-label">Tipe Kendaraan</label>
                        <select class="form-select" id="vehicle_type" name="vehicle_type">
                            <?php foreach ($vehicle_types as $key => $label): ?>
                                <option value="<?php echo $key; ?>" 
                                    <?php echo (isset($vehicle) && $vehicle->vehicle_type == $key) || set_value('vehicle_type') == $key ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Transmisi -->
                    <div class="mb-3">
                        <label for="transmission" class="form-label">Transmisi</label>
                        <select class="form-select" id="transmission" name="transmission">
                            <?php foreach ($transmissions as $key => $label): ?>
                                <option value="<?php echo $key; ?>" 
                                    <?php echo (isset($vehicle) && $vehicle->transmission == $key) || set_value('transmission') == $key ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Warna -->
                    <div class="mb-3">
                        <label for="color" class="form-label">Warna</label>
                        <input type="text" class="form-control" 
                               id="color" name="color" 
                               value="<?php echo isset($vehicle) ? htmlspecialchars($vehicle->color) : set_value('color'); ?>"
                               placeholder="Contoh: Hitam, Putih, Silver">
                    </div>

                    <!-- Kilometer Terakhir -->
                    <div class="mb-3">
                        <label for="current_km" class="form-label">Kilometer Terakhir <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control <?php echo form_error('current_km') ? 'is-invalid' : ''; ?>" 
                                   id="current_km" name="current_km" min="0" step="1"
                                   value="<?php echo isset($vehicle) ? $vehicle->current_km : set_value('current_km'); ?>" required>
                            <span class="input-group-text">km</span>
                        </div>
                        <div class="invalid-feedback"><?php echo form_error('current_km'); ?></div>
                    </div>

                    <!-- VIN (Optional) -->
                    <div class="mb-3">
                        <label for="vin" class="form-label">VIN (Vehicle Identification Number)</label>
                        <input type="text" class="form-control <?php echo form_error('vin') ? 'is-invalid' : ''; ?>" 
                               id="vin" name="vin" maxlength="17"
                               value="<?php echo isset($vehicle) ? htmlspecialchars($vehicle->vin) : set_value('vin'); ?>"
                               placeholder="17 karakter (opsional)">
                        <div class="invalid-feedback"><?php echo form_error('vin'); ?></div>
                        <div class="form-text">Format: 17 karakter alfanumerik (tanpa I, O, Q)</div>
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            <div class="mb-3">
                <label for="notes" class="form-label">Catatan</label>
                <textarea class="form-control" id="notes" name="notes" rows="3" 
                          placeholder="Catatan tambahan tentang kendaraan..."><?php echo isset($vehicle) ? htmlspecialchars($vehicle->notes) : set_value('notes'); ?></textarea>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="<?php echo site_url('user/vehicles'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo isset($vehicle) ? 'Perbarui' : 'Simpan'; ?> Kendaraan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2 for brand
    $('#brand').select2({
        placeholder: '-- Pilih Merk --',
        allowClear: true,
        dropdownParent: $('#brand').closest('.modal').length ? $('#brand').closest('.modal') : $(document.body)
    });

    // Real-time plate number validation
    let plateCheckTimeout;
    $('#vehicle_number').on('input', function() {
        clearTimeout(plateCheckTimeout);
        const plate = $(this).val().trim();
        const excludeId = '<?php echo isset($vehicle) ? $vehicle->id : ''; ?>';
        
        if (plate.length < 3) {
            $('#plate_check_result').html('').removeClass('text-success text-danger');
            return;
        }
        
        plateCheckTimeout = setTimeout(function() {
            $.ajax({
                url: '<?php echo site_url("user/check_vehicle_number"); ?>',
                type: 'GET',
                data: {
                    vehicle_number: plate,
                    exclude_id: excludeId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.available) {
                        $('#plate_check_result')
                            .html('<i class="fas fa-check-circle text-success"></i> ' + response.message)
                            .removeClass('text-danger')
                            .addClass('text-success');
                    } else {
                        $('#plate_check_result')
                            .html('<i class="fas fa-times-circle text-danger"></i> ' + response.message)
                            .removeClass('text-success')
                            .addClass('text-danger');
                    }
                }
            });
        }, 500);
    });

    // Real-time year validation
    $('#year').on('change', function() {
        const year = $(this).val();
        
        if (!year) {
            $('#year_check_result').html('').removeClass('text-success text-danger');
            return;
        }
        
        $.ajax({
            url: '<?php echo site_url("user/validate_year"); ?>',
            type: 'GET',
            data: { year: year },
            dataType: 'json',
            success: function(response) {
                if (response.valid) {
                    $('#year_check_result')
                        .html('<i class="fas fa-check-circle text-success"></i> ' + response.message)
                        .removeClass('text-danger')
                        .addClass('text-success');
                } else {
                    $('#year_check_result')
                        .html('<i class="fas fa-times-circle text-danger"></i> ' + response.message)
                        .removeClass('text-success')
                        .addClass('text-danger');
                }
            }
        });
    });

    // VIN auto-uppercase and validation
    $('#vin').on('input', function() {
        let val = $(this).val().toUpperCase();
        // Remove invalid characters (I, O, Q are not used in VIN)
        val = val.replace(/[IOQ]/g, '');
        $(this).val(val);
    });

    // Form validation before submit
    $('#vehicleForm').on('submit', function(e) {
        const plateResult = $('#plate_check_result');
        if (plateResult.hasClass('text-danger')) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: 'Nomor polisi sudah terdaftar. Silakan gunakan nomor yang berbeda.'
            });
            return false;
        }
    });
});
</script>

<style>
.select2-container {
    width: 100% !important;
}
</style>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
