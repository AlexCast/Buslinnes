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
Elimina un pasajero por su ID
Autor: @yerson
*/

if (!isset($_POST["id_pasajero"])) {
    echo "No se especific� el pasajero a eliminar.";
    exit();
}

$id_pasajero = $_POST["id_pasajero"];

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_pasajeros(?);");
$resultado = $sentencia->execute([$id_pasajero]);

if ($resultado === true) {
    header("Location: listar_pasajeros.php");
    exit();
} else {
    echo "No se pudo eliminar el pasajero. Verifica que el ID exista o no est� relacionado con otras tablas.";
    $error = $sentencia->errorInfo();
    echo "Error en la consulta: " . $error[2];
}
?>



