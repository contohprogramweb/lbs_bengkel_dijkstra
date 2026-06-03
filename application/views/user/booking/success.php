<?php
/**
 * Booking Success View
 */
?>

<div class="form-section" style="text-align: center; padding: 40px 20px;">
    <div style="width: 80px; height: 80px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="3">
            <path d="M20 6L9 17l-5-5"/>
        </svg>
    </div>
    
    <h2 style="color: #065f46; margin-bottom: 10px;">Booking Berhasil!</h2>
    <p style="color: #666; margin-bottom: 20px;">Terima kasih telah melakukan pemesanan layanan.</p>
    
    <!-- Booking Code -->
    <div style="background: #f0fdf4; border: 2px dashed #16a34a; border-radius: 8px; padding: 20px; margin: 20px 0;">
        <div style="font-size: 13px; color: #666; margin-bottom: 5px;">Kode Booking Anda:</div>
        <div style="font-size: 28px; font-weight: bold; color: #16a34a; letter-spacing: 2px;"><?= $booking['booking_number'] ?></div>
    </div>
    
    <!-- Booking Details -->
    <div class="confirmation-box" style="text-align: left; max-width: 400px; margin: 0 auto 20px;">
        <div class="confirmation-row">
            <span class="confirmation-label">Bengkel:</span>
            <span class="confirmation-value"><?= $workshop['name'] ?></span>
        </div>
        <div class="confirmation-row">
            <span class="confirmation-label">Tanggal:</span>
            <span class="confirmation-value"><?= date('d M Y', strtotime($booking['scheduled_date'])) ?></span>
        </div>
        <div class="confirmation-row">
            <span class="confirmation-label">Waktu:</span>
            <span class="confirmation-value"><?= date('H:i', strtotime($booking['scheduled_time'])) ?> WIB</span>
        </div>
        <div class="confirmation-row">
            <span class="confirmation-label">Kendaraan:</span>
            <span class="confirmation-value"><?= $vehicle['vehicle_number'] ?></span>
        </div>
        <div class="confirmation-row">
            <span class="confirmation-label">Status:</span>
            <span class="confirmation-value" style="background: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 12px;"><?= ucfirst($booking['status']) ?></span>
        </div>
    </div>
    
    <!-- Next Steps -->
    <div style="background: #dbeafe; border-radius: 8px; padding: 15px; margin: 20px 0; text-align: left;">
        <strong style="color: #1e40af;">Langkah Selanjutnya:</strong>
        <ol style="margin: 10px 0 0 20px; color: #1e40af; font-size: 13px;">
            <li>Simpan kode booking ini untuk referensi Anda.</li>
            <li>Cek email untuk konfirmasi detail booking lengkap.</li>
            <li>Datang ke bengkel 10 menit sebelum jadwal.</li>
            <li>Tunjukkan kode booking saat check-in.</li>
        </ol>
    </div>
    
    <!-- Action Buttons -->
    <div class="btn-group" style="justify-content: center; max-width: 400px; margin: 0 auto;">
        <a href="<?= site_url('bookings') ?>" class="btn btn-secondary">Lihat Semua Booking</a>
        <a href="<?= site_url('booking/detail/' . $booking['id']) ?>" class="btn btn-primary">Detail Booking</a>
    </div>
    
    <!-- Print/Share Options -->
    <div style="margin-top: 20px;">
        <button onclick="window.print()" class="btn" style="background: #f3f4f6; color: #374151; font-size: 12px;">
            📄 Cetak Bukti Booking
        </button>
    </div>
</div>

<style>
@media print {
    .sidebar, .btn-group, button {
        display: none !important;
    }
    .map-wrapper {
        display: none !important;
    }
    .booking-container {
        display: block !important;
    }
    .form-section {
        box-shadow: none !important;
    }
}
</style>
