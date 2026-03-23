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
Elimina (soft delete) una ruta-bus por su ID
Autor: oto
*/

if (!isset($_POST["id_ruta_bus"])) {
    echo "No se especific� la ruta-bus a eliminar";
    exit();
}

$id_ruta_bus = $_POST["id_ruta_bus"];

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_ruta_bus(?);");
$sentencia->execute([$id_ruta_bus]);
$resultado = $sentencia->fetchColumn();

if ($resultado === true) {
    header("Location: listar_rutas_buses.php");
    exit();
} else {
    echo "Algo sali� mal. Verifica que la ruta-bus exista.";
    $error = $sentencia->errorInfo();
    echo " Error en la consulta: " . $error[2];
}
?>




