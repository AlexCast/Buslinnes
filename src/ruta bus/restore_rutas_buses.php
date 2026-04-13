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
Archivo para procesar la restauración de rutas-buses
@oto
*/
include_once "../base_de_datos.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_ruta_bus'])) {
    $id_ruta_bus = trim((string) $_POST['id_ruta_bus']);
    if (!preg_match('/^[0-9]+$/', $id_ruta_bus)) {
        header("Location: listar_rutas_buses.php?error=1");
        exit();
    }
    
    // Llamar a la función de restauración en PostgreSQL
    $sentencia = $base_de_datos->prepare("SELECT fun_restore_ruta_bus(?);");
    $sentencia->execute([$id_ruta_bus]);
    $resultado = $sentencia->fetchColumn();
    $ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';
    
    if ($ok) {
        header("Location: listar_rutas_buses.php?restaurado=1");
    } else {
        header("Location: listar_rutas_buses.php?error=1");
    }
    exit();
}

header("Location: listar_rutas_buses.php");


