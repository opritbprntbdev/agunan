<?php
session_start();

// Enable error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// ============================================
// STEP 1: Cek di DB agunan_capture (user biasa)
// ============================================
$stmt = $conn->prepare("SELECT * FROM user WHERE username=? AND password=?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user) {
    // Login berhasil sebagai USER AGUNAN/VOUCHER
    $_SESSION['login'] = true;
    $_SESSION['user_type'] = 'agunan_voucher'; // Flag: user agunan/voucher
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama_kc'] = $user['nama_kc'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['kode_kantor'] = $user['kode_kantor'];
    $_SESSION['login_time'] = time();
    
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
    
    // Redirect ke home agunan/voucher
    header("Location: home.php");
    exit;
}

// ============================================
// STEP 2: Cek di DB patroli_security (penjaga/admin)
// ============================================
$stmt_patroli = $conn_patroli->prepare("SELECT * FROM users_security WHERE username=? AND password=? AND is_active=1");
$stmt_patroli->bind_param("ss", $username, $password);
$stmt_patroli->execute();
$result_patroli = $stmt_patroli->get_result();
$user_patroli = $result_patroli->fetch_assoc();
$stmt_patroli->close();

if ($user_patroli) {
    // Login berhasil sebagai PENJAGA/ADMIN PUSAT
    $_SESSION['login'] = true;
    $_SESSION['user_type'] = 'patroli_security'; // Flag: user patroli
    $_SESSION['user_id'] = $user_patroli['id'];
    $_SESSION['username'] = $user_patroli['username'];
    $_SESSION['nama_lengkap'] = $user_patroli['nama_lengkap'];
    $_SESSION['kode_kantor'] = $user_patroli['kode_kantor'];
    $_SESSION['nama_kc'] = $user_patroli['nama_kc'];
    $_SESSION['role'] = $user_patroli['role']; // penjaga atau admin_pusat
    $_SESSION['login_time'] = time();
    
    $user_id = $user_patroli['id'];
    
    // Update last_login
    $stmt_update_patroli = $conn_patroli->prepare("UPDATE users_security SET last_login = NOW() WHERE id = ?");
    $stmt_update_patroli->bind_param('i', $user_id);
    $stmt_update_patroli->execute();
    $stmt_update_patroli->close();
    
    // Redirect ke home patroli
    header("Location: patroli/ui/patroli_home.php");
    exit;
}

// ============================================
// STEP 3: Jika tidak ketemu di kedua database
// ============================================
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
exit;
?>
