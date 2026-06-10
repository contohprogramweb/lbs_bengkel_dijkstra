<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3"><i class="fas fa-lock"></i> <?php echo $page_title; ?></h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-key"></i> Ubah Password</h5>
                </div>
                <div class="card-body">
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        </div>
                    <?php endif; ?>

                    <?php echo validation_errors('<div class="alert alert-warning">', '</div>'); ?>

                    <?php echo form_open('mechanic/change_password'); ?>
                        <div class="form-group mb-3">
                            <label for="current_password">Password Saat Ini <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="current_password"
                                   name="current_password" required
                                   placeholder="Masukkan password saat ini">
                            <small class="form-text text-muted">Masukkan password Anda saat ini untuk verifikasi.</small>
                        </div>

                        <hr class="my-4">

                        <div class="form-group mb-3">
                            <label for="new_password">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="new_password"
                                   name="new_password" required minlength="6" maxlength="50"
                                   placeholder="Minimal 6 karakter">
                            <small class="form-text text-muted">Password minimal 6 karakter. Gunakan kombinasi huruf, angka, dan simbol untuk keamanan lebih baik.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="confirm_password">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password"
                                   name="confirm_password" required minlength="6" maxlength="50"
                                   placeholder="Ulangi password baru">
                            <small class="form-text text-muted">Ketik ulang password baru Anda.</small>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Ubah Password
                            </button>
                            <a href="<?php echo site_url('mechanic/profile'); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali ke Profil
                            </a>
                        </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Tips Keamanan</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Panjang Password:</strong> Minimal 6 karakter
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Kombinasi:</strong> Gunakan huruf besar, kecil, angka, dan simbol
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Unik:</strong> Jangan gunakan password yang sama dengan akun lain
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Rahasia:</strong> Jangan bagikan password kepada siapapun
                        </li>
                        <li>
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Update Berkala:</strong> Ganti password secara berkala untuk keamanan
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
