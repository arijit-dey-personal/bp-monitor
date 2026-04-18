<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

validateToken();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="bp_readings_' . date('Y-m-d') . '.csv"');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');

$out = fopen('php://output', 'w');

// Header row
fputcsv($out, ['Date', 'Session', 'Slot', 'Time', 'Systolic', 'Diastolic', 'Pulse', 'Classification', 'Comments']);

$slots = [
    'morning' => ['9:00 AM', '9:15 AM', '9:30 AM'],
    'evening' => ['9:00 PM', '9:15 PM', '9:30 PM'],
];

$files = is_dir(READINGS_DIR) ? (glob(READINGS_DIR . '*.json') ?: []) : [];
sort($files);

foreach ($files as $file) {
    $data = json_decode(file_get_contents($file), true);
    if (!$data) continue;

    foreach (['morning', 'evening'] as $session) {
        foreach (($data[$session] ?? []) as $i => $r) {
            $sys   = $r['sys']   ?? null;
            $dia   = $r['dia']   ?? null;
            $pulse = $r['pulse'] ?? null;
            $cls   = '';
            if ($sys !== null && $dia !== null) {
                if ($sys > 180 || $dia > 120)      $cls = 'Crisis';
                elseif ($sys >= 140 || $dia >= 90) $cls = 'Stage 2 High';
                elseif ($sys >= 130 || $dia >= 80) $cls = 'Stage 1 High';
                elseif ($sys >= 120)               $cls = 'Elevated';
                else                               $cls = 'Normal';
            }
            fputcsv($out, [
                $data['date'],
                ucfirst($session),
                $i + 1,
                $slots[$session][$i] ?? '',
                $sys ?? '',
                $dia ?? '',
                $pulse ?? '',
                $cls,
                $i === 0 ? ($data['comments'] ?? '') : '',
            ]);
        }
    }
}

fclose($out);
