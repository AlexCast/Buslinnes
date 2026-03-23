<?php
header('Content-Type: text/html; charset=utf-8');

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
==================================================================
Este archivo inserta los datos enviados a trav�s de forma_cambio_bus.php
Tabla: tab_cambio_bus
==================================================================
*/

// Validar que existan los campos requeridos
if (!isset($_POST["id_cambio"])      ||
    !isset($_POST["id_bus_salida"])  ||
    !isset($_POST["id_bus_entrada"]) ||
    !isset($_POST["fecha_cambio"])   ||
    !isset($_POST["motivo"])         ||
    !isset($_POST["usuario"]))
{
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

include_once "../base_de_datos.php";

//Recoger valores del formulario
$id_cambio     = (int)$_POST["id_cambio"];
$id_bus_salida = (int)$_POST["id_bus_salida"];
$id_bus_entrada= (int)$_POST["id_bus_entrada"];
$fecha_cambio  = (string)$_POST["fecha_cambio"];  // formato YYYY-MM-DD
$motivo        = (string)$_POST["motivo"];
$usuario       = (string)$_POST["usuario"];

try {
    //Preparar llamada a la funci�n de PostgreSQL
    //Debes tener una funci�n fun_insert_cambio_bus que reciba estos par�metros
    $sentencia = $base_de_datos->prepare("
        SELECT fun_insert_cambio_bus(?, ?, ?, ?, ?, ?);
    ");

    // 4?? Ejecutar con par�metros en el orden correcto
    $resultado = $sentencia->execute([
        $id_cambio,
        $id_bus_salida,
        $id_bus_entrada,
        $fecha_cambio,
        $motivo,
        $usuario
    ]);
    
    if ($resultado) {
        header("Location: listar_cambio_bus.php?insertado=1");
        exit();
    } else {
        $errorInfo = $sentencia->errorInfo();
        throw new Exception("Error al insertar: " . $errorInfo[2]);
    }

} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold; margin: 2em;'>";
    echo "Error al insertar el cambio de bus: " . htmlspecialchars($e->getMessage());
    echo "</div>";
    exit();
}



