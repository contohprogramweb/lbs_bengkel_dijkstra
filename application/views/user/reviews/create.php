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
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            font-size: 3rem;
            color: #ddd;
        }
        .star-rating input[type="radio"] {
            display: none;
        }
        .star-rating label {
            cursor: pointer;
            transition: color 0.2s;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input[type="radio"]:checked ~ label {
            color: #ffc107;
        }
        .booking-info-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .photo-preview {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 8px;
            margin-bottom: 8px;
        }
        .char-counter {
            font-size: 0.875rem;
            color: #6c757d;
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
                        <a class="nav-link" href="<?= base_url('review/my_reviews') ?>">Review Saya</a>
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
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= validation_errors() ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <h2><i class="fas fa-star"></i> <?= $page_title ?></h2>
                <hr>
            </div>
        </div>

        <!-- Booking Info -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="booking-info-card">
                    <h5><i class="fas fa-tools"></i> <?= esc($booking['workshop_name']) ?></h5>
                    <p class="mb-0 text-muted">
                        <strong>Booking:</strong> <?= esc($booking['booking_number']) ?> |
                        <strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($booking['scheduled_date'])) ?> |
                        <strong>Waktu:</strong> <?= date('H:i', strtotime($booking['scheduled_time'])) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Review Form -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-pen"></i> Tulis Review Anda</h5>
                    </div>
                    <div class="card-body">
                        <?= form_open_multipart('review/create/' . $booking['id']) ?>
                            
                            <!-- Rating -->
                            <div class="form-group">
                                <label><strong>Rating</strong> <span class="text-danger">*</span></label>
                                <div class="star-rating">
                                    <input type="radio" name="rating" id="star5" value="5" required>
                                    <label for="star5" title="Sangat Bagus"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" id="star4" value="4">
                                    <label for="star4" title="Bagus"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" id="star3" value="3">
                                    <label for="star3" title="Cukup"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" id="star2" value="2">
                                    <label for="star2" title="Kurang"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" id="star1" value="1">
                                    <label for="star1" title="Sangat Kurang"><i class="fas fa-star"></i></label>
                                </div>
                                <small class="form-text text-muted">Klik bintang untuk memberikan rating (1-5)</small>
                            </div>

                            <!-- Review Text -->
                            <div class="form-group">
                                <label for="review_text"><strong>Ulasan</strong> <span class="text-muted">(Opsional, min <?= $min_chars ?> karakter)</span></label>
                                <textarea name="review_text" 
                                          id="review_text" 
                                          class="form-control" 
                                          rows="5" 
                                          minlength="<?= $min_chars ?>"
                                          maxlength="<?= $max_chars ?>"
                                          placeholder="Ceritakan pengalaman Anda dengan bengkel ini..."><?= set_value('review_text') ?></textarea>
                                <div class="char-counter text-right">
                                    <span id="char_count">0</span> / <?= $max_chars ?> karakter
                                </div>
                            </div>

                            <!-- Photo Upload -->
                            <div class="form-group">
                                <label for="photos"><strong>Foto</strong> <span class="text-muted">(Opsional, maks <?= $max_photos ?> foto)</span></label>
                                <div class="custom-file">
                                    <input type="file" name="photos[]" id="photos" class="custom-file-input" multiple accept="image/*" max="3">
                                    <label class="custom-file-label" for="photos">Pilih foto...</label>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Maksimal <?= $max_photos ?> foto, ukuran maks 2MB per foto. Foto akan di-resize ke maksimal 800x800px.
                                </small>
                                <div id="photo_previews" class="mt-2"></div>
                            </div>

                            <hr>

                            <!-- Submit Buttons -->
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane"></i> Kirim Review
                                </button>
                                <a href="<?= base_url('review/my_reviews') ?>" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-arrow-left"></i> Batal
                                </a>
                            </div>

                        <?= form_close() ?>
                    </div>
                </div>
            </div>

            <!-- Tips Sidebar -->
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="fas fa-lightbulb text-warning"></i> Tips Menulis Review</h6>
                        <ul class="small mb-0">
                            <li>Berikan rating yang sesuai dengan pengalaman Anda</li>
                            <li>Jelaskan layanan yang Anda terima</li>
                            <li>Sebutkan kelebihan dan kekurangan</li>
                            <li>Foto hasil servis dapat membantu pengguna lain</li>
                            <li>Gunakan bahasa yang sopan dan konstruktif</li>
                        </ul>
                    </div>
                </div>

                <div class="card bg-info text-white mt-3">
                    <div class="card-body">
                        <h6><i class="fas fa-shield-alt"></i> Business Rules</h6>
                        <ul class="small mb-0">
                            <li><strong>BR-65:</strong> Satu review per booking</li>
                            <li><strong>BR-66:</strong> Hanya booking Completed yang bisa direview</li>
                            <li><strong>BR-67:</strong> Rating bengkel otomatis terupdate</li>
                            <li><strong>BR-69:</strong> Hanya bisa review bengkel yang pernah dipesan</li>
                        </ul>
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
        // Character counter
        $('#review_text').on('input', function() {
            var length = $(this).val().length;
            $('#char_count').text(length);
            
            if (length > 0 && length < <?= $min_chars ?>) {
                $('#char_count').addClass('text-danger').removeClass('text-success');
            } else if (length >= <?= $min_chars ?>) {
                $('#char_count').addClass('text-success').removeClass('text-danger');
            } else {
                $('#char_count').removeClass('text-danger text-success');
            }
        });

        // Initialize character counter
        $('#char_count').text($('#review_text').val().length);

        // Photo preview
        document.getElementById('photos').addEventListener('change', function(e) {
            var files = e.target.files;
            var previews = document.getElementById('photo_previews');
            previews.innerHTML = '';
            
            if (files.length > <?= $max_photos ?>) {
                alert('Maksimal <?= $max_photos ?> foto!');
                this.value = '';
                return;
            }
            
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                
                // Check file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file "' + file.name + '" melebihi 2MB!');
                    this.value = '';
                    previews.innerHTML = '';
                    return;
                }
                
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'photo-preview';
                    previews.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
            
            // Update file input label
            var fileNames = [];
            for (var i = 0; i < files.length; i++) {
                fileNames.push(files[i].name);
            }
            document.querySelector('.custom-file-label').textContent = fileNames.join(', ') || 'Pilih foto...';
        });

        // Star rating visual feedback
        $('.star-rating input[type="radio"]').on('change', function() {
            var value = $(this).val();
            console.log('Rating selected: ' + value);
        });
    </script>
</body>
</html>
