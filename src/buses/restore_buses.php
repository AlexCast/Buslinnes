<?php
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
Archivo para procesar la restauración de buses
@alexndrcastt
*/

if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']);

include_once "../base_de_datos.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_bus'])) {
    $id_bus_txt = strtoupper(trim((string) $_POST['id_bus']));
    $id_bus_compacto = preg_replace('/\s+/', '', $id_bus_txt);
    if (!preg_match('/^[A-Z]{3}[0-9]{3}$/', $id_bus_compacto)) {
        header("Location: listar_buses.php?error_restore=1&msg=" . urlencode("id_bus invalido"));
        exit();
    }
    $id_bus = $id_bus_compacto;

    $sentencia = $base_de_datos->prepare("SELECT fun_restore_buses(?);");
    $resultado = $sentencia->execute([$id_bus]);
    
    if ($resultado) {
        header("Location: listar_buses.php?restaurado=1");
    } else {
        header("Location: listar_buses.php?error_restore=1");
    }
    exit();
}

header("Location: listar_buses.php");

