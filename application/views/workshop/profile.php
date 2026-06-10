<?php
/**
 * Workshop Profile View
 *
 * @var object $user Current user data
 * @var object|null $workshop Workshop data
 * @var array $services Workshop services
 */
 
?>

<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle"></i> <?= $this->session->flashdata('info') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($workshop): ?>
    <!-- Workshop Profile Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-store"></i> Profil Bengkel</h6>
            <div>
                <span class="badge badge-<?= $workshop->status === 'active' ? 'success' : ($workshop->status === 'pending' ? 'warning' : 'danger') ?> me-2">
                    <?= ucfirst($workshop->status) ?>
                </span>
                <a href="<?= site_url('workshop/edit') ?>" class="btn btn-sm btn-primary-primary">
                    <i class="fas fa-edit"></i> Edit Profil
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Left Column - Basic Info -->
                <div class="col-lg-8">
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <?php if ($workshop->logo): ?>
                                <img src="<?= base_url($workshop->logo) ?>" alt="Logo Bengkel" class="img-fluid rounded">
                            <?php else: ?>
                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 120px; height: 120px; font-size: 2.5rem;">
                                    <?= strtoupper(substr($workshop->name, 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <h4><?= htmlspecialchars($workshop->name) ?></h4>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($workshop->description)) ?></p>

                            <div class="mt-3">
                                <p class="mb-2"><i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    <strong>Alamat:</strong><br>
                                    <?= nl2br(htmlspecialchars($workshop->address)) ?><br>
                                    <?= htmlspecialchars($workshop->city) ?>, <?= htmlspecialchars($workshop->province) ?> <?= htmlspecialchars($workshop->postal_code) ?>
                                </p>

                                <p class="mb-2"><i class="fas fa-phone text-primary me-2"></i>
                                    <strong>Telepon:</strong> <?= htmlspecialchars($workshop->phone ?? '-') ?>
                                </p>

                                <p class="mb-2"><i class="fab fa-whatsapp text-success me-2"></i>
                                    <strong>WhatsApp:</strong>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $workshop->whatsapp) ?>" target="_blank" class="text-decoration-none">
                                        <?= htmlspecialchars($workshop->whatsapp) ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Operating Hours -->
                    <h6 class="border-bottom pb-2 mb-3"><i class="fas fa-clock text-primary"></i> Jam Operasional</h6>
                    <?php
                    $operating_hours = json_decode($workshop->operating_hours, TRUE) ?? [];
                    $days = [
                        'monday' => 'Senin',
                        'tuesday' => 'Selasa',
                        'wednesday' => 'Rabu',
                        'thursday' => 'Kamis',
                        'friday' => 'Jumat',
                        'saturday' => 'Sabtu',
                        'sunday' => 'Minggu'
                    ];
                    ?>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <?php foreach (array_slice($days, 0, 4) as $key => $label):
                                    $hours = $operating_hours[$key] ?? null;
                                ?>
                                    <tr>
                                        <td width="40%"><strong><?= $label ?></strong></td>
                                        <td>
                                            <?php if ($hours): ?>
                                                <?= $hours['open'] ?> - <?= $hours['close'] ?>
                                            <?php else: ?>
                                                <span class="text-muted">Tutup</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <?php foreach (array_slice($days, 4) as $key => $label):
                                    $hours = $operating_hours[$key] ?? null;
                                ?>
                                    <tr>
                                        <td width="40%"><strong><?= $label ?></strong></td>
                                        <td>
                                            <?php if ($hours): ?>
                                                <?= $hours['open'] ?> - <?= $hours['close'] ?>
                                            <?php else: ?>
                                                <span class="text-muted">Tutup</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Stats & Services -->
                <div class="col-lg-4">
                    <!-- Statistics -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-chart-line"></i> Statistik</h6>
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="display-6 text-primary"><?= count($services) ?></div>
                                    <small class="text-muted">Total Layanan</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="display-6 text-warning">
                                        <i class="fas fa-star"></i> <?= number_format($workshop->rating_avg ?? 0, 1) ?>
                                    </div>
                                    <small class="text-muted">Rating (<?= $workshop->total_reviews ?? 0 ?> review)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="d-grid gap-2">
                        <a href="<?= site_url('workshop/services') ?>" class="btn btn-outline-primary-primary">
                            <i class="fas fa-tools"></i> Kelola Layanan
                        </a>
                        <a href="<?= site_url('workshop/schedule') ?>" class="btn btn-outline-primary-primary">
                            <i class="fas fa-calendar-alt"></i> Jadwal Booking
                        </a>
                        <a href="<?= site_url('workshop/dashboard') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-tools"></i> Layanan Tersedia (<?= count($services) ?>)</h6>
            <a href="<?= site_url('workshop/services') ?>" class="btn btn-sm btn-primary-primary">
                <i class="fas fa-plus"></i> Tambah Layanan Baru
            </a>
        </div>
        <div class="card-body">
            <?php if (!empty($services)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Nama Layanan</th>
                                <th width="15%">Kategori</th>
                                <th width="20%">Harga</th>
                                <th width="15%">Durasi</th>
                                <th width="10%">Status</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($services as $service): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($service->service_name) ?></strong>
                                        <?php if ($service->description): ?>
                                            <br><small class="text-muted"><?= substr(htmlspecialchars($service->description), 0, 50) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= isset($categories[$service->service_category]) ? $categories[$service->service_category] : ucfirst($service->service_category) ?></span>
                                    </td>
                                    <td>
                                        Rp <?= number_format($service->price_min, 0, ',', '.') ?>
                                        <?php if ($service->price_max && $service->price_max != $service->price_min): ?>
                                            - Rp <?= number_format($service->price_max, 0, ',', '.') ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $service->duration_minutes ?> menit</td>
                                    <td>
                                        <?php if ($service->is_available): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary-primary" onclick="editService(<?= $service->id ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                    <h5>Belum Ada Layanan</h5>
                    <p class="text-muted">Anda belum menambahkan layanan apapun. Mulai tambahkan layanan untuk menarik pelanggan.</p>
                    <a href="<?= site_url('workshop/services') ?>" class="btn btn-primary-primary">
                        <i class="fas fa-plus"></i> Tambah Layanan Pertama
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- No Workshop -->
    <div class="card shadow mb-4">
        <div class="card-body text-center py-5">
            <i class="fas fa-store fa-4x text-muted mb-3"></i>
            <h4>Anda Belum Memiliki Profil Bengkel</h4>
            <p class="text-muted mb-4">Buat profil bengkel Anda untuk mulai menerima booking dari pelanggan dan mengelola layanan.</p>
            <a href="<?= site_url('workshop/create') ?>" class="btn btn-primary-primary btn-lg">
                <i class="fas fa-plus"></i> Buat Profil Bengkel Sekarang
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Layanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editServiceForm">
                    <input type="hidden" id="serviceId">
                    <div class="mb-3">
                        <label for="editServiceName" class="form-label">Nama Layanan</label>
                        <input type="text" class="form-control" id="editServiceName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editServiceCategory" class="form-label">Kategori</label>
                        <select class="form-select" id="editServiceCategory" required>
                            <?php foreach ($categories as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="editDescription" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editPriceMin" class="form-label">Harga Minimum</label>
                            <input type="number" class="form-control" id="editPriceMin" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editPriceMax" class="form-label">Harga Maximum</label>
                            <input type="number" class="form-control" id="editPriceMax">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editDuration" class="form-label">Durasi (menit)</label>
                        <input type="number" class="form-control" id="editDuration" value="60">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="editIsAvailable">
                        <label class="form-check-label" for="editIsAvailable">Layanan Tersedia</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary-primary" onclick="saveServiceChanges()">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<script>
// Edit service function
function editService(serviceId) {
    // Fetch service data via AJAX
    $.ajax({
        url: '<?= site_url('workshop/edit_service/') ?>' + serviceId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response) {
                $('#serviceId').val(response.id);
                $('#editServiceName').val(response.service_name);
                $('#editServiceCategory').val(response.service_category);
                $('#editDescription').val(response.description || '');
                $('#editPriceMin').val(response.price_min);
                $('#editPriceMax').val(response.price_max || response.price_min);
                $('#editDuration').val(response.duration_minutes || 60);
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
    const serviceId = $('#serviceId').val();
    const formData = {
        service_name: $('#editServiceName').val(),
        service_category: $('#editServiceCategory').val(),
        description: $('#editDescription').val(),
        price_min: $('#editPriceMin').val(),
        price_max: $('#editPriceMax').val(),
        duration_minutes: $('#editDuration').val(),
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
</script>