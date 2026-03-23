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
CRUD con PostgreSQL y PHP
autor: alexndrcastt
=================================================================
Actualiza registros en la tabla tab_cambio_bus
=================================================================
*/

# Verificar que todos los campos obligatorios estén presentes
if (
    !isset($_POST["id_cambio"])      ||
    !isset($_POST["id_bus"])         ||
    !isset($_POST["id_conductor"])   ||
    !isset($_POST["fecha_cambio"])   ||
    !isset($_POST["motivo_cambio"])  ||
    !isset($_POST["usr_update"])
) {
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

include_once "../base_de_datos.php";

/* 
   Sanitizar / convertir datos.
   Ajusta los tipos según tu estructura real de tab_cambio_bus
*/
$id_cambio      = (int)    $_POST["id_cambio"];
$id_bus         = (int)    $_POST["id_bus"];
$id_conductor   = (int)    $_POST["id_conductor"];
$fecha_cambio   = (string) $_POST["fecha_cambio"];   // formato 'YYYY-MM-DD' o 'YYYY-MM-DD HH:MM'
$motivo_cambio  = (string) $_POST["motivo_cambio"];
$usr_update     = (string) $_POST["usr_update"];

try {
    // Llamada a la función que actualiza en PostgreSQL
    $sentencia = $base_de_datos->prepare("
        SELECT fun_update_cambio_bus(?, ?, ?, ?, ?, ?);
    ");

    $resultado = $sentencia->execute([
        $id_cambio,
        $id_bus,
        $id_conductor,
        $fecha_cambio,
        $motivo_cambio,
        $usr_update
    ]);

    if ($resultado) {
        // Redirigir a la lista de cambios con mensaje de éxito
        header("Location: listar_cambio_bus.php?actualizado=1");
        exit();
    } else {
        $errorInfo = $sentencia->errorInfo();
        throw new Exception("Error al actualizar: " . $errorInfo[2]);
    }
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold; margin: 2em;'>";
    echo "Error al actualizar el registro de cambio de bus: " . htmlspecialchars($e->getMessage());
    echo "</div>";
    exit();
}


