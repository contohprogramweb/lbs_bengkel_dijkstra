<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- Sidebar -->
<div class="col-md-2 sidebar p-0">
    <div class="py-3">
        <a href="<?php echo site_url('admin/dashboard'); ?>" class="<?php echo ($this->uri->segment(2) == 'dashboard') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="<?php echo site_url('admin/users'); ?>" class="<?php echo (in_array($this->uri->segment(2), ['users', 'view_user'])) ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Manajemen User
        </a>
        <a href="<?php echo site_url('admin/workshops'); ?>" class="<?php echo (in_array($this->uri->segment(2), ['workshops', 'view_workshop'])) ? 'active' : ''; ?>">
            <i class="fas fa-wrench"></i> Manajemen Bengkel
        </a>
        <a href="<?php echo site_url('admin/pending_verification'); ?>" class="<?php echo ($this->uri->segment(2) == 'pending_verification') ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i> Verifikasi Bengkel
        </a>
        <a href="<?php echo site_url('admin/review_moderation'); ?>" class="<?php echo ($this->uri->segment(2) == 'review_moderation') ? 'active' : ''; ?>">
            <i class="fas fa-star"></i> Moderasi Review
        </a>
        <a href="<?php echo site_url('admin/activity_logs'); ?>" class="<?php echo ($this->uri->segment(2) == 'activity_logs') ? 'active' : ''; ?>">
            <i class="fas fa-history"></i> Log Aktivitas
        </a>
        
        <!-- Road Graph Menu -->
        <div class="sidebar-dropdown">
            <a href="#" class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#roadGraphMenu">
                <i class="fas fa-project-diagram"></i> Road Graph <i class="fas fa-chevron-down float-end mt-1"></i>
            </a>
            <div class="collapse show" id="roadGraphMenu">
                <a href="<?php echo site_url('admin/road_graph'); ?>" class="sidebar-sub <?php echo ($this->uri->segment(3) == 'road_graph' || $this->uri->segment(2) == 'road_graph') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="<?php echo site_url('admin/road_graph/nodes'); ?>" class="sidebar-sub <?php echo ($this->uri->segment(3) == 'nodes') ? 'active' : ''; ?>">
                    <i class="fas fa-circle"></i> Nodes
                </a>
                <a href="<?php echo site_url('admin/road_graph/edges'); ?>" class="sidebar-sub <?php echo ($this->uri->segment(3) == 'edges') ? 'active' : ''; ?>">
                    <i class="fas fa-exchange-alt"></i> Edges
                </a>
            </div>
        </div>
        
        <!-- Notification Menu -->
        <div class="sidebar-dropdown">
            <a href="#" class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#notificationMenu">
                <i class="fas fa-bell"></i> Notifikasi <i class="fas fa-chevron-down float-end mt-1"></i>
            </a>
            <div class="collapse show" id="notificationMenu">
                <a href="<?php echo site_url('admin/notification/templates'); ?>" class="sidebar-sub <?php echo ($this->uri->segment(3) == 'templates' || $this->uri->segment(2) == 'notification') ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> Template
                </a>
            </div>
        </div>
        
        <!-- Report Menu -->
        <div class="sidebar-dropdown">
            <a href="#" class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#reportMenu">
                <i class="fas fa-chart-bar"></i> Laporan <i class="fas fa-chevron-down float-end mt-1"></i>
            </a>
            <div class="collapse show" id="reportMenu">
                <a href="<?php echo site_url('admin/report/global'); ?>" class="sidebar-sub <?php echo ($this->uri->segment(3) == 'global' || $this->uri->segment(2) == 'report') ? 'active' : ''; ?>">
                    <i class="fas fa-globe"></i> Global Report
                </a>
            </div>
        </div>
        
        <a href="<?php echo site_url('admin/settings'); ?>" class="<?php echo ($this->uri->segment(2) == 'settings') ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Pengaturan Sistem
        </a>
    </div>
</div>
