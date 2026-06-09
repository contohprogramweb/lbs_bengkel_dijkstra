<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row mb-4 page-header">
    <div class="col-12">
        <h2><i class="fas fa-star"></i> <?php echo $page_title; ?></h2>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card bg-admin-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title opacity-75">Review Pending Moderasi</h6>
                        <h3 class="mb-0"><?php echo number_format($pending_count); ?></h3>
                    </div>
                    <i class="fas fa-flag fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">Menunggu persetujuan admin</small>
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
            <table id="reviewsTable" class="table datatable" style="width: 100%;">
                <thead>
                    <tr>
                        <th width="5%">No</th>
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

<!-- Modal Approve -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="approveForm">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="">
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
                    <button type="button" class="btn btn-admin-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-success">Setujui</button>
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
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="">
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
                    <button type="button" class="btn btn-admin-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Function to refresh CSRF token
    function refreshCsrfToken() {
        $.ajax({
            url: '<?php echo site_url("admin/get_csrf_token"); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('input[name="<?php echo $this->security->get_csrf_token_name(); ?>"]').val(response.csrf_hash);
            }
        });
    }
    
    // Refresh token on page load
    refreshCsrfToken();
    
    // Refresh token when modal is shown
    $('#approveModal').on('show.bs.modal', function() {
        refreshCsrfToken();
    });
    
    $('#rejectModal').on('show.bs.modal', function() {
        refreshCsrfToken();
    });

    $('#reviewsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?php echo site_url("admin/pending_reviews_data"); ?>'
        },
        columns: [
            { 
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
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
                data: 'review_text',
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
                    return '<button class="btn btn-sm btn-admin-success" onclick="openApproveModal(' + row.id + ')"><i class="fas fa-check"></i></button> ' +
                           '<button class="btn btn-sm btn-admin-danger" onclick="openRejectModal(' + row.id + ')"><i class="fas fa-times"></i></button>';
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        },
        order: [[1, 'asc']]
    });
});

function openApproveModal(reviewId) {
    $('#approveReviewId').val(reviewId);
    $('#approveForm').attr('action', '<?php echo site_url("admin/approve_review/"); ?>' + reviewId);
    // Refresh CSRF token before opening modal
    $.ajax({
        url: '<?php echo site_url("admin/get_csrf_token"); ?>',
        type: 'GET',
        dataType: 'json',
        async: false,
        success: function(response) {
            $('#approveForm input[name="<?php echo $this->security->get_csrf_token_name(); ?>"]').val(response.csrf_hash);
        }
    });
    $('#approveModal').modal('show');
}

function openRejectModal(reviewId) {
    $('#rejectReviewId').val(reviewId);
    $('#rejectForm').attr('action', '<?php echo site_url("admin/reject_review/"); ?>' + reviewId);
    // Refresh CSRF token before opening modal
    $.ajax({
        url: '<?php echo site_url("admin/get_csrf_token"); ?>',
        type: 'GET',
        dataType: 'json',
        async: false,
        success: function(response) {
            $('#rejectForm input[name="<?php echo $this->security->get_csrf_token_name(); ?>"]').val(response.csrf_hash);
        }
    });
    $('#rejectModal').modal('show');
}
</script>
