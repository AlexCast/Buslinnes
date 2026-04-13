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
    $id_parque_automotor = trim((string) $_POST['id_parque_automotor']);
    if (!preg_match('/^[0-9]+$/', $id_parque_automotor)) {
        header("Location: listar_parque_automotor.php?error=1");
        exit();
    }

    $sentencia = $base_de_datos->prepare("SELECT fun_restore_parque_automotor(?);");
    $sentencia->execute([$id_parque_automotor]);
    $resultado = $sentencia->fetchColumn();
    $ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';
    
    if ($ok) {
        header("Location: listar_parque_automotor.php?restaurado=1");
    } else {
        header("Location: listar_parque_automotor.php?error=1");
    }
    exit();
}

header("Location: listar_parque_automotor.php");

