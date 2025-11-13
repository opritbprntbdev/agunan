<?php
/**
 * Reset MySQL Prepared Statements
 */
require_once __DIR__ . '/config.php';

echo "=== RESET MYSQL PREPARED STATEMENTS ===\n\n";

// Check current count on IBS
$result = $conn_dbibs->query("SHOW STATUS LIKE 'Prepared_stmt_count'");
if ($result) {
    $row = $result->fetch_assoc();
    echo "IBS - Current prepared statements: " . $row['Value'] . "\n";
}

// Check max limit
$result = $conn_dbibs->query("SHOW VARIABLES LIKE 'max_prepared_stmt_count'");
if ($result) {
    $row = $result->fetch_assoc();
    echo "IBS - Max limit: " . $row['Value'] . "\n";
}

echo "\n--- Closing all connections and reconnecting ---\n";

// Close and reconnect
$conn_dbibs->close();
$conn->close();

// Reconnect
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
$conn_dbibs = new mysqli($db_host_ibs, $db_user_ibs, $db_pass_ibs, $db_name_ibs, $db_port_ibs);

if ($conn->connect_error) {
    die("Local DB reconnect failed: " . $conn->connect_error . "\n");
}

if ($conn_dbibs->connect_error) {
    die("IBS reconnect failed: " . $conn_dbibs->connect_error . "\n");
}

echo "✓ Reconnected successfully\n\n";

// Check again
$result = $conn_dbibs->query("SHOW STATUS LIKE 'Prepared_stmt_count'");
if ($result) {
    $row = $result->fetch_assoc();
    echo "IBS - Current prepared statements after reconnect: " . $row['Value'] . "\n";
}

echo "\n=== DONE ===\n";
