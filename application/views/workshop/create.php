<?php
/**
 * Workshop Create Profile View
 *
 * @var object $user Current user data
 * @var array $categories Service categories
 */
$this->load->view('layouts/workshop_layout', ['content_for_layout' => '']);
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-store"></i> Buat Profil Bengkel Baru</h6>
    </div>
    <div class="card-body">
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= validation_errors() ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= form_open('workshop/create', ['enctype' => 'multipart/form-data']) ?>
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <h6 class="mb-3 text-primary">Informasi Dasar</h6>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Bengkel <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" 
                                   value="<?= set_value('name') ?>" 
                                   placeholder="Contoh: Bengkel Maju Jaya" required>
                            <small class="text-muted">Masukkan nama bengkel Anda dengan jelas dan profesional</small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea name="description" id="description" class="form-control" rows="4"
                                      placeholder="Ceritakan tentang bengkel Anda, keahlian khusus, pengalaman, dll..."><?= set_value('description') ?></textarea>
                            <small class="text-muted">Maksimal 1000 karakter</small>
                        </div>

                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo Bengkel</label>
                            <input type="file" name="logo" id="logo" class="form-control" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG. Maksimal 2MB. Logo akan ditampilkan di profil bengkel Anda.</small>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <h6 class="mb-3 text-primary">Alamat Lokasi</h6>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="address" id="address" class="form-control" rows="3" required
                                      placeholder="Nama jalan, nomor, RT/RW, kelurahan"><?= set_value('address') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="province" class="form-label">Provinsi <span class="text-danger">*</span></label>
                                <select name="province" id="province" class="form-select select2" required>
                                    <option value="">-- Pilih Provinsi --</option>
                                    <option value="DKI Jakarta" <?= set_value('province') == 'DKI Jakarta' ? 'selected' : '' ?>>DKI Jakarta</option>
                                    <option value="Jawa Barat" <?= set_value('province') == 'Jawa Barat' ? 'selected' : '' ?>>Jawa Barat</option>
                                    <option value="Jawa Tengah" <?= set_value('province') == 'Jawa Tengah' ? 'selected' : '' ?>>Jawa Tengah</option>
                                    <option value="Jawa Timur" <?= set_value('province') == 'Jawa Timur' ? 'selected' : '' ?>>Jawa Timur</option>
                                    <option value="Banten" <?= set_value('province') == 'Banten' ? 'selected' : '' ?>>Banten</option>
                                    <option value="Yogyakarta" <?= set_value('province') == 'Yogyakarta' ? 'selected' : '' ?>>Yogyakarta</option>
                                    <option value="Sumatera Utara" <?= set_value('province') == 'Sumatera Utara' ? 'selected' : '' ?>>Sumatera Utara</option>
                                    <option value="Sumatera Barat" <?= set_value('province') == 'Sumatera Barat' ? 'selected' : '' ?>>Sumatera Barat</option>
                                    <option value="Sumatera Selatan" <?= set_value('province') == 'Sumatera Selatan' ? 'selected' : '' ?>>Sumatera Selatan</option>
                                    <option value="Bali" <?= set_value('province') == 'Bali' ? 'selected' : '' ?>>Bali</option>
                                    <option value="Sulawesi Selatan" <?= set_value('province') == 'Sulawesi Selatan' ? 'selected' : '' ?>>Sulawesi Selatan</option>
                                    <option value="Kalimantan Timur" <?= set_value('province') == 'Kalimantan Timur' ? 'selected' : '' ?>>Kalimantan Timur</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">Kota/Kabupaten <span class="text-danger">*</span></label>
                                <input type="text" name="city" id="city" class="form-control" 
                                       value="<?= set_value('city') ?>" 
                                       placeholder="Contoh: Jakarta Selatan" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="postal_code" class="form-label">Kode Pos</label>
                            <input type="text" name="postal_code" id="postal_code" class="form-control" 
                                   value="<?= set_value('postal_code') ?>" 
                                   placeholder="Contoh: 12345" maxlength="10">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Koordinat Lokasi</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="latitude" id="latitude" class="form-control" 
                                           value="<?= set_value('latitude', '-6.2088') ?>" 
                                           placeholder="Latitude" step="any">
                                    <small class="text-muted">Contoh: -6.2088</small>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="longitude" id="longitude" class="form-control" 
                                           value="<?= set_value('longitude', '106.8456') ?>" 
                                           placeholder="Longitude" step="any">
                                    <small class="text-muted">Contoh: 106.8456</small>
                                </div>
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle"></i> Koordinat akan otomatis diisi berdasarkan alamat, atau Anda dapat mengisinya manual</small>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <h6 class="mb-3 text-primary">Informasi Kontak</h6>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Telepon</label>
                                <input type="text" name="phone" id="phone" class="form-control" 
                                       value="<?= set_value('phone') ?>" 
                                       placeholder="Contoh: 021-1234567">
                                <small class="text-muted">Nomor telepon bengkel</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="whatsapp" class="form-label">WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" name="whatsapp" id="whatsapp" class="form-control" 
                                       value="<?= set_value('whatsapp') ?>" 
                                       placeholder="Contoh: 08123456789" required>
                                <small class="text-muted">Nomor WhatsApp untuk komunikasi dengan pelanggan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Operating Hours -->
                <h6 class="mb-3 text-primary">Jam Operasional</h6>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Senin</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="time" name="open_monday" class="form-control form-control-sm" value="08:00">
                                    <small class="text-muted">Buka</small>
                                </div>
                                <div class="col-6">
                                    <input type="time" name="close_monday" class="form-control form-control-sm" value="17:00">
                                    <small class="text-muted">Tutup</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Selasa</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="time" name="open_tuesday" class="form-control form-control-sm" value="08:00">
                                    <small class="text-muted">Buka</small>
                                </div>
                                <div class="col-6">
                                    <input type="time" name="close_tuesday" class="form-control form-control-sm" value="17:00">
                                    <small class="text-muted">Tutup</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Rabu</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="time" name="open_wednesday" class="form-control form-control-sm" value="08:00">
                                    <small class="text-muted">Buka</small>
                                </div>
                                <div class="col-6">
                                    <input type="time" name="close_wednesday" class="form-control form-control-sm" value="17:00">
                                    <small class="text-muted">Tutup</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kamis</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="time" name="open_thursday" class="form-control form-control-sm" value="08:00">
                                    <small class="text-muted">Buka</small>
                                </div>
                                <div class="col-6">
                                    <input type="time" name="close_thursday" class="form-control form-control-sm" value="17:00">
                                    <small class="text-muted">Tutup</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jumat</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="time" name="open_friday" class="form-control form-control-sm" value="08:00">
                                    <small class="text-muted">Buka</small>
                                </div>
                                <div class="col-6">
                                    <input type="time" name="close_friday" class="form-control form-control-sm" value="17:00">
                                    <small class="text-muted">Tutup</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sabtu</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="time" name="open_saturday" class="form-control form-control-sm" value="08:00">
                                    <small class="text-muted">Buka</small>
                                </div>
                                <div class="col-6">
                                    <input type="time" name="close_saturday" class="form-control form-control-sm" value="15:00">
                                    <small class="text-muted">Tutup</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Minggu</label>
                            <p class="text-muted small mb-0"><i class="fas fa-info-circle"></i> Default: Tutup</p>
                        </div>
                    </div>
                </div>

                <!-- Services Offered -->
                <h6 class="mb-3 text-primary">Layanan yang Ditawarkan</h6>
                <div class="card mb-4">
                    <div class="card-body">
                        <small class="text-muted mb-2 d-block">Pilih minimal satu layanan:</small>
                        <?php foreach ($categories as $key => $label): ?>
                            <div class="form-check mb-2">
                                <input type="checkbox" name="services_offered[]" id="service_<?= $key ?>" 
                                       class="form-check-input" value="<?= $key ?>" 
                                       <?= in_array($key, set_value('services_offered', [])) ? 'checked' : '' ?>>
                                <label for="service_<?= $key ?>" class="form-check-label"><?= $label ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Buat Profil Bengkel
                    </button>
                    <a href="<?= site_url('workshop/dashboard') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                </div>

                <!-- Info Box -->
                <div class="alert alert-info mt-3 mb-0">
                    <h6><i class="fas fa-info-circle"></i> Informasi</h6>
                    <p class="small mb-0">
                        Setelah membuat profil, bengkel Anda akan menunggu verifikasi dari admin. 
                        Proses verifikasi biasanya memakan waktu 1-2 hari kerja.
                    </p>
                </div>
            </div>
        </div>

        <?= form_close() ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation feedback
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });

    // Character counter for description
    const descriptionTextarea = document.getElementById('description');
    if (descriptionTextarea) {
        const maxLength = 1000;
        const counter = document.createElement('small');
        counter.className = 'text-muted float-end';
        descriptionTextarea.parentNode.appendChild(counter);
        
        descriptionTextarea.addEventListener('input', function() {
            const length = this.value.length;
            counter.textContent = length + ' / ' + maxLength + ' karakter';
            
            if (length > maxLength) {
                counter.classList.add('text-danger');
                this.value = this.value.substring(0, maxLength);
            } else {
                counter.classList.remove('text-danger');
            }
        });
    }
});
</script>
