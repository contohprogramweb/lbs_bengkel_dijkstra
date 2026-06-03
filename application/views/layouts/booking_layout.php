<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= $app_name ?></title>
    
    <!-- Leaflet.js CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .booking-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 400px;
            background: #fff;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        
        .step {
            display: flex;
            align-items: center;
            font-size: 12px;
        }
        
        .step-number {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 5px;
            font-weight: bold;
        }
        
        .step.active .step-number {
            background: #fff;
            color: #667eea;
        }
        
        .step.completed .step-number {
            background: #4ade80;
            color: white;
        }
        
        /* Map Container */
        .map-wrapper {
            flex: 1;
            position: relative;
        }
        
        #map {
            height: 100vh;
            width: 100%;
        }
        
        /* Form Styles */
        .form-section {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        /* Calendar Styles */
        .calendar-container {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .calendar-nav {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        
        .calendar-day-header {
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            padding: 5px;
        }
        
        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        
        .calendar-day:hover:not(.disabled) {
            background: #f0f0f0;
        }
        
        .calendar-day.available {
            background: #d1fae5;
            color: #065f46;
        }
        
        .calendar-day.available:hover {
            background: #a7f3d0;
        }
        
        .calendar-day.selected {
            background: #667eea !important;
            color: white !important;
        }
        
        .calendar-day.full {
            background: #e5e7eb;
            color: #9ca3af;
            cursor: not-allowed;
        }
        
        .calendar-day.holiday {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .calendar-day.has-slots::after {
            content: '';
            position: absolute;
            bottom: 3px;
            width: 6px;
            height: 6px;
            background: #4ade80;
            border-radius: 50%;
        }
        
        /* Slot Selection */
        .slots-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .slot-btn {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        
        .slot-btn:hover {
            border-color: #667eea;
            background: #f5f3ff;
        }
        
        .slot-btn.selected {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }
        
        .slot-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f5f5f5;
        }
        
        .slot-remaining {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }
        
        /* Service Selection */
        .service-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .service-item:hover {
            border-color: #667eea;
        }
        
        .service-item.selected {
            border-color: #667eea;
            background: #f5f3ff;
        }
        
        .service-checkbox {
            margin-right: 12px;
            width: 20px;
            height: 20px;
        }
        
        .service-info {
            flex: 1;
        }
        
        .service-name {
            font-weight: 600;
            color: #333;
        }
        
        .service-price {
            color: #667eea;
            font-weight: 600;
        }
        
        /* Buttons */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-block {
            width: 100%;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        /* Alert Messages */
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        
        /* Confirmation Box */
        .confirmation-box {
            background: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .confirmation-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .confirmation-row:last-child {
            border-bottom: none;
        }
        
        .confirmation-label {
            color: #666;
            font-size: 13px;
        }
        
        .confirmation-value {
            font-weight: 600;
            color: #333;
        }
        
        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .booking-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                max-height: 50vh;
            }
            
            #map {
                height: 50vh;
            }
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><?= $page_title ?></h3>
                <div class="progress-steps">
                    <div class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">
                        <div class="step-number">1</div>
                        <span>Kendaraan</span>
                    </div>
                    <div class="step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">
                        <div class="step-number">2</div>
                        <span>Jadwal</span>
                    </div>
                    <div class="step <?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'completed' : '' ?>">
                        <div class="step-number">3</div>
                        <span>Layanan</span>
                    </div>
                    <div class="step <?= $step >= 4 ? 'active' : '' ?>">
                        <div class="step-number">4</div>
                        <span>Konfirmasi</span>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success">
                        <?= $this->session->flashdata('success') ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-error">
                        <?= $this->session->flashdata('error') ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($this->session->flashdata('info')): ?>
                    <div class="alert alert-info">
                        <?= $this->session->flashdata('info') ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Content will be injected by step views -->
            <?= $content ?? '' ?>
        </div>
        
        <!-- Map Wrapper -->
        <div class="map-wrapper">
            <div id="map"></div>
        </div>
    </div>
    
    <!-- Leaflet.js -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Initialize map
        var map = L.map('map').setView([-6.2088, 106.8456], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Add workshop marker if available
        <?php if (isset($workshop) && $workshop['latitude'] && $workshop['longitude']): ?>
        var workshopMarker = L.marker([<?= $workshop['latitude'] ?>, <?= $workshop['longitude'] ?>])
            .addTo(map)
            .bindPopup('<b><?= addslashes($workshop['name']) ?></b><br><?= addslashes($workshop['address']) ?>');
        
        map.setView([<?= $workshop['latitude'] ?>, <?= $workshop['longitude'] ?>], 14);
        <?php endif; ?>
    </script>
    
    <!-- Additional scripts will be injected by step views -->
</body>
</html>

