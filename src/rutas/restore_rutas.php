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
Archivo para procesar la restauración de rutas
@oto
*/
include_once "../base_de_datos.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_ruta'])) {
    $id_ruta = $_POST['id_ruta'];
    
    // Llamar a la función de restauración en PostgreSQL
    $sentencia = $base_de_datos->prepare("SELECT fun_restore_rutas(?);");
    $resultado = $sentencia->execute([$id_ruta]);
    
    if ($resultado) {
        header("Location: listar_rutas.php?restaurado=1");
    } else {
        header("Location: restore_rutas.php?error=1");
    }
    exit();
}

header("Location: restore_rutas.php");


