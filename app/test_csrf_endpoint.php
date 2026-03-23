<?php
/**
 * Endpoint de prueba para validar CSRF
 */

require_once __DIR__ . '/SecurityMiddleware.php';

// Proteger con CSRF
SecurityMiddleware::protect([
    'csrf' => true,
    'rateLimit' => false,  // Desactivar para tests
    'origin' => false,     // Desactivar para tests
    'userAgent' => false,  // Desactivar para tests
    'securityHeaders' => true
]);

header('Content-Type: application/json');

// Si llegamos aquí, el CSRF es válido
$data = SecurityMiddleware::validateJSON() ?? $_POST;

echo json_encode([
    'success' => true,
    'message' => 'CSRF token válido',
    'data_received' => $data,
    'timestamp' => time(),
    'request_method' => $_SERVER['REQUEST_METHOD']
]);


