<?php
session_start();
if (!isset($_SESSION['login'])) {
  header('Location: index.php');
  exit;
}
$nama_kc = $_SESSION['nama_kc'] ?? '';
$username = $_SESSION['username'] ?? '';
$kode_kantor = $_SESSION['kode_kantor'] ?? '000';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#2563eb">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <link rel="manifest" href="manifest.json">
  <title>Agunan Capture - Home</title>
  <style>
    * {
      box-sizing: border-box
    }

    body {
      margin: 0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: #f5f5f5;
      color: #333;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px
    }

    .card {
      width: 100%;
      max-width: 520px;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, .1)
    }

    .head {
      background: #2563eb;
      color: #fff;
      padding: 16px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between
    }

    .body {
      padding: 20px
    }

    .hello {
      font-size: 14px;
      color: #666;
      margin: 0 0 16px
    }

    .grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 12px
    }

    .btn {
      display: block;
      text-align: center;
      padding: 16px;
      border-radius: 12px;
      border: 1px solid #2563eb;
      background: #2563eb;
      color: #fff;
      text-decoration: none;
      font-weight: 600
    }

    .btn.secondary {
      background: #fff;
      border-color: #ddd;
      color: #333
    }

    .foot {
      padding: 16px 20px;
      border-top: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center
    }

    .small {
      font-size: 12px;
      color: #999
    }

    .logout {
      background: #dc2626;
      border: 1px solid #dc2626;
      color: #fff;
      padding: 10px 14px;
      border-radius: 10px;
      text-decoration: none
    }
  </style>
</head>

<body>
  <div class="card">
    <div class="head">
      <div>🏦 Pilih Modul </div>
      <a class="logout" href="logout.php">Logout</a>
    </div>
    <div class="body">
      <p class="hello">User: <strong><?= htmlspecialchars($username) ?></strong> · KC:
        <strong><?= htmlspecialchars($nama_kc) ?></strong> · Kode:
        <strong><?= htmlspecialchars($kode_kantor) ?></strong>
        <?php if ($kode_kantor === '000'): ?>
          <span style="background:#10b981;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;margin-left:4px">ADMIN</span>
        <?php endif; ?>
      </p>
      <div class="grid">
        <a class="btn" href="ui/voucher_capture.php">💳 Voucher Capture</a>
        <a class="btn" href="ui/capture_batch.php">🏠 Agunan Capture</a>
        <a class="btn secondary" href="ui/voucher_history.php">💳 Riwayat Voucher</a>
        <a class="btn secondary" href="ui/history.php">📄 Riwayat Agunan</a>

        <!-- <a class="btn secondary" href="ui/capture.php">📸 Kamera Single (lama)</a> -->
        <!-- <a class="btn secondary" href="form.php">📝 Form Klasik (upload file)</a> -->
      </div>
      <p class="small" style="margin-top:16px">Saran: untuk HP gunakan Kamera (UI baru). Torch/flash hanya didukung
        sebagian perangkat.</p>
    </div>
    <div class="foot">
      <span class="small">© <?= date('Y') ?> · Module Capture</span>
      <span class="small">Versi UI Mobile</span>
    </div>
  </div>

  <script>
    // Request notification permission saat halaman load
    if ('Notification' in window && Notification.permission === 'default') {
      Notification.requestPermission().then(permission => {
        console.log('Notification permission:', permission);
      });
    }
  </script>
</body>

</html>