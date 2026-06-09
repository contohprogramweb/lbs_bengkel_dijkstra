<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container mt-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fa fa-lock"></i> <?php echo $page_title; ?></h4>
                </div>
                <div class="card-body">
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success">
                            <?php echo $this->session->flashdata('success'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?php echo $this->session->flashdata('error'); ?>
                        </div>
                    <?php endif; ?>

                    <?php echo validation_errors('<div class="alert alert-warning">', '</div>'); ?>

                    <?php echo form_open('user/change_password'); ?>
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="current_password"
                                   name="current_password" required
                                   placeholder="Masukkan password saat ini">
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="new_password">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="new_password"
                                   name="new_password" required minlength="6" maxlength="50"
                                   placeholder="Minimal 6 karakter">
                            <small class="form-text text-muted">Password minimal 6 karakter.</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password"
                                   name="confirm_password" required minlength="6" maxlength="50"
                                   placeholder="Ulangi password baru">
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Ubah Password
                        </button>
                        <a href="<?php echo site_url('user/profile'); ?>" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>