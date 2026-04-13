<?php
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
Restaura una notificaci�n eliminada (soft delete).
@yerson @2025
*/
if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']);

include_once "../base_de_datos.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_notificacion'])) {
    header("Location: listar_notificaciones.php");
    exit();
}

$id_notificacion_txt = trim((string) $_POST['id_notificacion']);
if (!preg_match('/^[0-9]+$/', $id_notificacion_txt) || (int) $id_notificacion_txt <= 0) {
    header("Location: listar_notificaciones.php?error_restore=1&msg=" . urlencode("id_notificacion invalido"));
    exit();
}
$id_notificacion = (int) $id_notificacion_txt;

$sentencia = $base_de_datos->prepare("SELECT fun_restore_notificaciones(?);");
$resultado = $sentencia->execute([$id_notificacion]);

if ($resultado) {
    header("Location: listar_notificaciones.php?restaurado=1");
} else {
    header("Location: listar_notificaciones.php?error_restore=1");
}
exit();


