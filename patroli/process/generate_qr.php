<?php
// Enable error logging
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// CRITICAL SECURITY: Load security guard
require_once '../security_guard.php';

// Additional check for admin role
if ($_SESSION['role'] !== 'admin_pusat') {
    echo json_encode(['success' => false, 'message' => 'Admin only']);
    exit;
}

require_once '../config_patroli.php';

header('Content-Type: application/json');

$kode_kantor = $_POST['kode_kantor'] ?? '';
$jumlah_ruangan = (int)($_POST['jumlah_ruangan'] ?? 0);
$prefix_nama = $_POST['prefix_nama'] ?? '';
$created_by = $_SESSION['username'] ?? 'admin';

if (empty($kode_kantor) || $jumlah_ruangan < 1 || $jumlah_ruangan > 20) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

// Create folder for QR codes if not exists
$qr_folder = __DIR__ . '/../qr-codes/' . $kode_kantor;
if (!is_dir($qr_folder)) {
    mkdir($qr_folder, 0755, true);
}

$generated = 0;
$skipped = 0;
$errors = [];

for ($i = 1; $i <= $jumlah_ruangan; $i++) {
    $kode_ruangan = 'R' . str_pad($i, 2, '0', STR_PAD_LEFT); // R01, R02, etc.
    $nama_ruangan = empty($prefix_nama) ? $kode_ruangan : $prefix_nama . ' ' . str_pad($i, 2, '0', STR_PAD_LEFT);
    $qr_content = $kode_kantor . '-' . $kode_ruangan; // Format: 028-R01
    $qr_filename = $kode_ruangan . '.png';
    $qr_path = $qr_folder . '/' . $qr_filename;
    $qr_path_relative = 'qr-codes/' . $kode_kantor . '/' . $qr_filename;
    
    // Check if already exists
    $stmt_check = $conn_patroli->prepare("SELECT id FROM ruangan WHERE kode_kantor=? AND kode_ruangan=?");
    $stmt_check->bind_param('ss', $kode_kantor, $kode_ruangan);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $skipped++;
        $stmt_check->close();
        continue;
    }
    $stmt_check->close();
    
    // Generate QR Code using QR Server API (more reliable)
    try {
        $qr_size = 500;
        // Using qrserver.com API - free and reliable
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size={$qr_size}x{$qr_size}&data=" . urlencode($qr_content);
        
        // Download QR image with timeout and retry
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'Mozilla/5.0',
                'ignore_errors' => true
            ]
        ]);
        
        $max_retries = 3;
        $retry_count = 0;
        $qr_image_data = false;
        
        while ($retry_count < $max_retries && $qr_image_data === false) {
            $qr_image_data = @file_get_contents($qr_url, false, $context);
            if ($qr_image_data === false) {
                $retry_count++;
                if ($retry_count < $max_retries) {
                    usleep(500000); // Wait 0.5 second before retry
                }
            }
        }
        
        if ($qr_image_data === false) {
            $errors[] = "Gagal download QR $kode_ruangan setelah $max_retries percobaan";
            continue;
        }
        
        // Validate image data
        if (strlen($qr_image_data) < 100) {
            $errors[] = "QR $kode_ruangan terlalu kecil (invalid)";
            continue;
        }
        
        // Save QR image
        if (file_put_contents($qr_path, $qr_image_data) === false) {
            $errors[] = "Gagal menyimpan QR $kode_ruangan";
            continue;
        }
        
        // Verify file saved
        if (!file_exists($qr_path)) {
            $errors[] = "File QR $kode_ruangan tidak tersimpan";
            continue;
        }
        
        // Insert to database
        $stmt_insert = $conn_patroli->prepare("INSERT INTO ruangan (kode_kantor, kode_ruangan, nama_ruangan, qr_code_content, qr_code_image_path, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param('ssssss', $kode_kantor, $kode_ruangan, $nama_ruangan, $qr_content, $qr_path_relative, $created_by);
        
        if ($stmt_insert->execute()) {
            $generated++;
            // Small delay to avoid rate limiting
            usleep(200000); // 0.2 second
        } else {
            $errors[] = "Gagal insert $kode_ruangan: " . $stmt_insert->error;
        }
        $stmt_insert->close();
        
    } catch (Exception $e) {
        $errors[] = "Gagal generate QR $kode_ruangan: " . $e->getMessage();
    }
}

if ($generated > 0 || $skipped > 0) {
    echo json_encode([
        'success' => true,
        'message' => "Berhasil generate $generated ruangan" . ($skipped > 0 ? ", $skipped sudah ada sebelumnya" : ""),
        'generated' => $generated,
        'skipped' => $skipped,
        'errors' => $errors
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Tidak ada ruangan yang di-generate',
        'errors' => $errors
    ]);
}
