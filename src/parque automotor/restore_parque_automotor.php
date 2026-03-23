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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_parque_automotor'])) {
    $id_parque_automotor = $_POST['id_parque_automotor'];

    $sentencia = $base_de_datos->prepare("SELECT fun_restore_parque_automotor(?);");
    $resultado = $sentencia->execute([$id_parque_automotor]);
    
    if ($resultado) {
        header("Location: listar_parque_automotor.php?restaurado=1");
    } else {
        header("Location: restore_parque_automotor.php?error=1");
    }
    exit();
}

header("Location: restore_parque_automotor.php");

