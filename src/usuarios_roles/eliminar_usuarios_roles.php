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
CRUD con PostgreSQL y PHP
autor: alexndrcastt
==================================================================
Este archivo elimina (soft delete) una asignaci�n usuario-rol
==================================================================
*/

if (!isset($_POST["id_usuario"]) || !isset($_POST["id_rol"])) {
    exit();
}

$id_usuario = $_POST["id_usuario"];
$id_rol = $_POST["id_rol"];
include_once "../base_de_datos.php";

try {
    $sentencia = $base_de_datos->prepare("SELECT fun_softdelete_usuarios_roles(?, ?);");
    $resultado = $sentencia->execute([$id_usuario, $id_rol]);

    if ($resultado === true) {
        header("Location: listar_usuarios_roles.php");
    } else {
        echo "Algo sali� mal";
    }
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
?>



