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
Elimina un mantenimiento por su ID
Autor: @yerson
*/

if (!isset($_POST["id_mantenimiento"])) {
    echo "No se especific� el mantenimiento a eliminar.";
    exit();
}

$id_mantenimiento = trim((string) $_POST["id_mantenimiento"]);
if (!preg_match('/^[0-9]+$/', $id_mantenimiento)) {
    echo "El ID de mantenimiento no es valido.";
    exit();
}

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_mantenimiento(?);");
$sentencia->execute([$id_mantenimiento]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header("Location: listar_mantenimiento.php");
    exit();
} else {
    echo "No se pudo eliminar el mantenimiento. Verifica que el ID exista o no est� relacionado con otras tablas.";
    $error = $sentencia->errorInfo();
    echo "Error en la consulta: " . $error[2];
}
?>



