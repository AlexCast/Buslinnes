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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario'])) {
    $id_usuario = trim((string) $_POST['id_usuario']);
    if (!preg_match('/^[0-9]+$/', $id_usuario)) {
        header("Location: listar_conductores.php?error=1");
        exit();
    }

    try {
        $sentencia = $base_de_datos->prepare("SELECT fun_restore_conductores(?);");
        $sentencia->execute([$id_usuario]);
        $resultado = $sentencia->fetchColumn();
        $ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

        if ($ok) {
            header("Location: listar_conductores.php?restaurado=1");
        } else {
            header("Location: listar_conductores.php?error=1");
        }
    } catch (PDOException $e) {
        header("Location: listar_conductores.php?error=1");
    }
    exit();
}

header("Location: listar_conductores.php");


