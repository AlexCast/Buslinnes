<?php
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../src/branding_config/get_branding.php';

try {
    $payload = brandingConfigGetPayload();
    echo json_encode([
        'success' => true,
        'data' => $payload
    ]);
} catch (Throwable $e) {
    $defaults = [
        'primary_color' => '#8059d4ff',
        'logo_url' => '/buslinnes/assets/img/logomorado.svg',
        'favicon_url' => '/buslinnes/mkcert/favicon.ico',
        'updated_at' => null
    ];

    echo json_encode([
        'success' => true,
        'data' => $defaults
    ]);
}
