<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .setting-card { min-height: 250px; }
        .sidebar { min-height: calc(100vh - 56px); background-color: #343a40; }
        .sidebar a { color: #fff; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #495057; }
        .sidebar i { width: 25px; }
        .form-switch input:checked + label::after { background-color: #0d6efd; }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand"><i class="fas fa-shield-alt"></i> Admin Panel</span>
            <div class="d-flex align-items-center">
                <span class="text-light me-3"><?php echo $user->full_name; ?></span>
                <a href="<?php echo site_url('auth/logout'); ?>" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <?php $this->load->view('admin/_sidebar'); ?>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2><i class="fas fa-cog"></i> <?php echo $page_title; ?></h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Pengaturan Sistem</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <?php if($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo site_url('admin/settings'); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="row">
                        <?php 
                        $categories = [
                            'general' => ['icon' => 'fa-globe', 'title' => 'Umum'],
                            'booking' => ['icon' => 'fa-calendar', 'title' => 'Booking'],
                            'emergency' => ['icon' => 'fa-ambulance', 'title' => 'Emergency'],
                            'notification' => ['icon' => 'fa-bell', 'title' => 'Notifikasi'],
                            'review' => ['icon' => 'fa-star', 'title' => 'Review'],
                            'billing' => ['icon' => 'fa-file-invoice-dollar', 'title' => 'Billing'],
                            'system' => ['icon' => 'fa-server', 'title' => 'Sistem']
                        ];
                        
                        foreach($categories as $cat_key => $cat): 
                            $cat_settings = array_filter($settings, function($s) use ($cat_key) {
                                return $s->category === $cat_key || empty($s->category);
                            });
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="card setting-card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas <?php echo $cat['icon']; ?>"></i> <?php echo $cat['title']; ?></h5>
                                </div>
                                <div class="card-body">
                                    <?php if(empty($cat_settings)): ?>
                                        <p class="text-muted mb-0">Tidak ada pengaturan untuk kategori ini.</p>
                                    <?php else: ?>
                                        <?php foreach($cat_settings as $setting): ?>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $setting->setting_key))); ?>
                                                    <?php if(!empty($setting->description)): ?>
                                                        <small class="text-muted d-block fw-normal"><?php echo htmlspecialchars($setting->description); ?></small>
                                                    <?php endif; ?>
                                                </label>
                                                
                                                <?php 
                                                $value = $setting->setting_value;
                                                $type = $setting->setting_type ?? 'string';
                                                ?>
                                                
                                                <?php if($type === 'boolean'): ?>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="setting_<?php echo $setting->setting_key; ?>"
                                                               name="settings[<?php echo $setting->setting_key; ?>]" 
                                                               value="1" 
                                                               <?php echo $value ? 'checked' : ''; ?>
                                                               onchange="this.nextElementSibling.textContent = this.checked ? 'Aktif' : 'Nonaktif'">
                                                        <label class="form-check-label" for="setting_<?php echo $setting->setting_key; ?>">
                                                            <?php echo $value ? 'Aktif' : 'Nonaktif'; ?>
                                                        </label>
                                                    </div>
                                                    <input type="hidden" name="types[<?php echo $setting->setting_key; ?>]" value="boolean">
                                                <?php elseif($type === 'integer' || $type === 'float' || $type === 'decimal'): ?>
                                                    <input type="number" 
                                                           step="<?php echo ($type === 'float' || $type === 'decimal') ? '0.01' : '1'; ?>"
                                                           class="form-control" 
                                                           name="settings[<?php echo $setting->setting_key; ?>]" 
                                                           value="<?php echo htmlspecialchars($value); ?>"
                                                           required>
                                                    <input type="hidden" name="types[<?php echo $setting->setting_key; ?>]" value="<?php echo $type; ?>">
                                                <?php elseif($type === 'json'): ?>
                                                    <textarea class="form-control font-monospace" 
                                                              name="settings[<?php echo $setting->setting_key; ?>]" 
                                                              rows="4"
                                                              required><?php echo htmlspecialchars($value); ?></textarea>
                                                    <small class="text-muted">Format: JSON</small>
                                                    <input type="hidden" name="types[<?php echo $setting->setting_key; ?>]" value="json">
                                                <?php elseif($type === 'text'): ?>
                                                    <textarea class="form-control" 
                                                              name="settings[<?php echo $setting->setting_key; ?>]" 
                                                              rows="3"
                                                              required><?php echo htmlspecialchars($value); ?></textarea>
                                                    <input type="hidden" name="types[<?php echo $setting->setting_key; ?>]" value="text">
                                                <?php else: ?>
                                                    <input type="text" class="form-control" 
                                                           name="settings[<?php echo $setting->setting_key; ?>]" 
                                                           value="<?php echo htmlspecialchars($value); ?>"
                                                           required>
                                                    <input type="hidden" name="types[<?php echo $setting->setting_key; ?>]" value="string">
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Simpan Pengaturan
                            </button>
                            <a href="<?php echo site_url('admin/dashboard'); ?>" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
</body>
</html>