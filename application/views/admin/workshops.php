<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row mb-4 page-header">
    <div class="col-12">
        <h2><i class="fas fa-wrench"></i> <?php echo $page_title; ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Manajemen Bengkel</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list"></i> Daftar Bengkel</h5>
        <div>
            <select id="verificationFilter" class="form-select form-select-sm d-inline-block w-auto">
                <option value="">Semua Status</option>
                <option value="verified">Terverifikasi</option>
                <option value="pending">Pending</option>
                <option value="not_submitted">Belum Diajukan</option>
            </select>
            <a href="<?php echo site_url('admin/pending_verification'); ?>" class="btn btn-sm btn-warning ms-2">
                <i class="fas fa-clock"></i> Pending Verification
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="workshopsTable" class="table table-striped table-hover datatable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Bengkel</th>
                        <th>Pemilik</th>
                        <th>Lokasi</th>
                        <th>Status Verifikasi</th>
                        <th>Featured</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Set Featured -->
<div class="modal fade" id="setFeaturedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="setFeaturedForm">
                <div class="modal-header">
                    <h5 class="modal-title">Set Featured Workshop</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="featuredWorkshopId">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeaturedCheck">
                        <label class="form-check-label" for="isFeaturedCheck">Jadikan Featured</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('#workshopsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?php echo site_url("admin/workshops_data"); ?>',
            data: function(d) { d.verification_status = $('#verificationFilter').val(); }
        },
        columns: [
            { data: 'id' },
            { data: 'name', render: function(data, type, row) { return '<a href="<?php echo site_url("admin/view_workshop/"); ?>' + row.id + '">' + (data || '-') + '</a>'; } },
            { data: 'owner_name' },
            { data: 'city', render: function(data) { return data ? data : '-'; } },
            { data: 'verified_at', render: function(data) { return data ? '<span class="badge bg-admin-success">Terverifikasi</span>' : '<span class="badge bg-admin-warning text-dark">Belum Terverifikasi</span>'; } },
            { data: 'is_featured', render: function(data) { return data == 1 ? '<span class="badge bg-admin-primary">Yes</span>' : '<span class="badge bg-admin-secondary">No</span>'; } },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    var actions = '<a href="<?php echo site_url("admin/view_workshop/"); ?>' + row.id + '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> ';
                    
                    if (!row.verified_at) {
                        actions += '<a href="#" class="btn btn-sm btn-success" onclick="confirmAction(\'<?php echo site_url("admin/verify_workshop/"); ?>' + row.id + '\', \'Verifikasi Bengkel\', \'Verifikasi bengkel ini?\', \'Ya, Verifikasi\', \'#198754\')"><i class="fas fa-check"></i></a> ';
                    }
                    
                    actions += '<button class="btn btn-sm btn-primary" onclick="openFeaturedModal(' + row.id + ', ' + (row.is_featured == 1 ? 'true' : 'false') + ')"><i class="fas fa-star"></i></button>';
                    
                    return actions;
                }
            }
        ],
        responsive: true,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
        order: [[0, 'desc']]
    });

    $('#verificationFilter').on('change', function() { table.ajax.reload(); });
});

function openFeaturedModal(workshopId, isFeatured) {
    $('#featuredWorkshopId').val(workshopId);
    $('#isFeaturedCheck').prop('checked', isFeatured);
    $('#setFeaturedForm').attr('action', '<?php echo site_url("admin/set_featured/"); ?>' + workshopId);
    $('#setFeaturedModal').modal('show');
}
</script>

