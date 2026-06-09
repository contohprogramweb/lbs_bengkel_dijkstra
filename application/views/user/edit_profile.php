<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class=" mt-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fa fa-user-edit"></i> <?php echo $page_title; ?></h4>
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

                    <?php echo form_open_multipart('user/edit_profile'); ?>
                        <div class="form-group">
                            <label for="full_name">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   value="<?php echo set_value('full_name', $user->full_name); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo set_value('email', $user->email); ?>" disabled>
                            <small class="form-text text-muted">Email tidak dapat diubah.</small>
                        </div>

                        <div class="form-group">
                            <label for="phone">Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   value="<?php echo set_value('phone', $user->phone); ?>"
                                   placeholder="Contoh: 081234567890">
                        </div>

                        <div class="form-group">
                            <label for="address">Alamat</label>
                            <textarea class="form-control" id="address" name="address" rows="3"
                                      placeholder="Masukkan alamat lengkap"><?php echo set_value('address', $user->address); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="avatar">Foto Profil</label>
                            <div class="mb-2">
                                <?php if ($user->avatar && file_exists(FCPATH . $user->avatar)): ?>
                                    <img src="<?php echo base_url($user->avatar); ?>" alt="Avatar"
                                         class="img-thumbnail" style="max-width: 150px;">
                                <?php else: ?>
                                    <img src="<?php echo base_url('assets/images/default-avatar.png'); ?>"
                                         alt="Default Avatar" class="img-thumbnail" style="max-width: 150px;">
                                <?php endif; ?>
                            </div>
                            <input type="file" class="form-control-file" id="avatar" name="avatar"
                                   accept="image/*">
                            <small class="form-text text-muted">Maksimal 2MB. Format: JPG, PNG, GIF.</small>
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan Perubahan
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