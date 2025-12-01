<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config.php';

// Auth check
if (!isset($_SESSION['login'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get JSON body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

// Validate required fields
$required = ['user_id', 'username', 'latitude', 'longitude'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Field $field required"]);
        exit;
    }
}

$user_id = (int)$data['user_id'];
$username = trim($data['username']);
$latitude = (float)$data['latitude'];
$longitude = (float)$data['longitude'];
$accuracy = isset($data['accuracy']) ? (float)$data['accuracy'] : null;

// Validate coordinates
if ($latitude < -90 || $latitude > 90) {
    echo json_encode(['success' => false, 'message' => 'Invalid latitude']);
    exit;
}

if ($longitude < -180 || $longitude > 180) {
    echo json_encode(['success' => false, 'message' => 'Invalid longitude']);
    exit;
}

// Insert to database
$stmt = $conn->prepare("
    INSERT INTO user_location_log 
    (user_id, username, latitude, longitude, accuracy, logged_at) 
    VALUES (?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param('isddd', $user_id, $username, $latitude, $longitude, $accuracy);

if ($stmt->execute()) {
    $insert_id = $stmt->insert_id;
    echo json_encode([
        'success' => true, 
        'message' => 'Location saved successfully',
        'id' => $insert_id,
        'data' => [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy
        ]
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $stmt->error
    ]);
}

$stmt->close();
?>
