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
include_once "../base_de_datos.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_bus'])) {
    $id_bus = $_POST['id_bus'];

    $sentencia = $base_de_datos->prepare("SELECT fun_restore_buses(?);");
    $resultado = $sentencia->execute([$id_bus]);
    
    if ($resultado) {
        header("Location: listar_buses.php?restaurado=1");
    } else {
        header("Location: restore_buses.php?error=1");
    }
    exit();
}

header("Location: restore_buses.php");

