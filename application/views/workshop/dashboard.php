<?php
/**
 * Workshop Dashboard View
 * 
 * @var object $user Current user data
 * @var object|null $workshop Workshop data
 * @var array $stats Statistics
 * @var int $services_count Number of services
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4 text-dark"><?= $page_title ?></h1>
            
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-card-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Booking</div>
                            <div class="h5 mb-0 font-weight-bold text-dark"><?= $stats['total_bookings'] ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar fa-2x text-primary-opacity"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-card-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-dark"><?= $stats['pending_bookings'] ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-warning-opacity"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-card-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Selesai</div>
                            <div class="h5 mb-0 font-weight-bold text-dark"><?= $stats['completed_bookings'] ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-success-opacity"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-card-info shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Rating</div>
                            <div class="h5 mb-0 font-weight-bold text-dark">
                                <i class="fas fa-star text-warning"></i> <?= number_format($stats['avg_rating'], 1) ?>
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-star-half-alt fa-2x text-info-opacity"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workshop Info -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Bengkel</h6>
                    <?php if ($workshop): ?>
                        <a href="<?= site_url('workshop/edit') ?>" class="btn btn-sm btn-primary-primary">
                            <i class="fas fa-edit"></i> Edit Profil
                        </a>
                    <?php else: ?>
                        <a href="<?= site_url('workshop/create') ?>" class="btn btn-sm btn-primary-primary">
                            <i class="fas fa-plus"></i> Buat Profil Bengkel
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body bg-white">
                    <?php if ($workshop): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <h5><?= htmlspecialchars($workshop->name) ?></h5>
                                <p class="text-muted"><?= htmlspecialchars($workshop->description) ?></p>
                                <p><strong>Alamat:</strong><br><?= nl2br(htmlspecialchars($workshop->address)) ?><br>
                                <?= htmlspecialchars($workshop->city) ?>, <?= htmlspecialchars($workshop->province) ?> <?= htmlspecialchars($workshop->postal_code) ?></p>
                                <p><strong>Telepon:</strong> <?= htmlspecialchars($workshop->phone) ?></p>
                                <p><strong>WhatsApp:</strong> <?= htmlspecialchars($workshop->whatsapp) ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-<?= $workshop->status === 'active' ? 'success' : ($workshop->status === 'pending' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($workshop->status) ?>
                                    </span>
                                </p>
                                <?php if ($workshop->status === 'pending'): ?>
                                    <p class="text-warning"><small>Profil bengkel Anda sedang menunggu verifikasi admin.</small></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <?php if ($workshop->logo): ?>
                                    <img src="<?= base_url($workshop->logo) ?>" alt="Logo Bengkel" class="img-fluid rounded mb-3">
                                <?php endif; ?>
                                <div class="mt-3">
                                    <h6>Layanan Tersedia: <?= $services_count ?> layanan</h6>
                                    <a href="<?= site_url('workshop/services') ?>" class="btn btn-sm btn-outline-primary-primary">
                                        <i class="fas fa-tools"></i> Kelola Layanan
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                            <h5>Anda belum memiliki profil bengkel</h5>
                            <p class="text-muted">Buat profil bengkel Anda untuk mulai menerima booking dari pelanggan.</p>
                            <a href="<?= site_url('workshop/create') ?>" class="btn btn-primary-primary">
                                <i class="fas fa-plus"></i> Buat Profil Bengkel Sekarang
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Menu Cepat</h6>
                </div>
                <div class="card-body bg-white">
                    <div class="list-group">
                        <a href="<?= site_url('workshop/profile') ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-store mr-2"></i> Profil Bengkel
                        </a>
                        <a href="<?= site_url('workshop/services') ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-tools mr-2"></i> Kelola Layanan
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-calendar-alt mr-2"></i> Jadwal Booking
                        </a>
                        <a href="<?= site_url('workshop/edit_profile') ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-edit mr-2"></i> Edit Profil Pribadi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>