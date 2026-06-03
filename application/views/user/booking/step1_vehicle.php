<?php
/**
 * Step 1: Select Vehicle View
 */
?>

<div class="form-section">
    <h4>Pilih Kendaraan</h4>
    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
        Pilih kendaraan yang akan dibawa ke bengkel
    </p>
    
    <?= form_open('booking/step1/' . $workshop['id']) ?>
    
    <div class="form-group">
        <label for="vehicle_id">Kendaraan Anda</label>
        <select name="vehicle_id" id="vehicle_id" class="form-control" required>
            <option value="">-- Pilih Kendaraan --</option>
            <?php foreach ($vehicles as $v): ?>
                <option value="<?= $v['id'] ?>" <?= $v['is_primary'] ? 'selected' : '' ?>>
                    <?= strtoupper($v['vehicle_type']) ?> - <?= $v['vehicle_number'] ?> 
                    (<?= $v['brand'] ?> <?= $v['model'] ?>, <?= $v['year'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <!-- Selected vehicle preview -->
    <div id="vehicle-preview" style="display: none; background: #f9fafb; padding: 12px; border-radius: 6px; margin-top: 10px;">
        <strong>Detail Kendaraan:</strong>
        <div id="vehicle-details"></div>
    </div>
    
    <div class="btn-group">
        <a href="<?= site_url('user/vehicles') ?>" class="btn btn-secondary">Kelola Kendaraan</a>
        <button type="submit" class="btn btn-primary btn-block">Lanjut ke Jadwal →</button>
    </div>
    
    <?= form_close() ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicleSelect = document.getElementById('vehicle_id');
    const vehiclePreview = document.getElementById('vehicle-preview');
    const vehicleDetails = document.getElementById('vehicle-details');
    
    // Vehicle data from PHP
    const vehicles = <?= json_encode($vehicles) ?>;
    
    vehicleSelect.addEventListener('change', function() {
        const selectedId = this.value;
        
        if (selectedId) {
            const vehicle = vehicles.find(v => v.id == selectedId);
            
            if (vehicle) {
                vehicleDetails.innerHTML = `
                    <div style="font-size: 13px; margin-top: 5px;">
                        <div><strong>Nomor Polisi:</strong> ${vehicle.vehicle_number.toUpperCase()}</div>
                        <div><strong>Jenis:</strong> ${vehicle.vehicle_type === 'car' ? 'Mobil' : 'Motor'}</div>
                        <div><strong>Merk/Model:</strong> ${vehicle.brand} ${vehicle.model}</div>
                        <div><strong>Tahun:</strong> ${vehicle.year}</div>
                        <div><strong>Warna:</strong> ${vehicle.color || '-'}</div>
                        <div><strong>KM Terakhir:</strong> ${vehicle.current_km ? vehicle.current_km.toLocaleString() : '-'} km</div>
                    </div>
                `;
                vehiclePreview.style.display = 'block';
            }
        } else {
            vehiclePreview.style.display = 'none';
        }
    });
    
    // Trigger change on load if preselected
    if (vehicleSelect.value) {
        vehicleSelect.dispatchEvent(new Event('change'));
    }
});
</script>
