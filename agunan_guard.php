<?php
/**
 * SECURITY: Agunan/Voucher Session Guard
 * Mencegah user patroli akses modul agunan/voucher
 */

if (!isset($_SESSION)) {
    session_start();
}

// Check login
if (!isset($_SESSION['login'])) {
    header("Location: ../index.php?error=Please login");
    exit;
}

// CRITICAL: Block patroli users
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'patroli_security') {
    header("Location: ../patroli/ui/patroli_home.php?error=Access denied");
    exit;
}

// Set default user_type for old sessions
if (!isset($_SESSION['user_type'])) {
    $_SESSION['user_type'] = 'agunan_voucher';
}

return true;
