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
autor: alexndrcastt
==================================================================
Este archivo inserta los datos enviados a trav�s de forma_buses.php
==================================================================
*/
if (!isset($_POST["id_bus"])   ||
    !isset($_POST["id_conductor"])     ||
    !isset($_POST["num_chasis"])       ||
    !isset($_POST["matricula"])        ||
    !isset($_POST["anio_fab"])         ||
    !isset($_POST["capacidad_pasajeros"]) ||
    !isset($_POST["tipo_bus"])         ||
    !isset($_POST["gps"])              ||
    !isset($_POST["ind_estado_buses"])) 
{
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

include_once "../base_de_datos.php";

// Recoger todos los valores del formulario

$id_bus               = (int)$_POST["id_bus"];
$id_conductor         = (int)$_POST["id_conductor"];
$num_chasis           = (string)$_POST["num_chasis"];
$matricula            = (string)$_POST["matricula"];
$anio_fab             = is_numeric($_POST["anio_fab"]) ? (float)$_POST["anio_fab"] : null;
$capacidad_pasajeros  = is_numeric($_POST["capacidad_pasajeros"]) ? (float)$_POST["capacidad_pasajeros"] : null;
$tipo_bus             = (string)$_POST["tipo_bus"];
$gps                  = $_POST["gps"] === 'true' ? true : false;
$ind_estado_buses     = (string)$_POST["ind_estado_buses"];

try {
    // Preparar la llamada a la funci�n de PostgreSQL
    $sentencia = $base_de_datos->prepare("SELECT fun_insert_buses(?, ?, ?, ?, ?, ?, ?, ?, ?);");
    // Ejecutar con todos los par�metros en el orden correcto
    $resultado = $sentencia->execute([
        $id_bus,
        $id_conductor,
        $num_chasis,
        $matricula,
        $anio_fab,
        $capacidad_pasajeros,
        $tipo_bus,
        $gps,
        $ind_estado_buses
    ]);
    
    if ($resultado) {
        // �xito: redirigir al listado con mensaje
        header("Location: listar_buses.php?insertado=1");
        exit();
    } else {
        // Error en la ejecuci�n
        $errorInfo = $sentencia->errorInfo();
        throw new Exception("Error al insertar: " . $errorInfo[2]);
    }
} catch (Exception $e) {
    // Mostrar el error directamente en pantalla para depuraci�n
    echo "<div style='color: red; font-weight: bold; margin: 2em;'>";
    echo "Error al insertar el bus: " . htmlspecialchars($e->getMessage());
    echo "</div>";
    exit();
}



