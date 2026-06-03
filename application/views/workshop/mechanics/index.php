<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="mb-0"><?= $page_title; ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= site_url('mechanic/create'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Mekanik
                </a>
                <a href="<?= site_url('mechanic/productivity'); ?>" class="btn btn-info ml-2">
                    <i class="fas fa-chart-bar"></i> Laporan Produktivitas
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Mekanik</h6>
                            <h2 class="mb-0"><?= $stats['total']; ?></h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Tersedia</h6>
                            <h2 class="mb-0"><?= $stats['available']; ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Tidak Tersedia</h6>
                            <h2 class="mb-0"><?= $stats['unavailable']; ?></h2>
                        </div>
                        <i class="fas fa-times-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mechanics Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Mekanik</h5>
        </div>
        <div class="card-body">
            <?php if (empty($mechanics)): ?>
                <div class="alert alert-info">
                    Belum ada mekanik. Silakan tambahkan mekanik terlebih dahulu.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="mechanicsTable">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Spesialisasi</th>
                                <th>Pengalaman</th>
                                <th>Kontak</th>
                                <th>Status</th>
                                <th width="200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mechanics as $mech): ?>
                                <?php 
                                    $specializations = json_decode($mech['specialization'] ?? '[]', TRUE);
                                    $spec_badges = '';
                                    if (!empty($specializations)) {
                                        foreach ($specializations as $spec) {
                                            $spec_badges .= '<span class="badge badge-info mr-1">' . ucfirst($spec) . '</span>';
                                        }
                                    } else {
                                        $spec_badges = '<span class="text-muted">-</span>';
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($mech['name']); ?></strong><br>
                                        <small class="text-muted">ID: #<?= $mech['id']; ?></small>
                                    </td>
                                    <td><?= $spec_badges; ?></td>
                                    <td><?= $mech['experience_years']; ?> tahun</td>
                                    <td>
                                        <?= esc($mech['phone'] ?? '-'); ?><br>
                                        <small class="text-muted"><?= esc($mech['email'] ?? '-'); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($mech['is_available']): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Non-Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('mechanic/detail/' . $mech['id']); ?>" 
                                               class="btn btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= site_url('mechanic/edit/' . $mech['id']); ?>" 
                                               class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-<?= $mech['is_available'] ? 'secondary' : 'success'; ?>" 
                                                    onclick="toggleAvailability(<?= $mech['id']; ?>, <?= $mech['is_available']; ?>)"
                                                    title="<?= $mech['is_available'] ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                                                <i class="fas fa-<?= $mech['is_available'] ? 'pause' : 'play'; ?>"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-danger" 
                                                    onclick="deleteMechanic(<?= $mech['id']; ?>, '<?= esc($mech['name']); ?>')"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleAvailability(mechanicId, currentStatus) {
    if (!confirm('Apakah Anda yakin ingin mengubah status ketersediaan mekanik ini?')) {
        return;
    }

    $.ajax({
        url: '<?= site_url('mechanic/toggle_availability/'); ?>' + mechanicId,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Gagal mengubah status');
            }
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Terjadi kesalahan';
            alert(msg);
        }
    });
}

function deleteMechanic(mechanicId, mechanicName) {
    if (!confirm('Apakah Anda yakin ingin menghapus mekanik "' + mechanicName + '"?\n\nMekanik yang sudah ditugaskan ke pesanan tidak akan terhapus dari history.')) {
        return;
    }

    $.ajax({
        url: '<?= site_url('mechanic/delete/'); ?>' + mechanicId,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Gagal menghapus mekanik');
            }
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Terjadi kesalahan';
            alert(msg);
        }
    });
}

$(document).ready(function() {
    $('#mechanicsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json'
        }
    });
});
</script>
