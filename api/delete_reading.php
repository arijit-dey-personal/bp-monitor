<?php
declare(strict_types=1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

validateToken();

$body    = json_decode(file_get_contents('php://input'), true);
$dateKey = preg_replace('/[^0-9\-]/', '', $body['date'] ?? '');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateKey)) {
    http_response_code(400); echo json_encode(['error' => 'Invalid date']); exit;
}

$filePath = READINGS_DIR . $dateKey . '.json';

if (!file_exists($filePath)) {
    http_response_code(404); echo json_encode(['error' => 'Not found']); exit;
}

unlink($filePath);
echo json_encode(['success' => true, 'deleted' => $dateKey]);
