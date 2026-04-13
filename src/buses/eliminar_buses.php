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
Autor: alexndrcastt
*/

if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']);

if (!isset($_POST["id_bus"])) {
    echo "No se especific� el bus a eliminar";
    exit();
}

$id_bus_txt = strtoupper(trim((string) $_POST["id_bus"]));
$id_bus_compacto = preg_replace('/\s+/', '', $id_bus_txt);
if (!preg_match('/^[A-Z]{3}[0-9]{3}$/', $id_bus_compacto)) {
    header("Location: listar_buses.php?error_delete=1&msg=" . urlencode("id_bus invalido"));
    exit();
}
$id_bus = $id_bus_compacto;

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT fun_softdelete_buses(?);");
$resultado = $sentencia->execute([$id_bus]);

if ($resultado === true) {
    header("Location: listar_buses.php");
    exit();
} else {
    echo "Algo sali� mal. Verifica que el bus exista.";
    $error = $sentencia->errorInfo();
    echo "Error en la consulta: " . $error[2];
}
?>



