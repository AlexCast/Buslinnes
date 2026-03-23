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
Archivo para procesar la restauración de cambio bus
*/
include_once "../base_de_datos.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cambio_bus'])) {
    $id_cambio_bus = $_POST['id_cambio_bus'];

    $sentencia = $base_de_datos->prepare("SELECT fun_restore_cambio_bus(?);");
    $resultado = $sentencia->execute([$id_cambio_bus]);
    
    if ($resultado) {
        header("Location: listar_cambio_bus.php?restaurado=1");
    } else {
        header("Location: restore_cambio_bus.php?error=1");
    }
    exit();
}

header("Location: restore_cambio_bus.php");

