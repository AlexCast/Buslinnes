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

if (!isset($_POST["id_usuario"]) || !isset($_POST["nombre"]) || !isset($_POST["correo"]) || !isset($_POST["contrasena"])) {
    echo "Faltan campos obligatorios";
    exit();
}

include_once "../base_de_datos.php";

$id_usuario = $_POST["id_usuario"];
$nombre = $_POST["nombre"];
$correo = $_POST["correo"];
$contrasena = $_POST["contrasena"];
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


