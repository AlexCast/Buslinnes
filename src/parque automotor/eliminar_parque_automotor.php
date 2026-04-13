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

$id_parque_automotor = trim((string) $_POST["id_parque_automotor"]);
if (!preg_match('/^[0-9]+$/', $id_parque_automotor)) {
    echo "El ID de parque automotor no es valido.";
    exit();
}

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_parque_automotor(?);");
$sentencia->execute([$id_parque_automotor]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true' || strtolower((string) $resultado) === 'registro eliminado lógicamente correctamente.';

if ($ok) {
    header("Location: listar_parque_automotor.php");
    exit();
} else {
    echo "Algo sali� mal. Verifica que el parque automotor exista.";
    $error = $sentencia->errorInfo();
    echo "Error en la consulta: " . $error[2];
}
?>



