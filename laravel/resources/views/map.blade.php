<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPS Tracker - Live Location</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #1a1a1a;
            color: #fff;
        }

        #map {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: 1;
        }

        .info-panel {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            min-width: 280px;
            color: #333;
        }

        .info-panel h2 {
            margin: 0 0 15px 0;
            font-size: 18px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
        }

        .info-item {
            margin: 10px 0;
            font-size: 14px;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            display: inline-block;
            width: 90px;
        }

        .info-value {
            color: #2c3e50;
            font-family: 'Courier New', monospace;
        }

        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }

        .status.active {
            background: #2ecc71;
            color: white;
        }

        .status.waiting {
            background: #f39c12;
            color: white;
        }

        .status.error {
            background: #e74c3c;
            color: white;
        }

        .last-update {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #7f8c8d;
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            z-index: 10000;
            font-weight: 600;
            font-size: 15px;
            animation: slideDown 0.3s ease-out;
            display: none;
            max-width: 400px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        /* Speedometer Styles */
        .speed-indicator {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 15px;
            font-size: 16px;
            font-weight: bold;
            margin-top: 15px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .speed-value {
            font-size: 28px;
            font-weight: 800;
            display: block;
            margin: 5px 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .speed-label {
            font-size: 12px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Clear Trail Button */
        .clear-trail-btn {
            width: 100%;
            margin-top: 10px;
            padding: 8px 12px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .clear-trail-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .clear-trail-btn:active {
            transform: translateY(0);
        }
    </style>
</head>

<body>
    <!-- Map Container -->
    <div id="map"></div>

    <!-- Notification -->
    <div id="notification" class="notification">
        📍 Location Updated!
    </div>

    <!-- Info Panel -->
    <div class="info-panel">
        <h2>🚌 GPS Tracker</h2>
        <div class="info-item">
            <span class="info-label">Device:</span>
            <span class="info-value" id="device-id">---</span>
            <span class="status waiting" id="status">Waiting</span>
        </div>
        <div class="info-item">
            <span class="info-label">Latitude:</span>
            <span class="info-value" id="latitude">---</span>
        </div>
        <div class="info-item">
            <span class="info-label">Longitude:</span>
            <span class="info-value" id="longitude">---</span>
        </div>

        <!-- Speedometer -->
        <div class="speed-indicator">
            <span class="speed-label">Current Speed</span>
            <span class="speed-value" id="speed">0.00</span>
            <span class="speed-label">km/h</span>
        </div>

        <!-- Clear Trail Button -->
        <button class="clear-trail-btn" onclick="clearTrail()">🗑️ Clear Trail</button>

        <div class="last-update">
            <strong>Last Update:</strong><br>
            <span id="last-update">Never</span>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Initialize map (centered on Philippines)
        const map = L.map('map').setView([14.5995, 120.9842], 13);

        // Add CartoDB tiles (more reliable, works well with tunnels/proxies)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors © <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        // Custom marker icon
        const busIcon = L.icon({
            iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSIjZTc0YzNjIj48cGF0aCBkPSJNMTIgMkM4LjEzIDIgNSA1LjEzIDUgOWMwIDUuMjUgNyAxMyA3IDEzczctNy43NSA3LTEzYzAtMy44Ny0zLjEzLTctNy03em0wIDkuNWMtMS4zOCAwLTIuNS0xLjEyLTIuNS0yLjVzMS4xMi0yLjUgMi41LTIuNSAyLjUgMS4xMiAyLjUgMi41LTEuMTIgMi41LTIuNSAyLjV6Ii8+PC9zdmc+',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        // Variables for tracking
        let marker = null;
        let pathCoordinates = []; // Store path coordinates for trail
        let pathPolyline = null; // Polyline for displaying trail
        let lastPosition = null; // For speed calculation
        let lastUpdateTime = null; // For speed calculation

        // Function to calculate distance between two points (Haversine formula)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Earth's radius in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c; // Distance in km
        }

        // Function to show notification
        function showNotification(message = '📍 Location Updated!') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.style.display = 'block';

            // Hide after 3 seconds
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        // Function to clear trail
        function clearTrail() {
            pathCoordinates = [];
            if (pathPolyline) {
                map.removeLayer(pathPolyline);
                pathPolyline = null;
            }
            showNotification('🗑️ Trail cleared!');
        }

        // Fetch and update location
        async function updateLocation() {
            try {
                const response = await fetch('/api/location/latest');
                const result = await response.json();

                console.log('📍 GPS Data:', result);

                if (result.success && result.data) {
                    const {
                        device_id,
                        latitude,
                        longitude,
                        updated_at
                    } = result.data;

                    console.log(`🗺️ Lat=${latitude}, Lon=${longitude}`);

                    // Update info panel
                    document.getElementById('device-id').textContent = device_id;
                    document.getElementById('latitude').textContent = latitude.toFixed(6);
                    document.getElementById('longitude').textContent = longitude.toFixed(6);
                    document.getElementById('last-update').textContent = new Date(updated_at).toLocaleString();

                    const latLng = [parseFloat(latitude), parseFloat(longitude)];
                    const currentTime = new Date(updated_at);

                    // Calculate speed if we have previous position
                    if (lastPosition && lastUpdateTime) {
                        const distance = calculateDistance(
                            lastPosition[0], lastPosition[1],
                            latLng[0], latLng[1]
                        );
                        const timeDiff = (currentTime - lastUpdateTime) / 1000 / 3600; // hours
                        const speed = timeDiff > 0 ? (distance / timeDiff).toFixed(2) : 0;

                        document.getElementById('speed').textContent = speed;

                        console.log(`🚗 Speed: ${speed} km/h`);
                    } else {
                        document.getElementById('speed').textContent = '0.00';
                    }

                    // Add to path trail
                    pathCoordinates.push(latLng);

                    // Draw or update polyline (path trail)
                    if (pathPolyline) {
                        pathPolyline.setLatLngs(pathCoordinates);
                    } else {
                        pathPolyline = L.polyline(pathCoordinates, {
                            color: '#3498db',
                            weight: 4,
                            opacity: 0.8,
                            smoothFactor: 1,
                            dashArray: '10, 5',
                            lineJoin: 'round',
                            lineCap: 'round'
                        }).addTo(map);
                    }

                    // Show notification if position changed
                    if (lastPosition &&
                        (lastPosition[0] !== latLng[0] || lastPosition[1] !== latLng[1])) {
                        showNotification(`📍 New location: ${latitude.toFixed(4)}°, ${longitude.toFixed(4)}°`);
                    }

                    // Update status
                    const statusEl = document.getElementById('status');
                    statusEl.textContent = 'Active';
                    statusEl.className = 'status active';

                    // Update or create marker
                    console.log('🎯 Marker at:', latLng);

                    if (marker) {
                        // Move existing marker
                        marker.setLatLng(latLng);
                        map.panTo(latLng);
                    } else {
                        // Create new marker
                        marker = L.marker(latLng, {
                            icon: busIcon
                        }).addTo(map);
                        marker.bindPopup(
                            `<b>${device_id}</b><br>Lat: ${latitude.toFixed(6)}<br>Lon: ${longitude.toFixed(6)}`);
                        // Zoom to marker
                        map.setView(latLng, 15);
                    }

                    // Store current position for next calculation
                    lastPosition = latLng;
                    lastUpdateTime = currentTime;

                } else {
                    // No data available
                    const statusEl = document.getElementById('status');
                    statusEl.textContent = 'Waiting';
                    statusEl.className = 'status waiting';
                }

            } catch (error) {
                console.error('Error fetching location:', error);
                const statusEl = document.getElementById('status');
                statusEl.textContent = 'Error';
                statusEl.className = 'status error';
            }
        }

        // Initial load
        updateLocation();

        // Auto-refresh every 5 seconds
        setInterval(updateLocation, 5000);
    </script>
</body>

</html>
