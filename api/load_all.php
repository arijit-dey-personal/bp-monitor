<?php
declare(strict_types=1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

validateToken();

if (!is_dir(READINGS_DIR)) {
    echo json_encode(['readings' => [], 'count' => 0]);
    exit;
}

$files    = glob(READINGS_DIR . '*.json') ?: [];
$readings = [];

foreach ($files as $file) {
    $data = json_decode(file_get_contents($file), true);
    if ($data && isset($data['date'])) {
        $readings[] = $data;
    }
}

// Sort by date descending (most recent first)
usort($readings, fn($a, $b) => strcmp($b['date'], $a['date']));

// Optional: filter by days parameter e.g. ?days=30
$days = isset($_GET['days']) ? (int)$_GET['days'] : 0;
if ($days > 0) {
    $cutoff   = date('Y-m-d', strtotime("-{$days} days"));
    $readings = array_filter($readings, fn($r) => $r['date'] >= $cutoff);
    $readings = array_values($readings);
}

echo json_encode(['readings' => $readings, 'count' => count($readings)]);
