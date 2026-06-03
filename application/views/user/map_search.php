<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { margin: 0; padding: 0; }
        #map-container { display: flex; height: 100vh; }
        #sidebar { width: 400px; background: #f8f9fa; overflow-y: auto; border-right: 1px solid #dee2e6; }
        #map { flex: 1; height: 100vh; }
        .sidebar-header { padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .sidebar-content { padding: 15px; }
        .search-controls { background: white; padding: 15px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 15px; }
        .workshop-card { background: white; border-radius: 10px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.2s; }
        .workshop-card:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
        .workshop-card.active { border: 2px solid #667eea; }
        .location-btn { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; border-radius: 8px; }
        .loading-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.9); display: flex; justify-content: center; align-items: center; z-index: 1000; }
        .spinner { width: 50px; height: 50px; border: 4px solid #f3f3f3; border-top: 4px solid #667eea; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .route-info-panel { background: #e7f3ff; border-left: 4px solid #667eea; padding: 15px; margin-bottom: 15px; border-radius: 5px; display: none; }
    </style>
</head>
<body>
    <div id="map-container">
        <div id="sidebar">
            <div class="sidebar-header"><h4 class="mb-0"><i class="fas fa-map-marked-alt"></i> Cari Bengkel Terdekat</h4></div>
            <div class="sidebar-content">
                <div class="search-controls">
                    <button id="btn-locate" class="location-btn"><i class="fas fa-location-arrow"></i> Gunakan Lokasi Saya</button>
                    <div class="mt-2">
                        <label class="form-label">Radius:</label>
                        <select id="radius-select" class="form-select form-select-sm">
                            <option value="5">5 km</option>
                            <option value="10" selected>10 km</option>
                            <option value="20">20 km</option>
                            <option value="50">50 km</option>
                        </select>
                    </div>
                    <div id="manual-location" style="display:none;" class="mt-2"><small class="text-muted">Klik peta untuk lokasi manual</small></div>
                </div>
                <div id="route-info" class="route-info-panel"><h6><i class="fas fa-route"></i> Rute</h6><div id="route-details"></div></div>
                <div id="results-container">
                    <div class="text-center text-muted p-4"><i class="fas fa-map-marker-alt fa-3x mb-3"></i><p>Klik "Gunakan Lokasi Saya" untuk mencari bengkel terdekat.</p></div>
                </div>
            </div>
        </div>
        <div id="map"></div>
    </div>
    <div id="loading" class="loading-overlay" style="display:none;"><div class="spinner"></div></div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    $(document).ready(function() {
        var csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        var csrfTokenHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        var map = L.map('map').setView([-2.5489, 118.0149], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors', maxZoom: 19 }).addTo(map);
        
        var userMarker = null, workshopMarkers = [], routePolyline = null, userLocation = null, manualPinMarker = null;
        var workshopIcon = L.divIcon({ className: 'workshop-marker', html: '<div style="background:#dc3545;border:3px solid white;border-radius:50% 50% 50% 0;transform:rotate(-45deg);width:20px;height:20px;margin:5px;"></div>', iconSize: [30, 30] });
        var userIcon = L.divIcon({ className: 'user-marker', html: '<div style="background:#667eea;border:3px solid white;border-radius:50%;width:20px;height:20px;margin:5px;"></div>', iconSize: [30, 30] });

        $('#btn-locate').click(getUserLocation);

        function getUserLocation() {
            if (!navigator.geolocation) { alert('Browser tidak mendukung geolokasi.'); return; }
            $('#loading').show();
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    userLocation = { latitude: pos.coords.latitude, longitude: pos.coords.longitude };
                    updateUserMarker(userLocation);
                    searchWorkshops(userLocation.latitude, userLocation.longitude);
                },
                function(err) {
                    $('#loading').hide();
                    if (err.code === err.PERMISSION_DENIED && confirm('Lokasi ditolak. Tentukan manual dengan klik peta?')) {
                        $('#manual-location').show();
                        enableManualLocation();
                    }
                }, { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        function updateUserMarker(loc) {
            if (userMarker) map.removeLayer(userMarker);
            userMarker = L.marker([loc.latitude, loc.longitude], { icon: userIcon }).addTo(map);
            map.setView([loc.latitude, loc.longitude], 13);
        }

        function enableManualLocation() {
            map.on('click', function(e) {
                if (manualPinMarker) map.removeLayer(manualPinMarker);
                userLocation = { latitude: e.latlng.lat, longitude: e.latlng.lng };
                manualPinMarker = L.marker([userLocation.latitude, userLocation.longitude], { icon: userIcon, draggable: true }).addTo(map);
                manualPinMarker.on('dragend', function(ev) {
                    var p = ev.target.getLatLng();
                    userLocation = { latitude: p.lat, longitude: p.lng };
                    searchWorkshops(userLocation.latitude, userLocation.longitude);
                });
                searchWorkshops(userLocation.latitude, userLocation.longitude);
            });
        }

        function searchWorkshops(lat, lng) {
            $('#loading').show();
            $.post('<?php echo site_url("map/get_nearby_workshops"); ?>', {
                latitude: lat, longitude: lng, radius: $('#radius-select').val(), [csrfTokenName]: csrfTokenHash
            }, function(res) {
                $('#loading').hide();
                if (res.success && res.data.workshops) displayWorkshops(res.data.workshops);
                else displayNoResults();
            }, 'json').fail(function() { $('#loading').hide(); displayNoResults(); });
        }

        function displayWorkshops(workshops) {
            workshopMarkers.forEach(function(m) { map.removeLayer(m); });
            workshopMarkers = [];
            var container = $('#results-container').empty();
            if (workshops.length === 0) { displayNoResults(); return; }
            var bounds = userMarker ? [[userLocation.latitude, userLocation.longitude]] : [];
            workshops.forEach(function(w) {
                var marker = L.marker([w.latitude, w.longitude], { icon: workshopIcon }).addTo(map);
                marker.bindPopup('<strong>'+escapeHtml(w.name)+'</strong><br>'+escapeHtml(w.address)+'<br>Jarak: '+w.route_distance_km+' km<br>Estimasi: '+w.travel_time_minutes+' menit');
                marker.on('click', function() { selectWorkshop(w); });
                workshopMarkers.push(marker);
                bounds.push([w.latitude, w.longitude]);
                container.append('<div class="workshop-card" data-id="'+w.id+'"><div class="fw-bold">'+escapeHtml(w.name)+'</div><div class="text-muted small"><i class="fas fa-map-marker-alt"></i> '+escapeHtml(w.address)+'</div><div class="d-flex justify-content-between mt-2"><span class="text-warning"><i class="fas fa-star"></i> '+w.rating_avg.toFixed(1)+'</span><span class="badge bg-primary">'+w.route_distance_km+' km</span></div><div class="text-success small mt-1"><i class="fas fa-clock"></i> ~'+w.travel_time_minutes+' menit</div></div>');
            });
            if (bounds.length > 1) map.fitBounds(bounds, { padding: [50, 50] });
            $('.workshop-card').click(function() {
                var w = workshops.find(x => x.id == $(this).data('id'));
                if (w) selectWorkshop(w);
            });
        }

        function displayNoResults() {
            $('#results-container').html('<div class="text-center text-muted p-4"><i class="fas fa-search fa-3x mb-3"></i><p>Tidak ditemukan bengkel dalam radius ini.</p></div>');
        }

        function selectWorkshop(w) {
            $('.workshop-card').removeClass('active');
            $('[data-id="'+w.id+'"]').addClass('active');
            $('#route-info').show();
            $('#route-details').html('<strong>'+escapeHtml(w.name)+'</strong><br>Jarak: <strong>'+w.route_distance_km+' km</strong><br>Estimasi: <strong>'+w.travel_time_minutes+' menit</strong>');
            if (routePolyline) map.removeLayer(routePolyline);
            if (w.route_path && w.route_path.length > 0) {
                var coords = [];
                w.route_path.forEach(function(e) {
                    if (e.from_coords) coords.push([e.from_coords.latitude, e.from_coords.longitude]);
                    if (e.to_coords) coords.push([e.to_coords.latitude, e.to_coords.longitude]);
                });
                if (coords.length > 0) {
                    routePolyline = L.polyline(coords, { color: '#667eea', weight: 5, opacity: 0.7 }).addTo(map);
                    map.fitBounds(routePolyline.getBounds(), { padding: [50, 50] });
                }
            }
            map.setView([w.latitude, w.longitude], 15);
        }

        $('#radius-select').change(function() { if (userLocation) searchWorkshops(userLocation.latitude, userLocation.longitude); });
        function escapeHtml(t) { if (!t) return ''; var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    });
    </script>
</body>
</html>
