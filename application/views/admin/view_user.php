<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { 
            min-height: 100vh; 
            background: #212529; 
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar a { 
            color: #adb5bd; 
            text-decoration: none; 
            padding: 10px 15px; 
            display: block; 
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active { 
            background: #343a40; 
            color: #fff; 
            border-left: 3px solid #0d6efd;
        }
        .sidebar-sub { 
            padding-left: 30px !important; 
            font-size: 0.9em;
        }
        .sidebar-dropdown .dropdown-toggle::after { display: none; }
        .main-content { padding: 20px; }
        .profile-img { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; }
        .info-label { font-weight: 600; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php $this->load->view('admin/_sidebar'); ?>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <!-- Top Navbar -->
                <nav class="navbar navbar-dark bg-dark mb-4 rounded">
                    <div class="container-fluid">
                        <span class="navbar-brand"><i class="fas fa-shield-alt"></i> Admin Panel</span>
                        <div class="d-flex align-items-center">
                            <span class="text-light me-3"><?php echo $user->full_name; ?></span>
                            <a href="<?php echo site_url('auth/logout'); ?>" class="btn btn-outline-light btn-sm">Logout</a>
                        </div>
                    </div>
                </nav>
                
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

        <?php if($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="<?php echo !empty($user_detail->avatar) ? base_url($user_detail->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user_detail->full_name).'&size=150'; ?>" 
                             alt="Profile" class="profile-img mb-3">
                        <h4><?php echo htmlspecialchars($user_detail->full_name); ?></h4>
                        <span class="badge bg-info"><?php echo ucfirst($user_detail->role); ?></span>
                        <span class="badge <?php echo $user_detail->is_active ? 'bg-success' : 'bg-danger'; ?> mt-2">
                            <?php echo $user_detail->is_active ? 'Aktif' : 'Nonaktif'; ?>
                        </span>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <a href="<?php echo site_url('admin/users'); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <?php if($user_detail->is_active): ?>
                                <a href="<?php echo site_url('admin/deactivate_user/'.$user_detail->id); ?>" 
                                   class="btn btn-warning" onclick="return confirm('Nonaktifkan pengguna ini?')">
                                    <i class="fas fa-ban"></i> Nonaktifkan
                                </a>
                            <?php else: ?>
                                <a href="<?php echo site_url('admin/activate_user/'.$user_detail->id); ?>" 
                                   class="btn btn-success" onclick="return confirm('Aktifkan pengguna ini?')">
                                    <i class="fas fa-check"></i> Aktifkan
                                </a>
                            <?php endif; ?>
                            <?php if($user_detail->id != $this->session->userdata('user_id')): ?>
                                <a href="<?php echo site_url('admin/delete_user/'.$user_detail->id); ?>" 
                                   class="btn btn-danger" onclick="return confirm('Hapus pengguna ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Pengguna</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
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
                                <td><span class="badge bg-info"><?php echo ucfirst($user_detail->role); ?></span></td>
                            </tr>
                            <tr>
                                <td class="info-label">Status</td>
                                <td>
                                    <?php if($user_detail->is_active): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">Terdaftar Pada</td>
                                <td><?php echo date('d F Y, H:i', strtotime($user_detail->created_at)); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Terakhir Login</td>
                                <td><?php echo $user_detail->last_login ? date('d F Y, H:i', strtotime($user_detail->last_login)) : 'Belum pernah'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if(!empty($workshops)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-wrench"></i> Bengkel Dimiliki</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                                <span class="badge bg-success">Terverifikasi</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Belum Terverifikasi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('admin/view_workshop/'.$workshop->id); ?>" 
                                               class="btn btn-sm btn-info">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>