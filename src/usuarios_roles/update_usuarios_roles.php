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

if (!isset($_POST["id_usuario"]) || !isset($_POST["id_rol_old"]) || !isset($_POST["id_rol_new"])) {
    echo "Faltan campos obligatorios";
    exit();
}

include_once "../base_de_datos.php";

$id_usuario = $_POST["id_usuario"];
$id_rol_old = $_POST["id_rol_old"];
$id_rol_new = $_POST["id_rol_new"];

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


