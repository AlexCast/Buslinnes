<?php
/**
 * Punto de entrada maestro de la aplicación Buslinnes
 * Maneja la redirección según el estado de autenticación
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

// Cargar configuración de forma segura
$config_path = __DIR__ . '/config/database.php';
if (file_exists($config_path)) {
    try {
        require_once $config_path;
    } catch (Exception $e) {
        // Si falla la BD, mostrar inicio sin autenticación
    }
}

// Intentar validar JWT existente
$token_valido = false;
$usuario_rol = null;

if (isset($_COOKIE['auth_token'])) {
    $jwt_path = __DIR__ . '/src/validar_jwt.php';
    if (file_exists($jwt_path)) {
        try {
            require_once $jwt_path;
            $token_data = validar_token($_COOKIE['auth_token']);
            $token_valido = true;
            $usuario_rol = $token_data['rol'] ?? null;
        } catch (Exception $e) {
            // Token inválido, proceder sin autenticación
        }
    }
}

// Determinar qué página mostrar
if (!$token_valido) {
    // No autenticado: mostrar página de inicio
    $template_path = __DIR__ . '/templates/index.html';
    if (file_exists($template_path)) {
        include $template_path;
    } else {
        echo "<h1>Bienvenido a Buslinnes</h1><p>Página en construcción. <a href='/buslinnes/public/login.php'>Ir a login</a></p>";
    }
} else {
    // Autenticado: redirigir según rol
    switch ($usuario_rol) {
        case 'admin':
            header('Location: /buslinnes/templates/buslinnes_interface.html');
            break;
        case 'conductor':
            header('Location: /buslinnes/templates/driver_interface.html');
            break;
        case 'pasajero':
            header('Location: /buslinnes/templates/passenger_interface.html');
            break;
        default:
            header('Location: /buslinnes/templates/guest_interface.html');
    }
    exit;
}
?>

