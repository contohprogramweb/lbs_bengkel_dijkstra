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
                        <h2><i class="fas fa-wrench"></i> <?php echo $page_title; ?></h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Manajemen Bengkel</li>
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
                    <table id="workshopsTable" class="table table-striped table-hover" style="width: 100%;">
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
                        <tbody>
                        </tbody>
                    </table>
                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#workshopsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?php echo site_url("admin/workshops_data"); ?>',
                    data: function(d) {
                        d.verification_status = $('#verificationFilter').val();
                    }
                },
                columns: [
                    { data: 'id' },
                    { 
                        data: 'name',
                        render: function(data, type, row) {
                            return '<a href="<?php echo site_url("admin/view_workshop/"); ?>' + row.id + '">' + data + '</a>';
                        }
                    },
                    { data: 'owner_name' },
                    { 
                        data: 'address',
                        render: function(data) {
                            return data ? data.substring(0, 30) + (data.length > 30 ? '...' : '') : '-';
                        }
                    },
                    { 
                        data: 'verification_status',
                        render: function(data, type, row) {
                            if (row.is_verified) {
                                return '<span class="badge bg-success">Terverifikasi</span>';
                            } else if (data === 'pending') {
                                return '<span class="badge bg-warning">Pending</span>';
                            } else {
                                return '<span class="badge bg-secondary">Belum Diajukan</span>';
                            }
                        }
                    },
                    { 
                        data: 'is_featured',
                        render: function(data) {
                            return data == 1 
                                ? '<span class="badge bg-primary">Yes</span>' 
                                : '<span class="badge bg-secondary">No</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            var actions = '<a href="<?php echo site_url("admin/view_workshop/"); ?>' + row.id + '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a> ';
                            
                            if (!row.is_verified && row.verification_status === 'pending') {
                                actions += '<a href="<?php echo site_url("admin/verify_workshop/"); ?>' + row.id + '" class="btn btn-sm btn-success" onclick="return confirm(\'Verifikasi bengkel ini?\')"><i class="fas fa-check"></i></a> ';
                            }
                            
                            actions += '<button class="btn btn-sm btn-primary" onclick="openFeaturedModal(' + row.id + ', ' + (row.is_featured == 1 ? 'true' : 'false') + ')"><i class="fas fa-star"></i></a>';
                            
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

            $('#verificationFilter').on('change', function() {
                table.ajax.reload();
            });
        });

        function openFeaturedModal(workshopId, isFeatured) {
            $('#featuredWorkshopId').val(workshopId);
            $('#isFeaturedCheck').prop('checked', isFeatured);
            $('#setFeaturedForm').attr('action', '<?php echo site_url("admin/set_featured/"); ?>' + workshopId);
            $('#setFeaturedModal').modal('show');
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
