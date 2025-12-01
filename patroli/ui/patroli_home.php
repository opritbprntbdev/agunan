<?php
session_start();

// CRITICAL SECURITY: Load security guard
require_once '../security_guard.php';

require_once '../config_patroli.php';

$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? '';
$nama_lengkap = $_SESSION['nama_lengkap'] ?? '';
$kode_kantor = $_SESSION['kode_kantor'] ?? '';
$nama_kc = $_SESSION['nama_kc'] ?? '';
$role = $_SESSION['role'] ?? 'penjaga';

// Hitung statistik untuk dashboard
$stats = [
    'total_ruangan' => 0,
    'scan_hari_ini' => 0,
    'periode_aktif' => '-'
];

// Get total ruangan untuk cabang ini (untuk penjaga) atau semua (untuk admin)
if ($role === 'penjaga') {
    $stmt_ruangan = $conn_patroli->prepare("SELECT COUNT(*) as total FROM ruangan WHERE kode_kantor = ? AND is_active = 1");
    $stmt_ruangan->bind_param('s', $kode_kantor);
} else {
    $stmt_ruangan = $conn_patroli->prepare("SELECT COUNT(*) as total FROM ruangan WHERE is_active = 1");
}
$stmt_ruangan->execute();
$result_ruangan = $stmt_ruangan->get_result();
$row_ruangan = $result_ruangan->fetch_assoc();
$stats['total_ruangan'] = $row_ruangan['total'] ?? 0;
$stmt_ruangan->close();

// Get scan hari ini
if ($role === 'penjaga') {
    $stmt_scan = $conn_patroli->prepare("SELECT COUNT(*) as total FROM patroli_log WHERE user_id = ? AND DATE(scan_datetime) = CURDATE()");
    $stmt_scan->bind_param('i', $user_id);
} else {
    $stmt_scan = $conn_patroli->prepare("SELECT COUNT(*) as total FROM patroli_log WHERE DATE(scan_datetime) = CURDATE()");
}
$stmt_scan->execute();
$result_scan = $stmt_scan->get_result();
$row_scan = $result_scan->fetch_assoc();
$stats['scan_hari_ini'] = $row_scan['total'] ?? 0;
$stmt_scan->close();

// Get jadwal patroli (struktur baru: jam_mulai_shift, durasi_scan_menit, durasi_istirahat_menit, jumlah_periode)
$stmt_jadwal = $conn_patroli->prepare("SELECT jam_mulai_shift, durasi_scan_menit, durasi_istirahat_menit, jumlah_periode FROM jadwal_patroli WHERE kode_kantor = ? AND is_active = 1");
$stmt_jadwal->bind_param('s', $kode_kantor);
$stmt_jadwal->execute();
$result_jadwal = $stmt_jadwal->get_result();
$row_jadwal = $result_jadwal->fetch_assoc();
$jadwal_array = [];

if ($row_jadwal) {
    // Generate periode scan berdasarkan setting
    $jam_mulai = $row_jadwal['jam_mulai_shift'];
    $durasi_scan = (int)$row_jadwal['durasi_scan_menit'];
    $durasi_istirahat = (int)$row_jadwal['durasi_istirahat_menit'];
    $jumlah_periode = (int)$row_jadwal['jumlah_periode'];
    
    $current_time = strtotime("2000-01-01 " . $jam_mulai);
    
    for ($i = 1; $i <= $jumlah_periode; $i++) {
        $start_scan = date('H:i', $current_time);
        $end_scan = date('H:i', strtotime("+{$durasi_scan} minutes", $current_time));
        
        $jadwal_array[] = [
            'periode' => "Periode $i",
            'waktu_scan' => "$start_scan - $end_scan",
            'start' => $start_scan,
            'end' => $end_scan
        ];
        
        // Jump to next periode (skip istirahat)
        $current_time = strtotime("+{$durasi_scan} minutes", $current_time);
        $current_time = strtotime("+{$durasi_istirahat} minutes", $current_time);
    }
}
$stmt_jadwal->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0, minimal-ui, viewport-fit=cover">
  <meta name="theme-color" content="#1e40af">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="format-detection" content="telephone=no">
  <meta name="msapplication-tap-highlight" content="no">
  <title>Patroli Security - Home</title>
  <link rel="manifest" href="../../manifest.json">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: system-ui, -apple-system, sans-serif;
      background: linear-gradient(135deg, #1e3a8a 0%, #312e81 100%);
      min-height: 100vh;
      padding-bottom: 80px;
      overflow-x: hidden;
      -webkit-user-select: none;
      user-select: none;
      -webkit-tap-highlight-color: transparent;
      -webkit-touch-callout: none;
    }

    .header {
      background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
      color: #fff;
      padding: 20px;
      box-shadow: 0 2px 12px rgba(0,0,0,.2);
    }

    .header-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px;
    }

    .logo {
      font-size: 28px;
    }

    .app-name {
      font-size: 18px;
      font-weight: 700;
    }

    .logout-btn {
      background: rgba(255,255,255,.2);
      color: #fff;
      border: none;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 13px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
    }

    .user-info {
      background: rgba(255,255,255,.15);
      padding: 10px 14px;
      border-radius: 12px;
      font-size: 13px;
    }

    .role-badge {
      background: #10b981;
      color: #fff;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 10px;
      margin-left: 6px;
      font-weight: 700;
    }

    .container {
      padding: 20px;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
      margin-bottom: 20px;
    }

    .stat-card {
      background: #fff;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,.1);
    }

    .stat-icon {
      font-size: 32px;
      margin-bottom: 8px;
    }

    .stat-value {
      font-size: 28px;
      font-weight: 700;
      color: #1e293b;
    }

    .stat-label {
      font-size: 12px;
      color: #64748b;
      margin-top: 4px;
    }

    .menu-section {
      margin-bottom: 24px;
    }

    .menu-title {
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      margin-bottom: 12px;
      opacity: 0.95;
    }

    .menu-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }

    .menu-card {
      background: #fff;
      border-radius: 16px;
      padding: 20px 16px;
      text-align: center;
      text-decoration: none;
      box-shadow: 0 4px 12px rgba(0,0,0,.1);
      transition: all .3s;
      position: relative;
      overflow: hidden;
    }

    .menu-card:active {
      transform: scale(0.97);
    }

    .menu-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, #1e40af, #3730a3);
    }

    .menu-icon {
      font-size: 40px;
      margin-bottom: 10px;
    }

    .menu-label {
      font-size: 14px;
      font-weight: 600;
      color: #1e293b;
    }

    .jadwal-section {
      background: #fff;
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,.1);
    }

    .jadwal-title {
      font-size: 16px;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 12px;
    }

    .jadwal-list {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .jadwal-item {
      background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
      color: #1e40af;
      padding: 10px 14px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      border: 2px solid #bfdbfe;
      flex: 1;
      min-width: 140px;
      text-align: center;
    }

    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background: #fff;
      box-shadow: 0 -2px 12px rgba(0,0,0,.1);
      padding: 12px 20px;
      display: flex;
      justify-content: space-around;
    }

    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-decoration: none;
      color: #94a3b8;
      font-size: 11px;
      padding: 8px 12px;
      border-radius: 12px;
    }

    .nav-item.active {
      color: #1e40af;
      background: #eff6ff;
    }

    .nav-icon {
      font-size: 22px;
      margin-bottom: 4px;
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="header-top">
      <div>
        <span class="logo">🛡️</span>
        <span class="app-name">Patroli Security</span>
      </div>
      <a href="../../logout.php" class="logout-btn" onclick="return confirm('Yakin ingin logout?')">Logout</a>
    </div>
    <div class="user-info">
      <strong><?= htmlspecialchars($nama_lengkap) ?></strong> · 
      <?= htmlspecialchars($nama_kc) ?>
      <span class="role-badge"><?= strtoupper($role) ?></span>
    </div>
  </div>

  <div class="container">
    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">🏢</div>
        <div class="stat-value"><?= $stats['total_ruangan'] ?></div>
        <div class="stat-label">Total Ruangan</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-value"><?= $stats['scan_hari_ini'] ?></div>
        <div class="stat-label">Scan Hari Ini</div>
      </div>
    </div>

    <!-- Jadwal Patroli -->
    <?php if (!empty($jadwal_array)): ?>
    <div class="jadwal-section">
      <div class="jadwal-title">📅 Jadwal Patroli Hari Ini</div>
      <div class="jadwal-list">
        <?php foreach ($jadwal_array as $jadwal): ?>
          <div class="jadwal-item">
            <div style="font-weight: 700; font-size: 13px;"><?= htmlspecialchars($jadwal['periode']) ?></div>
            <div style="font-size: 11px; margin-top: 2px;"><?= htmlspecialchars($jadwal['waktu_scan']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Menu Penjaga -->
    <?php if ($role === 'penjaga'): ?>
    <div class="menu-section">
      <div class="menu-title">📋 Menu Penjaga</div>
      <div class="menu-grid">
        <a href="patroli_scan.php" class="menu-card">
          <div class="menu-icon">📷</div>
          <div class="menu-label">Scan Patroli</div>
        </a>
        <a href="patroli_my_history.php" class="menu-card">
          <div class="menu-icon">📊</div>
          <div class="menu-label">History Saya</div>
        </a>
      </div>
    </div>
    <?php endif; ?>

    <!-- Menu Admin Pusat -->
    <?php if ($role === 'admin_pusat'): ?>
    <div class="menu-section">
      <div class="menu-title">⚙️ Menu Admin Pusat</div>
      <div class="menu-grid">
        <a href="patroli_manage_room.php" class="menu-card">
          <div class="menu-icon">🏢</div>
          <div class="menu-label">Kelola Ruangan</div>
        </a>
        <a href="patroli_report.php" class="menu-card">
          <div class="menu-icon">📊</div>
          <div class="menu-label">Report Patroli</div>
        </a>
        <a href="patroli_manage_user.php" class="menu-card">
          <div class="menu-icon">👥</div>
          <div class="menu-label">Kelola User</div>
        </a>
        <a href="patroli_manage_schedule.php" class="menu-card">
          <div class="menu-icon">⏰</div>
          <div class="menu-label">Kelola Jadwal</div>
        </a>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Bottom Nav -->
  <div class="bottom-nav">
    <a href="patroli_home.php" class="nav-item active">
      <div class="nav-icon">🏠</div>
      <div>Home</div>
    </a>
    <?php if ($role === 'penjaga'): ?>
    <a href="patroli_scan.php" class="nav-item">
      <div class="nav-icon">📷</div>
      <div>Scan</div>
    </a>
    <a href="patroli_my_history.php" class="nav-item">
      <div class="nav-icon">📊</div>
      <div>History</div>
    </a>
    <?php else: ?>
    <a href="patroli_report.php" class="nav-item">
      <div class="nav-icon">📊</div>
      <div>Report</div>
    </a>
    <a href="patroli_manage_room.php" class="nav-item">
      <div class="nav-icon">🏢</div>
      <div>Ruangan</div>
    </a>
    <?php endif; ?>
    <a href="../../logout.php" class="nav-item" onclick="return confirm('Yakin ingin logout?')">
      <div class="nav-icon">🚪</div>
      <div>Logout</div>
    </a>
  </div>

  <script>
    // Service Worker untuk PWA
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('../../sw.js').catch(err => {
        console.log('SW registration failed:', err);
      });
    }

    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
      Notification.requestPermission();
    }
  </script>
</body>
</html>
