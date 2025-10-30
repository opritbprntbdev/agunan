<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: index.php');
    exit;
}
$nama_kc = $_SESSION['nama_kc'] ?? '';
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Agunan Capture - Home</title>
  <style>
    :root { color-scheme: light dark; }
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#0b0e14;color:#e6edf3;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
    .card{width:100%;max-width:520px;background:#0f172a;border:1px solid #24304a;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.35)}
    .head{background:#0d3d88;color:#fff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between}
    .body{padding:20px}
    .hello{font-size:14px;color:#cbd5e1;margin:0 0 16px}
    .grid{display:grid;grid-template-columns:1fr;gap:12px}
    .btn{display:block;text-align:center;padding:16px;border-radius:12px;border:1px solid #355cac;background:#1e3a8a;color:#fff;text-decoration:none;font-weight:600}
    .btn.secondary{background:#0b1220;border-color:#334155;color:#cbd5e1}
    .foot{padding:16px 20px;border-top:1px solid #24304a;display:flex;justify-content:space-between;align-items:center}
    .small{font-size:12px;color:#94a3b8}
    .logout{background:#b91c1c;border:1px solid #7f1d1d;color:#fff;padding:10px 14px;border-radius:10px;text-decoration:none}
  </style>
  </head>
  <body>
    <div class="card">
      <div class="head">
        <div>🏦 Agunan Capture</div>
        <a class="logout" href="logout.php">Logout</a>
      </div>
      <div class="body">
        <p class="hello">User: <strong><?= htmlspecialchars($username) ?></strong> · KC: <strong><?= htmlspecialchars($nama_kc) ?></strong></p>
        <div class="grid">
          <a class="btn" href="ui/capture_batch.php">� Batch Capture (disarankan)</a>
          <a class="btn secondary" href="ui/history.php">📄 Riwayat (Preview/Download PDF)</a>
          <!-- <a class="btn secondary" href="ui/capture.php">📸 Kamera Single (lama)</a> -->
          <!-- <a class="btn secondary" href="form.php">📝 Form Klasik (upload file)</a> -->
        </div>
        <p class="small" style="margin-top:16px">Saran: untuk HP gunakan Kamera (UI baru). Torch/flash hanya didukung sebagian perangkat.</p>
      </div>
      <div class="foot">
        <span class="small">© <?= date('Y') ?> · Agunan Capture</span>
        <span class="small">Versi UI Mobile</span>
      </div>
    </div>
  </body>
  </html>
