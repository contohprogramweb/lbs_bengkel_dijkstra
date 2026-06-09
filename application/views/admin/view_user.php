
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-user"></i> <?php echo $page_title; ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/users'); ?>">Pengguna</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-dark-theme">
                <div class="card-body text-center">
                    <img src="<?php echo !empty($user_detail->avatar) ? base_url($user_detail->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user_detail->full_name).'&size=150'; ?>" 
                         alt="Profile" class="profile-img mb-3">
                    <h4><?php echo htmlspecialchars($user_detail->full_name); ?></h4>
                    <span class="badge badge-dark-theme-info"><?php echo ucfirst($user_detail->role); ?></span>
                    <span class="badge <?php echo $user_detail->is_active ? 'badge-dark-theme-success' : 'badge-dark-theme-danger'; ?> mt-2">
                        <?php echo $user_detail->is_active ? 'Aktif' : 'Nonaktif'; ?>
                    </span>
                    
                    <hr class="border-secondary">
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo site_url('admin/users'); ?>" class="btn btn-admin-secondary-dark">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <?php if($user_detail->is_active): ?>
                            <button onclick="confirmAction('<?php echo site_url('admin/deactivate_user/'.$user_detail->id); ?>', 'Nonaktifkan Pengguna', 'Apakah Anda yakin ingin menonaktifkan pengguna ini?', 'Ya, Nonaktifkan', '#ffc107')"
                                   class="btn btn-admin-warning-dark">
                                <i class="fas fa-ban"></i> Nonaktifkan
                            </button>
                        <?php else: ?>
                            <button onclick="confirmAction('<?php echo site_url('admin/activate_user/'.$user_detail->id); ?>', 'Aktifkan Pengguna', 'Apakah Anda yakin ingin mengaktifkan pengguna ini?', 'Ya, Aktifkan', '#28a745')"
                                   class="btn btn-admin-success-dark">
                                <i class="fas fa-check"></i> Aktifkan
                            </button>
                        <?php endif; ?>
                        <?php if($user_detail->id != $this->session->userdata('user_id')): ?>
                            <button onclick="confirmDelete('<?php echo site_url('admin/delete_user/'.$user_detail->id); ?>', 'Hapus Pengguna', 'Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.')"
                                   class="btn btn-admin-danger-dark">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card card-dark-theme mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Pengguna</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-dark-theme">
                        <tr>
                            <td class="info-label" style="width: 200px;">Nomor</td>
                            <td><?php echo $user_detail->id; ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">Nama Lengkap</td>
                            <td><?php echo htmlspecialchars($user_detail->full_name); ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">Email</td>
                            <td><?php echo htmlspecialchars($user_detail->email); ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">No. Telepon</td>
                            <td><?php echo htmlspecialchars($user_detail->phone ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">Role</td>
                            <td><span class="badge badge-dark-theme-info"><?php echo ucfirst($user_detail->role); ?></span></td>
                        </tr>
                        <tr>
                            <td class="info-label">Status</td>
                            <td>
                                <?php if($user_detail->is_active): ?>
                                    <span class="badge badge-dark-theme-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-dark-theme-danger">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Terdaftar Pada</td>
                            <td><?php echo date('d F Y, H:i', strtotime($user_detail->created_at)); ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">Terakhir Login</td>
                            <td><?php echo isset($user_detail->last_login) && $user_detail->last_login ? date('d F Y, H:i', strtotime($user_detail->last_login)) : 'Belum pernah'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if(!empty($workshops)): ?>
            <div class="card card-dark-theme">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-wrench"></i> Bengkel Dimiliki</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-dark-theme">
                            <thead>
                                <tr>
                                    <th>Nama Bengkel</th>
                                    <th>Lokasi</th>
                                    <th>Status Verifikasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($workshops as $workshop): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($workshop->name); ?></td>
                                    <td><?php echo htmlspecialchars($workshop->address); ?></td>
                                    <td>
                                        <?php if(!empty($workshop->verified_at)): ?>
                                            <span class="badge badge-dark-theme-success">Terverifikasi</span>
                                        <?php else: ?>
                                            <span class="badge badge-dark-theme-warning">Belum Terverifikasi</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url('admin/view_workshop/'.$workshop->id); ?>" 
                                           class="btn btn-sm btn-admin-info-dark">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.profile-img { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; }
.info-label { font-weight: 600; color: #a0a0a0; }
.card-dark-theme {
    background-color: #fff;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.card-dark-theme .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    color: #212529;
}
.card-dark-theme .card-body {
    background-color: #fff;
    color: #212529;
}
.form-control-dark, .form-control-dark:focus {
    background-color: #fff;
    border-color: #ced4da;
    color: #212529;
}
.form-check-input-dark {
    background-color: #fff;
    border-color: #ced4da;
}
.form-check-input-dark:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
.text-light, .form-check-label.text-light {
    color: #212529 !important;
}
</style>

