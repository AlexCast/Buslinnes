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

if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']);

#Salir si alguno de los datos no está presente
if (!isset($_POST["id_bus"]) || 
    !isset($_POST["id_usuario"]) || 
    !isset($_POST["anio_fab"]) || 
    !isset($_POST["capacidad_pasajeros"]) || 
    !isset($_POST["tipo_bus"]) || 
    !isset($_POST["gps"]) || 
    !isset($_POST["ind_estado_buses"])) 
{
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

$salirConError = static function (string $mensaje): void {
    echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    exit();
};

$validarEntero = static function ($valor, string $campo, int $min, int $max) use ($salirConError): int {
    $texto = trim((string) $valor);
    if (!preg_match('/^[0-9]+$/', $texto)) {
        $salirConError("El campo {$campo} debe ser un entero positivo.");
    }
    $numero = (int) $texto;
    if ($numero < $min || $numero > $max) {
        $salirConError("El campo {$campo} esta fuera del rango permitido.");
    }
    return $numero;
};

$normalizarPlacaBus = static function ($valor) use ($salirConError): string {
    $texto = strtoupper(trim((string) $valor));
    $compacto = preg_replace('/\s+/', '', $texto);
    if (!preg_match('/^[A-Z]{3}[0-9]{3}$/', $compacto)) {
        $salirConError('El id_bus debe tener formato AAA 123.');
    }
    return $compacto;
};

#Si todo va bien, se ejecuta esta parte del código...

include_once "../base_de_datos.php";

$id_bus = $normalizarPlacaBus($_POST["id_bus"]);
$id_usuario = $validarEntero($_POST["id_usuario"], "id_usuario", 1, 2147483647);
$anio_fab = $validarEntero($_POST["anio_fab"], "anio_fab", 1950, (int) date('Y'));
$capacidad_pasajeros = $validarEntero($_POST["capacidad_pasajeros"], "capacidad_pasajeros", 1, 99);
$tipo_bus = strtoupper(trim((string) $_POST["tipo_bus"]));
if (!in_array($tipo_bus, ['U', 'M', 'A', 'E'], true)) {
    $salirConError('Tipo de bus invalido.');
}
$gps = filter_var($_POST["gps"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
if ($gps === null) {
    $salirConError('El campo gps debe ser booleano.');
}
$ind_estado_buses = strtoupper(trim((string) $_POST["ind_estado_buses"]));
if (!in_array($ind_estado_buses, ['L', 'F', 'D', 'S', 'T', 'A'], true)) {
    $salirConError('Estado de bus invalido.');
}


$sentencia = $base_de_datos->prepare("SELECT fun_update_buses(?,?,?,?,?,?,?);");
$sentencia->execute([
    $id_bus, 
    $id_usuario, 
    $anio_fab, 
    $capacidad_pasajeros, 
    $tipo_bus, 
    $gps, 
    $ind_estado_buses
]);

$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header("Location: listar_buses.php");
} else {
    echo "Algo salió mal. Por favor verifica que el bus exista y que los datos sean validos.";
}



