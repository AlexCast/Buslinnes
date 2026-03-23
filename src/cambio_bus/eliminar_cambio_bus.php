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
Elimina un cambio de bus por su ID
*/

if (!isset($_POST["id_cambio_bus"])) {
    echo "No se especific� el cambio del bus a eliminar";
    exit();
}

$id_cambio_bus = $_POST["id_cambio_bus"];

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_cambio_bus(?);");
$resultado = $sentencia->execute([$id_cambio_bus]);

if ($resultado === true) {
    header("Location: listar_cambio_bus.php");
    exit();
} else {
    echo "Algo sali� mal. Verifica que el cambio del bus exista.";
    $error = $sentencia->errorInfo();
    echo "Error en la consulta: " . $error[2];
}
?>



