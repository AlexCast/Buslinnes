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
@yerson
*/
include_once "../base_de_datos.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_mantenimiento'])) {
    $id_mantenimiento = $_POST['id_mantenimiento'];

    $sentencia = $base_de_datos->prepare("SELECT fun_restore_mantenimiento(?);");
    $resultado = $sentencia->execute([$id_mantenimiento]);
    
    if ($resultado) {
        header("Location: listar_mantenimiento.php?restaurado=1");
    } else {
        header("Location: restore_mantenimiento.php?error=1");
    }
    exit();
}

header("Location: restore_mantenimiento.php");

