<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('layouts/user_layout', ['page_title' => $page_title]); ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2><?= $page_title ?></h2>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Apakah Anda yakin ingin membatalkan booking ini?
                    </div>

                    <div class="mb-4">
                        <h5>Detail Booking</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">No. Booking</th>
                                <td><?= $booking['booking_number'] ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal Booking</th>
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
                            <tr>
                                <th>Status</th>
                                <td><span class="badge badge-warning"><?= ucfirst($booking['status']) ?></span></td>
                            </tr>
                        </table>
                    </div>

                    <form method="post" action="<?= site_url('user/bookings/cancel/' . $booking['id']) ?>">
                        <div class="form-group">
                            <label for="cancellation_reason">Alasan Pembatalan</label>
                            <textarea name="cancellation_reason" id="cancellation_reason" class="form-control" rows="4" placeholder="Jelaskan alasan pembatalan..." required></textarea>
                        </div>

                        <hr>

                        <a href="<?= site_url('user/bookings/detail/' . $booking['id']) ?>" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-danger float-right">Ya, Batalkan Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>