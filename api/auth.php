<?php
declare(strict_types=1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$pin  = $body['pin'] ?? '';

if (empty($pin) || !is_string($pin)) {
    http_response_code(400);
    echo json_encode(['error' => 'PIN required']);
    exit;
}

// Rate limiting: max 5 attempts per minute stored in a temp file
$attemptFile = sys_get_temp_dir() . '/bp_attempts.json';
$attempts    = file_exists($attemptFile) ? json_decode(file_get_contents($attemptFile), true) : [];
$now         = time();
$attempts    = array_filter($attempts, fn($t) => $now - $t < 60);
if (count($attempts) >= 5) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many attempts. Wait 1 minute.']);
    exit;
}

if (!password_verify($pin, ACCESS_PIN_HASH)) {
    $attempts[] = $now;
    file_put_contents($attemptFile, json_encode(array_values($attempts)), LOCK_EX);
    http_response_code(403);
    echo json_encode(['error' => 'Incorrect PIN']);
    exit;
}

echo json_encode(['success' => true, 'token' => generateToken()]);
