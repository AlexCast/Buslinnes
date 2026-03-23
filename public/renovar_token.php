<?php
/**
 * Endpoint para renovar el token JWT
 * Se llama automáticamente cuando el usuario está activo
 */
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require_once('../app/SecurityMiddleware.php');
SecurityMiddleware::protect([
    'csrf' => false,
    'rateLimit' => true,
    'origin' => true,
    'userAgent' => true,
    'securityHeaders' => true
]);

require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!defined('JWT_SECRET')) { define('JWT_SECRET', 'TU_CLAVE_SECRETA_AQUI'); }
if (!defined('JWT_ISSUER')) { define('JWT_ISSUER', 'buslinnes'); }
if (!defined('JWT_AUDIENCE')) { define('JWT_AUDIENCE', 'buslinnes_users'); }
if (!defined('JWT_LIFETIME')) { define('JWT_LIFETIME', 1200); }

$jwt_secret = JWT_SECRET;
$jwt_issuer = JWT_ISSUER;
$jwt_audience = JWT_AUDIENCE;

header('Content-Type: application/json');

// Obtener el token de la cookie o del header
$token = null;
if (isset($_COOKIE['jwt_token'])) {
    $token = $_COOKIE['jwt_token'];
} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = $matches[1];
    }
}

if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'No token provided']);
    exit();
}

try {
    // Decodificar el token actual
    $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));
    
    // Validar issuer y audience
    if ($decoded->iss !== $jwt_issuer || $decoded->aud !== $jwt_audience) {
        throw new Exception('Token inválido');
    }
    
    // Crear nuevo token con los mismos datos pero nueva expiración (15 minutos)
    $now = time();
    $payload = [
        'iss' => $jwt_issuer,
        'aud' => $jwt_audience,
        'iat' => $now,
        'exp' => $now + 1200, // 20 minutos
        'sub' => $decoded->sub,
        'email' => $decoded->email,
        'rol' => $decoded->rol,
        'nombre' => $decoded->nombre,
        'id_rol' => $decoded->id_rol ?? null,
        'id_usuario' => $decoded->id_usuario ?? null
    ];
    
    $newToken = JWT::encode($payload, $jwt_secret, 'HS256');
    
    // Actualizar la cookie
    setcookie('jwt_token', $newToken, $now + 1200, '/', '', false, true);
    
    echo json_encode([
        'success' => true,
        'token' => $newToken,
        'expires_in' => 900
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido o expirado']);
}


