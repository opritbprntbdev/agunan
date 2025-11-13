<?php
session_start();
if (!isset($_SESSION['login'])) { 
    header('Location: ../index.php'); 
    exit; 
}

require_once __DIR__ . '/../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    die('ID tidak valid');
}

$kode_kantor = $_SESSION['kode_kantor'] ?? '000';

// Query data voucher dengan permission check
if ($kode_kantor === '000') {
    $sql = "SELECT * FROM voucher_data WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
} else {
    $sql = "SELECT * FROM voucher_data WHERE id = ? AND kode_kantor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $id, $kode_kantor);
}

$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    die('Data tidak ditemukan atau akses ditolak');
}

// Ambil semua foto
$stmt = $conn->prepare('SELECT * FROM voucher_foto WHERE voucher_data_id = ? ORDER BY foto_order ASC, id ASC');
$stmt->bind_param('i', $id);
$stmt->execute();
$photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Parse verified_data JSON jika ada
$verified_data = null;
if (!empty($data['verified_data'])) {
    $verified_data = json_decode($data['verified_data'], true);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Voucher - <?= htmlspecialchars($data['trans_id']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #212529;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .header .subtitle {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: white;
            color: #f5576c;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 24px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(to right, #fff5f7, #ffe5e9);
            padding: 16px 24px;
            border-bottom: 2px solid #ffccd5;
        }
        
        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: #c92a4a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .card-body {
            padding: 24px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        
        .info-value {
            font-size: 15px;
            color: #212529;
            font-weight: 500;
        }
        
        .info-value.highlight {
            color: #f5576c;
            font-size: 18px;
            font-weight: 600;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #dee2e6, transparent);
            margin: 24px 0;
        }
        
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
        }
        
        .photo-item {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .photo-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        
        .photo-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .photo-caption {
            padding: 12px;
            background: #f8f9fa;
            font-size: 13px;
            color: #495057;
        }
        
        .photo-caption strong {
            display: block;
            color: #212529;
            margin-bottom: 4px;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #f5576c;
            color: white;
        }
        
        .btn-primary:hover {
            background: #e94560;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(245, 87, 108, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .empty {
            text-align: center;
            padding: 48px 24px;
            color: #6c757d;
        }
        
        @media print {
            .back-btn, .actions, .header { display: none; }
            body { background: white; }
            .card { box-shadow: none; break-inside: avoid; }
        }
        
        @media (max-width: 768px) {
            .container { padding: 16px; }
            .card-body { padding: 16px; }
            .info-grid { grid-template-columns: 1fr; }
            .photo-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🎫 Detail Voucher</h1>
            <div class="subtitle">Informasi Lengkap Transaksi dan Dokumentasi Foto</div>
        </div>
    </div>
    
    <div class="container">
        <a href="voucher_history.php" class="back-btn">
            ← Kembali ke Riwayat
        </a>
        
        <!-- Info Voucher -->
        <div class="card">
            <div class="card-header">
                <h2>💳 Informasi Transaksi</h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Transaction ID</div>
                        <div class="info-value highlight"><?= htmlspecialchars($data['trans_id']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nama Nasabah</div>
                        <div class="info-value"><?= htmlspecialchars($data['nama_nasabah']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nomor Referensi</div>
                        <div class="info-value"><?= htmlspecialchars($data['nomor_referensi']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kode Kantor</div>
                        <div class="info-value"><?= htmlspecialchars($data['kode_kantor']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tanggal Capture</div>
                        <div class="info-value"><?= date('d M Y H:i', strtotime($data['created_at'])) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Jumlah Foto</div>
                        <div class="info-value">
                            <span class="badge badge-success"><?= count($photos) ?> Foto</span>
                        </div>
                    </div>
                </div>
                
                <?php if ($verified_data): ?>
                <div class="divider"></div>
                <h3 style="margin-bottom: 16px; color: #c92a4a; font-size: 16px;">📊 Data dari IBS</h3>
                <div class="info-grid">
                    <?php if (!empty($verified_data['no_rekening'])): ?>
                    <div class="info-item">
                        <div class="info-label">No. Rekening</div>
                        <div class="info-value"><?= htmlspecialchars($verified_data['no_rekening']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($verified_data['nama_nasabah'])): ?>
                    <div class="info-item">
                        <div class="info-label">Nama Nasabah (IBS)</div>
                        <div class="info-value"><?= htmlspecialchars($verified_data['nama_nasabah']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($verified_data['tgl_transaksi'])): ?>
                    <div class="info-item">
                        <div class="info-label">Tanggal Transaksi</div>
                        <div class="info-value"><?= htmlspecialchars($verified_data['tgl_transaksi']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($verified_data['jenis_transaksi'])): ?>
                    <div class="info-item">
                        <div class="info-label">Jenis Transaksi</div>
                        <div class="info-value">
                            <span class="badge badge-info"><?= htmlspecialchars($verified_data['jenis_transaksi']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card">
            <div class="card-body">
                <div class="actions">
                    <?php if (!empty($data['pdf_path'])): ?>
                    <a href="../<?= htmlspecialchars($data['pdf_path']) ?>" target="_blank" class="btn btn-primary">
                        👁️ Preview PDF
                    </a>
                    <a href="../<?= htmlspecialchars($data['pdf_path']) ?>" download class="btn btn-secondary">
                        ⬇️ Download PDF
                    </a>
                    <?php endif; ?>
                    <button onclick="window.print()" class="btn btn-secondary">
                        🖨️ Print Halaman
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Foto-foto -->
        <div class="card">
            <div class="card-header">
                <h2>📸 Dokumentasi Foto (<?= count($photos) ?>)</h2>
            </div>
            <div class="card-body">
                <?php if (empty($photos)): ?>
                    <div class="empty">
                        <p>Tidak ada foto</p>
                    </div>
                <?php else: ?>
                    <div class="photo-grid">
                        <?php foreach ($photos as $idx => $photo): ?>
                            <div class="photo-item">
                                <img src="../<?= htmlspecialchars($photo['foto_path']) ?>" 
                                     alt="Foto <?= $idx + 1 ?>"
                                     loading="lazy">
                                <div class="photo-caption">
                                    <strong>Foto <?= $idx + 1 ?></strong>
                                    <?php if (!empty($photo['keterangan'])): ?>
                                        <?= htmlspecialchars($photo['keterangan']) ?>
                                    <?php else: ?>
                                        <span style="color: #999;">Tanpa keterangan</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
