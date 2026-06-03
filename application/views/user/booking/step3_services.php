<?php
/**
 * Step 3: Select Services View
 */
?>

<div class="form-section">
    <h4>Pilih Layanan</h4>
    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
        Pilih layanan yang dibutuhkan atau deskripsikan keluhan kendaraan Anda
    </p>
    
    <?= form_open('booking/step3/' . $workshop['id']) ?>
    
    <!-- Service Type -->
    <div class="form-group">
        <label for="service_type">Tipe Layanan</label>
        <select name="service_type" id="service_type" class="form-control">
            <option value="regular">Servis Berkala</option>
            <option value="maintenance">Perawatan Rutin</option>
            <option value="repair">Perbaikan</option>
            <option value="emergency">Darurat</option>
            <option value="custom">Lainnya</option>
        </select>
    </div>
    
    <!-- Services List -->
    <div class="form-group">
        <label>Layanan Tersedia</label>
        
        <?php if (!empty($services)): ?>
            <?php foreach ($services as $svc): ?>
                <label class="service-item" data-service-id="<?= $svc['id'] ?>">
                    <input type="checkbox" 
                           name="service_ids[]" 
                           value="<?= $svc['id'] ?>" 
                           class="service-checkbox"
                           data-price="<?= $svc['price_min'] ?? 0 ?>"
                           data-name="<?= $svc['service_name'] ?>">
                    <div class="service-info">
                        <div class="service-name"><?= $svc['service_name'] ?></div>
                        <div style="font-size: 12px; color: #666;"><?= $svc['description'] ?? '' ?></div>
                        <?php if ($svc['price_min']): ?>
                            <div class="service-price">Rp <?= number_format($svc['price_min'], 0, ',', '.') ?></div>
                        <?php endif; ?>
                    </div>
                </label>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #999; font-size: 13px;">Tidak ada layanan terdaftar. Silakan deskripsikan kebutuhan Anda di bawah.</p>
        <?php endif; ?>
    </div>
    
    <!-- Service Description -->
    <div class="form-group">
        <label for="service_description">Deskripsi Keluhan / Catatan Tambahan</label>
        <textarea name="service_description" id="service_description" class="form-control" rows="4" placeholder="Jelaskan keluhan kendaraan Anda atau catatan khusus..."></textarea>
    </div>
    
    <!-- Estimated Total -->
    <div class="confirmation-box">
        <div class="confirmation-row">
            <span class="confirmation-label">Estimasi Biaya:</span>
            <span class="confirmation-value" id="estimatedTotal">Rp 0</span>
        </div>
        <div class="confirmation-row">
            <span class="confirmation-label">Estimasi Durasi:</span>
            <span class="confirmation-value" id="estimatedDuration">0 menit</span>
        </div>
        <small style="color: #999;">*Estimasi dapat berubah setelah pemeriksaan oleh mekanik</small>
    </div>
    
    <div class="btn-group">
        <a href="<?= site_url('booking/step2/' . $workshop['id']) ?>" class="btn btn-secondary">← Kembali</a>
        <button type="submit" class="btn btn-primary btn-block" id="submitBtn" disabled>Lanjut ke Konfirmasi →</button>
    </div>
    
    <?= form_close() ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    const descriptionInput = document.getElementById('service_description');
    const submitBtn = document.getElementById('submitBtn');
    const estimatedTotal = document.getElementById('estimatedTotal');
    const estimatedDuration = document.getElementById('estimatedDuration');
    
    let total = 0;
    const PRICE_PER_SERVICE = 60000; // Default estimate per service
    
    // Service checkbox change handler
    serviceCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const parent = this.closest('.service-item');
            
            if (this.checked) {
                parent.classList.add('selected');
                total += parseFloat(this.dataset.price) || PRICE_PER_SERVICE;
            } else {
                parent.classList.remove('selected');
                total -= parseFloat(this.dataset.price) || PRICE_PER_SERVICE;
            }
            
            updateTotal();
            validateForm();
        });
    });
    
    // Description input handler
    descriptionInput.addEventListener('input', function() {
        validateForm();
    });
    
    function updateTotal() {
        estimatedTotal.textContent = 'Rp ' + total.toLocaleString('id-ID');
        
        // Estimate duration: 60 minutes per service
        const selectedCount = document.querySelectorAll('.service-checkbox:checked').length;
        const duration = selectedCount > 0 ? selectedCount * 60 : 0;
        estimatedDuration.textContent = duration + ' menit';
    }
    
    function validateForm() {
        const hasServices = document.querySelectorAll('.service-checkbox:checked').length > 0;
        const hasDescription = descriptionInput.value.trim().length > 0;
        
        // Allow submission if at least one service OR description is provided
        submitBtn.disabled = !(hasServices || hasDescription);
    }
    
    // Initial validation
    validateForm();
});
</script>
