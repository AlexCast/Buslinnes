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
@yerson
@2025
=================================================================
Este archivo guarda los datos del formulario en donde se editan
=================================================================
*/
?>

<?php

# Validación mínima y valores por defecto
if (
    !isset($_POST["id_pasajero"]) || strlen($_POST["id_pasajero"]) < 1 ||
    !isset($_POST["nom_pasajero"]) || strlen($_POST["nom_pasajero"]) < 3 ||
    !isset($_POST["tel_pasajero"]) || strlen($_POST["tel_pasajero"]) < 5
) {
    echo "Salió mal: datos insuficientes o inválidos";
    exit();
}

#Si todo va bien, se ejecuta esta parte del código...

include_once "../base_de_datos.php";
$id_pasajero       = $_POST["id_pasajero"];
$nom_pasajero      = $_POST["nom_pasajero"];
$email_pasajero    = $_POST["tel_pasajero"];

$sentencia = $base_de_datos->prepare("SELECT fun_update_pasajeros(?,?,?) AS resultado;");

$sentencia->execute([$id_pasajero, $nom_pasajero, $email_pasajero]);
$respuesta = $sentencia->fetch(PDO::FETCH_ASSOC);
$mensaje = $respuesta['resultado'] ?? '';

if (stripos($mensaje, 'funcion') !== false || stripos($mensaje, 'actualiz') !== false) {
    header("Location: listar_pasajeros.php");
} else {
    echo "Algo salió mal: " . htmlspecialchars($mensaje);
}


