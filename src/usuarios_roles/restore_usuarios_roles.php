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
Este archivo restaura una asignación usuario-rol eliminada
==================================================================
*/

define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST["id_usuario"]) || !isset($_POST["id_rol"])) {
    header("Location: listar_usuarios_roles.php");
    exit();
}

$id_usuario_txt = trim((string) $_POST["id_usuario"]);
$id_rol_txt = trim((string) $_POST["id_rol"]);
if (!preg_match('/^[0-9]+$/', $id_usuario_txt) || (int) $id_usuario_txt <= 0) {
    header("Location: listar_usuarios_roles.php?error_restore=1&msg=" . urlencode("id_usuario invalido"));
    exit();
}
if (!preg_match('/^[0-9]+$/', $id_rol_txt) || (int) $id_rol_txt <= 0) {
    header("Location: listar_usuarios_roles.php?error_restore=1&msg=" . urlencode("id_rol invalido"));
    exit();
}
$id_usuario = (int) $id_usuario_txt;
$id_rol = (int) $id_rol_txt;
include_once "../base_de_datos.php";

try {
    $sentencia = $base_de_datos->prepare("SELECT fun_restore_usuarios_roles(?, ?);");
    $resultado = $sentencia->execute([$id_usuario, $id_rol]);

    if ($resultado === true) {
        header("Location: listar_usuarios_roles.php");
    } else {
        echo "Algo salió mal";
    }
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
?>


