<?php
declare(strict_types=1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

validateToken();

$dateKey = preg_replace('/[^0-9\-]/', '', $_GET['date'] ?? '');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateKey)) {
    http_response_code(400); echo json_encode(['error' => 'Invalid date']); exit;
}

$filePath = READINGS_DIR . $dateKey . '.json';

if (!file_exists($filePath)) {
    // Return empty structure for this date — not an error
    echo json_encode([
        'date'     => $dateKey,
        'morning'  => [['sys'=>null,'dia'=>null,'pulse'=>null],['sys'=>null,'dia'=>null,'pulse'=>null],['sys'=>null,'dia'=>null,'pulse'=>null]],
        'evening'  => [['sys'=>null,'dia'=>null,'pulse'=>null],['sys'=>null,'dia'=>null,'pulse'=>null],['sys'=>null,'dia'=>null,'pulse'=>null]],
        'comments' => '',
        'exists'   => false,
    ]);
    exit;
}

$data = json_decode(file_get_contents($filePath), true);
$data['exists'] = true;
echo json_encode($data);
