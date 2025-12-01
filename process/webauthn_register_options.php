<?php
/**
 * WebAuthn Biometric Registration
 * Register fingerprint/biometric credential after successful login
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'unknown';

// Generate challenge (random bytes)
$challenge = base64_encode(random_bytes(32));
$_SESSION['webauthn_challenge'] = $challenge;

// Prepare registration options
$options = [
    'challenge' => $challenge,
    'rp' => [
        'name' => 'Agunan Capture - BPR',
        'id' => $_SERVER['HTTP_HOST'] ?? 'localhost'
    ],
    'user' => [
        'id' => base64_encode(strval($user_id)),
        'name' => $username,
        'displayName' => $_SESSION['nama_kc'] ?? $username
    ],
    'pubKeyCredParams' => [
        ['type' => 'public-key', 'alg' => -7],  // ES256
        ['type' => 'public-key', 'alg' => -257] // RS256
    ],
    'timeout' => 60000,
    'attestation' => 'none',
    'authenticatorSelection' => [
        'authenticatorAttachment' => 'platform', // Built-in authenticator (fingerprint)
        'userVerification' => 'required',
        'residentKey' => 'preferred'
    ]
];

echo json_encode([
    'success' => true,
    'options' => $options
]);
