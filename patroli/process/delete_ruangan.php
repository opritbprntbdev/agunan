<?php
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

$id = (int)($_POST['id'] ?? 0);

if ($id < 1) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

// Get file path before delete
$stmt_get = $conn_patroli->prepare("SELECT qr_code_image_path FROM ruangan WHERE id=?");
$stmt_get->bind_param('i', $id);
$stmt_get->execute();
$result_get = $stmt_get->get_result();
$row = $result_get->fetch_assoc();
$stmt_get->close();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Ruangan tidak ditemukan']);
    exit;
}

// Delete from database
$stmt_delete = $conn_patroli->prepare("DELETE FROM ruangan WHERE id=?");
$stmt_delete->bind_param('i', $id);

if ($stmt_delete->execute()) {
    // Delete QR image file
    $qr_file = __DIR__ . '/../' . $row['qr_code_image_path'];
    if (file_exists($qr_file)) {
        unlink($qr_file);
    }
    
    echo json_encode(['success' => true, 'message' => 'Ruangan berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus ruangan: ' . $stmt_delete->error]);
}

$stmt_delete->close();
