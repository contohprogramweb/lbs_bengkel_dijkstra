
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-circle"></i> <?php echo $page_title; ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/road_graph'); ?>">Road Graph</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/road_graph/nodes'); ?>">Nodes</a></li>
                    <li class="breadcrumb-item active"><?php echo isset($node) ? 'Edit' : 'Tambah'; ?> Simpul</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card card-dark-theme">
        <div class="card-header">
            <h5 class="mb-0"><?php echo isset($node) ? 'Edit Simpul' : 'Tambah Simpul Baru'; ?></h5>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Nama Simpul <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control form-control-dark" required 
                           value="<?php echo isset($node) ? htmlspecialchars($node->name) : set_value('name'); ?>">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Latitude <span class="text-danger">*</span></label>
                        <input type="number" step="0.000001" name="latitude" class="form-control form-control-dark" required
                               value="<?php echo isset($node) ? $node->latitude : set_value('latitude'); ?>">
                        <small class="text-muted">Contoh: -6.200000</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Longitude <span class="text-danger">*</span></label>
                        <input type="number" step="0.000001" name="longitude" class="form-control form-control-dark" required
                               value="<?php echo isset($node) ? $node->longitude : set_value('longitude'); ?>">
                        <small class="text-muted">Contoh: 106.816666</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipe Simpul</label>
                    <select name="node_type" class="form-select form-select-dark">
                        <option value="intersection" <?php echo (isset($node) && $node->node_type == 'intersection') ? 'selected' : ''; ?>>Persimpangan</option>
                        <option value="landmark" <?php echo (isset($node) && $node->node_type == 'landmark') ? 'selected' : ''; ?>>Landmark</option>
                        <option value="custom" <?php echo (isset($node) && $node->node_type == 'custom') ? 'selected' : ''; ?>>Custom</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control form-control-dark" rows="3"><?php echo isset($node) ? htmlspecialchars($node->description) : ''; ?></textarea>
                </div>
                <?php if (isset($node)): ?>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input form-check-input-dark" id="is_active" value="1"
                           <?php echo $node->is_active ? 'checked' : ''; ?>>
                    <label class="form-check-label text-light" for="is_active">Aktif</label>
                </div>
                <?php endif; ?>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-dark"><i class="fas fa-save"></i> Simpan</button>
                    <a href="<?php echo site_url('admin/road_graph/nodes'); ?>" class="btn btn-secondary-dark"><i class="fas fa-times"></i> Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

