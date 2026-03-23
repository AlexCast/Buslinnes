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
Este archivo inserta los datos enviados a trav�s de forma_usuarios.php
==================================================================
*/
if (!isset($_POST["nombre"]) || !isset($_POST["correo"]) || !isset($_POST["contrasena"])) {
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

// Incluir archivos necesarios
include_once "../base_de_datos.php";
include_once "../../config/database.php";
include_once "../../app/userClass.php";

$nombre = $_POST["nombre"];
$correo = $_POST["correo"];
$contrasena = $_POST["contrasena"];

try {
    // Usar el mismo m�todo que el registro p�blico para mantener consistencia
    // Este m�todo hashea la contrase�a correctamente con password_hash()
    $userClass = new userClass();
    $resultado = $userClass->fun_insert_usuario($contrasena, $correo, $nombre);

    if ($resultado === true) {
        header("Location: listar_usuarios.php");
        exit();
    } else {
        echo "Error: El usuario ya existe o hubo un problema al crear el usuario.";
    }
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>



