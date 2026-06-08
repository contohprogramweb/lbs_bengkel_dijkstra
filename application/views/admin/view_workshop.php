<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .workshop-img { max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 8px; }
        .info-label { font-weight: 600; color: #6c757d; }
        .document-list img { max-width: 150px; margin: 5px; border-radius: 5px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand"><i class="fas fa-shield-alt"></i> Admin Panel</span>
            <div class="d-flex align-items-center">
                <span class="text-light me-3"><?php echo $user->full_name; ?></span>
                <a href="<?php echo site_url('auth/logout'); ?>" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-wrench"></i> <?php echo $page_title; ?></h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo site_url('admin/workshops'); ?>">Bengkel</a></li>
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
                    <?php if(!empty($workshop->logo)): ?>
                        <img src="<?php echo base_url($workshop->logo); ?>" class="card-img-top workshop-img mx-auto mt-3" alt="<?php echo htmlspecialchars($workshop->name); ?>">
                    <?php else: ?>
                        <div class="card-body text-center py-5">
                            <i class="fas fa-wrench fa-5x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body text-center">
                        <h4><?php echo htmlspecialchars($workshop->name); ?></h4>
                        <span class="badge bg-info"><?php echo ucfirst($workshop->status ?? 'pending'); ?></span>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <strong>Status:</strong><br>
                            <?php if(!empty($workshop->verified_at)): ?>
                                <span class="badge bg-success">Terverifikasi</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Belum Terverifikasi</span>
                            <?php endif; ?>
                            
                            <?php if($workshop->is_featured): ?>
                                <span class="badge bg-primary ms-1">Featured</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="<?php echo site_url('admin/workshops'); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <?php if(empty($workshop->verified_at)): ?>
                                <a href="<?php echo site_url('admin/verify_workshop/'.$workshop->id); ?>" 
                                   class="btn btn-success" onclick="return confirm('Verifikasi bengkel ini?')">
                                    <i class="fas fa-check"></i> Verifikasi Bengkel
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Bengkel</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="info-label" style="width: 200px;">ID</td>
                                <td><?php echo $workshop->id; ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Nama Bengkel</td>
                                <td><?php echo htmlspecialchars($workshop->name); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Spesialisasi</td>
                                <td><?php echo ucfirst(htmlspecialchars($workshop->status ?? 'pending')); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Alamat</td>
                                <td><?php echo htmlspecialchars($workshop->address); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Koordinat</td>
                                <td>
                                    <?php if($workshop->latitude && $workshop->longitude): ?>
                                        <a href="https://www.google.com/maps?q=<?php echo $workshop->latitude; ?>,<?php echo $workshop->longitude; ?>" target="_blank">
                                            <?php echo $workshop->latitude; ?>, <?php echo $workshop->longitude; ?>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">No. Telepon</td>
                                <td><?php echo htmlspecialchars($workshop->phone ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Jam Operasional</td>
                                <td>
                                    <?php if($workshop->operating_hours): ?>
                                        <?php 
                                        $oh = is_string($workshop->operating_hours) ? json_decode($workshop->operating_hours, true) : $workshop->operating_hours;
                                        echo is_array($oh) ? nl2br(htmlspecialchars(json_encode($oh, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) : nl2br(htmlspecialchars($workshop->operating_hours));
                                        ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">Rating</td>
                                <td>
                                    <?php if($workshop->rating_avg): ?>
                                        <span class="text-warning">
                                            <i class="fas fa-star"></i> <?php echo number_format($workshop->rating_avg, 1); ?>
                                        </span>
                                        (<?php echo $workshop->total_reviews; ?> review)
                                    <?php else: ?>
                                        Belum ada rating
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">Terdaftar Pada</td>
                                <td><?php echo date('d F Y, H:i', strtotime($workshop->created_at)); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if(!empty($workshop->business_license) || !empty($workshop->certification_doc)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-alt"></i> Dokumen Verifikasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="document-list">
                            <?php if($workshop->business_license): ?>
                                <div class="mb-3">
                                    <strong>Izin Usaha:</strong><br>
                                    <a href="<?php echo base_url($workshop->business_license); ?>" target="_blank">
                                        <img src="<?php echo base_url($workshop->business_license); ?>" alt="Business License">
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($workshop->certification_doc): ?>
                                <div>
                                    <strong>Dokumen Sertifikasi:</strong><br>
                                    <a href="<?php echo base_url($workshop->certification_doc); ?>" target="_blank">
                                        <img src="<?php echo base_url($workshop->certification_doc); ?>" alt="Certification Document">
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
