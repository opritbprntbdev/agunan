<?php
/**
 * Get PDF Path - Simple API
 * Ambil pdf_path dari database lokal berdasarkan no_bukti
 */

header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config.php';

// Cek login
if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ambil no_bukti dari request
$no_bukti = isset($_POST['no_bukti']) ? trim($_POST['no_bukti']) : '';

if (empty($no_bukti)) {
    echo json_encode(['success' => false, 'message' => 'No bukti tidak boleh kosong']);
    exit;
}

try {
    // Query ke database lokal
    $stmt = $conn->prepare("SELECT pdf_path, pdf_filename FROM voucher_data WHERE no_bukti = ? AND pdf_path IS NOT NULL AND pdf_path != ''");
    $stmt->bind_param('s', $no_bukti);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Cek apakah file PDF benar-benar ada
        $fullPath = __DIR__ . '/../' . $row['pdf_path'];
        
        if (file_exists($fullPath)) {
            echo json_encode([
                'success' => true,
                'pdf_path' => $row['pdf_path'],
                'pdf_filename' => $row['pdf_filename']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'File PDF tidak ditemukan di server'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Voucher belum di-finalize atau PDF belum dibuat'
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
