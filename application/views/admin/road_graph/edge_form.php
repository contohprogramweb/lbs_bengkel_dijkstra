
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-exchange-alt"></i> <?php echo $page_title; ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/road_graph'); ?>">Road Graph</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/road_graph/edges'); ?>">Edges</a></li>
                    <li class="breadcrumb-item active"><?php echo isset($edge) ? 'Edit' : 'Tambah'; ?> Edge</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card card-dark-theme">
        <div class="card-header">
            <h5 class="mb-0"><?php echo isset($edge) ? 'Edit Edge' : 'Tambah Edge Baru'; ?></h5>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Nama Jalan</label>
                    <input type="text" name="road_name" class="form-control form-control-dark" 
                           value="<?php echo isset($edge) ? htmlspecialchars($edge->road_name) : set_value('road_name'); ?>"
                           placeholder="Contoh: Jl. Sudirman">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Dari Simpul <span class="text-danger">*</span></label>
                        <select name="from_node_id" class="form-select form-select-dark" required>
                            <option value="">-- Pilih Simpul Asal --</option>
                            <?php foreach ($nodes as $node): ?>
                                <option value="<?php echo $node->id; ?>" 
                                    <?php echo (isset($edge) && $edge->from_node_id == $node->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($node->name); ?> (<?php echo number_format($node->latitude, 4); ?>, <?php echo number_format($node->longitude, 4); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ke Simpul <span class="text-danger">*</span></label>
                        <select name="to_node_id" class="form-select form-select-dark" required>
                            <option value="">-- Pilih Simpul Tujuan --</option>
                            <?php foreach ($nodes as $node): ?>
                                <option value="<?php echo $node->id; ?>" 
                                    <?php echo (isset($edge) && $edge->to_node_id == $node->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($node->name); ?> (<?php echo number_format($node->latitude, 4); ?>, <?php echo number_format($node->longitude, 4); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jarak (km) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="distance_km" class="form-control form-control-dark" required
                           value="<?php echo isset($edge) ? $edge->distance_km : set_value('distance_km'); ?>"
                           placeholder="Contoh: 1.50">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_bidirectional" class="form-check-input form-check-input-dark" id="is_bidirectional" value="1"
                           <?php echo (!isset($edge) || $edge->is_bidirectional) ? 'checked' : ''; ?>>
                    <label class="form-check-label text-light" for="is_bidirectional">Dua Arah (Bidirectional)</label>
                    <small class="d-block text-muted">Centang jika jalan bisa dilalui dari dua arah</small>
                </div>
                <?php if (isset($edge)): ?>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input form-check-input-dark" id="is_active" value="1"
                           <?php echo $edge->is_active ? 'checked' : ''; ?>>
                    <label class="form-check-label text-light" for="is_active">Aktif</label>
                </div>
                <?php endif; ?>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-dark"><i class="fas fa-save"></i> Simpan</button>
                    <a href="<?php echo site_url('admin/road_graph/edges'); ?>" class="btn btn-secondary-dark"><i class="fas fa-times"></i> Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

