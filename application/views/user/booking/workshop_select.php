<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('layouts/user_layout', ['page_title' => $page_title]); ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><?= $page_title ?></h2>
            <p class="text-muted">Pilih bengkel yang ingin Anda kunjungi untuk melakukan servis kendaraan.</p>

            <?php if (empty($workshops)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Belum ada bengkel tersedia saat ini.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($workshops as $workshop): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <?php if (!empty($workshop['image'])): ?>
                                    <img src="<?= base_url('uploads/workshops/' . $workshop['image']) ?>" class="card-img-top" alt="<?= $workshop['name'] ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-wrench text-white fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= $workshop['name'] ?></h5>
                                    <p class="card-text text-muted">
                                        <i class="fas fa-map-marker-alt"></i> <?= $workshop['address'] ?>
                                    </p>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-phone"></i> <?= $workshop['phone'] ?><br>
                                            <i class="fas fa-clock"></i> <?= $workshop['operating_hours'] ?? 'Buka 24 Jam' ?>
                                        </small>
                                    </div>
                                    <?php if (!empty($workshop['rating'])): ?>
                                        <div class="mb-2">
                                            <span class="text-warning">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <i class="fas fa-star<?= $i < floor($workshop['rating']) ? '' : '-o' ?>"></i>
                                                <?php endfor; ?>
                                            </span>
                                            <small>(<?= $workshop['rating'] ?? 0 ?>)</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <a href="<?= site_url('booking/step1/' . $workshop['id']) ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-calendar-plus"></i> Pilih Bengkel Ini
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>