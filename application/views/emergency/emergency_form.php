<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo $app_name; ?></title>
    
    <!-- CSRF Token -->
    <meta name="csrf_token_name" content="<?php echo $this->security->get_csrf_token_name(); ?>">
    <meta name="csrf_token_hash" content="<?php echo $this->security->get_csrf_hash(); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        /* Emergency Button - Fixed Position */
        .emergency-button-fixed {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 80px;
            height: 80px;
            background: radial-gradient(circle, #dc3545 0%, #c82333 100%);
            border: 4px solid white;
            border-radius: 50%;
            color: white;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(220, 53, 69, 0.5);
            cursor: pointer;
            z-index: 1000;
            animation: pulse 2s infinite;
            text-decoration: none;
            transition: transform 0.3s;
        }
        
        .emergency-button-fixed:hover {
            transform: scale(1.1);
            color: white;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
        
        .emergency-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .emergency-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .emergency-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .emergency-header h1 {
            color: #dc3545;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .emergency-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .form-section {
            margin-bottom: 25px;
        }
        
        .form-section label {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 8px;
        }
        
        .btn-emergency-submit {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            color: white;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
            transition: all 0.3s;
        }
        
        .btn-emergency-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.5);
            color: white;
        }
        
        .btn-emergency-submit:disabled {
            background: #6c757d;
            box-shadow: none;
            transform: none;
        }
        
        #map {
            height: 300px;
            border-radius: 10px;
            margin-top: 10px;
            display: none;
        }
        
        .location-status {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .location-status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .location-status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .location-status.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .active-request-alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .workshop-list {
            margin-top: 20px;
        }
        
        .workshop-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .workshop-item .distance {
            background: #667eea;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .workshop-item .status {
            color: #ffc107;
            font-weight: 600;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .result-panel {
            display: none;
            background: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-top: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Emergency Button (Fixed Position) - FR-EMG-01 -->
    <a href="<?php echo site_url('emergency'); ?>" class="emergency-button-fixed" title="Bantuan Darurat">
        <i class="fas fa-exclamation-triangle"></i><br>DARURAT
    </a>
    
    <div class="emergency-container">
        <div class="emergency-card">
            <div class="emergency-header">
                <h1><i class="fas fa-ambulance"></i> LAYANAN DARURAT</h1>
                <p>Bantuan darurat 24/7 untuk masalah kendaraan Anda</p>
            </div>
            
            <?php if (isset($has_active_request) && $has_active_request): ?>
                <div class="active-request-alert">
                    <h5><i class="fas fa-exclamation-circle"></i> Permintaan Aktif</h5>
                    <p>Anda sudah memiliki permintaan darurat yang aktif. Silakan tunggu respons dari bengkel.</p>
                    <a href="<?php echo site_url('emergency/track'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-search-location"></i> Lacak Permintaan
                    </a>
                </div>
            <?php else: ?>
            
            <form id="emergency-form" method="POST" action="<?php echo site_url('emergency/create'); ?>">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                
                <!-- User Information -->
                <div class="form-section">
                    <label for="user_name"><i class="fas fa-user"></i> Nama Lengkap</label>
                    <input type="text" class="form-control" id="user_name" name="user_name" 
                           value="<?php echo htmlspecialchars($user_name ?? ''); ?>" 
                           placeholder="Nama lengkap Anda" required>
                </div>
                
                <div class="form-section">
                    <label for="user_phone"><i class="fas fa-phone"></i> Nomor Telepon</label>
                    <input type="tel" class="form-control" id="user_phone" name="user_phone" 
                           value="<?php echo htmlspecialchars($user_phone ?? ''); ?>" 
                           placeholder="08xx-xxxx-xxxx" required>
                </div>
                
                <!-- Vehicle Selection (if logged in) -->
                <?php if (!empty($vehicles)): ?>
                <div class="form-section">
                    <label for="vehicle_id"><i class="fas fa-car"></i> Kendaraan (Opsional)</label>
                    <select class="form-select" id="vehicle_id" name="vehicle_id">
                        <option value="">-- Pilih Kendaraan --</option>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?php echo $vehicle['id']; ?>">
                                <?php echo $vehicle['vehicle_number'] . ' - ' . $vehicle['brand'] . ' ' . $vehicle['model']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <!-- Emergency Type Dropdown -->
                <div class="form-section">
                    <label for="emergency_type"><i class="fas fa-tools"></i> Jenis Masalah</label>
                    <select class="form-select" id="emergency_type" name="emergency_type" required>
                        <option value="">-- Pilih Jenis Darurat --</option>
                        <?php foreach ($emergency_types as $value => $label): ?>
                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Description -->
                <div class="form-section">
                    <label for="description"><i class="fas fa-comment-alt"></i> Deskripsi Masalah</label>
                    <textarea class="form-control" id="description" name="description" rows="3" 
                              placeholder="Jelaskan masalah Anda secara detail..." required maxlength="500"></textarea>
                    <small class="text-muted">Maksimal 500 karakter</small>
                </div>
                
                <!-- Location Section -->
                <div class="form-section">
                    <label><i class="fas fa-map-marker-alt"></i> Lokasi Kejadian</label>
                    
                    <div id="location-status" class="location-status warning" style="display:none;">
                        <i class="fas fa-info-circle"></i> <span id="location-message">Mengambil lokasi...</span>
                    </div>
                    
                    <button type="button" id="btn-get-location" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-location-arrow"></i> Gunakan Lokasi Saya (GPS)
                    </button>
                    
                    <button type="button" id="btn-manual-location" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="fas fa-map"></i> Pilih Lokasi Manual di Peta
                    </button>
                    
                    <div id="map"></div>
                    
                    <input type="hidden" id="latitude" name="latitude" required>
                    <input type="hidden" id="longitude" name="longitude" required>
                    <input type="text" class="form-control mt-2" id="location_address" name="location_address" 
                           placeholder="Alamat lengkap (opsional)" readonly>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" id="btn-submit" class="btn btn-emergency-submit" disabled>
                    <i class="fas fa-exclamation-triangle"></i> KIRIM PERMINTAAN DARURAT
                </button>
                
                <!-- Loading Spinner -->
                <div id="loading" class="loading-spinner">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memproses permintaan darurat...</p>
                </div>
            </form>
            
            <!-- Result Panel -->
            <div id="result-panel" class="result-panel">
                <h5><i class="fas fa-check-circle"></i> Permintaan Berhasil Dikirim!</h5>
                <p id="result-message"></p>
                <div id="workshop-list" class="workshop-list"></div>
                <a href="<?php echo site_url('emergency/track'); ?>" class="btn btn-primary mt-3">
                    <i class="fas fa-search-location"></i> Lacak Status Permintaan
                </a>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    $(document).ready(function() {
        var csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        var csrfTokenHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        
        var map = null;
        var userMarker = null;
        var manualPinMarker = null;
        var userLocation = null;
        var locationSet = false;
        
        // Get current CSRF token
        function getCsrfData() {
            return {
                [csrfTokenName]: csrfTokenHash
            };
        }
        
        // Initialize map
        function initMap(lat, lng) {
            if (map) {
                map.remove();
            }
            
            map = L.map('map').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
            $('#map').show();
            
            // Add click event for manual location
            map.on('click', function(e) {
                setManualLocation(e.latlng.lat, e.latlng.lng);
            });
        }
        
        // Set manual location
        function setManualLocation(lat, lng) {
            if (manualPinMarker) {
                map.removeLayer(manualPinMarker);
            }
            
            manualPinMarker = L.marker([lat, lng]).addTo(map)
                .bindPopup('Lokasi Anda')
                .openPopup();
            
            $('#latitude').val(lat);
            $('#longitude').val(lng);
            locationSet = true;
            
            updateLocationStatus('success', 'Lokasi berhasil dipilih!');
            $('#btn-submit').prop('disabled', false);
        }
        
        // Update location status message
        function updateLocationStatus(type, message) {
            $('#location-status')
                .removeClass('success error warning')
                .addClass(type)
                .show();
            $('#location-message').text(message);
        }
        
        // Get user location via GPS
        $('#btn-get-location').click(function() {
            if (!navigator.geolocation) {
                updateLocationStatus('error', 'Browser Anda tidak mendukung geolokasi.');
                return;
            }
            
            updateLocationStatus('warning', 'Mengambil lokasi dari GPS...');
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    
                    userLocation = { latitude: lat, longitude: lng };
                    
                    initMap(lat, lng);
                    
                    if (userMarker) {
                        map.removeLayer(userMarker);
                    }
                    
                    userMarker = L.marker([lat, lng]).addTo(map)
                        .bindPopup('Lokasi Anda')
                        .openPopup();
                    
                    $('#latitude').val(lat);
                    $('#longitude').val(lng);
                    locationSet = true;
                    
                    updateLocationStatus('success', 'Lokasi berhasil ditemukan!');
                    $('#btn-submit').prop('disabled', false);
                    
                    // Optional: Reverse geocoding could be added here
                },
                function(error) {
                    updateLocationStatus('error', 'Gagal mendapatkan lokasi GPS. Silakan pilih lokasi manual di peta.');
                    console.error('Geolocation error:', error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
        
        // Show manual location selection
        $('#btn-manual-location').click(function() {
            if (userLocation) {
                initMap(userLocation.latitude, userLocation.longitude);
            } else {
                // Default to Jakarta if no location
                initMap(-6.2088, 106.8456);
            }
            
            updateLocationStatus('info', 'Klik pada peta untuk memilih lokasi.');
        });
        
        // Form submission
        $('#emergency-form').submit(function(e) {
            e.preventDefault();
            
            if (!locationSet) {
                alert('Silakan pilih lokasi terlebih dahulu!');
                return;
            }
            
            $('#loading').show();
            $('#btn-submit').prop('disabled', true);
            
            var formData = {
                [csrfTokenName]: csrfTokenHash,
                user_name: $('#user_name').val(),
                user_phone: $('#user_phone').val(),
                vehicle_id: $('#vehicle_id').val(),
                emergency_type: $('#emergency_type').val(),
                description: $('#description').val(),
                latitude: $('#latitude').val(),
                longitude: $('#longitude').val(),
                location_address: $('#location_address').val()
            };
            
            $.ajax({
                url: '<?php echo site_url('emergency/create'); ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#loading').hide();
                    
                    if (response.success) {
                        $('#emergency-form').hide();
                        $('#result-panel').show();
                        $('#result-message').text(response.message);
                        
                        // Display workshop list
                        var workshopHtml = '<h6>Bengkel yang dihubuti:</h6>';
                        response.data.workshops.forEach(function(workshop) {
                            workshopHtml += '<div class="workshop-item">' +
                                '<div>' +
                                    '<strong>' + workshop.name + '</strong><br>' +
                                    '<small><i class="fas fa-phone"></i> ' + workshop.phone + '</small>' +
                                '</div>' +
                                '<div>' +
                                    '<span class="distance">' + workshop.distance + ' km</span>' +
                                    '<span class="status ml-2">' + workshop.status + '</span>' +
                                '</div>' +
                            '</div>';
                        });
                        
                        $('#workshop-list').html(workshopHtml);
                        
                        // Update CSRF token
                        csrfTokenHash = response.csrf_hash || csrfTokenHash;
                    } else {
                        alert(response.message || 'Terjadi kesalahan. Silakan coba lagi.');
                        $('#btn-submit').prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    $('#loading').hide();
                    $('#btn-submit').prop('disabled', false);
                    
                    var errorMsg = 'Terjadi kesalahan. Silakan coba lagi.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    alert(errorMsg);
                }
            });
        });
    });
    </script>
</body>
</html>
