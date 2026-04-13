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
Este archivo restaura un usuario eliminado
==================================================================
*/

define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST["id_usuario"])) {
    header("Location: listar_usuarios.php");
    exit();
}

$id_usuario_txt = trim((string) $_POST["id_usuario"]);
if (!preg_match('/^[0-9]+$/', $id_usuario_txt) || (int) $id_usuario_txt <= 0) {
    header("Location: listar_usuarios.php?error_restore=1&msg=" . urlencode("id_usuario invalido"));
    exit();
}
$id_usuario = (int) $id_usuario_txt;
include_once "../base_de_datos.php";

try {
    $sentencia = $base_de_datos->prepare("SELECT fun_restore_usuarios(?);");
    $resultado = $sentencia->execute([$id_usuario]);

    if ($resultado === true) {
        header("Location: listar_usuarios.php");
    } else {
        echo "Algo salió mal";
    }
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
?>


