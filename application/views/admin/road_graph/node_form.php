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
                <label class="form-label">Nama Simpul *</label>
                <input type="text" name="name" class="form-control" required 
                       value="<?php echo isset($node) ? htmlspecialchars($node->name) : ''; ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Latitude *</label>
                    <input type="number" step="0.000001" name="latitude" class="form-control" required
                           value="<?php echo isset($node) ? $node->latitude : ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Longitude *</label>
                    <input type="number" step="0.000001" name="longitude" class="form-control" required
                           value="<?php echo isset($node) ? $node->longitude : ''; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipe Simpul</label>
                <select name="node_type" class="form-select">
                    <option value="intersection" <?php echo (isset($node) && $node->node_type == 'intersection') ? 'selected' : ''; ?>>Persimpangan</option>
                    <option value="landmark" <?php echo (isset($node) && $node->node_type == 'landmark') ? 'selected' : ''; ?>>Landmark</option>
                    <option value="custom" <?php echo (isset($node) && $node->node_type == 'custom') ? 'selected' : ''; ?>>Custom</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"><?php echo isset($node) ? htmlspecialchars($node->description) : ''; ?></textarea>
            </div>
            <?php if (isset($node)): ?>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1"
                       <?php echo $node->is_active ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_active">Aktif</label>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?php echo site_url('admin/road_graph/nodes'); ?>" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>
