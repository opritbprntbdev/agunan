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
header("Location: index.php");
exit;
?>
