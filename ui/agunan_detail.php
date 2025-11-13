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

// Query data agunan dengan permission check
if ($kode_kantor === '000') {
    // Admin KPNO - bisa lihat semua
    $sql = "SELECT * FROM agunan_data WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
} else {
    // Cabang - hanya data kantornya
    $sql = "SELECT * FROM agunan_data WHERE id = ? AND kode_kantor = ?";
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
$stmt = $conn->prepare('SELECT * FROM agunan_foto WHERE agunan_data_id = ? ORDER BY foto_order ASC, id ASC');
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
    <title>Detail Agunan - <?= htmlspecialchars($data['id_agunan']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #212529;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #667eea;
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
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            padding: 16px 24px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: #495057;
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
            color: #667eea;
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
            text-align: center;
            font-weight: 500;
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
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
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
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
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
            <h1>📋 Detail Agunan</h1>
            <div class="subtitle">Informasi Lengkap dan Dokumentasi Foto</div>
        </div>
    </div>
    
    <div class="container">
        <a href="history.php" class="back-btn">
            ← Kembali ke Riwayat
        </a>
        
        <!-- Info Agunan -->
        <div class="card">
            <div class="card-header">
                <h2>🏦 Informasi Agunan</h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">ID Agunan</div>
                        <div class="info-value highlight"><?= htmlspecialchars($data['id_agunan']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nama Nasabah</div>
                        <div class="info-value"><?= htmlspecialchars($data['nama_nasabah']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">No. Rekening</div>
                        <div class="info-value"><?= htmlspecialchars($data['no_rek']) ?></div>
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
                <h3 style="margin-bottom: 16px; color: #495057; font-size: 16px;">📊 Data dari IBS</h3>
                <div class="info-grid">
                    <?php if (!empty($verified_data['cif'])): ?>
                    <div class="info-item">
                        <div class="info-label">CIF</div>
                        <div class="info-value"><?= htmlspecialchars($verified_data['cif']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($verified_data['nama_nasabah'])): ?>
                    <div class="info-item">
                        <div class="info-label">Nama Nasabah (IBS)</div>
                        <div class="info-value"><?= htmlspecialchars($verified_data['nama_nasabah']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($verified_data['alamat'])): ?>
                    <div class="info-item">
                        <div class="info-label">Alamat</div>
                        <div class="info-value"><?= htmlspecialchars($verified_data['alamat']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($verified_data['jenis_agunan'])): ?>
                    <div class="info-item">
                        <div class="info-label">Jenis Agunan</div>
                        <div class="info-value">
                            <span class="badge badge-info"><?= htmlspecialchars($verified_data['jenis_agunan']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($verified_data['deskripsi_ringkas'])): ?>
                    <div class="info-item">
                        <div class="info-label">Deskripsi</div>
                        <div class="info-value"><?= htmlspecialchars($verified_data['deskripsi_ringkas']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Detail Tanah
                    if (!empty($verified_data['tanah_no_shm']) || !empty($verified_data['tanah_no_shgb'])): 
                    ?>
                    <div class="info-item">
                        <div class="info-label">No. Sertifikat</div>
                        <div class="info-value">
                            <?php if (!empty($verified_data['tanah_no_shm'])): ?>
                                SHM: <?= htmlspecialchars($verified_data['tanah_no_shm']) ?>
                            <?php endif; ?>
                            <?php if (!empty($verified_data['tanah_no_shgb'])): ?>
                                SHGB: <?= htmlspecialchars($verified_data['tanah_no_shgb']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($verified_data['tanah_luas'])): ?>
                    <div class="info-item">
                        <div class="info-label">Luas Tanah</div>
                        <div class="info-value"><?= htmlspecialchars($verified_data['tanah_luas']) ?> m²</div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($verified_data['tanah_lokasi'])): ?>
                    <div class="info-item">
                        <div class="info-label">Lokasi</div>
                        <div class="info-value"><?= htmlspecialchars($verified_data['tanah_lokasi']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Detail Kendaraan
                    if (!empty($verified_data['kend_no_polisi'])): 
                    ?>
                    <div class="info-item">
                        <div class="info-label">No. Polisi</div>
                        <div class="info-value"><?= htmlspecialchars($verified_data['kend_no_polisi']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($verified_data['kend_merk'])): ?>
                    <div class="info-item">
                        <div class="info-label">Merk Kendaraan</div>
                        <div class="info-value"><?= htmlspecialchars($verified_data['kend_merk']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($verified_data['kend_tahun'])): ?>
                    <div class="info-item">
                        <div class="info-label">Tahun</div>
                        <div class="info-value"><?= htmlspecialchars($verified_data['kend_tahun']) ?></div>
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
                                    Foto <?= $idx + 1 ?>
                                    <?php if (!empty($photo['keterangan'])): ?>
                                        <br><small><?= htmlspecialchars($photo['keterangan']) ?></small>
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
