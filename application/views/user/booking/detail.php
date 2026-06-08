<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= $page_title ?></h2>
                <a href="<?= site_url('user/bookings') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
            <?php endif; ?>

            <div class="row">
                <!-- Main Info -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Booking</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">No. Booking</th>
                                    <td><strong><?= esc($booking['booking_number']) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <?php
                                        $status_badges = [
                                            'pending' => 'warning',
                                            'accepted' => 'info',
                                            'approved' => 'primary',
                                            'in_progress' => 'warning',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $badge = $status_badges[$booking['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $badge ?> badge-lg"><?= ucfirst($booking['status']) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Bengkel</th>
                                    <td><?= esc($workshop['name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <td><?= date('d M Y, H:i', strtotime($booking['booking_date'])) ?> WIB</td>
                                </tr>
                                <tr>
                                    <th>Layanan</th>
                                    <td><?= esc($booking['service_name'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Estimasi Biaya</th>
                                    <td>Rp <?= number_format($booking['estimated_price'] ?? 0, 0, ',', '.') ?></td>
                                </tr>
                                <?php if (!empty($booking['final_cost'])): ?>
                                <tr>
                                    <th>Biaya Final</th>
                                    <td><strong>Rp <?= number_format($booking['final_cost'], 0, ',', '.') ?></strong></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($booking['cancellation_reason'])): ?>
                                <tr>
                                    <th>Alasan Pembatalan</th>
                                    <td class="text-danger"><?= esc($booking['cancellation_reason']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <!-- Vehicle Info -->
                    <?php if (!empty($vehicle)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Kendaraan</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Merk/Model</th>
                                    <td><?= esc($vehicle['brand']) ?> <?= esc($vehicle['model']) ?></td>
                                </tr>
                                <tr>
                                    <th>Tahun</th>
                                    <td><?= esc($vehicle['year']) ?></td>
                                </tr>
                                <tr>
                                    <th>Plat Nomor</th>
                                    <td><strong><?= esc($vehicle['license_plate']) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Jenis Kendaraan</th>
                                    <td><?= ucfirst($vehicle['vehicle_type']) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Workshop Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Bengkel</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Nama Bengkel</th>
                                    <td><?= esc($workshop['name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td><?= esc($workshop['address']) ?></td>
                                </tr>
                                <tr>
                                    <th>Telepon</th>
                                    <td><?= esc($workshop['phone']) ?></td>
                                </tr>
                                <?php if (!empty($workshop['email'])): ?>
                                <tr>
                                    <th>Email</th>
                                    <td><?= esc($workshop['email']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($workshop['operating_hours'])): ?>
                                <tr>
                                    <th>Jam Operasional</th>
                                    <td><?= esc($workshop['operating_hours']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Action Panel -->
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 20px;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Aksi</h5>
                        </div>
                        <div class="card-body">
                            <?php if (in_array($booking['status'], ['pending', 'accepted', 'approved'])): ?>
                                <a href="<?= site_url('user/bookings/cancel/' . $booking['id']) ?>" class="btn btn-danger btn-block mb-2">
                                    <i class="fas fa-times"></i> Batalkan Booking
                                </a>
                            <?php endif; ?>

                            <?php if ($booking['status'] == 'pending'): ?>
                                <a href="<?= site_url('user/bookings/reschedule/' . $booking['id']) ?>" class="btn btn-warning btn-block mb-2">
                                    <i class="fas fa-calendar-alt"></i> Ubah Jadwal
                                </a>
                            <?php endif; ?>

                            <?php if (!in_array($booking['status'], ['pending', 'accepted', 'approved'])): ?>
                                <p class="text-muted text-center mb-0">Tidak ada aksi yang tersedia untuk status ini</p>
                            <?php endif; ?>
                            
                            <hr>
                            <a href="<?= site_url('user/bookings') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-list"></i> Lihat Semua Booking
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-lg { padding: 0.5em 1em; font-size: 0.9rem; }
</style>
