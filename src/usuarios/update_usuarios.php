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
Este archivo actualiza los datos enviados a través de editar_usuarios.php
==================================================================
*/

define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']);

if (!isset($_POST["id_usuario"]) || !isset($_POST["nombre"]) || !isset($_POST["correo"]) || !isset($_POST["contrasena"])) {
    echo "Faltan campos obligatorios";
    exit();
}

$id_usuario_txt = trim((string) $_POST["id_usuario"]);
if (!preg_match('/^[0-9]+$/', $id_usuario_txt) || (int) $id_usuario_txt <= 0) {
    echo "El id_usuario es invalido";
    exit();
}
$id_usuario = (int) $id_usuario_txt;

$nombre = trim((string) $_POST["nombre"]);
if (function_exists('mb_strlen')) {
    $longitudNombre = mb_strlen($nombre);
} elseif (function_exists('iconv_strlen')) {
    $longitudNombre = iconv_strlen($nombre, 'UTF-8');
    if ($longitudNombre === false) {
        $longitudNombre = strlen($nombre);
    }
} else {
    $longitudNombre = strlen($nombre);
}
if ($longitudNombre < 3 || $longitudNombre > 120) {
    echo "El nombre debe tener entre 3 y 120 caracteres";
    exit();
}

$correo = trim((string) $_POST["correo"]);
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo "El correo no es valido";
    exit();
}

$contrasena = (string) $_POST["contrasena"];
$longitudContrasena = strlen($contrasena);
if ($longitudContrasena < 8 || $longitudContrasena > 72) {
    echo "La contrasena debe tener entre 8 y 72 caracteres";
    exit();
}

include_once "../base_de_datos.php";

$usr_update = '1'; // Usuario por defecto o tomar de sesión

try {
    $sentencia = $base_de_datos->prepare("SELECT fun_update_usuarios(?, ?, ?, ?, ?);");
    $resultado = $sentencia->execute([$id_usuario, $contrasena, $correo, $nombre, $usr_update]);

    if ($resultado === true) {
        header("Location: listar_usuarios.php");
    } else {
        echo "Algo salió mal. Por favor verifica que la tabla exista";
    }
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
?>


