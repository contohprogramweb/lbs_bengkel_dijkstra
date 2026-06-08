<?php
/**
 * Workshop Services Management View
 *
 * @var object $user Current user data
 * @var object $workshop Workshop data
 * @var array $services Workshop services
 * @var array $categories Service categories
 */
$this->load->view('layouts/workshop_layout', ['content_for_layout' => '']);
?>

<div class="row mb-4">
    <div class="col-12">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Service Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-plus-circle"></i> Tambah Layanan Baru</h6>
    </div>
    <div class="card-body">
        <?php if (validation_errors()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= validation_errors() ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= form_open('workshop/add_service') ?>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="service_name" class="form-label">Nama Layanan <span class="text-danger">*</span></label>
                <input type="text" name="service_name" id="service_name" class="form-control" 
                       value="<?= set_value('service_name') ?>" 
                       placeholder="Contoh: Ganti Oli, Servis Rem, dll" required maxlength="150">
                <small class="text-muted">Masukkan nama layanan dengan jelas</small>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="service_category" class="form-label">Kategori <span class="text-danger">*</span></label>
                <select name="service_category" id="service_category" class="form-select select2" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $key => $label): ?>
                        <option value="<?= $key ?>" <?= set_value('service_category') == $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea name="description" id="description" class="form-control" rows="3"
                      placeholder="Jelaskan layanan ini secara detail..."><?= set_value('description') ?></textarea>
            <small class="text-muted">Deskripsi singkat tentang layanan yang ditawarkan</small>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="price_min" class="form-label">Harga Minimum (Rp) <span class="text-danger">*</span></label>
                <input type="number" name="price_min" id="price_min" class="form-control" 
                       value="<?= set_value('price_min') ?>" 
                       placeholder="Contoh: 50000" required min="0">
                <small class="text-muted">Harga terendah untuk layanan ini</small>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="price_max" class="form-label">Harga Maximum (Rp)</label>
                <input type="number" name="price_max" id="price_max" class="form-control" 
                       value="<?= set_value('price_max') ?>" 
                       placeholder="Contoh: 100000" min="0">
                <small class="text-muted">Opsional. Kosongkan jika harga tetap</small>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="duration_minutes" class="form-label">Durasi (menit)</label>
                <input type="number" name="duration_minutes" id="duration_minutes" class="form-control" 
                       value="<?= set_value('duration_minutes', '60') ?>" 
                       placeholder="Contoh: 60" min="0">
                <small class="text-muted">Estimasi waktu pengerjaan</small>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="unit" class="form-label">Satuan Harga</label>
                <select name="unit" id="unit" class="form-select">
                    <option value="fixed" <?= set_value('unit') == 'fixed' ? 'selected' : '' ?>>Harga Tetap</option>
                    <option value="per_hour" <?= set_value('unit') == 'per_hour' ? 'selected' : '' ?>>Per Jam</option>
                    <option value="per_item" <?= set_value('unit') == 'per_item' ? 'selected' : '' ?>>Per Item</option>
                    <option value="per_service" <?= set_value('unit') == 'per_service' ? 'selected' : '' ?>>Per Servis</option>
                </select>
            </div>
            
            <div class="col-md-6 mb-3 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="is_available" id="is_available" class="form-check-input" 
                           value="1" <?= set_value('is_available', '1') == '1' ? 'checked' : '' ?>>
                    <label for="is_available" class="form-check-label fw-bold">
                        Layanan Tersedia
                    </label>
                    <br>
                    <small class="text-muted">Centang jika layanan ini aktif dan dapat dipesan</small>
                </div>
            </div>
        </div>

        <hr>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Layanan
            </button>
            <a href="<?= site_url('workshop/profile') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Profil
            </a>
        </div>

        <?= form_close() ?>
    </div>
</div>

<!-- Services List -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-tools"></i> Daftar Layanan (<?= count($services) ?>)</h6>
        
        <!-- Filter -->
        <div class="d-flex gap-2">
            <select id="filterCategory" class="form-select form-select-sm" style="width: 150px;">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= $key ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <select id="filterStatus" class="form-select form-select-sm" style="width: 120px;">
                <option value="">Semua Status</option>
                <option value="1">Aktif</option>
                <option value="0">Tidak Aktif</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($services)): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="servicesTable">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Nama Layanan</th>
                            <th width="12%">Kategori</th>
                            <th width="18%">Harga</th>
                            <th width="10%">Durasi</th>
                            <th width="8%">Satuan</th>
                            <th width="10%">Status</th>
                            <th width="12%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($services as $service): ?>
                            <tr data-category="<?= $service->service_category ?>" data-status="<?= $service->is_available ?>">
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($service->service_name) ?></strong>
                                    <?php if ($service->description): ?>
                                        <br><small class="text-muted"><?= substr(htmlspecialchars($service->description), 0, 60) ?><?= strlen($service->description) > 60 ? '...' : '' ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= isset($categories[$service->service_category]) ? $categories[$service->service_category] : ucfirst($service->service_category) ?></span>
                                </td>
                                <td>
                                    Rp <?= number_format($service->price_min, 0, ',', '.') ?>
                                    <?php if ($service->price_max && $service->price_max != $service->price_min): ?>
                                        <br><small class="text-muted">s/d Rp <?= number_format($service->price_max, 0, ',', '.') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= $service->duration_minutes ?> menit</td>
                                <td>
                                    <small class="text-muted">
                                        <?php
                                        $units = [
                                            'fixed' => 'Tetap',
                                            'per_hour' => 'Per Jam',
                                            'per_item' => 'Per Item',
                                            'per_service' => 'Per Servis'
                                        ];
                                        echo $units[$service->unit] ?? ucfirst($service->unit);
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($service->is_available): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editService(<?= $service->id ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-<?= $service->is_available ? 'warning' : 'success' ?>" 
                                                onclick="toggleServiceStatus(<?= $service->id ?>, <?= $service->is_available ?>)" 
                                                title="<?= $service->is_available ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                            <i class="fas fa-<?= $service->is_available ? 'pause' : 'play' ?>"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteService(<?= $service->id ?>)" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-tools fa-4x text-gray-300 mb-3"></i>
                <h5>Belum Ada Layanan</h5>
                <p class="text-muted">Anda belum menambahkan layanan apapun. Gunakan form di atas untuk menambah layanan pertama Anda.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Layanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editServiceForm">
                    <input type="hidden" id="editServiceId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editServiceName" class="form-label">Nama Layanan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editServiceName" required maxlength="150">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="editServiceCategory" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" id="editServiceCategory" required>
                                <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="editDescription" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="editPriceMin" class="form-label">Harga Minimum (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editPriceMin" required min="0">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="editPriceMax" class="form-label">Harga Maximum (Rp)</label>
                            <input type="number" class="form-control" id="editPriceMax" min="0">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="editDuration" class="form-label">Durasi (menit)</label>
                            <input type="number" class="form-control" id="editDuration" value="60" min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editUnit" class="form-label">Satuan Harga</label>
                            <select class="form-select" id="editUnit">
                                <option value="fixed">Harga Tetap</option>
                                <option value="per_hour">Per Jam</option>
                                <option value="per_item">Per Item</option>
                                <option value="per_service">Per Servis</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="editIsAvailable">
                                <label class="form-check-label fw-bold" for="editIsAvailable">Layanan Tersedia</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveServiceChanges()">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const filterCategory = document.getElementById('filterCategory');
    const filterStatus = document.getElementById('filterStatus');
    const tableRows = document.querySelectorAll('#servicesTable tbody tr');
    
    function filterTable() {
        const categoryValue = filterCategory.value;
        const statusValue = filterStatus.value;
        
        tableRows.forEach(row => {
            const rowCategory = row.dataset.category;
            const rowStatus = row.dataset.status;
            
            const matchCategory = !categoryValue || rowCategory === categoryValue;
            const matchStatus = !statusValue || rowStatus === statusValue;
            
            if (matchCategory && matchStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    filterCategory.addEventListener('change', filterTable);
    filterStatus.addEventListener('change', filterTable);
});

// Edit service function
function editService(serviceId) {
    $.ajax({
        url: '<?= site_url('workshop/edit_service/') ?>' + serviceId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response) {
                $('#editServiceId').val(response.id);
                $('#editServiceName').val(response.service_name);
                $('#editServiceCategory').val(response.service_category);
                $('#editDescription').val(response.description || '');
                $('#editPriceMin').val(response.price_min);
                $('#editPriceMax').val(response.price_max || response.price_min);
                $('#editDuration').val(response.duration_minutes || 60);
                $('#editUnit').val(response.unit || 'fixed');
                $('#editIsAvailable').prop('checked', response.is_available == 1);
                
                $('#editServiceModal').modal('show');
            }
        },
        error: function(xhr) {
            showError('Gagal mengambil data layanan');
        }
    });
}

// Save service changes
function saveServiceChanges() {
    const serviceId = $('#editServiceId').val();
    const formData = {
        service_name: $('#editServiceName').val(),
        service_category: $('#editServiceCategory').val(),
        description: $('#editDescription').val(),
        price_min: $('#editPriceMin').val(),
        price_max: $('#editPriceMax').val(),
        duration_minutes: $('#editDuration').val(),
        unit: $('#editUnit').val(),
        is_available: $('#editIsAvailable').is(':checked') ? 1 : 0
    };

    $.ajax({
        url: '<?= site_url('workshop/edit_service/') ?>' + serviceId,
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.message) {
                showSuccess(response.message);
                $('#editServiceModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Gagal menyimpan perubahan';
            showError(errorMsg);
        }
    });
}

// Toggle service status
function toggleServiceStatus(serviceId, currentStatus) {
    confirmAction('Apakah Anda yakin ingin mengubah status layanan ini?', function() {
        $.ajax({
            url: '<?= site_url('workshop/toggle_service/') ?>' + serviceId,
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.is_available !== undefined) {
                    showSuccess('Status layanan berhasil diubah');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Gagal mengubah status layanan';
                showError(errorMsg);
            }
        });
    });
}

// Delete service
function deleteService(serviceId) {
    confirmAction('Apakah Anda yakin ingin menghapus layanan ini? Data tidak dapat dikembalikan.', function() {
        $.ajax({
            url: '<?= site_url('workshop/delete_service/') ?>' + serviceId,
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.message) {
                    showSuccess(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Gagal menghapus layanan';
                showError(errorMsg);
            }
        });
    });
}
</script>
