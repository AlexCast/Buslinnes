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
Elimina un conductor por su ID
Autor: @alexndrcastt
*/

if (!isset($_POST["id_conductor"])) {
    echo "No se especific� el conductor a eliminar.";
    exit();
}

$id_conductor = $_POST["id_conductor"];

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_conductores(?);");
$resultado = $sentencia->execute([$id_conductor]);

if ($resultado === true) {
    header("Location: listar_conductores.php");
    exit();
} else {
    echo "No se pudo eliminar el conductor. Verifica que el ID exista o no est� relacionado con otras tablas.";
    $error = $sentencia->errorInfo();
    echo "Error en la consulta: " . $error[2];
}
?>



