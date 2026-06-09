
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-history"></i> <?php echo $page_title; ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Log Aktivitas</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Log</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo site_url('admin/activity_logs'); ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Pengguna</label>
                    <input type="text" class="form-control" name="user_id" 
                           value="<?php echo htmlspecialchars($filters['user_id'] ?? ''); ?>" 
                           placeholder="ID Pengguna">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bengkel</label>
                    <input type="text" class="form-control" name="workshop_id" 
                           value="<?php echo htmlspecialchars($filters['workshop_id'] ?? ''); ?>" 
                           placeholder="ID Bengkel">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenis Aksi</label>
                    <select class="form-select" name="action_type">
                        <option value="">Semua</option>
                        <?php foreach($action_types as $type): ?>
                            <option value="<?php echo $type['action_type']; ?>" 
                                    <?php echo ($filters['action_type'] ?? '') === $type['action_type'] ? 'selected' : ''; ?>>
                                <?php echo $type['action_type']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" class="form-control" name="date_from" 
                           value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" class="form-control" name="date_to" 
                           value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-admin-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="<?php echo site_url('admin/activity_logs'); ?>" class="btn btn-admin-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> Log Aktivitas 
                <small class="text-muted">(Total: <?php echo number_format($total_logs); ?>)</small>
            </h5>
        </div>
        <div class="card-body">
            <?php if(empty($logs)): ?>
                <p class="text-muted text-center">Tidak ada log aktivitas yang ditemukan.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Aksi</th>
                                <th>Deskripsi</th>
                                <th>Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1 + ($filters['page'] * 50);
                            foreach($logs as $log): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <small><?php echo date('d M Y, H:i', strtotime($log->created_at)); ?></small>
                                </td>
                                <td>
                                    <?php if(!empty($log->user_name)): ?>
                                        <strong><?php echo htmlspecialchars($log->user_name); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($log->user_email ?? ''); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">System</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($log->action_type); ?></span>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($log->action_description); ?></small>
                                </td>
                                <td>
                                    <?php if($log->target_type === 'user' && !empty($log->target_id)): ?>
                                        <a href="<?php echo site_url('admin/view_user/'.$log->target_id); ?>" class="badge bg-primary">
                                            User #<?php echo $log->target_id; ?>
                                        </a>
                                    <?php elseif($log->target_type === 'workshop' && !empty($log->target_id)): ?>
                                        <a href="<?php echo site_url('admin/view_workshop/'.$log->target_id); ?>" class="badge bg-success">
                                            Workshop #<?php echo $log->target_id; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php 
                $currentPage = max(0, (int)($filters['page'] ?? 0));
                $totalPages = ceil($total_logs / 50);
                ?>
                <?php if($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $currentPage <= 0 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>">
                                <i class="fas fa-chevron-left"></i> Prev
                            </a>
                        </li>
                        
                        <?php for($i = max(0, $currentPage - 2); $i <= min($totalPages - 1, $currentPage + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i + 1; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $currentPage >= $totalPages - 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div><!-- /.card-body -->
    </div><!-- /.card -->
</div><!-- /.container-fluid -->

