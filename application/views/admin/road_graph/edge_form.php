<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h2 class="mb-4"><?php echo $page_title; ?></h2>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
        <?php endif; ?>

        <form method="post" class="card p-4">
            <div class="mb-3">
                <label class="form-label">Nama Jalan</label>
                <input type="text" name="road_name" class="form-control" 
                       value="<?php echo isset($edge) ? htmlspecialchars($edge->road_name) : ''; ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Dari Simpul *</label>
                    <select name="from_node_id" class="form-select" required>
                        <option value="">-- Pilih Simpul --</option>
                        <?php foreach ($nodes as $node): ?>
                            <option value="<?php echo $node->id; ?>" 
                                <?php echo (isset($edge) && $edge->from_node_id == $node->id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($node->name); ?> (<?php echo number_format($node->latitude, 4); ?>, <?php echo number_format($node->longitude, 4); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ke Simpul *</label>
                    <select name="to_node_id" class="form-select" required>
                        <option value="">-- Pilih Simpul --</option>
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
                <label class="form-label">Jarak (km) *</label>
                <input type="number" step="0.01" name="distance_km" class="form-control" required
                       value="<?php echo isset($edge) ? $edge->distance_km : ''; ?>">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_bidirectional" class="form-check-input" id="is_bidirectional" value="1"
                       <?php echo (isset($edge) && $edge->is_bidirectional) ? 'checked' : 'checked'; ?>>
                <label class="form-check-label" for="is_bidirectional">Dua Arah (Bidirectional)</label>
            </div>
            <?php if (isset($edge)): ?>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1"
                       <?php echo $edge->is_active ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_active">Aktif</label>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?php echo site_url('admin/road_graph/edges'); ?>" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>
