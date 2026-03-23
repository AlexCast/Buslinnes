<?php
header('Content-Type: text/html; charset=utf-8');

// === SEGURIDAD: Proteccion anti-scraping y CSRF ===
require_once __DIR__ . '/../../app/SecurityMiddleware.php';

SecurityMiddleware::protect([
    'csrf' => true,  // POST/PUT/DELETE requiere CSRF
    'rateLimit' => true,
    'origin' => true,
    'userAgent' => true,
    'securityHeaders' => true
]);
// === FIN SEGURIDAD ===

/*
CRUD con PostgreSQL y PHP
Elimina una notificaci�n por su ID (soft delete).
@yerson @2025
*/

if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']);

if (!isset($_POST["id_notificacion"])) {
    echo "No se especific� la notificaci�n a eliminar";
    exit();
}

$id_notificacion = $_POST["id_notificacion"];

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_notificaciones(?);");
$resultado = $sentencia->execute([$id_notificacion]);

if ($resultado === true) {
    header("Location: listar_notificaciones.php");
    exit();
} else {
    echo "Algo sali� mal. Verifica que la notificaci�n exista.";
    $error = $sentencia->errorInfo();
    echo " Error en la consulta: " . $error[2];
}
?>



