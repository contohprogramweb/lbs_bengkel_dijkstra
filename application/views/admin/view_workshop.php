
<div class="container-fluid">
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

    <div class="row">
        <div class="col-md-4">
            <div class="card card-dark-theme">
                <?php if(!empty($workshop->logo)): ?>
                    <img src="<?php echo base_url($workshop->logo); ?>" class="card-img-top workshop-img mx-auto mt-3" alt="<?php echo htmlspecialchars($workshop->name); ?>">
                <?php else: ?>
                    <div class="card-body text-center py-5">
                        <i class="fas fa-wrench fa-5x text-muted"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body text-center">
                    <h4><?php echo htmlspecialchars($workshop->name); ?></h4>
                    <span class="badge badge-dark-theme-info"><?php echo ucfirst($workshop->status ?? 'pending'); ?></span>
                    
                    <hr class="border-secondary">
                    
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        <?php if(!empty($workshop->verified_at)): ?>
                            <span class="badge badge-dark-theme-success">Terverifikasi</span>
                        <?php else: ?>
                            <span class="badge badge-dark-theme-warning">Belum Terverifikasi</span>
                        <?php endif; ?>
                        
                        <?php if($workshop->is_featured): ?>
                            <span class="badge badge-dark-theme-primary ms-1">Featured</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo site_url('admin/workshops'); ?>" class="btn btn-secondary-dark">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <?php if(empty($workshop->verified_at)): ?>
                            <button onclick="confirmAction('<?php echo site_url('admin/verify_workshop/'.$workshop->id); ?>', 'Verifikasi Bengkel', 'Apakah Anda yakin ingin memverifikasi bengkel ini?', 'Ya, Verifikasi', '#28a745')"
                                   class="btn btn-success-dark">
                                <i class="fas fa-check"></i> Verifikasi Bengkel
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card card-dark-theme mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Bengkel</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-dark-theme">
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
            <div class="card card-dark-theme">
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

<style>
.workshop-img { max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 8px; }
.info-label { font-weight: 600; color: #a0a0a0; }
.document-list img { max-width: 150px; margin: 5px; border-radius: 5px; }
</style>

