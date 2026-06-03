<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= $app_name ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
    <style>
        .star-rating {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
        }
        .star-rating .star {
            transition: color 0.2s;
        }
        .star-rating .star.active,
        .star-rating .star:hover,
        .star-rating .star:hover ~ .star {
            color: #ffc107;
        }
        .star-rating input[type="radio"] {
            display: none;
        }
        .review-card {
            border-left: 4px solid #007bff;
            margin-bottom: 1rem;
        }
        .review-photo {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 8px;
            margin-bottom: 8px;
        }
        .booking-card {
            transition: transform 0.2s;
        }
        .booking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>"><?= $app_name ?></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('user/dashboard') ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('user/bookings') ?>">Pesanan Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= base_url('review/my_reviews') ?>">Review</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= $current_user->full_name ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="<?= base_url('user/profile') ?>">Profil</a>
                            <a class="dropdown-item" href="<?= base_url('auth/logout') ?>">Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <h2><i class="fas fa-star"></i> <?= $page_title ?></h2>
                <hr>
            </div>
        </div>

        <!-- Pending Reviews Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-pen"></i> Perlu Ditulis (<?= count($pending_reviews) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pending_reviews)): ?>
                            <p class="text-muted mb-0">Tidak ada booking yang perlu direview saat ini.</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($pending_reviews as $booking): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card booking-card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title"><?= esc($booking['workshop_name']) ?></h6>
                                                <p class="card-text small mb-2">
                                                    <strong>Booking:</strong> <?= esc($booking['booking_number']) ?><br>
                                                    <strong>Kendaraan:</strong> <?= esc($booking['vehicle_number'] ?? '-') ?><br>
                                                    <strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($booking['scheduled_date'])) ?>
                                                </p>
                                                <a href="<?= base_url('review/create/' . $booking['booking_id']) ?>" 
                                                   class="btn btn-primary btn-sm btn-block">
                                                    <i class="fas fa-star"></i> Tulis Review
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
        </div>

        <!-- Submitted Reviews Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-comment-alt"></i> Review Saya</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($submitted_reviews)): ?>
                            <p class="text-muted mb-0">Anda belum menulis review apapun.</p>
                        <?php else: ?>
                            <?php foreach ($submitted_reviews as $review): ?>
                                <div class="review-card card p-3">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6>
                                                <i class="fas fa-tools"></i> <?= esc($review['workshop_name']) ?>
                                                <span class="badge badge-<?= $review['status'] === 'active' ? 'success' : ($review['status'] === 'pending' ? 'warning' : 'secondary') ?> float-right">
                                                    <?= ucfirst($review['status']) ?>
                                                </span>
                                            </h6>
                                            <p class="small text-muted mb-2">
                                                <i class="fas fa-hashtag"></i> <?= esc($review['booking_number']) ?> | 
                                                <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($review['created_at'])) ?>
                                            </p>
                                            
                                            <!-- Rating Stars -->
                                            <div class="mb-2">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star text-<?= $i <= $review['rating'] ? 'warning' : 'light' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            
                                            <!-- Review Text -->
                                            <?php if (!empty($review['review_text'])): ?>
                                                <p class="card-text"><?= nl2br(esc($review['review_text'])) ?></p>
                                            <?php endif; ?>
                                            
                                            <!-- Photos -->
                                            <?php if (!empty($review['photos'])): ?>
                                                <div class="mt-2">
                                                    <?php foreach ($review['photos'] as $photo): ?>
                                                        <img src="<?= base_url($photo['photo_path']) ?>" 
                                                             alt="Review Photo" 
                                                             class="review-photo"
                                                             data-toggle="modal" 
                                                             data-target="#photoModal<?= $review['id'] ?>">
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Admin Response -->
                                            <?php if (!empty($review['admin_response'])): ?>
                                                <div class="alert alert-secondary mt-3 mb-0">
                                                    <strong><i class="fas fa-user-shield"></i> Respons Admin:</strong>
                                                    <p class="mb-0"><?= nl2br(esc($review['admin_response'])) ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <?php if ($review['status'] !== 'hidden'): ?>
                                                <a href="<?= base_url('review/edit/' . $review['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            <?php endif; ?>
                                            <button onclick="deleteReview(<?= $review['id'] ?>)" 
                                                    class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light mt-5 py-4">
        <div class="container text-center">
            <p class="text-muted mb-0">&copy; <?= date('Y') ?> <?= $app_name ?> v<?= $app_version ?></p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
        function deleteReview(reviewId) {
            if (confirm('Apakah Anda yakin ingin menghapus review ini?')) {
                $.ajax({
                    url: '<?= base_url('review/delete/') ?>' + reviewId,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.message || 'Gagal menghapus review');
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat menghapus review');
                    }
                });
            }
        }
    </script>
</body>
</html>
