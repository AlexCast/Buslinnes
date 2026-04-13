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
Este archivo actualiza los datos enviados a través de editar_roles.php
==================================================================
*/

if (!isset($_POST["id_rol"]) || !isset($_POST["nombre_rol"])) {
    echo "Faltan campos obligatorios";
    exit();
}

$id_rol_txt = trim((string) $_POST["id_rol"]);
if (!preg_match('/^[0-9]+$/', $id_rol_txt) || (int) $id_rol_txt <= 0) {
    echo "El id_rol es invalido";
    exit();
}
$id_rol = (int) $id_rol_txt;

$nombre_rol = trim((string) $_POST["nombre_rol"]);
if (function_exists('mb_strlen')) {
    $longitud = mb_strlen($nombre_rol);
} elseif (function_exists('iconv_strlen')) {
    $longitud = iconv_strlen($nombre_rol, 'UTF-8');
    if ($longitud === false) {
        $longitud = strlen($nombre_rol);
    }
} else {
    $longitud = strlen($nombre_rol);
}
if ($longitud < 3 || $longitud > 40) {
    echo "El nombre del rol debe tener entre 3 y 40 caracteres";
    exit();
}
if (!preg_match('/^[A-Za-z0-9_\-\s]+$/', $nombre_rol)) {
    echo "El nombre del rol contiene caracteres no permitidos";
    exit();
}

include_once "../base_de_datos.php";

try {
    $sentencia = $base_de_datos->prepare("SELECT fun_update_roles(?, ?);");
    $resultado = $sentencia->execute([$id_rol, $nombre_rol]);

    if ($resultado === true) {
        header("Location: listar_roles.php");
    } else {
        echo "Algo salió mal. Por favor verifica que la tabla exista";
    }
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
?>


