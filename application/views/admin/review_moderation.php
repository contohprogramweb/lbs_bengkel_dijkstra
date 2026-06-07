<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
    
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-star"></i> <?php echo $page_title; ?></h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Moderasi Review</li>
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

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3><i class="fas fa-flag"></i> <?php echo number_format($pending_count); ?></h3>
                        <p class="mb-0">Review Pending Moderasi</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Review Menunggu Persetujuan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="reviewsTable" class="table table-striped table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pemberi Review</th>
                                <th>Bengkel</th>
                                <th>Rating</th>
                                <th>Komentar</th>
                                <th>Laporan</th>
                                <th>Tanggal</th>
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

    <!-- Modal Approve -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="approveForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Setujui Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="approveReviewId">
                        <div class="mb-3">
                            <label class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Setujui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Reject -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="rejectForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Tolak Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="rejectReviewId">
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="notes" rows="3" required></textarea>
                            <small class="text-muted">Alasan penolakan wajib diisi</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#reviewsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?php echo site_url("admin/pending_reviews_data"); ?>'
                },
                columns: [
                    { data: 'id' },
                    { 
                        data: 'reviewer_name',
                        render: function(data, type, row) {
                            return '<strong>' + data + '</strong><br><small class="text-muted">' + row.reviewer_email + '</small>';
                        }
                    },
                    { data: 'workshop_name' },
                    { 
                        data: 'rating',
                        render: function(data) {
                            var stars = '';
                            for(var i = 1; i <= 5; i++) {
                                if(i <= data) {
                                    stars += '<i class="fas fa-star text-warning"></i>';
                                } else {
                                    stars += '<i class="far fa-star text-muted"></i>';
                                }
                            }
                            return stars;
                        }
                    },
                    { 
                        data: 'comment',
                        render: function(data) {
                            return data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : '-';
                        }
                    },
                    { 
                        data: 'report_count',
                        render: function(data) {
                            if(data >= 3) {
                                return '<span class="badge bg-danger">' + data + ' Laporan</span>';
                            } else if(data > 0) {
                                return '<span class="badge bg-warning">' + data + ' Laporan</span>';
                            }
                            return '<span class="badge bg-secondary">0</span>';
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
                            return '<button class="btn btn-sm btn-success" onclick="openApproveModal(' + row.id + ')"><i class="fas fa-check"></i></button> ' +
                                   '<button class="btn btn-sm btn-danger" onclick="openRejectModal(' + row.id + ')"><i class="fas fa-times"></i></button>';
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                order: [[0, 'desc']]
            });
        });

        function openApproveModal(reviewId) {
            $('#approveReviewId').val(reviewId);
            $('#approveForm').attr('action', '<?php echo site_url("admin/approve_review/"); ?>' + reviewId);
            $('#approveModal').modal('show');
        }

        function openRejectModal(reviewId) {
            $('#rejectReviewId').val(reviewId);
            $('#rejectForm').attr('action', '<?php echo site_url("admin/reject_review/"); ?>' + reviewId);
            $('#rejectModal').modal('show');
        }
    </script>
</body>
</html>
