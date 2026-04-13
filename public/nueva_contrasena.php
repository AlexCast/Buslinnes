<?php
// Inicializar buffer de salida
ob_start();

// Deshabilitar errores en pantalla
error_reporting(0);
ini_set('display_errors', 0);

// Este endpoint es público (para resetear contraseña sin sesión)
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
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;

// Función para responder JSON
function jsonResponse($data, $httpCode = 200) {
    ob_end_clean(); // Limpiar cualquier salida previa
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Función para registrar errores
function logError($message) {
    error_log("[nueva_contrasena.php] " . $message);
}

if (!defined('JWT_SECRET')) { define('JWT_SECRET', 'TU_CLAVE_SECRETA_AQUI'); }
if (!defined('JWT_ISSUER')) { define('JWT_ISSUER', 'buslinnes'); }
if (!defined('JWT_AUDIENCE')) { define('JWT_AUDIENCE', 'buslinnes_users'); }

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
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

// Obtener datos CRUDOS antes de sanitización del middleware
$rawPassword = $_POST['password'] ?? '';
$rawConfirmPassword = $_POST['confirm_password'] ?? '';

// Si viene por JSON, obtener del raw input
if (empty($rawPassword) && $_raw) {
    $_parsed = json_decode($_raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($_parsed)) {
        $rawPassword = $_parsed['password'] ?? '';
        $rawConfirmPassword = $_parsed['confirm_password'] ?? '';
    }
}

// Obtener datos (POST form or JSON body) - estos ya están sanitizados
$token = trim($_POST['token'] ?? $_jsonBody['token'] ?? '');
$password = trim($rawPassword); // Normalizar espacios laterales accidentales
$confirmPassword = trim($rawConfirmPassword); // Normalizar espacios laterales accidentales

// Validaciones básicas
if (empty($token)) {
    jsonResponse(['success' => false, 'message' => 'Token requerido'], 400);
}

if (empty($password) || empty($confirmPassword)) {
    jsonResponse(['success' => false, 'message' => 'Por favor ingresa la contraseña'], 400);
}

if ($password !== $confirmPassword) {
    jsonResponse(['success' => false, 'message' => 'Las contraseñas no coinciden'], 400);
}

if (strlen($password) < 6) {
    jsonResponse(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'], 400);
}

// Conectar a la base de datos
try {
    include_once('../config/database.php');
    // $db está disponible desde database.php
    $pdo = $db;
} catch (PDOException $e) {
    logError("Error de conexión: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error de conexión a la base de datos'], 500);
}

try {
    // Iniciar transacción para asegurar atomicidad
    $pdo->beginTransaction();
    
    // Verificar token
    $stmt = $pdo->prepare("SELECT email_usuario, expires_at FROM password_reset_tokens WHERE token = ? LIMIT 1");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Token inválido o no encontrado'], 400);
    }
    
    // Verificar expiración
    $expiresAt = strtotime($row['expires_at']);
    $now = time();
    
    if ($expiresAt < $now) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'El token ha expirado. Por favor, solicita un nuevo enlace de recuperación.'], 400);
    }

    $email = $row['email_usuario'];
    
    // Verificar que el usuario existe y está activo
    $stmt = $pdo->prepare("SELECT id_usuario, contrasena FROM tab_usuarios WHERE email_usuario = ? AND usr_delete IS NULL LIMIT 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Usuario no encontrado o inactivo'], 400);
    }

    // Actualizar contraseña con hash (usar id_usuario para evitar problemas por email/casing)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE tab_usuarios SET contrasena = ?, usr_update = 'system_reset', fec_update = NOW() WHERE id_usuario = ?");
    $result = $stmt->execute([$hashedPassword, $usuario['id_usuario']]);
    
    if (!$result) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error actualizando contraseña'], 500);
    }

    if ($stmt->rowCount() < 1) {
        $pdo->rollBack();
        logError("UPDATE sin filas afectadas para id_usuario=" . $usuario['id_usuario'] . " email=" . $email);
        jsonResponse(['success' => false, 'message' => 'No se pudo actualizar la contraseña del usuario'], 500);
    }

    // Verificar que la contraseña quedó realmente persistida antes de confirmar éxito
    $stmtVerify = $pdo->prepare("SELECT contrasena FROM tab_usuarios WHERE id_usuario = ? LIMIT 1");
    $stmtVerify->execute([$usuario['id_usuario']]);
    $storedHash = $stmtVerify->fetchColumn();
    if (!$storedHash || !password_verify($password, $storedHash)) {
        $pdo->rollBack();
        logError("Verificación post-update falló para id_usuario=" . $usuario['id_usuario']);
        jsonResponse(['success' => false, 'message' => 'No fue posible confirmar el cambio de contraseña'], 500);
    }
    
    // Eliminar token usado
    $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
    $result = $stmt->execute([$token]);
    
    if (!$result) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error eliminando token'], 500);
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    $pwdFingerprint = substr(hash('sha256', $password), 0, 12);
    $hashFingerprint = substr(hash('sha256', (string)$storedHash), 0, 12);
    logError(
        "Contraseña actualizada exitosamente para: {$email} " .
        "id_usuario={$usuario['id_usuario']} " .
        "pwd_fp={$pwdFingerprint} hash_fp={$hashFingerprint}"
    );
    // Auto-login después del reset para evitar fricción en el paso manual de login
    $stmtPerfil = $pdo->prepare("
        SELECT u.id_usuario, u.nom_usuario, u.email_usuario, r.nombre_rol, ur.id_rol
        FROM tab_usuarios u
        LEFT JOIN tab_usuarios_roles ur ON ur.id_usuario = u.id_usuario AND ur.usr_delete IS NULL
        LEFT JOIN tab_roles r ON r.id_rol = ur.id_rol AND r.usr_delete IS NULL
        WHERE u.id_usuario = ?
        LIMIT 1
    ");
    $stmtPerfil->execute([$usuario['id_usuario']]);
    $perfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);

    if ($perfil) {
        $now = time();
        $payload = [
            'iss' => JWT_ISSUER,
            'aud' => JWT_AUDIENCE,
            'iat' => $now,
            'exp' => $now + (365 * 24 * 60 * 60),
            'sub' => $perfil['id_usuario'],
            'email' => $perfil['email_usuario'],
            'rol' => $perfil['nombre_rol'] ?? null,
            'id_rol' => isset($perfil['id_rol']) ? (int)$perfil['id_rol'] : null,
            'id_usuario' => $perfil['id_usuario'],
            'nombre' => $perfil['nom_usuario'] ?? null
        ];
        $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');
        setcookie('jwt_token', $jwt, 0, '/', '', false, true);

        jsonResponse([
            'success' => true,
            'message' => 'Contraseña cambiada exitosamente.',
            'token' => $jwt,
            'rol' => $perfil['nombre_rol'] ?? null
        ]);
    }

    jsonResponse(['success' => true, 'message' => 'Contraseña cambiada exitosamente. Ahora puedes iniciar sesión.']);

} catch (PDOException $e) {
    // Hacer rollback si hay una transacción activa
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logError("Error de base de datos: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error procesando la solicitud'], 500);
} catch (Exception $e) {
    // Hacer rollback si hay una transacción activa
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logError("Error general: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error procesando la solicitud'], 500);
}
?>


