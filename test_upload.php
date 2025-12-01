<?php
ob_start();
session_start();
require_once __DIR__ . '/config.php';
ob_end_clean();

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Test upload endpoint OK',
    'session_login' => isset($_SESSION['login']),
    'conn_local' => $conn ? 'OK' : 'FAIL',
    'conn_ibs' => $conn_dbibs ? 'OK' : 'FAIL',
    'post_data' => $_POST,
    'files' => isset($_FILES['image']) ? 'Image uploaded' : 'No image'
]);
?>
