<?php $this->load->view('layouts/admin_header', ['page_title' => $page_title, 'user' => $user]); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-<?= $template ? 'edit' : 'plus' ?>"></i> 
                        <?= $page_title ?>
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
                    <?php endif; ?>

                    <form action="<?= $form_action ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="form-group">
                            <label for="event_key">Event Key *</label>
                            <?php if ($template): ?>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($template['event_key']) ?>" readonly>
                                <small class="text-muted">Event key tidak dapat diubah.</small>
                            <?php else: ?>
                                <select name="event_key" id="event_key" class="form-control" required>
                                    <option value="">-- Pilih Event --</option>
                                    <option value="booking_accepted">Booking Diterima</option>
                                    <option value="booking_processed">Booking Sedang Dikerjakan</option>
                                    <option value="booking_completed">Booking Selesai</option>
                                    <option value="booking_cancelled">Booking Dibatalkan</option>
                                    <option value="booking_rejected">Booking Ditolak</option>
                                    <option value="service_reminder">Pengingat Servis Berkala</option>
                                    <option value="emergency_alert">Alert Darurat</option>
                                    <option value="workshop_approved">Workshop Disetujui</option>
                                    <option value="workshop_rejected">Workshop Ditolak</option>
                                    <option value="password_reset">Reset Password</option>
                                    <option value="welcome_user">Selamat Datang</option>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="event_name">Nama Event *</label>
                            <input type="text" name="event_name" id="event_name" class="form-control" 
                                   value="<?= $template ? htmlspecialchars($template['event_name']) : '' ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="subject_template">Subject Template *</label>
                            <textarea name="subject_template" id="subject_template" class="form-control" rows="2" required><?= $template ? htmlspecialchars($template['subject_template']) : '' ?></textarea>
                            <small class="text-muted">Gunakan format {{nama_variabel}} untuk variabel dinamis (BR-84).</small>
                        </div>

                        <div class="form-group">
                            <label for="body_template">Body Template *</label>
                            <textarea name="body_template" id="body_template" class="form-control" rows="10" required><?= $template ? htmlspecialchars($template['body_template']) : '' ?></textarea>
                            <small class="text-muted">HTML didukung. Gunakan format {{nama_variabel}} untuk variabel dinamis.</small>
                        </div>

                        <!-- Available Variables Helper -->
                        <div class="card card-info mb-3">
                            <div class="card-header">
                                <h6 class="card-title"><i class="fas fa-info-circle"></i> Variabel yang Tersedia</h6>
                            </div>
                            <div class="card-body p-2">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Variabel</th>
                                            <th>Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($available_variables as $var => $desc): ?>
                                            <tr>
                                                <td><code>{{<?= $var ?>}}</code></td>
                                                <td><?= $desc ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="language">Bahasa</label>
                            <select name="language" id="language" class="form-control">
                                <option value="id" <?= (!$template || $template['language'] === 'id') ? 'selected' : '' ?>>Indonesia</option>
                                <option value="en" <?= (isset($template['language']) && $template['language'] === 'en') ? 'selected' : '' ?>>English</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" id="is_active" class="custom-control-input" 
                                       value="1" <?= (!$template || $template['is_active']) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="is_active">Aktif</label>
                            </div>
                            <small class="text-muted">Jika nonaktif, sistem akan menggunakan template default hardcoded (BR-85).</small>
                        </div>

                        <hr>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Template
                            </button>
                            <a href="<?= site_url('notification/templates') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-fill subject and body when event is selected (for new templates)
    $('#event_key').change(function() {
        var eventKey = $(this).val();
        if (eventKey && !<?= $template ? 'true' : 'false' ?>) {
            // Could implement auto-fill from server-side defaults
            console.log('Event selected:', eventKey);
        }
    });
});
</script>

<?php $this->load->view('layouts/admin_footer'); ?>
