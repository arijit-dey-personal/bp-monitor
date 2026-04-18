<?php
declare(strict_types=1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

validateToken();

$body = json_decode(file_get_contents('php://input'), true);

$patient = [
    'name'         => substr(strip_tags((string)($body['name']        ?? 'Patient')), 0, 100),
    'age'          => max(1, min(120, (int)($body['age']              ?? 0))),
    'gender'       => in_array($body['gender'] ?? '', ['Male','Female','Other']) ? $body['gender'] : 'Female',
    'uhid'         => substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($body['uhid'] ?? '')), 0, 20),
    'hospitalSys'  => max(60,  min(250, (int)($body['hospitalSys']   ?? 120))),
    'hospitalDia'  => max(40,  min(150, (int)($body['hospitalDia']   ?? 80))),
    'visitDate'    => preg_match('/^\d{4}-\d{2}-\d{2}$/', $body['visitDate'] ?? '') ? $body['visitDate'] : date('Y-m-d'),
    'caseType'     => substr(strip_tags((string)($body['caseType']   ?? 'New Case')), 0, 50),
    'device'       => substr(strip_tags((string)($body['device']     ?? '')), 0, 200),
    'instruction'  => substr(strip_tags((string)($body['instruction']?? 'Check on right hand only')), 0, 200),
    'updatedAt'    => date('c'),
];

if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0750, true);

$written = file_put_contents(DATA_DIR . 'patient.json', json_encode($patient, JSON_PRETTY_PRINT), LOCK_EX);

echo $written !== false
    ? json_encode(['success' => true])
    : (http_response_code(500) && json_encode(['error' => 'Save failed']));
