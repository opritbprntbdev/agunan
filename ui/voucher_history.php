<?php
session_start();
if (!isset($_SESSION['login'])) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/../config.php';

$kode_kantor = $_SESSION['kode_kantor'] ?? '000';

$rows = [];

// Admin KPNO (000) bisa lihat semua data
// Cabang (001-044) hanya lihat data kantornya sendiri
if ($kode_kantor === '000') {
    // Pusat - lihat semua dengan jumlah foto
    $sql = "SELECT vd.*, 
            (SELECT COUNT(*) FROM voucher_foto vf WHERE vf.voucher_data_id = vd.id) as jumlah_foto_aktual
            FROM voucher_data vd 
            ORDER BY vd.created_at DESC 
            LIMIT 200";
    $res = $conn->query($sql);
} else {
    // Cabang - filter by kode_kantor dengan jumlah foto
    $sql = "SELECT vd.*, 
            (SELECT COUNT(*) FROM voucher_foto vf WHERE vf.voucher_data_id = vd.id) as jumlah_foto_aktual
            FROM voucher_data vd 
            WHERE vd.kode_kantor = ? 
            ORDER BY vd.created_at DESC 
            LIMIT 200";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $kode_kantor);
    $stmt->execute();
    $res = $stmt->get_result();
}

if ($res) { while($r = $res->fetch_assoc()) $rows[] = $r; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Riwayat Voucher</title>
  <link rel="manifest" href="../manifest.json">
  <meta name="theme-color" content="#16a34a">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <style>
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f5f5f5;color:#333}
    header{padding:12px 16px;background:#16a34a;color:#fff;display:flex;justify-content:space-between;align-items:center}
    .wrap{padding:12px;max-width:820px;margin:0 auto}
    .actions a{display:inline-block;text-decoration:none}
    .btn{padding:12px 14px;border-radius:12px;border:1px solid #16a34a;background:#16a34a;color:#fff;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px}
    .btn.secondary{background:#fff;border-color:#ddd;color:#333}
    .badge{padding:4px 8px;border-radius:999px;background:#f5f5f5;border:1px solid #ddd;font-size:12px;color:#666}
    .list{display:grid;grid-template-columns:1fr;gap:12px}
    .item{background:#fff;border:1px solid #ddd;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.1)}
    .row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .item .head{padding:12px;border-bottom:1px solid #eee}
    .title{font-weight:700;color:#333}
    .sub{color:#666;font-size:13px}
    .meta{padding:0 12px 12px 12px;display:flex;gap:8px;flex-wrap:wrap}
    .foot{padding:12px;border-top:1px solid #eee}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:8px}
    .grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px}
    .btn.danger{background:#dc2626;border-color:#dc2626}
    .empty{padding:24px;text-align:center;color:#999}
    @media (min-width:720px){ .grid2{grid-template-columns:140px 140px;justify-content:end} .grid3{grid-template-columns:140px 140px 120px;justify-content:end} }
  </style>
  </head>
  <body>
    <header>
      <div>💳 Riwayat Voucher</div>
      <nav class="row">
        <a class="btn secondary" href="voucher_capture.php">➕ Voucher Baru</a>
        <a class="btn secondary" href="../home.php">🏠 Home</a>
      </nav>
    </header>
    <div class="wrap">
      <?php if (!$rows): ?>
        <div class="item"><div class="empty">Belum ada data voucher</div></div>
      <?php else: ?>
        <div class="list">
          <?php foreach($rows as $r): ?>
            <div class="item">
              <div class="head">
                <div class="title">Trans ID: <?= htmlspecialchars($r['trans_id']) ?></div>
                <div class="sub">
                  No Bukti: <?= htmlspecialchars($r['no_bukti'] ?: '-') ?> · 
                  Tanggal: <?= htmlspecialchars($r['tgl_trans']) ?>
                </div>
                <div class="sub">Uraian: <?= htmlspecialchars($r['uraian']) ?></div>
              </div>
              <div class="meta">
                <span class="badge"><?= (int)$r['jumlah_foto_aktual'] ?> foto</span>
                <span class="badge">Kantor: <?= htmlspecialchars($r['nama_kantor'] ?: $r['kode_kantor']) ?></span>
                <span class="badge">Dibuat: <?= htmlspecialchars($r['created_at']) ?></span>
                <?php if ($kode_kantor === '000'): ?>
                  <span class="badge" style="background:#dcfce7;color:#16a34a">Admin View</span>
                <?php endif; ?>
              </div>
              <div class="foot">
                <?php if (!empty($r['pdf_path'])): ?>
                  <div class="grid3">
                    <a class="btn" href="voucher_detail.php?id=<?= $r['id'] ?>">📋 Detail</a>
                    <a class="btn secondary" target="_blank" href="../<?= htmlspecialchars($r['pdf_path']) ?>">👁️ PDF</a>
                    <button class="btn danger" onclick="hapusData(<?= $r['id'] ?>, '<?= htmlspecialchars($r['trans_id'], ENT_QUOTES) ?>')">🗑️ Hapus</button>
                  </div>
                <?php else: ?>
                  <div class="grid2">
                    <span class="badge">PDF belum dibuat</span>
                    <button class="btn danger" onclick="hapusData(<?= $r['id'] ?>, '<?= htmlspecialchars($r['trans_id'], ENT_QUOTES) ?>')">🗑️ Hapus</button>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <script>
    function hapusData(id, transId) {
      if (!confirm('Hapus data voucher "' + transId + '"?\n\nSemua foto dan PDF akan dihapus permanent!')) return;
      
      fetch('../process/delete_voucher.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          alert('✅ Data berhasil dihapus');
          location.reload();
        } else {
          alert('❌ Gagal hapus: ' + data.message);
        }
      })
      .catch(err => {
        alert('❌ Error: ' + err.message);
      });
    }
    </script>
  </body>
  </html>
