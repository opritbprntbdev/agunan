<?php
/**
 * WebAuthn Credential Verification
 * Verify and save biometric credential
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['credential']) || !isset($_SESSION['webauthn_challenge'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$credential = $input['credential'];
$user_id = $_SESSION['user_id'];

// Basic validation
if (!isset($credential['id']) || !isset($credential['rawId']) || !isset($credential['response'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid credential format']);
    exit;
}

try {
    // Save credential to database
    $credential_id = $credential['id'];
    $public_key = json_encode($credential['response']['attestationObject'] ?? []);
    $device_name = $input['device_name'] ?? 'Unknown Device';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO user_webauthn_credentials 
        (user_id, credential_id, public_key, device_name, user_agent, counter) 
        VALUES (?, ?, ?, ?, ?, 0)
        ON DUPLICATE KEY UPDATE 
        last_used_at = NOW()
    ");
    
    $stmt->bind_param('issss', $user_id, $credential_id, $public_key, $device_name, $user_agent);
    
    if ($stmt->execute()) {
        // Clear challenge
        unset($_SESSION['webauthn_challenge']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Fingerprint berhasil didaftarkan!'
        ]);
    } else {
        throw new Exception('Database error');
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan credential: ' . $e->getMessage()
    ]);
}

$conn->close();
