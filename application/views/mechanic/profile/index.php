<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3"><i class="fas fa-user"></i> Profil Saya</h2>
        </div>
    </div>

    <div class="row">
        <!-- Profile Info -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar-circle-lg bg-primary text-white mx-auto">
                            <?php echo strtoupper(substr($mechanic['name'], 0, 1)); ?>
                        </div>
                    </div>
                    <h4><?php echo e($mechanic['name']); ?></h4>
                    <p class="text-muted mb-2"><?php echo e($mechanic['workshop_name']); ?></p>
                    <span class="badge bg-<?php echo $mechanic['is_available'] ? 'success' : 'secondary'; ?>">
                        <?php echo $mechanic['is_available'] ? 'Tersedia' : 'Tidak Tersedia'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Profil</h5>
                </div>
                <div class="card-body">
                    <form id="profileForm" onsubmit="updateProfile(event)">
                        <?php echo form_hidden('csrf_test_name', $this->security->get_csrf_hash()); ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" value="<?php echo e($mechanic['name']); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo e($mechanic['email']); ?>" disabled>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Telepon</label>
                                <input type="text" class="form-control" value="<?php echo e($mechanic['phone']); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pengalaman (Tahun)</label>
                                <input type="number" name="experience_years" class="form-control" value="<?php echo e($mechanic['experience_years']); ?>" min="0" max="50">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Spesialisasi</label>
                            <select name="specialization[]" class="form-select select2-multiple" multiple="multiple">
                                <?php 
                                $current_spec = json_decode($mechanic['specialization'], TRUE) ?? [];
                                $specs = ['mesin', 'kelistrikan', 'body', 'ban', 'oli', 'ac', 'transmisi', 'rem'];
                                foreach ($specs as $spec): 
                                ?>
                                    <option value="<?php echo $spec; ?>" <?php echo in_array($spec, $current_spec) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($spec); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Tahan Ctrl/Cmd untuk memilih beberapa</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sertifikasi</label>
                            <textarea name="certification" class="form-control" rows="3"><?php echo e($mechanic['certification']); ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleAvailability()">
                                <i class="fas fa-toggle-on"></i> Toggle Ketersediaan
                            </button>
                            <a href="<?php echo site_url('mechanic/change_password'); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-key"></i> Ubah Password
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('.select2-multiple').select2({
        placeholder: 'Pilih spesialisasi',
        allowClear: true
    });
});

function updateProfile(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    // Handle multiple select
    const specSelect = document.querySelector('select[name="specialization[]"]');
    data.specialization = Array.from(specSelect.selectedOptions).map(opt => opt.value);
    
    fetch('<?php echo site_url("mechanic/update_profile"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: result.message
            }).then(() => {
                if (result.redirect) window.location.href = result.redirect;
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: result.message
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan pada sistem'
        });
    });
}

function toggleAvailability() {
    fetch('<?php echo site_url("mechanic/toggle_availability"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
        },
        body: JSON.stringify({})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: result.message
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan pada sistem'
        });
    });
}
</script>

<style>
.avatar-circle-lg {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
}
</style>
