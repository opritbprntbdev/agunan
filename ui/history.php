<?php
session_start();
if (!isset($_SESSION['login'])) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/../config.php';

$rows = [];
$res = $conn->query("SELECT * FROM vw_agunan_complete ORDER BY created_at DESC LIMIT 200");
if ($res) { while($r = $res->fetch_assoc()) $rows[] = $r; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Riwayat Batch</title>
  <style>
    :root{color-scheme:light dark}
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#0b0e14;color:#e6edf3}
    header{padding:12px 16px;background:#0d3d88;color:#fff;display:flex;justify-content:space-between;align-items:center}
    .wrap{padding:12px;max-width:820px;margin:0 auto}
    .actions a{display:inline-block;text-decoration:none}
    .btn{padding:12px 14px;border-radius:12px;border:1px solid #355cac;background:#1e3a8a;color:#fff;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px}
    .btn.secondary{background:#0b1220;border-color:#334155;color:#cbd5e1}
    .badge{padding:4px 8px;border-radius:999px;background:#0b1220;border:1px solid #334155;font-size:12px}
    .list{display:grid;grid-template-columns:1fr;gap:12px}
    .item{background:#0f172a;border:1px solid #24304a;border-radius:14px;overflow:hidden}
    .row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .item .head{padding:12px;border-bottom:1px solid #24304a}
    .title{font-weight:700}
    .sub{color:#94a3b8;font-size:13px}
    .meta{padding:0 12px 12px 12px;display:flex;gap:8px;flex-wrap:wrap}
    .foot{padding:12px;border-top:1px solid #24304a}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:8px}
    .empty{padding:24px;text-align:center;color:#94a3b8}
    @media (min-width:720px){ .grid2{grid-template-columns:140px 140px;justify-content:end} }
  </style>
  </head>
  <body>
    <header>
      <div>📄 Riwayat</div>
      <nav class="row">
        <a class="btn secondary" href="capture_batch.php">➕ Batch Baru</a>
        <a class="btn secondary" href="../home.php">🏠 Home</a>
      </nav>
    </header>
    <div class="wrap">
      <?php if (!$rows): ?>
        <div class="item"><div class="empty">Belum ada data</div></div>
      <?php else: ?>
        <div class="list">
          <?php foreach($rows as $r): ?>
            <div class="item">
              <div class="head">
                <div class="title">ID Agunan: <?= htmlspecialchars($r['id_agunan']) ?></div>
                <div class="sub">Nama: <?= htmlspecialchars($r['nama_nasabah']) ?> · No Rek: <?= htmlspecialchars($r['no_rek']) ?></div>
              </div>
              <div class="meta">
                <span class="badge"><?= (int)$r['jumlah_foto_aktual'] ?> foto</span>
                <span class="badge">Dibuat: <?= htmlspecialchars($r['created_at']) ?></span>
              </div>
              <div class="foot">
                <?php if (!empty($r['pdf_path'])): ?>
                  <div class="grid2">
                    <a class="btn" target="_blank" href="../<?= htmlspecialchars($r['pdf_path']) ?>">👁️ Preview</a>
                    <a class="btn secondary" href="../<?= htmlspecialchars($r['pdf_path']) ?>" download>⬇️ Download</a>
                  </div>
                <?php else: ?>
                  <span class="badge">PDF belum dibuat</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </body>
  </html>
