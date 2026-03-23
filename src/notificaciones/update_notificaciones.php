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
Procesa el formulario de edici�n de notificaci�n. Destino: usuario O rol.
@yerson @2025
*/
if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']);

if (!isset($_POST['id_notificacion']) || !isset($_POST['tipo_destino']) ||
    !isset($_POST['titulo_notificacion']) || !isset($_POST['descr_notificacion'])) {
    header("Location: listar_notificaciones.php?error_update=1");
    exit();
}

$id_notificacion     = (int) $_POST["id_notificacion"];
$tipo_destino        = $_POST["tipo_destino"];
$titulo_notificacion = trim((string) $_POST["titulo_notificacion"]);
$descr_notificacion  = trim((string) $_POST["descr_notificacion"]);

$id_usuario = null;
$id_rol     = null;
if ($tipo_destino === 'usuario') {
    if (!isset($_POST["id_usuario"]) || $_POST["id_usuario"] === '') {
        header("Location: editar_notificaciones.php?id_notificacion=" . $id_notificacion . "&error=1");
        exit();
    }
    $id_usuario = (int) $_POST["id_usuario"];
} elseif ($tipo_destino === 'rol') {
    if (!isset($_POST["id_rol"]) || $_POST["id_rol"] === '') {
        header("Location: editar_notificaciones.php?id_notificacion=" . $id_notificacion . "&error=1");
        exit();
    }
    $id_rol = (int) $_POST["id_rol"];
} else {
    header("Location: listar_notificaciones.php?error_update=1");
    exit();
}

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT fun_update_notificaciones(?, ?, ?, ?, ?);");
$sentencia->execute([
    $id_notificacion,
    $id_usuario,
    $id_rol,
    $titulo_notificacion,
    $descr_notificacion
]);

$fila    = $sentencia->fetch(PDO::FETCH_NUM);
$mensaje = $fila[0] ?? '';

if ($mensaje === 'Actualizaci�n exitosa') {
    // Enviar push v�a OneSignal a los destinatarios
    require_once __DIR__ . '/../onesignal_helper.php';
    $push = enviar_notificacion_onesignal($titulo_notificacion, $descr_notificacion, $id_usuario, $id_rol);
    if (!$push['ok'] && !empty($push['error'])) {
        header("Location: listar_notificaciones.php?actualizado=1&push_error=" . urlencode($push['error']));
    } else {
        header("Location: listar_notificaciones.php?actualizado=1");
    }
    exit();
} else {
    header("Location: listar_notificaciones.php?error_update=1&msg=" . urlencode($mensaje));
}
exit();


