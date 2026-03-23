<?php
/**
 * Endpoint para obtener token CSRF
 */

// Configurar sesión con cookies seguras
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);

session_start();

require_once __DIR__ . '/SecurityMiddleware.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Generar o retornar token CSRF existente
$token = SecurityMiddleware::generateCSRFToken();

echo json_encode([
    'csrf_token' => $token,
    'expires_in' => 1800, // 30 minutos
    'session_id' => session_id(),
    'generated_at' => time()
]);


