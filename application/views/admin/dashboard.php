<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row mb-4 page-header">
    <div class="col-12">
        <h2><i class="fas fa-tachometer-alt"></i> <?php echo $page_title; ?></h2>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-admin-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title opacity-75">Total Users</h6>
                        <h3 class="mb-0"><?php echo number_format($stats['total_users']); ?></h3>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">Customers: <?php echo number_format($stats['total_customers']); ?></small><br>
                <small class="opacity-75">Workshop Owners: <?php echo number_format($stats['total_workshop_owners']); ?></small><br>
                <small class="opacity-75">Mechanics: <?php echo number_format($stats['total_mechanics']); ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card bg-admin-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title opacity-75">Bengkel</h6>
                        <h3 class="mb-0"><?php echo number_format($stats['total_workshops']); ?></h3>
                    </div>
                    <i class="fas fa-wrench fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">Verified: <?php echo number_format($stats['verified_workshops']); ?></small><br>
                <small class="opacity-75">Pending: <?php echo number_format($stats['pending_verification_workshops']); ?></small><br>
                <small class="opacity-75">Featured: <?php echo number_format($stats['featured_workshops']); ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card bg-admin-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title opacity-75">Booking Hari Ini</h6>
                        <h3 class="mb-0"><?php echo number_format($stats['bookings_today']); ?></h3>
                    </div>
                    <i class="fas fa-calendar-day fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">Pending: <?php echo $stats['bookings_by_status']['pending']; ?></small><br>
                <small class="opacity-75">Accepted: <?php echo $stats['bookings_by_status']['accepted']; ?></small><br>
                <small class="opacity-75">Completed: <?php echo $stats['bookings_by_status']['completed']; ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card bg-admin-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title opacity-75">Emergency Aktif</h6>
                        <h3 class="mb-0"><?php echo number_format($stats['emergency_requests_active']); ?></h3>
                    </div>
                    <i class="fas fa-ambulance fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">Last 24 hours</small>
            </div>
        </div>
    </div>
</div>

<!-- Second Row Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card bg-admin-danger h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title opacity-75">Review Pending Moderasi</h6>
                        <h3 class="mb-0"><?php echo number_format($stats['reviews_pending_moderation']); ?></h3>
                    </div>
                    <i class="fas fa-flag fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">Flagged (report >= 3): <?php echo number_format($stats['reviews_flagged']); ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card bg-admin-secondary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title opacity-75">Revenue Hari Ini</h6>
                        <h3 class="mb-0">Rp <?php echo number_format($stats['revenue_today'], 0, ',', '.'); ?></h3>
                    </div>
                    <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                </div>
                <small class="opacity-75">Dari booking completed</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card bg-admin-dark h-100">
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
                <div class="chart-container" style="position: relative; height: 300px;">
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
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="workshopsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
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
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#333' } }, x: { grid: { color: '#333' } } }
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
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#333' } }, x: { grid: { color: '#333' } } }
        }
    });
});
</script>
