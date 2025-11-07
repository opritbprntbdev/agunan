<?php
session_start();
require 'config.php';

// Cegah akses langsung tanpa POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];

// Get IP dan User Agent
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

// Cari user dengan username dan password plain text
$stmt = $conn->prepare("SELECT * FROM user WHERE username=? AND password=?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    // Login berhasil
    $_SESSION['login'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama_kc'] = $user['nama_kc'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['kode_kantor'] = $user['kode_kantor']; // TAMBAH: Simpan kode kantor di session
    $_SESSION['login_time'] = time(); // untuk hitung durasi session
    
    $user_id = $user['id'];
    
    // Update tabel user: last_login_at, last_login_ip, increment login_count
    $stmt_update = $conn->prepare("
        UPDATE user 
        SET last_login_at = NOW(), 
            last_login_ip = ?, 
            login_count = login_count + 1 
        WHERE id = ?
    ");
    $stmt_update->bind_param('si', $ip_address, $user_id);
    $stmt_update->execute();
    $stmt_update->close();
    
    // Insert ke user_login_history
    $stmt_history = $conn->prepare("
        INSERT INTO user_login_history 
        (user_id, username, login_at, ip_address, user_agent, login_status) 
        VALUES (?, ?, NOW(), ?, ?, 'success')
    ");
    $stmt_history->bind_param('isss', $user_id, $username, $ip_address, $user_agent);
    $stmt_history->execute();
    
    // Simpan history_id di session untuk update logout nanti
    $_SESSION['login_history_id'] = $stmt_history->insert_id;
    $stmt_history->close();
    
    header("Location: home.php");
    
} else {
    // Login gagal - log failed attempt
    $stmt_failed = $conn->prepare("
        INSERT INTO user_login_history 
        (user_id, username, login_at, ip_address, user_agent, login_status) 
        VALUES (0, ?, NOW(), ?, ?, 'failed')
    ");
    $stmt_failed->bind_param('sss', $username, $ip_address, $user_agent);
    $stmt_failed->execute();
    $stmt_failed->close();
    
    header("Location: index.php?error=Login gagal!");
}

$stmt->close();
exit;
?>