<?php
/**
 * Diagnóstico completo del flujo CSRF
 * Ayuda a identificar dónde se rompe el flujo CSRF
 */

session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/SecurityMiddleware.php';

$diagnostic = [
    'timestamp' => time(),
    'session' => [
        'status' => session_status(),
        'id' => session_id(),
        'data' => $_SESSION,
        'save_path' => session_save_path()
    ],
    'request' => [
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI'],
        'hostname' => $_SERVER['HTTP_HOST'] ?? 'unknown'
    ],
    'cookies' => [
        'present' => !empty($_COOKIE),
        'session_cookie' => $_COOKIE[session_name()] ?? null,
        'count' => count($_COOKIE)
    ],
    'csrf_checks' => []
];

// Verificar token en diferentes ubicaciones
$diagnostic['csrf_checks']['post_data'] = isset($_POST['csrf_token']) ? 'Present' : 'Missing';
$diagnostic['csrf_checks']['header_http_x_csrf_token'] = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? 'Present' : 'Missing';

// Intentar obtener todos los headers
try {
    $headers = getallheaders();
    $diagnostic['csrf_checks']['header_getallheaders'] = isset($headers['X-CSRF-Token']) ? 'Present' : 'Missing';
    $diagnostic['all_headers'] = array_map(function($value) {
        // No mostrar valores sensibles completos, solo primeros 10 caracteres
        return strlen($value) > 20 ? substr($value, 0, 10) . '...' : $value;
    }, $headers);
} catch (Exception $e) {
    $diagnostic['csrf_checks']['header_getallheaders'] = 'Error: ' . $e->getMessage();
}

// Intentar generar un nuevo token
try {
    $token = SecurityMiddleware::generateCSRFToken();
    $diagnostic['token_generation'] = [
        'status' => 'Success',
        'token_exists' => isset($_SESSION['csrf_token']),
        'token_length' => strlen($_SESSION['csrf_token'] ?? ''),
        'token_age' => time() - ($_SESSION['csrf_token_time'] ?? 0) . ' segundos'
    ];
} catch (Exception $e) {
    $diagnostic['token_generation'] = [
        'status' => 'Error',
        'error' => $e->getMessage()
    ];
}

// Verificar si la sesión persiste
$diagnostic['session_persistence'] = [
    'session_cookies_enabled' => ini_get('session.use_cookies'),
    'session_cookie_httponly' => ini_get('session.cookie_httponly'),
    'session_cookie_samesite' => ini_get('session.cookie_samesite'),
    'session_cookie_secure' => ini_get('session.cookie_secure'),
    'session_cookie_lifetime' => ini_get('session.cookie_lifetime'),
    'session_gc_maxlifetime' => ini_get('session.gc_maxlifetime')
];

// Verificar el método de almacenamiento
$diagnostic['session_handler'] = [
    'handler' => session_module_name(),
    'save_path' => session_save_path(),
    'save_path_exists' => is_dir(session_save_path())
];

// Cuáles de los índices POST están presentes
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $diagnostic['request_body'] = [
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'unknown',
        'post_keys' => !empty($_POST) ? array_keys($_POST) : [],
        'post_has_csrf' => isset($_POST['csrf_token']),
        'body_size' => strlen(file_get_contents('php://input'))
    ];
}

// Mostrar información sobre el navegador
$diagnostic['client'] = [
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'origin' => $_SERVER['HTTP_ORIGIN'] ?? 'unknown',
    'referer' => $_SERVER['HTTP_REFERER'] ?? 'unknown'
];

echo json_encode($diagnostic, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


