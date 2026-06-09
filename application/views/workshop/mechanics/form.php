<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="mb-0"><?= $page_title; ?></h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Mekanik</h5>
                </div>
                <div class="card-body">
                    <?php echo form_open(current_url(), ['id' => 'mechanicForm']); ?>
                    
                    <?php if (empty($mechanic)): ?>
                        <!-- User selection for new mechanic -->
                        <div class="form-group">
                            <label for="user_id">Pilih User <span class="text-danger">*</span></label>
                            <select name="user_id" id="user_id" class="form-control select2" required>
                                <option value="">-- Pilih User --</option>
                                <?php 
                                // Load users that can be mechanics
                                $this->db->select('id, name, email, role');
                                $this->db->where('role', 'customer');
                                $users = $this->db->get('users')->result_array();
                                foreach ($users as $user): 
                                ?>
                                    <option value="<?= $user['id']; ?>">
                                        <?= esc($user['name']); ?> (<?= esc($user['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                User akan diubah role-nya menjadi mekanik
                            </small>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="user_id" value="<?= $mechanic['user_id']; ?>">
                        <div class="form-group">
                            <label>Nama Mekanik</label>
                            <input type="text" class="form-control" value="<?= esc($mechanic['name']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" value="<?= esc($mechanic['email']); ?>" readonly>
                        </div>
                    <?php endif; ?>

                    <!-- Specialization -->
                    <div class="form-group">
                        <label>Spesialisasi <span class="text-danger">*</span></label>
                        <div class="row">
                            <?php foreach ($specializations as $spec): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               name="specialization[]" 
                                               value="<?= $spec; ?>" 
                                               id="spec_<?= $spec; ?>"
                                               class="custom-control-input"
                                               <?php 
                                               if (!empty($mechanic)) {
                                                   $mech_specs = json_decode($mechanic['specialization'] ?? '[]', TRUE);
                                                   if (in_array($spec, $mech_specs)) echo 'checked';
                                               }
                                               ?>>
                                        <label class="custom-control-label" for="spec_<?= $spec; ?>">
                                            <?= ucfirst($spec); ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="form-text text-muted">Pilih minimal satu spesialisasi</small>
                    </div>

                    <!-- Experience Years -->
                    <div class="form-group">
                        <label for="experience_years">Pengalaman (Tahun)</label>
                        <input type="number" 
                               name="experience_years" 
                               id="experience_years" 
                               class="form-control" 
                               value="<?= $mechanic['experience_years'] ?? 0; ?>"
                               min="0" 
                               max="50">
                    </div>

                    <!-- Certification -->
                    <div class="form-group">
                        <label for="certification">Sertifikasi</label>
                        <textarea name="certification" 
                                  id="certification" 
                                  class="form-control" 
                                  rows="3"><?= esc($mechanic['certification'] ?? ''); ?></textarea>
                        <small class="form-text text-muted">Contoh: ASE Certified, Training Toyota, dll.</small>
                    </div>

                    <!-- Availability Status -->
                    <?php if (!empty($mechanic)): ?>
                    <div class="form-group">
                        <label>Status Ketersediaan</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" 
                                   name="is_available" 
                                   id="is_available" 
                                   class="custom-control-input"
                                   value="1"
                                   <?php if ($mechanic['is_available'] ?? 1): ?>checked<?php endif; ?>>
                            <label class="custom-control-label" for="is_available">
                                <?php if ($mechanic['is_available'] ?? 1): ?>
                                    Aktif - Tersedia untuk ditugaskan
                                <?php else: ?>
                                    Non-Aktif - Tidak tersedia
                                <?php endif; ?>
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <hr>
                    
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <a href="<?= site_url('mechanic'); ?>" class="btn btn-secondary ml-2">
                            Batal
                        </a>
                    </div>
                    
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Informasi</h6>
                </div>
                <div class="card-body">
                    <h6>Tips Menambahkan Mekanik:</h6>
                    <ul class="small">
                        <li>Pilih user yang akan menjadi mekanik</li>
                        <li>Tentukan spesialisasi sesuai keahlian</li>
                        <li>Masukkan pengalaman dan sertifikasi</li>
                        <li>Status aktif/non-aktif dapat diubah nanti</li>
                    </ul>
                    
                    <hr>
                    
                    <h6>Spesialisasi:</h6>
                    <ul class="small">
                        <li><strong>Mesin:</strong> Perbaikan mesin, tune-up</li>
                        <li><strong>Kelistrikan:</strong> Sistem elektrik, battery</li>
                        <li><strong>Body:</strong> Body repair, painting</li>
                        <li><strong>Ban:</strong> Wheel alignment, balancing</li>
                        <li><strong>Oli:</strong> Oil change, lubrication</li>
                        <li><strong>AC:</strong> Air conditioning service</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2
    if ($.fn.select2) {
        $('#user_id').select2({
            placeholder: 'Cari user...',
            allowClear: true
        });
    }

    // Form validation and submit
    $('#mechanicForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        // Check if at least one specialization is selected
        if ($('input[name="specialization[]"]:checked').length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Pilih minimal satu spesialisasi',
                timer: 2000,
                showConfirmButton: false
            });
            return false;
        }
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success || response.redirect) {
                    window.location.href = response.redirect || '<?= site_url('mechanic'); ?>';
                } else {
                    Swal.fire('Error!', response.message || 'Gagal menyimpan data', 'error');
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'Terjadi kesalahan';
                Swal.fire('Error!', msg, 'error');
            }
        });
        
        return false;
    });

    // Toggle switch label update
    $('#is_available').on('change', function() {
        if ($(this).is(':checked')) {
            $(this).next('label').html('Aktif - Tersedia untuk ditugaskan');
        } else {
            $(this).next('label').html('Non-Aktif - Tidak tersedia');
        }
    });
});
</script>
