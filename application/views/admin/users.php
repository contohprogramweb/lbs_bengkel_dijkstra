<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row mb-4 page-header">
    <div class="col-12">
        <h2><i class="fas fa-users"></i> <?php echo $page_title; ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Manajemen Pengguna</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list"></i> Daftar Pengguna</h5>
        <div>
            <select id="roleFilter" class="form-select form-select-sm d-inline-block w-auto">
                <option value="">Semua Role</option>
                <?php foreach($roles as $role): ?>
                    <option value="<?php echo $role; ?>"><?php echo ucfirst($role); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="usersTable" class="table table-striped table-hover datatable" style="width: 100%;">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="resetPasswordForm">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="resetUserId">
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" class="form-control" name="new_password" required minlength="6">
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-admin-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?php echo site_url("admin/users_data"); ?>',
            type: 'GET',
            data: function(d) { 
                d.role_filter = $('#roleFilter').val();
                // Add CSRF token if needed
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                if (csrfToken) {
                    d.csrf_token = csrfToken;
                }
            }
        },
        columns: [
            { data: null, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
            { data: 'full_name', render: function(data, type, row) { return '<a href="<?php echo site_url("admin/view_user/"); ?>' + row.id + '">' + data + '</a>'; } },
            { data: 'email' },
            { data: 'role', render: function(data) { return '<span class="badge bg-admin-info">' + data + '</span>'; } },
            { data: 'is_active', render: function(data) { return data == 1 ? '<span class="badge bg-admin-success">Aktif</span>' : '<span class="badge bg-admin-danger">Nonaktif</span>'; } },
            { data: 'created_at', render: function(data) { return new Date(data).toLocaleDateString('id-ID'); } },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    var actions = '<a href="<?php echo site_url("admin/view_user/"); ?>' + row.id + '" class="btn btn-sm btn-admin-info"><i class="fas fa-eye"></i></a> ';
                    
                    if (row.is_active == 1) {
                        actions += '<a href="#" class="btn btn-sm btn-admin-warning" onclick="confirmAction(\'<?php echo site_url("admin/deactivate_user/"); ?>' + row.id + '\', \'Nonaktifkan Pengguna\', \'Apakah Anda yakin ingin menonaktifkan pengguna ini?\', \'Ya, Nonaktifkan\', \'#ffc107\')"><i class="fas fa-ban"></i></a> ';
                    } else {
                        actions += '<a href="#" class="btn btn-sm btn-admin-success" onclick="confirmAction(\'<?php echo site_url("admin/activate_user/"); ?>' + row.id + '\', \'Aktifkan Pengguna\', \'Apakah Anda yakin ingin mengaktifkan pengguna ini?\', \'Ya, Aktifkan\', \'#198754\')"><i class="fas fa-check"></i></a> ';
                    }
                    
                    actions += '<button class="btn btn-sm btn-admin-primary" onclick="openResetModal(' + row.id + ')"><i class="fas fa-key"></i></button> ';
                    
                    if (row.id != "<?php echo $this->session->userdata('user_id'); ?>") {
                        actions += '<a href="#" class="btn btn-sm btn-admin-danger" onclick="confirmDelete(\'<?php echo site_url("admin/delete_user/"); ?>' + row.id + '\', \'Hapus Pengguna\', \'Hapus pengguna ini? Tindakan ini tidak dapat dibatalkan.\')"><i class="fas fa-trash"></i></a>';
                    }
                    
                    return actions;
                }
            }
        ],
        responsive: true,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
        order: [[1, 'asc']]
    });

    $('#roleFilter').on('change', function() { table.ajax.reload(); });
});

function openResetModal(userId) {
    $('#resetUserId').val(userId);
    $('#resetPasswordForm').attr('action', '<?php echo site_url("admin/reset_password/"); ?>' + userId);
    $('#resetPasswordModal').modal('show');
}
</script>

