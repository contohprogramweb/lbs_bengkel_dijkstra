<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('layouts/user_layout', ['page_title' => $page_title]); ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2><?= $page_title ?></h2>
            
            <div class="card mt-4">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-calendar-alt"></i> 
                        Pilih tanggal dan waktu baru untuk booking Anda.
                    </div>
                    
                    <div class="mb-4">
                        <h5>Detail Booking Saat Ini</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">No. Booking</th>
                                <td><?= $booking['booking_number'] ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal & Waktu Sekarang</th>
                                <td><?= date('d M Y, H:i', strtotime($booking['booking_date'])) ?></td>
                            </tr>
                            <tr>
                                <th>Bengkel</th>
                                <td><?= $workshop['name'] ?? '-' ?></td>
                            </tr>
                            <tr>
                                <th>Kendaraan</th>
                                <td><?= $vehicle['vehicle_name'] ?? '-' ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <form method="post" action="<?= site_url('user/bookings/reschedule/' . $booking['id']) ?>">
                        <div class="form-group">
                            <label for="new_date">Pilih Tanggal Baru</label>
                            <select name="new_date" id="new_date" class="form-control" required>
                                <option value="">-- Pilih Tanggal --</option>
                                <?php foreach ($available_dates as $date): ?>
                                    <option value="<?= $date['date'] ?>">
                                        <?= $date['formatted'] ?> (<?= $date['day_name'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_time">Pilih Waktu Baru</label>
                            <select name="new_time" id="new_time" class="form-control" required>
                                <option value="">-- Pilih Waktu --</option>
                                <?php
                                $time_slots = [
                                    '08:00' => '08:00 - 09:00',
                                    '09:00' => '09:00 - 10:00',
                                    '10:00' => '10:00 - 11:00',
                                    '11:00' => '11:00 - 12:00',
                                    '13:00' => '13:00 - 14:00',
                                    '14:00' => '14:00 - 15:00',
                                    '15:00' => '15:00 - 16:00',
                                    '16:00' => '16:00 - 17:00',
                                ];
                                foreach ($time_slots as $value => $label):
                                ?>
                                    <option value="<?= $value ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <hr>
                        
                        <a href="<?= site_url('user/bookings/detail/' . $booking['id']) ?>" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary float-right">Ubah Jadwal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Optional: Add AJAX to load available time slots based on selected date
    $('#new_date').change(function() {
        // You can implement AJAX call here to fetch available time slots
        console.log('Date selected:', $(this).val());
    });
});
</script>

<?php $this->load->view('layouts/footer'); ?>
