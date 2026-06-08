<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><?= $page_title ?></h2>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total</h5>
                            <h2><?= $stats['total'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Pending</h5>
                            <h2><?= $stats['pending'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Approved</h5>
                            <h2><?= $stats['approved'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Completed</h5>
                            <h2><?= $stats['completed'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" class="form-inline">
                        <label class="mr-2">Filter Status:</label>
                        <select name="status" class="form-control mr-2">
                            <option value="">Semua</option>
                            <option value="pending" <?= ($this->input->get('status') == 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="accepted" <?= ($this->input->get('status') == 'accepted') ? 'selected' : '' ?>>Accepted</option>
                            <option value="approved" <?= ($this->input->get('status') == 'approved') ? 'selected' : '' ?>>Approved</option>
                            <option value="in_progress" <?= ($this->input->get('status') == 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= ($this->input->get('status') == 'completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= ($this->input->get('status') == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="<?= site_url('user/bookings') ?>" class="btn btn-secondary ml-2">Reset</a>
                    </form>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($bookings)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Anda belum memiliki booking.
                            <a href="<?= site_url('booking') ?>" class="btn btn-primary btn-sm ml-2">Buat Booking Baru</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No. Booking</th>
                                        <th>Tanggal</th>
                                        <th>Bengkel</th>
                                        <th>Kendaraan</th>
                                        <th>Layanan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td><?= $booking['booking_number'] ?></td>
                                            <td><?= date('d M Y, H:i', strtotime($booking['booking_date'])) ?></td>
                                            <td><?= $booking['workshop_name'] ?></td>
                                            <td><?= $booking['vehicle_name'] ?></td>
                                            <td><?= $booking['service_name'] ?? '-' ?></td>
                                            <td>
                                                <?php
                                                $badge_class = 'secondary';
                                                switch($booking['status']) {
                                                    case 'pending': $badge_class = 'warning'; break;
                                                    case 'accepted': $badge_class = 'info'; break;
                                                    case 'approved': $badge_class = 'primary'; break;
                                                    case 'in_progress': $badge_class = 'warning'; break;
                                                    case 'completed': $badge_class = 'success'; break;
                                                    case 'cancelled': $badge_class = 'danger'; break;
                                                }
                                                ?>
                                                <span class="badge badge-<?= $badge_class ?>"><?= ucfirst($booking['status']) ?></span>
                                            </td>
                                            <td>
                                                <a href="<?= site_url('user/bookings/detail/' . $booking['id']) ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <?php if ($booking['status'] == 'pending'): ?>
                                                    <a href="<?= site_url('user/bookings/reschedule/' . $booking['id']) ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-calendar-alt"></i> Reschedule
                                                    </a>
                                                    <a href="<?= site_url('user/bookings/cancel/' . $booking['id']) ?>" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-times"></i> Batal
                                                    </a>
                                                <?php endif; ?>
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
