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
Este archivo inserta los datos enviados a trav�s de forma_roles.php
==================================================================
*/
if (!isset($_POST["nombre_rol"])) {
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

include_once "../base_de_datos.php";

$nombre_rol = $_POST["nombre_rol"];
$usr_insert = '1'; // Usuario por defecto o tomar de sesi�n

try {
    $sentencia = $base_de_datos->prepare("SELECT fun_insert_tab_roles(?, ?);");
    $resultado = $sentencia->execute([$nombre_rol, $usr_insert]);

    if ($resultado === true) {
        header("Location: listar_roles.php");
    } else {
        echo "Algo sali� mal. Por favor verifica que la tabla exista";
    }
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
?>



