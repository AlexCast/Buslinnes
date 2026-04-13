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

$id_notificacion_txt = trim((string) $_POST["id_notificacion"]);
if (!preg_match('/^[0-9]+$/', $id_notificacion_txt) || (int) $id_notificacion_txt <= 0) {
    header("Location: listar_notificaciones.php?error_update=1&msg=" . urlencode("id_notificacion invalido"));
    exit();
}
$id_notificacion = (int) $id_notificacion_txt;

$tipo_destino = trim((string) $_POST["tipo_destino"]);
if (!in_array($tipo_destino, ['usuario', 'rol', 'todos'], true)) {
    header("Location: listar_notificaciones.php?error_update=1&msg=" . urlencode("tipo_destino invalido"));
    exit();
}

$titulo_notificacion = trim((string) $_POST["titulo_notificacion"]);
$descr_notificacion = trim((string) $_POST["descr_notificacion"]);
if (function_exists('mb_strlen')) {
    $lenTitulo = mb_strlen($titulo_notificacion);
    $lenDescr = mb_strlen($descr_notificacion);
} elseif (function_exists('iconv_strlen')) {
    $lenTitulo = iconv_strlen($titulo_notificacion, 'UTF-8');
    $lenDescr = iconv_strlen($descr_notificacion, 'UTF-8');
    if ($lenTitulo === false) {
        $lenTitulo = strlen($titulo_notificacion);
    }
    if ($lenDescr === false) {
        $lenDescr = strlen($descr_notificacion);
    }
} else {
    $lenTitulo = strlen($titulo_notificacion);
    $lenDescr = strlen($descr_notificacion);
}
if ($lenTitulo < 3 || $lenTitulo > 120) {
    header("Location: listar_notificaciones.php?error_update=1&msg=" . urlencode("Titulo fuera de rango"));
    exit();
}
if ($lenDescr < 5 || $lenDescr > 2000) {
    header("Location: listar_notificaciones.php?error_update=1&msg=" . urlencode("Descripcion fuera de rango"));
    exit();
}

$id_usuario = null;
$id_rol     = null;
if ($tipo_destino === 'usuario') {
    if (!isset($_POST["id_usuario"]) || $_POST["id_usuario"] === '') {
        header("Location: editar_notificaciones.php?id_notificacion=" . $id_notificacion . "&error=1");
        exit();
    }
    $id_usuario_txt = trim((string) $_POST["id_usuario"]);
    if (!preg_match('/^[0-9]+$/', $id_usuario_txt) || (int) $id_usuario_txt <= 0) {
        header("Location: editar_notificaciones.php?id_notificacion=" . $id_notificacion . "&error=1");
        exit();
    }
    $id_usuario = (int) $id_usuario_txt;
} elseif ($tipo_destino === 'rol') {
    if (!isset($_POST["id_rol"]) || $_POST["id_rol"] === '') {
        header("Location: editar_notificaciones.php?id_notificacion=" . $id_notificacion . "&error=1");
        exit();
    }
    $id_rol_txt = trim((string) $_POST["id_rol"]);
    if (!preg_match('/^[0-9]+$/', $id_rol_txt) || (int) $id_rol_txt <= 0) {
        header("Location: editar_notificaciones.php?id_notificacion=" . $id_notificacion . "&error=1");
        exit();
    }
    $id_rol = (int) $id_rol_txt;
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

$esActualizacionExitosa = is_string($mensaje)
    && stripos($mensaje, 'actualiz') !== false
    && stripos($mensaje, 'exitosa') !== false;

if ($esActualizacionExitosa) {
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


