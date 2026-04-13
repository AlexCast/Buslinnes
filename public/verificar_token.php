<?php
// Inicializar buffer de salida
ob_start();

// Deshabilitar errores en pantalla
error_reporting(0);
ini_set('display_errors', 0);

// Este endpoint es público (para validar tokens de recuperación sin sesión)
// No validamos CSRF ni sesión, solo headers de seguridad
require_once('../app/SecurityMiddleware.php');
SecurityMiddleware::protect([
    'csrf' => false,
    'rateLimit' => false,
    'origin' => false,
    'userAgent' => false,
    'securityHeaders' => true,
    'jwt' => false
]);

// Función para responder JSON
function jsonResponse($data, $httpCode = 200) {
    ob_end_clean();
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['valid' => false, 'message' => 'Método no permitido'], 405);
}

// Support JSON body (sent by securityHelper.post)
$_jsonBody = [];
$_raw = SecurityMiddleware::getRawInput();
if ($_raw) {
    $_parsed = json_decode($_raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($_parsed)) {
        $_jsonBody = $_parsed;
    }
}

$token = trim($_POST['token'] ?? $_jsonBody['token'] ?? '');

if (empty($token)) {
    jsonResponse(['valid' => false, 'message' => 'Token requerido'], 400);
}

try {
    // Conectar a la base de datos usando la configuración centralizada
    include_once('../config/database.php');
    $pdo = $db;

    // Verificar token
    $stmt = $pdo->prepare("SELECT email_usuario, expires_at FROM password_reset_tokens WHERE token = ? LIMIT 1");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        jsonResponse(['valid' => false, 'message' => 'Token inválido o no encontrado'], 400);
    }
    
    // Verificar expiración
    $expiresAt = strtotime($row['expires_at']);
    $now = time();
    
    if ($expiresAt < $now) {
        jsonResponse(['valid' => false, 'message' => 'El token ha expirado. Por favor, solicita un nuevo enlace de recuperación.'], 400);
    }
    
    jsonResponse(['valid' => true, 'email' => $row['email_usuario']]);

} catch (PDOException $e) {
    error_log("[verificar_token.php] Error en base de datos: " . $e->getMessage());
    jsonResponse(['valid' => false, 'message' => 'Error del servidor'], 500);
} catch (Exception $e) {
    error_log("[verificar_token.php] Error general: " . $e->getMessage());
    jsonResponse(['valid' => false, 'message' => 'Error del servidor'], 500);
}
?>

