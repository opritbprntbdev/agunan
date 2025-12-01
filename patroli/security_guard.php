<?php
/**
 * SECURITY: Patroli Session Guard
 * File ini WAJIB di-include di SETIAP file patroli
 * Mencegah akses ke modul agunan/voucher saat login sebagai patroli
 */

if (!isset($_SESSION)) {
    session_start();
}

// CRITICAL: Cek user type
if (!isset($_SESSION['login']) || !isset($_SESSION['user_type'])) {
    // Not logged in - redirect to login
    header('Location: ../../index.php?error=Session expired');
    exit;
}

// CRITICAL: User patroli TIDAK BOLEH akses agunan/voucher
if ($_SESSION['user_type'] !== 'patroli_security') {
    // Agunan user trying to access patroli - BLOCK!
    header('Location: ../../home.php?error=Access denied');
    exit;
}

// CRITICAL: Pastikan role ada (penjaga atau admin_pusat)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['penjaga', 'admin_pusat'])) {
    session_destroy();
    header('Location: ../../index.php?error=Invalid role');
    exit;
}

// CRITICAL: Validasi session variables required
$required_vars = ['user_id', 'username', 'kode_kantor'];
foreach ($required_vars as $var) {
    if (!isset($_SESSION[$var])) {
        session_destroy();
        header('Location: ../../index.php?error=Invalid session');
        exit;
    }
}

// Session valid - proceed
return true;
