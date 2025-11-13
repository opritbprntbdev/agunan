<?php
/**
 * Delete Agunan Data
 * Hapus data agunan beserta semua foto dan PDF
 */

header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

$kode_kantor = $_SESSION['kode_kantor'] ?? '000';

try {
    // Get data agunan
    $stmt = $conn->prepare("SELECT * FROM agunan_data WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $agunan = $result->fetch_assoc();
    $stmt->close();
    
    if (!$agunan) {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
        exit;
    }
    
    // Cek permission - cabang hanya bisa hapus data kantornya sendiri
    if ($kode_kantor !== '000' && $agunan['kode_kantor'] !== $kode_kantor) {
        echo json_encode(['success' => false, 'message' => 'Anda tidak punya akses untuk menghapus data ini']);
        exit;
    }
    
    // Get all photos
    $stmt = $conn->prepare("SELECT foto_path FROM agunan_foto WHERE agunan_data_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $photos = [];
    while ($row = $result->fetch_assoc()) {
        $photos[] = $row['foto_path'];
    }
    $stmt->close();
    
    // Delete photos from filesystem
    $deleted_files = 0;
    foreach ($photos as $photo_path) {
        $full_path = __DIR__ . '/../' . $photo_path;
        if (file_exists($full_path)) {
            if (unlink($full_path)) {
                $deleted_files++;
            }
        }
    }
    
    // Delete PDF if exists
    if (!empty($agunan['pdf_path'])) {
        $pdf_full_path = __DIR__ . '/../' . $agunan['pdf_path'];
        if (file_exists($pdf_full_path)) {
            @unlink($pdf_full_path);
        }
    }
    
    // Delete from database
    $conn->begin_transaction();
    
    // Delete photos records
    $stmt = $conn->prepare("DELETE FROM agunan_foto WHERE agunan_data_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    
    // Delete agunan data
    $stmt = $conn->prepare("DELETE FROM agunan_data WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Data berhasil dihapus',
        'deleted_photos' => $deleted_files,
        'total_photos' => count($photos)
    ]);
    
} catch (Exception $e) {
    if ($conn->errno) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
