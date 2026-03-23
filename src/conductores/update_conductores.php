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
@Marzo de 2023

Adaptado por
@alexndrcastt
@2025
=================================================================
Este archivo guarda los datos del formulario en donde se editan
=================================================================
*/
?>

<?php

#Salir si alguno de los datos no está presente
if (
    !isset($_POST["id_conductor"]) ||
    !isset($_POST["nom_conductor"]) ||
    !isset($_POST["ape_conductor"]) ||
    !isset($_POST["tel_conductor"]) ||
    !isset($_POST["licencia_conductor"]) ||
    !isset($_POST["tipo_licencia"]) ||
    !isset($_POST["fec_venc_licencia"]) ||
    !isset($_POST["estado_conductor"]) ||
    !isset($_POST["edad"]) ||
    !isset($_POST["tipo_sangre"])
) {
    echo "Salió mal";
    exit();
}

#Si todo va bien, se ejecuta esta parte del código...

include_once "../base_de_datos.php";
$id_conductor       = $_POST["id_conductor"];
$nom_conductor      = $_POST["nom_conductor"];
$ape_conductor      = $_POST["ape_conductor"];
$email_conductor    = $_POST["tel_conductor"];
$licencia_conductor = $_POST["licencia_conductor"];
$tipo_licencia      = $_POST["tipo_licencia"];
$fec_venc_licencia  = $_POST["fec_venc_licencia"];
$estado_conductor   = $_POST["estado_conductor"];
$edad               = $_POST["edad"];
$tipo_sangre        = $_POST["tipo_sangre"];

$sentencia = $base_de_datos->prepare("SELECT fun_update_conductores(?,?,?,?,?,?,?,?,?,?) AS resultado;");

$sentencia->execute([
    $id_conductor,
    $nom_conductor,
    $ape_conductor,
    $email_conductor,
    $licencia_conductor,
    $tipo_licencia,
    $fec_venc_licencia,
    $estado_conductor,
    $edad,
    $tipo_sangre
]);
$respuesta = $sentencia->fetch(PDO::FETCH_ASSOC);
$mensaje = $respuesta['resultado'] ?? '';

if (stripos($mensaje, 'actualizado correctamente') !== false) {
    header("Location: listar_conductores.php");
} else {
    echo "Algo salió mal: " . htmlspecialchars($mensaje);
}


