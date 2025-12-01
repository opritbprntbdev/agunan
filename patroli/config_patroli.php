<?php
// ============================================
// CONFIG PATROLI SECURITY
// Koneksi ke database patroli_security (terpisah)
// ============================================

// Database Patroli
$host_patroli = "localhost";
$user_patroli = "root";
$pass_patroli = ""; 
$db_port_patroli = 3308; // Sesuaikan dengan port MySQL Anda
$db_patroli = "patroli_security";

// Koneksi ke DB Patroli
$conn_patroli = new mysqli($host_patroli, $user_patroli, $pass_patroli, $db_patroli, $db_port_patroli);

if ($conn_patroli->connect_error) {
    error_log("Koneksi DB Patroli gagal: " . $conn_patroli->connect_error);
    die("Database connection failed: " . $conn_patroli->connect_error);
}

// Set charset
$conn_patroli->set_charset("utf8mb4");

// Timezone
date_default_timezone_set('Asia/Jakarta');
?>
