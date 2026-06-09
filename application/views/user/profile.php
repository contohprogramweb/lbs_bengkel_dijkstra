<div class=" py-3">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="fas fa-user-circle me-2"></i>Informasi Profil</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Avatar Section -->
                        <div class="col-md-3 text-center">
                            <div class="position-relative d-inline-block">
                                <?php if ($user->avatar): ?>
                                    <img src="<?php echo base_url($user->avatar); ?>" alt="Avatar" class="rounded-circle shadow-sm" style="width: 160px; height: 160px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white shadow-sm" style="width: 160px; height: 160px; font-size: 4rem;">
                                        <?php echo strtoupper(substr($user->full_name, 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3">
                                <a href="<?php echo site_url('user/edit_profile'); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-camera me-1"></i> Edit Foto
                                </a>
                            </div>
                        </div>
                        
                        <!-- Profile Information -->
                        <div class="col-md-9">
                            <table class="table table-borderless table-hover mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row" class="text-muted fw-normal" style="width: 160px;">Nama Lengkap</th>
                                        <td class="fw-semibold"><?php echo e($user->full_name); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-muted fw-normal">Email</th>
                                        <td>
                                            <?php echo e($user->email); ?>
                                            <?php if ($user->email_verified_at): ?>
                                                <span class="badge bg-success ms-2"><i class="fas fa-check-circle me-1"></i>Terverifikasi</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark ms-2"><i class="fas fa-clock me-1"></i>Belum Terverifikasi</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-muted fw-normal">Telepon</th>
                                        <td><?php echo e($user->phone ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-muted fw-normal">Role</th>
                                        <td><span class="badge bg-info text-dark"><?php echo ucfirst(e($user->role)); ?></span></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-muted fw-normal">Terakhir Login</th>
                                        <td><?php echo $user->last_login_at ? date('d/m/Y H:i', strtotime($user->last_login_at)) : '-'; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div class="mt-4 pt-3 border-top">
                                <a href="<?php echo site_url('user/edit_profile'); ?>" class="btn btn-primary me-2 mb-2">
                                    <i class="fas fa-edit me-1"></i> Edit Profil
                                </a>
                                <a href="<?php echo site_url('user/change_password'); ?>" class="btn btn-outline-secondary mb-2">
                                    <i class="fas fa-key me-1"></i> Ubah Password
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>