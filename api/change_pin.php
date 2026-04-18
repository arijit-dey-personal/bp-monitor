<?php
declare(strict_types=1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

validateToken();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true);
$oldPin = $body['old_pin'] ?? '';
$newPin = $body['new_pin'] ?? '';

// Validate inputs
if (!is_string($oldPin) || !is_string($newPin)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

if (!preg_match('/^\d{4,6}$/', $newPin)) {
    http_response_code(400);
    echo json_encode(['error' => 'New PIN must be 4–6 digits']);
    exit;
}

// Verify old PIN
if (!password_verify($oldPin, ACCESS_PIN_HASH)) {
    http_response_code(403);
    echo json_encode(['error' => 'Current PIN is incorrect']);
    exit;
}

// Generate new hash and save
$newHash = password_hash($newPin, PASSWORD_BCRYPT);

if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0750, true);
}

$written = file_put_contents(PIN_HASH_FILE, $newHash, LOCK_EX);

if ($written === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save new PIN']);
    exit;
}

echo json_encode(['success' => true]);
