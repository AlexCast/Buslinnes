<?php
/**
 * Diagnóstico del sistema CSRF
 */

session_start();

header('Content-Type: application/json');

$diagnostic = [
    'session_status' => session_status(),
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'php_session_config' => [
        'save_path' => session_save_path(),
        'cookie_params' => session_get_cookie_params(),
        'module_name' => session_module_name()
    ],
    'request_info' => [
        'method' => $_SERVER['REQUEST_METHOD'],
        'headers' => [
            'X-CSRF-Token' => $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 'No presente',
            'Origin' => $_SERVER['HTTP_ORIGIN'] ?? 'No presente',
            'Referer' => $_SERVER['HTTP_REFERER'] ?? 'No presente'
        ],
        'post_data' => $_POST,
        'cookies' => $_COOKIE
    ]
];

// Intentar generar un token
require_once __DIR__ . '/SecurityMiddleware.php';

try {
    $token = SecurityMiddleware::generateCSRFToken();
    $diagnostic['csrf_token_generated'] = $token;
    $diagnostic['token_in_session'] = $_SESSION['csrf_token'] ?? 'No existe';
    $diagnostic['token_time'] = $_SESSION['csrf_token_time'] ?? 'No existe';
} catch (Exception $e) {
    $diagnostic['error_generating_token'] = $e->getMessage();
}

echo json_encode($diagnostic, JSON_PRETTY_PRINT);


