<?php
session_start();
header('Content-Type: application/json');

echo json_encode([
    'session_active' => isset($_SESSION['login']),
    'user_id' => $_SESSION['user_id'] ?? null,
    'username' => $_SESSION['username'] ?? null,
    'kode_kantor' => $_SESSION['kode_kantor'] ?? null,
    'nama_kc' => $_SESSION['nama_kc'] ?? null
]);
?>
