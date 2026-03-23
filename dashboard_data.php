<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Iniciar sesión si no está iniciada (necesario para SecurityMiddleware)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar autoload de Composer para JWT
require_once __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$jwt_secret = 'TU_CLAVE_SECRETA_AQUI';

try {
    // Obtener JWT del header Authorization (múltiples métodos)
    $token = null;
    
    // Método 1: $_SERVER['HTTP_AUTHORIZATION']
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }
    }
    
    // Método 2: getallheaders() - funciona incluso si Apache no pasa el header
    if (!$token && function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                $token = $matches[1];
            }
        }
    }
    
    // Método 3: apache_request_headers()
    if (!$token && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                $token = $matches[1];
            }
        }
    }
    
    // Método 4: Verificar en cookie como fallback
    if (!$token && isset($_COOKIE['jwt_token'])) {
        $token = $_COOKIE['jwt_token'];
    }
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado', 'message' => 'Token no proporcionado']);
        exit();
    }
    
    // Decodificar y validar el token
    $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));
    
    // Verificar que sea admin
    $rol = isset($decoded->rol) ? strtolower($decoded->rol) : '';
    if ($rol !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso denegado', 'message' => 'Solo administradores pueden acceder']);
        exit();
    }
    
    // Cargar conexión a BD
    require_once __DIR__ . '/src/base_de_datos.php';
    
    // Buses activos
    $stmt = $base_de_datos->query("SELECT COUNT(id_bus) FROM tab_buses WHERE ind_estado_buses = 'A' AND fec_delete IS NULL");
    $buses = $stmt->fetchColumn();

    // Conductores
    $stmt = $base_de_datos->query("SELECT COUNT(id_conductor) FROM tab_conductores WHERE fec_delete IS NULL");
    $conductores = $stmt->fetchColumn();

    // Usuarios/Pasajeros
    $stmt = $base_de_datos->query("SELECT COUNT(id_usuario) FROM tab_usuarios WHERE fec_delete IS NULL");
    $usuarios = $stmt->fetchColumn();

    // Rutas
    $stmt = $base_de_datos->query("SELECT COUNT(id_ruta) FROM tab_rutas WHERE fec_delete IS NULL");
    $rutas = $stmt->fetchColumn();

    echo json_encode([
        'buses' => intval($buses),
        'conductores' => intval($conductores),
        'usuarios' => intval($usuarios),
        'pasajeros' => intval($usuarios),
        'rutas' => intval($rutas)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error en dashboard_data.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'Error en el servidor',
        'message' => $e->getMessage()
    ]);
}

