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
Inserta una notificaci�n. Destino: un usuario O un rol (seg�n tipo_destino).
@yerson @2025
*/
if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']);

if (!isset($_POST["id_notificacion"]) || !isset($_POST["tipo_destino"]) ||
    !isset($_POST["titulo_notificacion"]) || !isset($_POST["descr_notificacion"])) {
    header("Location: forma_notificaciones.php?error=" . urlencode("Faltan campos obligatorios"));
    exit();
}

$id_notificacion_txt = trim((string) $_POST["id_notificacion"]);
if (!preg_match('/^[0-9]+$/', $id_notificacion_txt) || (int) $id_notificacion_txt <= 0) {
    header("Location: forma_notificaciones.php?error=" . urlencode("id_notificacion invalido"));
    exit();
}
$id_notificacion = (int) $id_notificacion_txt;

$tipo_destino = trim((string) $_POST["tipo_destino"]);
if (!in_array($tipo_destino, ['usuario', 'rol', 'todos'], true)) {
    header("Location: forma_notificaciones.php?error=" . urlencode("tipo_destino invalido"));
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
    header("Location: forma_notificaciones.php?error=" . urlencode("El titulo debe tener entre 3 y 120 caracteres"));
    exit();
}
if ($lenDescr < 5 || $lenDescr > 2000) {
    header("Location: forma_notificaciones.php?error=" . urlencode("La descripcion debe tener entre 5 y 2000 caracteres"));
    exit();
}

$id_usuario = null;
$id_rol     = null;
$onesignal_id_usuario = null;
$onesignal_id_rol     = null;

if ($tipo_destino === 'usuario') {
    if (!isset($_POST["id_usuario"]) || $_POST["id_usuario"] === '') {
        header("Location: forma_notificaciones.php?error=" . urlencode("Seleccione un usuario"));
        exit();
    }
    $id_usuario_txt = trim((string) $_POST["id_usuario"]);
    if (!preg_match('/^[0-9]+$/', $id_usuario_txt) || (int) $id_usuario_txt <= 0) {
        header("Location: forma_notificaciones.php?error=" . urlencode("id_usuario invalido"));
        exit();
    }
    $id_usuario = (int) $id_usuario_txt;
    $onesignal_id_usuario = $id_usuario;
} elseif ($tipo_destino === 'rol') {
    if (!isset($_POST["id_rol"]) || $_POST["id_rol"] === '') {
        header("Location: forma_notificaciones.php?error=" . urlencode("Seleccione un rol"));
        exit();
    }
    $id_rol_txt = trim((string) $_POST["id_rol"]);
    if (!preg_match('/^[0-9]+$/', $id_rol_txt) || (int) $id_rol_txt <= 0) {
        header("Location: forma_notificaciones.php?error=" . urlencode("id_rol invalido"));
        exit();
    }
    $id_rol = (int) $id_rol_txt;
    $onesignal_id_rol = $id_rol;
} elseif ($tipo_destino === 'todos') {
    $onesignal_id_usuario = null;
    $onesignal_id_rol = null;
} else {
    header("Location: forma_notificaciones.php?error=" . urlencode("Elija destino"));
    exit();
}

include_once "../base_de_datos.php";

try {
    $sentencia = $base_de_datos->prepare("SELECT fun_insert_notificaciones(?, ?, ?, ?, ?);");
    $resultado = $sentencia->execute([
        $id_notificacion,
        $id_usuario,
        $id_rol,    
        $titulo_notificacion,
        $descr_notificacion
    ]);
    $fila = $sentencia->fetch(PDO::FETCH_NUM);
    $msg  = $fila[0] ?? '';

    error_log("DEBUG insertar - resultado: " . ($resultado ? 'true' : 'false') . " | msg: " . var_export($msg, true));
    
    // Estrategia: validar verificando directamente en BD si el registro fue insertado
    // En lugar de confiar solo en el mensaje de retorno
    $insertOk = false;
    if ($resultado) {
        // Verificar si el registro existe en la base de datos
        $check = $base_de_datos->prepare("SELECT COUNT(*) as cnt FROM tab_notificaciones WHERE id_notificacion = ? AND fec_delete IS NULL;");
        $check->execute([$id_notificacion]);
        $check_result = $check->fetch(PDO::FETCH_OBJ);
        $insertOk = ($check_result && $check_result->cnt > 0);
        error_log("DEBUG insertar - check en BD: cnt=" . ($check_result->cnt ?? 0) . " | insertOk=" . ($insertOk ? 'true' : 'false'));
    }
    
    if ($insertOk) {
        // Verificar si se debe enviar push
        $enviar_push = isset($_POST['enviar_push']) && $_POST['enviar_push'] === '1';
        
        if ($enviar_push) {
            // Enviar push v�a OneSignal a los destinatarios
            require_once __DIR__ . '/../onesignal_helper.php';
            
            // Debug: Log de lo que se est� enviando
            error_log("OneSignal Debug - T�tulo: $titulo_notificacion, Usuario: " . ($onesignal_id_usuario ?? 'null') . ", Rol: " . ($onesignal_id_rol ?? 'null'));
            
            $push = enviar_notificacion_onesignal($titulo_notificacion, $descr_notificacion, $onesignal_id_usuario ?? null, $onesignal_id_rol ?? null);
            
            // Debug: Log de la respuesta
            error_log("OneSignal Response - OK: " . ($push['ok'] ? 'true' : 'false') . ", Error: " . ($push['error'] ?? 'none'));
            
            if (!$push['ok'] && !empty($push['error'])) {
                // Push fall�; la notificaci�n ya est� guardada. Redirigir con advertencia opcional.
                header("Location: listar_notificaciones.php?insertado=1&push_error=" . urlencode($push['error']));
            } else {
                header("Location: listar_notificaciones.php?insertado=1&push_enviado=1");
            }
        } else {
            header("Location: listar_notificaciones.php?insertado=1&sin_push=1");
        }
        exit();
    }
    header("Location: forma_notificaciones.php?error=" . urlencode($msg ?: "Error al insertar"));
    exit();
} catch (Exception $e) {
    header("Location: forma_notificaciones.php?error=" . urlencode($e->getMessage()));
    exit();
}



