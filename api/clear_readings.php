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

// Deliberately only touches readings/ — never patient.json or pin_hash.txt
if (!is_dir(READINGS_DIR)) {
    echo json_encode(['success' => true, 'deleted' => 0]);
    exit;
}

$files   = glob(READINGS_DIR . '*.json') ?: [];
$deleted = 0;
$failed  = 0;

foreach ($files as $file) {
    // Extra safety: only delete files that look like YYYY-MM-DD.json
    if (preg_match('/\d{4}-\d{2}-\d{2}\.json$/', basename($file))) {
        unlink($file) ? $deleted++ : $failed++;
    }
}

if ($failed > 0) {
    http_response_code(500);
    echo json_encode(['error' => "Deleted $deleted files but $failed could not be removed."]);
    exit;
}

echo json_encode(['success' => true, 'deleted' => $deleted]);
