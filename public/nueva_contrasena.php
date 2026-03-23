<?php
// Inicializar buffer de salida
ob_start();

// Deshabilitar errores en pantalla
error_reporting(0);
ini_set('display_errors', 0);

require_once('../app/SecurityMiddleware.php');
SecurityMiddleware::protect();

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

// Obtener datos (POST form or JSON body)
$token = trim($_POST['token'] ?? $_jsonBody['token'] ?? '');
$password = $_POST['password'] ?? $_jsonBody['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? $_jsonBody['confirm_password'] ?? '';

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
    // Configuración de PostgreSQL
    $host = "10.5.213.111";  // Cambia si tu base de datos no está en localhost
    $port = "5432";
    $dbname = "db_buslinnes";
    $user = "gr_buslinnes";
    $dbPassword = "buslinnes";

    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    logError("Error de conexión: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error de conexión a la base de datos'], 500);
}

try {
    // Verificar token
    $stmt = $pdo->prepare("SELECT email, expires_at FROM password_reset_tokens WHERE token = ? LIMIT 1");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        jsonResponse(['success' => false, 'message' => 'Token inválido o no encontrado'], 400);
    }
    
    // Verificar expiración
    $expiresAt = strtotime($row['expires_at']);
    $now = time();
    
    if ($expiresAt < $now) {
        jsonResponse(['success' => false, 'message' => 'El token ha expirado. Por favor, solicita un nuevo enlace de recuperación.'], 400);
    }

    $email = $row['email'];
    
    // Verificar que el usuario existe y está activo
    $stmt = $pdo->prepare("SELECT id_usuario FROM tab_usuarios WHERE correo = ? AND usr_delete IS NULL LIMIT 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        jsonResponse(['success' => false, 'message' => 'Usuario no encontrado o inactivo'], 400);
    }

    // Actualizar contraseña
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE tab_usuarios SET contrasena = ?, usr_update = 'system_reset', fec_update = NOW() WHERE correo = ?");
    $stmt->execute([$hashedPassword, $email]);
    
    // Eliminar token usado
    $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
    $stmt->execute([$token]);

    logError("Contraseña actualizada exitosamente para: " . $email);
    jsonResponse(['success' => true, 'message' => 'Contraseña cambiada exitosamente. Ahora puedes iniciar sesión.']);

} catch (PDOException $e) {
    logError("Error de base de datos: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error procesando la solicitud'], 500);
} catch (Exception $e) {
    logError("Error general: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error procesando la solicitud'], 500);
}
?>


