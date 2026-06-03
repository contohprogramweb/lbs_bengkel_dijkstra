<?php $this->load->view('layouts/user_header', ['page_title' => $page_title, 'user' => $user]); ?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-inbox"></i> <?= $page_title ?></h3>
                    <div class="card-tools">
                        <?php if ($unread_count > 0): ?>
                            <a href="<?= site_url('notifications/mark_all_read') ?>" class="btn btn-sm btn-success" title="Tandai semua dibaca">
                                <i class="fas fa-check-double"></i> Tandai Semua Dibaca (<?= $unread_count ?>)
                            </a>
                        <?php endif; ?>
                        <div class="btn-group ml-2">
                            <a href="<?= site_url('notifications/inbox') ?>" class="btn btn-sm <?= empty($current_filter) ? 'btn-primary' : 'btn-outline-secondary' ?>">Semua</a>
                            <a href="<?= site_url('notifications/inbox?filter=unread') ?>" class="btn btn-sm <?= $current_filter === 'unread' ? 'btn-primary' : 'btn-outline-secondary' ?>">Belum Dibaca</a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success m-3"><?= $this->session->flashdata('success') ?></div>
                    <?php endif; ?>

                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada notifikasi.</p>
                        </div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($notifications as $notif): ?>
                                <li class="list-group-item <?= empty($notif['opened_at']) ? 'bg-light' : '' ?>">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <?php if (empty($notif['opened_at'])): ?>
                                                <span class="badge badge-primary"><i class="fas fa-circle fa-xs"></i></span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><i class="fas fa-check fa-xs"></i></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-0">
                                                    <a href="<?= site_url('notifications/view/' . $notif['id']) ?>" 
                                                       class="<?= empty($notif['opened_at']) ? 'font-weight-bold' : '' ?>">
                                                        <?= htmlspecialchars($notif['subject']) ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?></small>
                                            </div>
                                            <p class="mb-0 text-muted small">
                                                <?php 
                                                $event_labels = [
                                                    'booking_accepted' => 'Booking Diterima',
                                                    'booking_processed' => 'Booking Diproses',
                                                    'booking_completed' => 'Booking Selesai',
                                                    'booking_cancelled' => 'Booking Dibatalkan',
                                                    'booking_rejected' => 'Booking Ditolak',
                                                    'service_reminder' => 'Pengingat Servis',
                                                    'emergency_alert' => 'Alert Darurat'
                                                ];
                                                ?>
                                                <span class="badge badge-info"><?= $event_labels[$notif['event_key']] ?? $notif['event_key'] ?></span>
                                            </p>
                                        </div>
                                        <div class="col-auto">
                                            <?php if (empty($notif['opened_at'])): ?>
                                                <button class="btn btn-sm btn-outline-primary mark-read-btn" data-id="<?= $notif['id'] ?>">
                                                    <i class="fas fa-envelope-open"></i> Baca
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <!-- Pagination -->
                        <?php
                        $total_pages = ceil(count($notifications) / 20);
                        $current_page = max(1, (int)$this->input->get('page') + 1);
                        if ($total_pages > 1):
                        ?>
                            <nav aria-label="Page navigation" class="mt-3">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= site_url('notifications/inbox?page=' . ($i - 1) . ($current_filter ? '&filter=' . $current_filter : '')) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.mark-read-btn').click(function(e) {
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');
        
        $.post('<?= site_url('notifications/mark_read') ?>', { id: id })
            .done(function(response) {
                if (response.success) {
                    // Update UI
                    btn.closest('li').removeClass('bg-light');
                    btn.closest('.col-auto').html('<span class="badge badge-secondary"><i class="fas fa-check fa-xs"></i></span>');
                    btn.remove();
                    
                    // Update badge count
                    updateUnreadBadge();
                }
            });
    });
});

function updateUnreadBadge() {
    $.get('<?= site_url('notifications/unread_count') ?>', function(response) {
        if (response.success) {
            var count = response.data.count;
            $('.notification-badge').text(count);
            if (count === 0) {
                $('.notification-badge').hide();
            }
        }
    });
}
</script>

<?php $this->load->view('layouts/user_footer'); ?>
