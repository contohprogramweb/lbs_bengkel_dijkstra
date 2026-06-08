<?php
/**
 * User Profile View
 * 
 * @var object $user User object
 */
?>

<div class="card shadow-sm" style="width: 100%; max-width: 100%; overflow: hidden;">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-user"></i> Informasi Profil</h5>
    </div>
    <div class="card-body">
        <div class="row g-3" style="margin: 0; width: 100%;">
            <div class="col-md-4 text-center mb-4" style="box-sizing: border-box; min-width: 0;">
                <?php if ($user->avatar): ?>
                    <img src="<?php echo base_url($user->avatar); ?>" alt="Avatar" class="rounded-circle" width="150" height="150" style="max-width: 100%; height: auto; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 150px; height: 150px; font-size: 3rem; max-width: 100%;">
                        <?php echo strtoupper(substr($user->full_name, 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div class="mt-3">
                    <a href="<?php echo site_url('user/edit_profile'); ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> Edit Foto</a>
                </div>
            </div>
            <div class="col-md-8" style="box-sizing: border-box; min-width: 0;">
                <div class="table-responsive" style="width: 100%;">
                    <table class="table table-borderless mb-0" style="width: 100%; margin: 0;">
                        <tr>
                            <th width="150" style="white-space: nowrap;">Nama Lengkap</th>
                            <td><?php echo htmlspecialchars($user->full_name); ?></td>
                        </tr>
                        <tr>
                            <th width="150" style="white-space: nowrap;">Email</th>
                            <td><?php echo htmlspecialchars($user->email); ?> 
                                <?php if ($user->email_verified_at): ?>
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Terverifikasi</span>
                                <?php else: ?>
                                    <span class="badge bg-warning"><i class="fas fa-clock"></i> Belum Terverifikasi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th width="150" style="white-space: nowrap;">Telepon</th>
                            <td><?php echo htmlspecialchars($user->phone ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th width="150" style="white-space: nowrap;">Role</th>
                            <td><span class="badge bg-info"><?php echo ucfirst($user->role); ?></span></td>
                        </tr>
                        <tr>
                            <th width="150" style="white-space: nowrap;">Terakhir Login</th>
                            <td><?php echo $user->last_login_at ? date('d/m/Y H:i', strtotime($user->last_login_at)) : '-'; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="mt-4">
                    <a href="<?php echo site_url('user/edit_profile'); ?>" class="btn btn-primary me-2"><i class="fas fa-edit"></i> Edit Profil</a>
                    <a href="<?php echo site_url('user/change_password'); ?>" class="btn btn-outline-secondary"><i class="fas fa-key"></i> Ubah Password</a>
                </div>
            </div>
        </div>
    </div>
</div>
