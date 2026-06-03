<?php $this->load->view('layouts/admin_header', ['page_title' => $page_title, 'user' => $user]); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-envelope"></i> <?= $page_title ?></h3>
                    <div class="card-tools">
                        <a href="<?= site_url('notification/create_template') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Template
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
                    <?php endif; ?>

                    <table class="table table-bordered table-striped" id="templatesTable">
                        <thead>
                            <tr>
                                <th width="5%">ID</th>
                                <th width="20%">Event</th>
                                <th width="25%">Subject</th>
                                <th width="15%">Status</th>
                                <th width="15%">Terakhir Diubah</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($templates)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada template notifikasi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($templates as $tpl): ?>
                                    <tr>
                                        <td><?= $tpl['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($tpl['event_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($tpl['event_key']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars(substr($tpl['subject_template'], 0, 50)) ?>...</td>
                                        <td>
                                            <?php if ($tpl['is_active']): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($tpl['updated_at'])) ?></td>
                                        <td class="text-center">
                                            <a href="<?= site_url('notification/edit_template/' . $tpl['id']) ?>" 
                                               class="btn btn-sm btn-info" title="Edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm <?= $tpl['is_active'] ? 'btn-warning' : 'btn-success' ?>" 
                                                    onclick="toggleTemplate(<?= $tpl['id'] ?>)"
                                                    title="<?= $tpl['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                <i class="fas fa-<?= $tpl['is_active'] ? 'pause' : 'play' ?>"></i>
                                                <?= $tpl['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-secondary" 
                                                    onclick="sendTest('<?= $tpl['event_key'] ?>')"
                                                    title="Test Notifikasi">
                                                <i class="fas fa-flask"></i> Test
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Test Notification -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kirim Test Notifikasi</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Kirim test notifikasi ke email admin: <strong><?= htmlspecialchars($user->email) ?></strong></p>
                <input type="hidden" id="test_event_key" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="confirmSendTest()">
                    <i class="fas fa-paper-plane"></i> Kirim Test
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#templatesTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'desc']]
    });
});

function toggleTemplate(id) {
    if (!confirm('Apakah Anda yakin ingin mengubah status template ini?')) {
        return;
    }

    $.post('<?= site_url('notification/toggle_template') ?>', { id: id })
        .done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Gagal mengubah status template.');
            }
        })
        .fail(function() {
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });
}

function sendTest(eventKey) {
    $('#test_event_key').val(eventKey);
    $('#testModal').modal('show');
}

function confirmSendTest() {
    var eventKey = $('#test_event_key').val();
    
    $.post('<?= site_url('notification/send_test') ?>', { event_key: eventKey })
        .done(function(response) {
            $('#testModal').modal('hide');
            if (response.success) {
                alert(response.message);
            } else {
                alert(response.message || 'Gagal mengirim test notifikasi.');
            }
        })
        .fail(function() {
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });
}
</script>

<?php $this->load->view('layouts/admin_footer'); ?>
