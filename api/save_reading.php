<?php
declare(strict_types=1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

validateToken();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit;
}

$body    = json_decode(file_get_contents('php://input'), true);
$dateKey = $body['date'] ?? '';  // e.g. "2026-04-24"

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateKey)) {
    http_response_code(400); echo json_encode(['error' => 'Invalid date format']); exit;
}

// Ensure readings directory exists
if (!is_dir(READINGS_DIR)) {
    mkdir(READINGS_DIR, 0750, true);
}

$filePath = READINGS_DIR . $dateKey . '.json';

// Sanitize and validate reading values
function sanitizeReading(mixed $r): array {
    $sys   = isset($r['sys'])   && is_numeric($r['sys'])   ? (int)$r['sys']   : null;
    $dia   = isset($r['dia'])   && is_numeric($r['dia'])   ? (int)$r['dia']   : null;
    $pulse = isset($r['pulse']) && is_numeric($r['pulse']) ? (int)$r['pulse'] : null;

    // Validate physiological ranges
    if ($sys   !== null && ($sys   < 60  || $sys   > 250)) $sys   = null;
    if ($dia   !== null && ($dia   < 40  || $dia   > 150)) $dia   = null;
    if ($pulse !== null && ($pulse < 40  || $pulse > 200)) $pulse = null;

    return ['sys' => $sys, 'dia' => $dia, 'pulse' => $pulse];
}

$data = [
    'date'     => $dateKey,
    'morning'  => array_map('sanitizeReading', (array)($body['morning'] ?? [[], [], []])),
    'evening'  => array_map('sanitizeReading', (array)($body['evening'] ?? [[], [], []])),
    'comments' => substr(strip_tags((string)($body['comments'] ?? '')), 0, 500),
    'savedAt'  => date('c'),  // ISO 8601 timestamp
];

// Pad to exactly 3 slots each session
while (count($data['morning']) < 3) $data['morning'][] = ['sys'=>null,'dia'=>null,'pulse'=>null];
while (count($data['evening']) < 3) $data['evening'][] = ['sys'=>null,'dia'=>null,'pulse'=>null];
$data['morning'] = array_slice($data['morning'], 0, 3);
$data['evening'] = array_slice($data['evening'], 0, 3);

$written = file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);

if ($written === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save data']);
    exit;
}

echo json_encode(['success' => true, 'date' => $dateKey, 'savedAt' => $data['savedAt']]);
