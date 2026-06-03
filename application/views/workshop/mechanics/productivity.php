<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="mb-0"><?= $page_title; ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= site_url('mechanic'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <?php echo form_open(current_url(), ['method' => 'get', 'class' => 'form-inline']); ?>
                <div class="form-group mr-3">
                    <label for="start_date" class="mr-2">Dari Tanggal:</label>
                    <input type="date" 
                           name="start_date" 
                           id="start_date" 
                           class="form-control" 
                           value="<?= esc($start_date); ?>">
                </div>
                <div class="form-group mr-3">
                    <label for="end_date" class="mr-2">Sampai Tanggal:</label>
                    <input type="date" 
                           name="end_date" 
                           id="end_date" 
                           class="form-control" 
                           value="<?= esc($end_date); ?>">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
            <?php echo form_close(); ?>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h2><?= $summary['total_mechanics']; ?></h2>
                    <p class="mb-0">Total Mekanik</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h2><?= $summary['total_completed_bookings']; ?></h2>
                    <p class="mb-0">Pesanan Selesai</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h2><?= number_format($summary['average_rating'], 2); ?></h2>
                    <p class="mb-0">Rating Rata-rata</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h5 class="mb-0">Top Performer</h5>
                    <?php if (!empty($summary['top_performer'])): ?>
                        <p class="mb-0 mt-2"><?= esc($summary['top_performer']['name']); ?></p>
                        <small><?= $summary['top_performer']['completed_count']; ?> selesai</small>
                    <?php else: ?>
                        <p class="mb-0 mt-2">-</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Productivity Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Produktivitas per Mekanik</h5>
        </div>
        <div class="card-body">
            <?php if (empty($productivity)): ?>
                <div class="alert alert-info">
                    Belum ada data produktivitas untuk periode yang dipilih.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="productivityTable">
                        <thead>
                            <tr>
                                <th>Mekanik</th>
                                <th>Spesialisasi</th>
                                <th>Status</th>
                                <th class="text-center">Total Pesanan</th>
                                <th class="text-center">Selesai</th>
                                <th class="text-center">Rating</th>
                                <th class="text-center">Review</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productivity as $mech): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($mech['name']); ?></strong><br>
                                        <small class="text-muted"><?= esc($mech['email']); ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                        $specs = json_decode($mech['specialization'] ?? '[]', TRUE);
                                        if (!empty($specs)):
                                            foreach ($specs as $spec):
                                                echo '<span class="badge badge-info mr-1">' . ucfirst($spec) . '</span>';
                                            endforeach;
                                        else:
                                            echo '<span class="text-muted">-</span>';
                                        endif;
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($mech['is_available']): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Non-Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-primary"><?= $mech['total_bookings']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success"><?= $mech['completed_count']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($mech['avg_rating'])): ?>
                                            <strong><?= number_format($mech['avg_rating'], 2); ?></strong>
                                            <div class="text-warning small">
                                                <?php 
                                                $rating = round($mech['avg_rating']);
                                                for ($i = 1; $i <= 5; $i++):
                                                    echo '<i class="fas fa-star' . ($i <= $rating ? '' : '-o') . '"></i>';
                                                endfor;
                                                ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info"><?= $mech['review_count']; ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('mechanic/detail/' . $mech['mechanic_id']); ?>" 
                                           class="btn btn-sm btn-info"
                                           title="Detail Mekanik">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
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
$(document).ready(function() {
    $('#productivityTable').DataTable({
        order: [[4, 'desc']], // Sort by completed count
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json'
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export Excel',
                className: 'btn btn-success btn-sm mb-3',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm mb-3 ml-2'
            }
        ]
    });
});
</script>
