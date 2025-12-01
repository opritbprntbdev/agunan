<?php
session_start();

// Update logout time dan session duration jika ada login history
if (isset($_SESSION['login_history_id']) && isset($_SESSION['login_time'])) {
    require 'config.php';
    
    $history_id = (int)$_SESSION['login_history_id'];
    $duration = time() - $_SESSION['login_time']; // durasi dalam detik
    
    $stmt = $conn->prepare("
        UPDATE user_login_history 
        SET logout_at = NOW(), session_duration = ? 
        WHERE id = ?
    ");
    $stmt->bind_param('ii', $duration, $history_id);
    $stmt->execute();
    $stmt->close();
}

session_destroy();

// Cek alasan logout
$reason = isset($_GET['reason']) ? $_GET['reason'] : '';
$message = '';

if ($reason === 'browser_access') {
    $message = 'Akses via browser diblokir. Silakan gunakan aplikasi yang sudah terinstall atau install aplikasi terlebih dahulu.';
}

header("Location: index.php" . ($message ? "?error=" . urlencode($message) : ""));
exit;
?>
