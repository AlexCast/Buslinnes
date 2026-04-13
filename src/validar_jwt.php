<?php
/**
 * Archivo de validación de JWT
 * Debe ser incluido al inicio de todas las páginas PHP que requieran autenticación
 * Valida que el token JWT exista, no esté expirado y tenga el rol correcto
 */

// Incluir las dependencias necesarias
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!defined('JWT_SECRET')) {
    define('JWT_SECRET', 'TU_CLAVE_SECRETA_AQUI'); // Cambia por clave segura
}
if (!defined('JWT_ISSUER')) {
    define('JWT_ISSUER', 'buslinnes');
}
if (!defined('JWT_AUDIENCE')) {
    define('JWT_AUDIENCE', 'buslinnes_users');
}
if (!defined('JWT_LIFETIME')) {
    define('JWT_LIFETIME', 0); // Sin expiración por tiempo
}

// Función para validar el token JWT
function validarTokenJWT($rolesPermitidos = []) {
    $jwt_secret = JWT_SECRET;
    $jwt_issuer = JWT_ISSUER;
    $jwt_audience = JWT_AUDIENCE;
    $jwt_lifetime = JWT_LIFETIME;
    
    // Detectar si es una petición AJAX
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    // Obtener el token de la cookie o del header Authorization
    $token = null;
    
    // Intentar obtener del header Authorization primero
    $authHeader = null;
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        // Común en Apache/Windows: el header se mueve a REDIRECT_HTTP_AUTHORIZATION
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } else {
        // Fallback: intentar leer de getallheaders()
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $authHeader = $headers['authorization'];
        }
    }

    if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = $matches[1];
    }
    // Si no está en header, intentar obtener de la cookie
    elseif (isset($_COOKIE['jwt_token'])) {
        $token = $_COOKIE['jwt_token'];
    }
    
    // Si no hay token, responder según el tipo de petición
    if (!$token) {
        // Si estamos en modo manual, retornar null o lanzar excepción maneable
        if (defined('VALIDAR_JWT_MANUAL') && VALIDAR_JWT_MANUAL) {
            throw new Exception('No token provided');
        }
        
        if ($isAjax) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autorizado', 'message' => 'Token no proporcionado']);
            exit();
        } else {
            header('Location: /buslinnes/public/login.php');
            exit();
        }
    }
    
    try {
        // Decodificar y validar el token
        $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));
        
        // Validar issuer y audience
        if ($decoded->iss !== $jwt_issuer || $decoded->aud !== $jwt_audience) {
            throw new Exception('Token inválido');
        }
        
        // Los tokens tienen expiración muy lejana (1 año) pero no expiran por inactividad
        // Solo validar que el token no esté completamente corrupto
        $now = time();
        if (isset($decoded->exp) && $decoded->exp < $now) {
            // Token realmente expirado (muy raro ya que expira en 1 año)
            setcookie('jwt_token', '', time() - 3600, '/');
            
            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Token expirado', 'message' => 'Por favor, inicie sesión nuevamente']);
                exit();
            } else {
                header('Location: /buslinnes/public/login.php?expired=1');
                exit();
            }
        }

        // Validar roles si se especificaron
        if (!empty($rolesPermitidos)) {
            $rolUsuario = isset($decoded->rol) ? strtolower($decoded->rol) : '';
            if (!in_array($rolUsuario, $rolesPermitidos)) {
                if ($isAjax) {
                    http_response_code(403);
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Acceso denegado', 'message' => 'No tiene permisos para acceder a este recurso']);
                    exit();
                }
                
                // Usuario no tiene el rol permitido, redirigir según su rol
                if ($rolUsuario === 'pasajero') {
                    header('Location: /buslinnes/templates/passenger_interface.html');
                } elseif ($rolUsuario === 'conductor') {
                    header('Location: /buslinnes/templates/driver_interface.html');
                } elseif ($rolUsuario === 'admin') {
                    header('Location: /buslinnes/templates/buslinnes_interface.html');
                } else {
                    header('Location: /buslinnes/public/login.php');
                }
                exit();
            }
        }
        
        // Token válido, retornar los datos decodificados
        return $decoded;
        
    } catch (Exception $e) {
        // Token inválido o expirado
        setcookie('jwt_token', '', time() - 3600, '/');
        
        if ($isAjax) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Token inválido', 'message' => $e->getMessage()]);
            exit();
        } else {
            header('Location: /buslinnes/public/login.php?error=invalid_token');
            exit();
        }
    }
}

// Validar automáticamente si se incluye el archivo (sin especificar roles)
// Si necesitas validar roles específicos, llama a validarTokenJWT(['admin']) después de incluir
if (!defined('VALIDAR_JWT_MANUAL') && !defined('VALIDAR_JWT_SKIP_AUTORUN')) {
    validarTokenJWT();
}


