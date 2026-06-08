<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <style>
        .stat-card { border-radius: 10px; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
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
                        <h2><i class="fas fa-users"></i> <?php echo $page_title; ?></h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Manajemen Pengguna</li>
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
                    <table id="usersTable" class="table table-striped table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?php echo site_url("admin/users_data"); ?>',
                    data: function(d) {
                        d.role_filter = $('#roleFilter').val();
                    }
                },
                columns: [
                    { data: 'id' },
                    { 
                        data: 'full_name',
                        render: function(data, type, row) {
                            return '<a href="<?php echo site_url("admin/view_user/"); ?>' + row.id + '">' + data + '</a>';
                        }
                    },
                    { data: 'email' },
                    { 
                        data: 'role',
                        render: function(data) {
                            return '<span class="badge bg-info">' + data + '</span>';
                        }
                    },
                    { 
                        data: 'is_active',
                        render: function(data) {
                            return data == 1 
                                ? '<span class="badge bg-success">Aktif</span>' 
                                : '<span class="badge bg-danger">Nonaktif</span>';
                        }
                    },
                    { 
                        data: 'created_at',
                        render: function(data) {
                            return new Date(data).toLocaleDateString('id-ID');
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            var actions = '<a href="<?php echo site_url("admin/view_user/"); ?>' + row.id + '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> ';
                            
                            if (row.is_active == 1) {
                                actions += '<a href="<?php echo site_url("admin/deactivate_user/"); ?>' + row.id + '" class="btn btn-sm btn-warning" onclick="return confirm(\'Nonaktifkan pengguna ini?\')"><i class="fas fa-ban"></i></a> ';
                            } else {
                                actions += '<a href="<?php echo site_url("admin/activate_user/"); ?>' + row.id + '" class="btn btn-sm btn-success" onclick="return confirm(\'Aktifkan pengguna ini?\')"><i class="fas fa-check"></i></a> ';
                            }
                            
                            actions += '<button class="btn btn-sm btn-primary" onclick="openResetModal(' + row.id + ')"><i class="fas fa-key"></i></a> ';
                            
                            if (row.id != <?php echo $this->session->userdata('user_id'); ?>) {
                                actions += '<a href="<?php echo site_url("admin/delete_user/"); ?>' + row.id + '" class="btn btn-sm btn-danger" onclick="return confirm(\'Hapus pengguna ini? Tindakan ini tidak dapat dibatalkan.\')"><i class="fas fa-trash"></i></a>';
                            }
                            
                            return actions;
                        }
                    }
                ],
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                order: [[0, 'desc']]
            });

            $('#roleFilter').on('change', function() {
                table.ajax.reload();
            });
        });

        function openResetModal(userId) {
            $('#resetUserId').val(userId);
            $('#resetPasswordForm').attr('action', '<?php echo site_url("admin/reset_password/"); ?>' + userId);
            $('#resetPasswordModal').modal('show');
        }
    </script>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
</body>
</html>
