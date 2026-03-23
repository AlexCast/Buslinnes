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

include_once "../base_de_datos.php";

$id_rol = $_POST["id_rol"];
$nombre_rol = $_POST["nombre_rol"];

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


