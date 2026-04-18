<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

validateToken();

if (!is_dir(READINGS_DIR)) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['error' => 'No readings to back up']);
    exit;
}

$files = glob(READINGS_DIR . '*.json') ?: [];

if (empty($files)) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['error' => 'No readings to back up']);
    exit;
}

if (!class_exists('ZipArchive')) {
    // Fallback: stream a single JSON with all readings bundled
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="bp_readings_backup_' . date('Y-m-d') . '.json"');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');

    $all = [];
    sort($files);
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data) $all[] = $data;
    }
    echo json_encode(['backup_date' => date('c'), 'readings' => $all], JSON_PRETTY_PRINT);
    exit;
}

// Build ZIP in memory then stream it
$tmpFile = tempnam(sys_get_temp_dir(), 'bp_backup_');
$zip     = new ZipArchive();

if ($zip->open($tmpFile, ZipArchive::OVERWRITE) !== true) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Could not create backup archive']);
    exit;
}

sort($files);
foreach ($files as $file) {
    $zip->addFile($file, 'readings/' . basename($file));
}

// Also include a manifest with backup metadata
$manifest = json_encode([
    'backup_date'   => date('c'),
    'reading_count' => count($files),
    'note'          => 'Patient info and PIN are not included in this backup.',
], JSON_PRETTY_PRINT);
$zip->addFromString('backup_manifest.json', $manifest);

$zip->close();

$filename = 'bp_readings_backup_' . date('Y-m-d_His') . '.zip';

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');

readfile($tmpFile);
unlink($tmpFile);
