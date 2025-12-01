<?php
/**
 * WebAuthn Biometric Login
 * Authenticate user using fingerprint credential
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['credential_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$credential_id = $input['credential_id'];

try {
    // Find user by credential
    $stmt = $conn->prepare("
        SELECT wc.user_id, wc.counter, u.username, u.nama_kc, u.kode_kantor
        FROM user_webauthn_credentials wc
        JOIN user u ON wc.user_id = u.id
        WHERE wc.credential_id = ?
        LIMIT 1
    ");
    
    $stmt->bind_param('s', $credential_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Credential not found']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    // Update counter and last_used_at
    $stmt = $conn->prepare("
        UPDATE user_webauthn_credentials 
        SET counter = counter + 1, last_used_at = NOW() 
        WHERE credential_id = ?
    ");
    $stmt->bind_param('s', $credential_id);
    $stmt->execute();
    
    // Update user login stats
    $user_id = $user['user_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $stmt = $conn->prepare("
        UPDATE user 
        SET last_login_at = NOW(), 
            last_login_ip = ?, 
            login_count = login_count + 1 
        WHERE id = ?
    ");
    $stmt->bind_param('si', $ip, $user_id);
    $stmt->execute();
    
    // Set session
    $_SESSION['login'] = true;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $user['username'];
    $_SESSION['nama_kc'] = $user['nama_kc'];
    $_SESSION['kode_kantor'] = $user['kode_kantor'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'username' => $user['username'],
            'nama_kc' => $user['nama_kc']
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Login failed: ' . $e->getMessage()
    ]);
}
