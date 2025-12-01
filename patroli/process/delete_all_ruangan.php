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

$kode_kantor = $_POST['kode_kantor'] ?? '';

if (empty($kode_kantor)) {
    echo json_encode(['success' => false, 'message' => 'Kode kantor tidak valid']);
    exit;
}

// Get all ruangan for this kantor
$stmt = $conn_patroli->prepare("SELECT id, kode_ruangan, qr_code_image_path FROM ruangan WHERE kode_kantor=?");
$stmt->bind_param('s', $kode_kantor);
$stmt->execute();
$result = $stmt->get_result();

$deleted_db = 0;
$deleted_files = 0;

while ($row = $result->fetch_assoc()) {
    // Delete file
    $qr_file = __DIR__ . '/../' . $row['qr_code_image_path'];
    if (file_exists($qr_file)) {
        if (unlink($qr_file)) {
            $deleted_files++;
        }
    }
}
$stmt->close();

// Delete from database
$stmt_delete = $conn_patroli->prepare("DELETE FROM ruangan WHERE kode_kantor=?");
$stmt_delete->bind_param('s', $kode_kantor);

if ($stmt_delete->execute()) {
    $deleted_db = $stmt_delete->affected_rows;
    echo json_encode([
        'success' => true, 
        'message' => "Berhasil menghapus $deleted_db ruangan",
        'deleted' => $deleted_db,
        'deleted_files' => $deleted_files
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus dari database: ' . $stmt_delete->error]);
}

$stmt_delete->close();
