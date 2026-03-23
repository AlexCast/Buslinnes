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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['id_pasajero']) || isset($_POST['id_usuario']))) {
    $id_pasajero = $_POST['id_pasajero'] ?? $_POST['id_usuario'];

    $sentencia = $base_de_datos->prepare("SELECT fun_restore_pasajeros(?);");
    $resultado = $sentencia->execute([$id_pasajero]);
    
    if ($resultado) {
        header("Location: listar_pasajeros.php?restaurado=1");
    } else {
        header("Location: restore_pasajeros.php?error=1");
    }
    exit();
}

header("Location: restore_pasajeros.php");

