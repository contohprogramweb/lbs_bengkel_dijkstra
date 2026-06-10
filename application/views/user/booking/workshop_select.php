<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style>
    .workshop-list-container {
        padding: 20px;
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
    
    .workshop-grid {
        display: grid;
        gap: 15px;
    }
    
    .workshop-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .workshop-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    
    .workshop-card-img {
        height: 150px;
        width: 100%;
        object-fit: cover;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .workshop-card-body {
        padding: 15px;
    }
    
    .workshop-card-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }
    
    .workshop-card-address {
        font-size: 13px;
        color: #666;
        margin-bottom: 10px;
        display: flex;
        align-items: flex-start;
        gap: 5px;
    }
    
    .workshop-card-info {
        font-size: 12px;
        color: #888;
        margin-bottom: 8px;
    }
    
    .workshop-card-rating {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 12px;
    }
    
    .workshop-card-rating .stars {
        color: #fbbf24;
    }
    
    .workshop-card-rating .rating-text {
        font-size: 12px;
        color: #666;
    }
    
    .btn-select-workshop {
        display: block;
        width: 100%;
        padding: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 500;
        transition: opacity 0.2s;
        border: none;
        cursor: pointer;
    }
    
    .btn-select-workshop:hover {
        opacity: 0.9;
    }
    
    .alert-warning {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        color: #92400e;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
    }
    
    .page-header {
        padding: 20px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .page-header h2 {
        font-size: 18px;
        color: #333;
        margin-bottom: 5px;
    }
    
    .page-header p {
        font-size: 13px;
        color: #666;
    }
</style>

<div class="page-header">
    <h2><?= $page_title ?></h2>
    <p>Pilih bengkel yang ingin Anda kunjungi untuk melakukan servis kendaraan.</p>
</div>

<div class="workshop-list-container">
    <?php if (empty($workshops)): ?>
        <div class="alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Belum ada bengkel tersedia saat ini.
        </div>
    <?php else: ?>
        <div class="workshop-grid">
            <?php foreach ($workshops as $workshop): ?>
                <div class="workshop-card">
                    <?php if (!empty($workshop['image'])): ?>
                        <img src="<?= base_url('uploads/workshops/' . $workshop['image']) ?>" 
                             class="workshop-card-img" 
                             alt="<?= $workshop['name'] ?>">
                    <?php else: ?>
                        <div class="workshop-card-img">
                            <i class="fas fa-wrench text-white fa-3x" style="color: #ccc;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="workshop-card-body">
                        <h5 class="workshop-card-title"><?= $workshop['name'] ?></h5>
                        
                        <div class="workshop-card-address">
                            <i class="fas fa-map-marker-alt" style="margin-top: 2px;"></i>
                            <span><?= $workshop['address'] ?></span>
                        </div>
                        
                        <div class="workshop-card-info">
                            <div><i class="fas fa-phone"></i> <?= $workshop['phone'] ?></div>
                            <div><i class="fas fa-clock"></i> <?= $workshop['operating_hours'] ?? 'Buka 24 Jam' ?></div>
                        </div>
                        
                        <?php if (!empty($workshop['rating'])): ?>
                            <div class="workshop-card-rating">
                                <span class="stars">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star<?= $i < floor($workshop['rating']) ? '' : '-o' ?>"></i>
                                    <?php endfor; ?>
                                </span>
                                <span class="rating-text">(<?= $workshop['rating'] ?>)</span>
                            </div>
                        <?php endif; ?>
                        
                        <a href="<?= site_url('booking/step1/' . $workshop['id']) ?>" class="btn-select-workshop">
                            <i class="fas fa-calendar-plus"></i> Pilih Bengkel Ini
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    // Auto-click first workshop if only one available
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.workshop-card');
        if (cards.length === 1) {
            // Optionally auto-focus on single workshop
            console.log('Only one workshop available');
        }
    });
</script>