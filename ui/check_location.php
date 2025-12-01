<?php
session_start();
if (!isset($_SESSION['login'])) {
  header('Location: ../index.php');
  exit;
}

$username = $_SESSION['username'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#2563eb">
  <title>Cek Lokasi</title>
  
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: system-ui, -apple-system, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 20px;
    }

    .container {
      max-width: 500px;
      margin: 0 auto;
    }

    .card {
      background: #fff;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 4px 20px rgba(0,0,0,.15);
      margin-bottom: 16px;
    }

    .header {
      text-align: center;
      margin-bottom: 24px;
    }

    .header h1 {
      font-size: 24px;
      color: #1e293b;
      margin-bottom: 8px;
    }

    .header p {
      color: #64748b;
      font-size: 14px;
    }

    .btn {
      width: 100%;
      padding: 16px;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all .3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn.primary {
      background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
      color: #fff;
    }

    .btn.primary:active {
      transform: scale(0.98);
    }

    .btn.secondary {
      background: #f1f5f9;
      color: #334155;
      margin-top: 12px;
    }

    .btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .status {
      padding: 16px;
      border-radius: 12px;
      margin-top: 16px;
      font-size: 14px;
      display: none;
    }

    .status.info {
      background: #dbeafe;
      color: #1e40af;
      display: block;
    }

    .status.success {
      background: #d1fae5;
      color: #065f46;
      display: block;
    }

    .status.error {
      background: #fee2e2;
      color: #991b1b;
      display: block;
    }

    .location-info {
      background: #f8fafc;
      padding: 16px;
      border-radius: 12px;
      margin-top: 16px;
      display: none;
    }

    .location-info.show {
      display: block;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px solid #e2e8f0;
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-label {
      color: #64748b;
      font-size: 13px;
    }

    .info-value {
      color: #1e293b;
      font-weight: 600;
      font-size: 13px;
    }

    .back-link {
      text-align: center;
      margin-top: 20px;
    }

    .back-link a {
      color: #fff;
      text-decoration: none;
      font-size: 14px;
    }

    #map {
      width: 100%;
      height: 300px;
      border-radius: 12px;
      margin-top: 16px;
      display: none;
    }

    #map.show {
      display: block;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="header">
        <h1>📍 Cek Lokasi</h1>
        <p>User: <strong><?= htmlspecialchars($username) ?></strong></p>
      </div>

      <button class="btn primary" id="getLocationBtn">
        <span>📍</span>
        <span>Dapatkan Lokasi Saya</span>
      </button>

      <button class="btn secondary" id="saveLocationBtn" disabled>
        <span>💾</span>
        <span>Simpan Lokasi</span>
      </button>

      <div class="status" id="statusBox"></div>

      <div class="location-info" id="locationInfo">
        <div class="info-row">
          <span class="info-label">Latitude:</span>
          <span class="info-value" id="latValue">-</span>
        </div>
        <div class="info-row">
          <span class="info-label">Longitude:</span>
          <span class="info-value" id="lngValue">-</span>
        </div>
        <div class="info-row">
          <span class="info-label">Akurasi:</span>
          <span class="info-value" id="accValue">-</span>
        </div>
        <div class="info-row">
          <span class="info-label">Waktu:</span>
          <span class="info-value" id="timeValue">-</span>
        </div>
      </div>

      <!-- Map Container -->
      <div id="map"></div>
    </div>

    <div class="back-link">
      <a href="../home.php">← Kembali ke Home</a>
    </div>
  </div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <script>
    let currentLocation = null;
    let map = null;
    let marker = null;
    let circle = null;
    const userId = <?= $user_id ?>;
    const username = '<?= htmlspecialchars($username) ?>';

    const els = {
      getBtn: document.getElementById('getLocationBtn'),
      saveBtn: document.getElementById('saveLocationBtn'),
      statusBox: document.getElementById('statusBox'),
      locationInfo: document.getElementById('locationInfo'),
      latValue: document.getElementById('latValue'),
      lngValue: document.getElementById('lngValue'),
      accValue: document.getElementById('accValue'),
      timeValue: document.getElementById('timeValue')
    };

    // Check geolocation support
    if (!('geolocation' in navigator)) {
      showStatus('Browser tidak support geolocation', 'error');
      els.getBtn.disabled = true;
    }

    // Get location button
    els.getBtn.addEventListener('click', getLocation);

    // Save location button
    els.saveBtn.addEventListener('click', saveLocation);

    function getLocation() {
      showStatus('Meminta izin lokasi...', 'info');
      els.getBtn.disabled = true;
      els.saveBtn.disabled = true;

      const options = {
        enableHighAccuracy: true, // Use GPS
        timeout: 10000, // 10 seconds
        maximumAge: 0 // No cache
      };

      navigator.geolocation.getCurrentPosition(
        onSuccess,
        onError,
        options
      );
    }

    function onSuccess(position) {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      const acc = Math.round(position.coords.accuracy);
      const timestamp = new Date(position.timestamp);

      currentLocation = {
        latitude: lat,
        longitude: lng,
        accuracy: acc,
        timestamp: timestamp
      };

      // Update UI
      els.latValue.textContent = lat.toFixed(6);
      els.lngValue.textContent = lng.toFixed(6);
      els.accValue.textContent = acc + ' meter';
      els.timeValue.textContent = timestamp.toLocaleString('id-ID');

      els.locationInfo.classList.add('show');
      showStatus('✅ Lokasi berhasil didapatkan!', 'success');
      
      els.getBtn.disabled = false;
      els.saveBtn.disabled = false;

      // Show map
      showMap(lat, lng, acc);

      // Auto open Google Maps link
      const mapsUrl = `https://www.google.com/maps?q=${lat},${lng}`;
      console.log('Google Maps:', mapsUrl);
    }

    function onError(error) {
      let message = '';
      switch(error.code) {
        case error.PERMISSION_DENIED:
          message = '❌ Izin lokasi ditolak. Aktifkan di pengaturan browser.';
          break;
        case error.POSITION_UNAVAILABLE:
          message = '❌ Lokasi tidak tersedia. Cek GPS/WiFi.';
          break;
        case error.TIMEOUT:
          message = '❌ Timeout. Coba lagi.';
          break;
        default:
          message = '❌ Error: ' + error.message;
      }
      showStatus(message, 'error');
      els.getBtn.disabled = false;
    }

    async function saveLocation() {
      if (!currentLocation) return;

      els.saveBtn.disabled = true;
      showStatus('Menyimpan lokasi...', 'info');

      try {
        const response = await fetch('../process/save_location.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            user_id: userId,
            username: username,
            latitude: currentLocation.latitude,
            longitude: currentLocation.longitude,
            accuracy: currentLocation.accuracy
          })
        });

        const data = await response.json();

        if (data.success) {
          showStatus('✅ Lokasi berhasil disimpan!', 'success');
          setTimeout(() => {
            showStatus('', 'info');
          }, 3000);
        } else {
          showStatus('❌ Gagal simpan: ' + data.message, 'error');
        }
      } catch (error) {
        showStatus('❌ Error: ' + error.message, 'error');
      }

      els.saveBtn.disabled = false;
    }

    function showStatus(message, type) {
      els.statusBox.textContent = message;
      els.statusBox.className = 'status ' + type;
    }

    function showMap(lat, lng, accuracy) {
      const mapEl = document.getElementById('map');
      mapEl.classList.add('show');

      // Initialize map if not exists
      if (!map) {
        map = L.map('map').setView([lat, lng], 16);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap contributors',
          maxZoom: 19
        }).addTo(map);
      } else {
        // Update view
        map.setView([lat, lng], 16);
      }

      // Remove old marker and circle
      if (marker) map.removeLayer(marker);
      if (circle) map.removeLayer(circle);

      // Add marker
      marker = L.marker([lat, lng]).addTo(map)
        .bindPopup(`<b>📍 Lokasi Anda</b><br>Akurasi: ${accuracy}m<br><a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank">Buka di Google Maps</a>`)
        .openPopup();

      // Add accuracy circle
      circle = L.circle([lat, lng], {
        color: '#2563eb',
        fillColor: '#3b82f6',
        fillOpacity: 0.2,
        radius: accuracy
      }).addTo(map);
    }
  </script>
</body>
</html>
