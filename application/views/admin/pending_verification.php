<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .workshop-img { max-width: 150px; max-height: 100px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand"><i class="fas fa-shield-alt"></i> Admin Panel</span>
            <div class="d-flex align-items-center">
                <span class="text-light me-3"><?php echo $user->name; ?></span>
                <a href="<?php echo site_url('auth/logout'); ?>" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <?php $this->load->view('admin/_sidebar'); ?>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2><i class="fas fa-clock"></i> <?php echo $page_title; ?></h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/workshops'); ?>">Bengkel</a></li>
                                <li class="breadcrumb-item active">Pending Verification</li>
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

        <?php if(empty($workshops)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Tidak ada bengkel yang menunggu verifikasi.
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Bengkel Menunggu Verifikasi (<?php echo count($workshops); ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Bengkel</th>
                                    <th>Pemilik</th>
                                    <th>Lokasi</th>
                                    <th>Dokumen</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($workshops as $workshop): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($workshop->name); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($workshop->specialization ?? 'Umum'); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($workshop->owner_name); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($workshop->email); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(substr($workshop->address, 0, 40)); ?>
                                        <?php if(strlen($workshop->address) > 40): ?>...<?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($workshop->business_license)): ?>
                                            <span class="badge bg-success"><i class="fas fa-check"></i> Izin Usaha</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="fas fa-times"></i> Izin Usaha</span>
                                        <?php endif; ?>
                                        
                                        <?php 
                                        $docs = json_decode($workshop->other_documents ?? '[]', true);
                                        if(is_array($docs) && count($docs) > 0):
                                        ?>
                                            <span class="badge bg-info ms-1"><?php echo count($docs); ?> Dokumen Lain</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($workshop->created_at)); ?></td>
                                    <td>
                                        <a href="<?php echo site_url('admin/view_workshop/'.$workshop->id); ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <a href="<?php echo site_url('admin/verify_workshop/'.$workshop->id); ?>" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('Verifikasi bengkel ini?')">
                                            <i class="fas fa-check"></i> Verifikasi
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

        <div class="mt-3">
            <a href="<?php echo site_url('admin/workshops'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Bengkel
            </a>
        </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
