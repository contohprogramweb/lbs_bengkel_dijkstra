<?php
/**
 * Step 4: Confirmation View
 */
?>

<div class="form-section">
    <h4>Konfirmasi Booking</h4>
    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
        Periksa kembali detail booking Anda sebelum konfirmasi
    </p>
    
    <?= form_open('booking/step4/' . $workshop['id']) ?>
    
    <!-- Booking Summary -->
    <div class="confirmation-box">
        <h5 style="margin-bottom: 12px; color: #667eea;">Ringkasan Booking</h5>
        
        <div class="confirmation-row">
            <span class="confirmation-label">Bengkel:</span>
            <span class="confirmation-value"><?= $workshop['name'] ?></span>
        </div>
        
        <div class="confirmation-row">
            <span class="confirmation-label">Alamat:</span>
            <span class="confirmation-value"><?= $workshop['address'] ?></span>
        </div>
        
        <div class="confirmation-row">
            <span class="confirmation-label">Kendaraan:</span>
            <span class="confirmation-value">
                <?= strtoupper($vehicle['vehicle_type']) ?> - <?= $vehicle['vehicle_number'] ?>
                <br><small style="color: #666;"><?= $vehicle['brand'] ?> <?= $vehicle['model'] ?> (<?= $vehicle['year'] ?>)</small>
            </span>
        </div>
        
        <div class="confirmation-row">
            <span class="confirmation-label">Tanggal:</span>
            <span class="confirmation-value"><?= date('d M Y', strtotime($booking_data['scheduled_date'])) ?></span>
        </div>
        
        <div class="confirmation-row">
            <span class="confirmation-label">Waktu:</span>
            <span class="confirmation-value"><?= date('H:i', strtotime($booking_data['scheduled_time'])) ?> WIB</span>
        </div>
        
        <div class="confirmation-row">
            <span class="confirmation-label">Tipe Layanan:</span>
            <span class="confirmation-value"><?= ucfirst($booking_data['service_type']) ?></span>
        </div>
        
        <?php if (!empty($booking_data['selected_services'])): ?>
        <div class="confirmation-row">
            <span class="confirmation-label">Layanan Dipilih:</span>
            <span class="confirmation-value">
                <?php foreach ($booking_data['selected_services'] as $svc): ?>
                    <div><?= $svc['service_name'] ?></div>
                <?php endforeach; ?>
            </span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($booking_data['service_description'])): ?>
        <div class="confirmation-row" style="display: block;">
            <span class="confirmation-label">Deskripsi Keluhan:</span>
            <span class="confirmation-value" style="display: block; margin-top: 5px;"><?= nl2br(htmlspecialchars($booking_data['service_description'])) ?></span>
        </div>
        <?php endif; ?>
        
        <div class="confirmation-row" style="background: #f0fdf4; padding: 10px; margin-top: 10px; border-radius: 4px;">
            <span class="confirmation-label" style="font-weight: 600;">Estimasi Biaya:</span>
            <span class="confirmation-value" style="color: #16a34a; font-size: 16px;">Rp <?= number_format($booking_data['estimated_price'], 0, ',', '.') ?></span>
        </div>
        
        <div class="confirmation-row">
            <span class="confirmation-label">Estimasi Durasi:</span>
            <span class="confirmation-value"><?= $booking_data['estimated_duration'] ?? 60 ?> menit</span>
        </div>
    </div>
    
    <!-- Terms & Conditions -->
    <div style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 6px; padding: 12px; margin-bottom: 15px; font-size: 12px;">
        <strong>⚠️ Penting:</strong>
        <ul style="margin: 8px 0 0 20px; padding: 0;">
            <li>Datang minimal 10 menit sebelum jadwal untuk check-in.</li>
            <li>Booking dapat dibatalkan atau diubah jadwalnya selama status masih Pending.</li>
            <li>Estimasi biaya dapat berubah setelah pemeriksaan oleh mekanik.</li>
            <li>Slot yang tidak digunakan lebih dari 15 menit akan diberikan ke pelanggan lain.</li>
        </ul>
    </div>
    
    <div class="btn-group">
        <a href="<?= site_url('booking/step3/' . $workshop['id']) ?>" class="btn btn-secondary">← Kembali</a>
        <button type="submit" class="btn btn-primary btn-block" onclick="return confirm('Apakah Anda yakin ingin membuat booking ini?')">
            ✓ Konfirmasi Booking
        </button>
    </div>
    
    <?= form_close() ?>
</div>

<script>
// Optional: Add any confirmation page specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Confirmation page loaded');
});
</script>
