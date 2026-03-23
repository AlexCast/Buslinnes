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

if (!isset($_POST["id_parque_automotor"])) {
    echo "No se especific� el parque automotor a eliminar";
    exit();
}

$id_parque_automotor = $_POST["id_parque_automotor"];

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_parque_automotor(?);");
$resultado = $sentencia->execute([$id_parque_automotor]);

if ($resultado === true) {
    header("Location: listar_parque_automotor.php");
    exit();
} else {
    echo "Algo sali� mal. Verifica que el parque automotor exista.";
    $error = $sentencia->errorInfo();
    echo "Error en la consulta: " . $error[2];
}
?>



