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
@Carlos Eduardo Perez Rueda
2023

Adaptado por
@alexndrcastt
2025
=================================================================
Este archivo guarda los datos del formulario en donde se editan
=================================================================
*/

#Salir si alguno de los datos no está presente
if (!isset($_POST["id_bus"]) || 
    !isset($_POST["id_conductor"]) || 
    !isset($_POST["num_chasis"]) || 
    !isset($_POST["matricula"]) || 
    !isset($_POST["anio_fab"]) || 
    !isset($_POST["capacidad_pasajeros"]) || 
    !isset($_POST["tipo_bus"]) || 
    !isset($_POST["gps"]) || 
    !isset($_POST["ind_estado_buses"])) 
{
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

#Si todo va bien, se ejecuta esta parte del código...

include_once "../base_de_datos.php";

$id_bus = $_POST["id_bus"];
$id_conductor = $_POST["id_conductor"];
$num_chasis = $_POST["num_chasis"];
$matricula = $_POST["matricula"];
$anio_fab = $_POST["anio_fab"];
$capacidad_pasajeros = $_POST["capacidad_pasajeros"];
$tipo_bus = $_POST["tipo_bus"];
$gps = $_POST["gps"] === 'true' ? true : false;
$ind_estado_buses = $_POST["ind_estado_buses"];


$sentencia = $base_de_datos->prepare("SELECT fun_update_buses(?,?,?,?,?,?,?,?,?);");
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

if ($resultado === true) {
    header("Location: listar_buses.php");
} else {
    echo "Algo salió mal. Por favor verifica que la tabla exista, así como el ID del bus";
}


