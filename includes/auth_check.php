<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function validateToken(): void {
    $token = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? '';
    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Token = base64(json({exp, sig})) where sig = HMAC of {exp} with TOKEN_SECRET
    $decoded = json_decode(base64_decode($token), true);
    if (!$decoded || !isset($decoded['exp'], $decoded['sig'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token']);
        exit;
    }

    if ($decoded['exp'] < time()) {
        http_response_code(401);
        echo json_encode(['error' => 'Token expired']);
        exit;
    }

    $expectedSig = hash_hmac('sha256', (string)$decoded['exp'], TOKEN_SECRET);
    if (!hash_equals($expectedSig, $decoded['sig'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }
}

function generateToken(): string {
    $exp = time() + TOKEN_TTL;
    $sig = hash_hmac('sha256', (string)$exp, TOKEN_SECRET);
    return base64_encode(json_encode(['exp' => $exp, 'sig' => $sig]));
}
