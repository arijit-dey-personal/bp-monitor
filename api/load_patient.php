<?php
declare(strict_types=1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

validateToken();

$file = DATA_DIR . 'patient.json';

if (!file_exists($file)) {
    echo json_encode([
        'name' => 'Mrs. Ruchi Gupta Dey', 'age' => 43, 'gender' => 'Female',
        'uhid' => '722604000958', 'hospitalSys' => 120, 'hospitalDia' => 80,
        'visitDate' => '2026-04-17', 'caseType' => 'New Case',
        'device' => 'Omron BP Machine Fully Automatic with Heart Rate',
        'instruction' => '',
        'morningReminderOn' => false, 'morningReminderTime' => '08:45',
        'eveningReminderOn' => false, 'eveningReminderTime' => '20:45',
        'exists' => false,
    ]);
    exit;
}

$data = json_decode(file_get_contents($file), true);
$data['exists'] = true;
echo json_encode($data);
