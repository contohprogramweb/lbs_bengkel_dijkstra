<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('admin/layouts/header'); ?>

<div class="row mb-4 page-header">
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
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Bengkel</th>
                        <th>Pemilik</th>
                        <th>Lokasi</th>
                        <th>Dokumen</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach($workshops as $workshop):
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($workshop->name); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($workshop->specialization ?? 'Umum'); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($workshop->owner_name); ?><br>
                            <small class="text-muted"><?php echo htmlspecialchars($workshop->owner_email); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars(substr($workshop->address, 0, 40)); ?>
                            <?php if(strlen($workshop->address) > 40): ?>...<?php endif; ?>
                        </td>
                        <td>
                            <?php if(!empty($workshop->business_license)): ?>
                                <span class="badge bg-dark-theme-success"><i class="fas fa-check"></i> Izin Usaha</span>
                            <?php else: ?>
                                <span class="badge bg-dark-theme-danger"><i class="fas fa-times"></i> Izin Usaha</span>
                            <?php endif; ?>

                            <?php if(!empty($workshop->certification_doc)): ?>
                                <span class="badge bg-dark-theme-info ms-1"><i class="fas fa-certificate"></i> Sertifikasi</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($workshop->created_at)); ?></td>
                        <td>
                            <a href="<?php echo site_url('admin/view_workshop/'.$workshop->id); ?>"
                               class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <a href="#" class="btn btn-sm btn-success"
                               onclick="confirmAction('<?php echo site_url('admin/verify_workshop/'.$workshop->id); ?>', 'Verifikasi Bengkel', 'Verifikasi bengkel ini?', 'Ya, Verifikasi', '#198754')">
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

<script>
$(document).ready(function() {
    // Show flash messages using SweetAlert
    <?php if($this->session->flashdata('success')): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '<?php echo addslashes($this->session->flashdata('success')); ?>',
        timer: 3000,
        showConfirmButton: false
    });
    <?php endif; ?>
    
    <?php if($this->session->flashdata('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '<?php echo addslashes($this->session->flashdata('error')); ?>',
        timer: 5000
    });
    <?php endif; ?>
});
</script>

<?php $this->load->view('admin/layouts/footer'); ?>
