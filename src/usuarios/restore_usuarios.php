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

if (!isset($_GET["id_usuario"])) {
    exit();
}

$id_usuario = $_GET["id_usuario"];
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


