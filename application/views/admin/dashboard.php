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
        .stat-card { border-radius: 10px; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .chart-container { position: relative; height: 300px; }
		
		.setting-card { min-height: 250px; }
        
		.sidebar { 
            min-height: 100vh; 
            background: #212529; 
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar a { 
            color: #adb5bd; 
            text-decoration: none; 
            padding: 10px 15px; 
            display: block; 
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active { 
            background: #343a40; 
            color: #fff; 
            border-left: 3px solid #0d6efd;
        }
        .sidebar-sub { 
            padding-left: 30px !important; 
            font-size: 0.9em;
        }
        .sidebar-dropdown .dropdown-toggle::after { display: none; }
		
		
    </style>
</head>
<body>
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
                        <h2><i class="fas fa-tachometer-alt"></i> <?php echo $page_title; ?></h2>
                    </div>
                </div>
        
        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Users</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['total_users']); ?></h3>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                        <small>Customers: <?php echo number_format($stats['total_customers']); ?></small><br>
                        <small>Workshop Owners: <?php echo number_format($stats['total_workshop_owners']); ?></small><br>
                        <small>Mechanics: <?php echo number_format($stats['total_mechanics']); ?></small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Bengkel</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['total_workshops']); ?></h3>
                            </div>
                            <i class="fas fa-wrench fa-3x opacity-50"></i>
                        </div>
                        <small>Verified: <?php echo number_format($stats['verified_workshops']); ?></small><br>
                        <small>Pending: <?php echo number_format($stats['pending_verification_workshops']); ?></small><br>
                        <small>Featured: <?php echo number_format($stats['featured_workshops']); ?></small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Booking Hari Ini</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['bookings_today']); ?></h3>
                            </div>
                            <i class="fas fa-calendar-day fa-3x opacity-50"></i>
                        </div>
                        <small>Pending: <?php echo $stats['bookings_by_status']['pending']; ?></small><br>
                        <small>Accepted: <?php echo $stats['bookings_by_status']['accepted']; ?></small><br>
                        <small>Completed: <?php echo $stats['bookings_by_status']['completed']; ?></small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Emergency Aktif</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['emergency_requests_active']); ?></h3>
                            </div>
                            <i class="fas fa-ambulance fa-3x opacity-50"></i>
                        </div>
                        <small>Last 24 hours</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Second Row Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-danger text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Review Pending Moderasi</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['reviews_pending_moderation']); ?></h3>
                            </div>
                            <i class="fas fa-flag fa-3x opacity-50"></i>
                        </div>
                        <small>Flagged (report >= 3): <?php echo number_format($stats['reviews_flagged']); ?></small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card stat-card bg-secondary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Revenue Hari Ini</h6>
                                <h3 class="mb-0">Rp <?php echo number_format($stats['revenue_today'], 0, ',', '.'); ?></h3>
                            </div>
                            <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                        </div>
                        <small>Dari booking completed</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card stat-card bg-dark text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Quick Actions</h6>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="<?php echo site_url('admin/users'); ?>" class="btn btn-sm btn-outline-light me-1"><i class="fas fa-users"></i> Users</a>
                            <a href="<?php echo site_url('admin/workshops'); ?>" class="btn btn-sm btn-outline-light me-1"><i class="fas fa-wrench"></i> Bengkel</a>
                            <a href="<?php echo site_url('admin/review_moderation'); ?>" class="btn btn-sm btn-outline-light me-1"><i class="fas fa-star"></i> Review</a>
                            <a href="<?php echo site_url('admin/settings'); ?>" class="btn btn-sm btn-outline-light"><i class="fas fa-cog"></i> Settings</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Trend Booking (7 Hari Terakhir)</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="bookingsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Registrasi Bengkel Baru (7 Hari Terakhir)</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="workshopsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Aktivitas Terbaru</h5>
                        <a href="<?php echo site_url('admin/activity_logs'); ?>" class="btn btn-sm btn-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Log aktivitas tersedia di halaman Activity Logs.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Bookings Trend Chart
        const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
        new Chart(bookingsCtx, {
            type: 'line',
            data: {
                labels: [<?php foreach($bookings_trend as $t): ?>'<?php echo $t['label']; ?>',<?php endforeach; ?>],
                datasets: [{
                    label: 'Jumlah Booking',
                    data: [<?php foreach($bookings_trend as $t): echo $t['count'] . ','; endforeach; ?>],
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
        
        // Workshops Trend Chart
        const workshopsCtx = document.getElementById('workshopsChart').getContext('2d');
        new Chart(workshopsCtx, {
            type: 'bar',
            data: {
                labels: [<?php foreach($workshop_trend as $t): ?>'<?php echo $t['label']; ?>',<?php endforeach; ?>],
                datasets: [{
                    label: 'Bengkel Baru',
                    data: [<?php foreach($workshop_trend as $t): echo $t['count'] . ','; endforeach; ?>],
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    </script>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>