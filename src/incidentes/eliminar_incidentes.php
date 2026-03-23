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
Elimina un bus por su ID
Autor: yerson
*/

if (!isset($_POST["id_incidente"])) {
    echo "No se especific� el incidente a eliminar";
    exit();
}

$id_incidente = $_POST["id_incidente"];

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_incidentes(?);");
$resultado = $sentencia->execute([$id_incidente]);

if ($resultado === true) {
    header("Location: listar_incidentes.php");
    exit();
} else {
    echo "Algo sali� mal. Verifica que el incidente exista.";
    $error = $sentencia->errorInfo();
    echo "Error en la consulta: " . $error[2];
}
?>



