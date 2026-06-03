<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo $app_name; ?></title>
    
    <!-- CSRF Token -->
    <meta name="csrf_token_name" content="<?php echo $this->security->get_csrf_token_name(); ?>">
    <meta name="csrf_token_hash" content="<?php echo $this->security->get_csrf_hash(); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .auth-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-logo i {
            font-size: 50px;
            color: #667eea;
        }
        .auth-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .alert {
            border-radius: 10px;
        }
        .role-selector {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2 class="auth-title"><?php echo $page_title; ?></h2>
            
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                </div>
            <?php endif; ?>

            <?php echo form_open('auth/process_register', ['class' => 'register-form']); ?>
                <?php echo csrf_field(); ?>
                
                <div class="role-selector">
                    <label class="form-label fw-bold">Pilih Tipe Akun:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="role_customer" value="customer" checked>
                        <label class="form-check-label" for="role_customer">
                            <i class="fas fa-car"></i> Pelanggan (Cari Bengkel)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="role_workshop" value="workshop_owner">
                        <label class="form-check-label" for="role_workshop">
                            <i class="fas fa-tools"></i> Pemilik Bengkel (Terima Booking)
                        </label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="full_name" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control <?php echo form_error('full_name') ? 'is-invalid' : ''; ?>" 
                           id="full_name" name="full_name" value="<?php echo set_value('full_name'); ?>" 
                           placeholder="John Doe" required>
                    <?php echo form_error('full_name', '<div class="invalid-feedback">', '</div>'); ?>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control <?php echo form_error('email') ? 'is-invalid' : ''; ?>" 
                           id="email" name="email" value="<?php echo set_value('email'); ?>" 
                           placeholder="nama@email.com" required>
                    <?php echo form_error('email', '<div class="invalid-feedback">', '</div>'); ?>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Telepon (Opsional)</label>
                    <input type="text" class="form-control <?php echo form_error('phone') ? 'is-invalid' : ''; ?>" 
                           id="phone" name="phone" value="<?php echo set_value('phone'); ?>" 
                           placeholder="081234567890">
                    <?php echo form_error('phone', '<div class="invalid-feedback">', '</div>'); ?>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control <?php echo form_error('password') ? 'is-invalid' : ''; ?>" 
                           id="password" name="password" placeholder="Minimal 6 karakter" required>
                    <?php echo form_error('password', '<div class="invalid-feedback">', '</div>'); ?>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control <?php echo form_error('confirm_password') ? 'is-invalid' : ''; ?>" 
                           id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                    <?php echo form_error('confirm_password', '<div class="invalid-feedback">', '</div>'); ?>
                </div>
                
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus"></i> Daftar
                    </button>
                </div>
                
                <div class="text-center">
                    <p class="mb-0">Sudah punya akun? <a href="<?php echo site_url('auth/login'); ?>">Login sekarang</a></p>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
