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
CRUD con PostgreSQL y PHP
autor: alexndrcastt
==================================================================
Este archivo actualiza los datos enviados a través de editar_usuarios_roles.php
==================================================================
*/

define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']);

if (!isset($_POST["id_usuario"]) || !isset($_POST["id_rol_old"]) || !isset($_POST["id_rol_new"])) {
    echo "Faltan campos obligatorios";
    exit();
}

$id_usuario_txt = trim((string) $_POST["id_usuario"]);
$id_rol_old_txt = trim((string) $_POST["id_rol_old"]);
$id_rol_new_txt = trim((string) $_POST["id_rol_new"]);

if (!preg_match('/^[0-9]+$/', $id_usuario_txt) || (int) $id_usuario_txt <= 0) {
    echo "El id_usuario es invalido";
    exit();
}
if (!preg_match('/^[0-9]+$/', $id_rol_old_txt) || (int) $id_rol_old_txt <= 0) {
    echo "El id_rol_old es invalido";
    exit();
}
if (!preg_match('/^[0-9]+$/', $id_rol_new_txt) || (int) $id_rol_new_txt <= 0) {
    echo "El id_rol_new es invalido";
    exit();
}

include_once "../base_de_datos.php";

$id_usuario = (int) $id_usuario_txt;
$id_rol_old = (int) $id_rol_old_txt;
$id_rol_new = (int) $id_rol_new_txt;

try {
    $sentencia = $base_de_datos->prepare("SELECT fun_update_usuarios_roles(?, ?, ?);");
    $resultado = $sentencia->execute([$id_usuario, $id_rol_old, $id_rol_new]);

    if ($resultado === true) {
        header("Location: listar_usuarios_roles.php");
    } else {
        echo "Algo salió mal. Por favor verifica que la tabla exista";
    }
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
?>


