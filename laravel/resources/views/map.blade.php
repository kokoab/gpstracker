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
    </style>
</head>

<body>
    <!-- Map Container -->
    <div id="map"></div>

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

        // Marker variable
        let marker = null;

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

                    // Update status
                    const statusEl = document.getElementById('status');
                    statusEl.textContent = 'Active';
                    statusEl.className = 'status active';

                    // Update or create marker
                    const latLng = [parseFloat(latitude), parseFloat(longitude)];

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
