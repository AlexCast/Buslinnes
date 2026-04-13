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
if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']);

if (!isset($_POST["id_bus"])   ||
    !isset($_POST["id_usuario"])     ||
    !isset($_POST["anio_fab"])         ||
    !isset($_POST["capacidad_pasajeros"]) ||
    !isset($_POST["tipo_bus"])         ||
    !isset($_POST["gps"])              ||
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

include_once "../base_de_datos.php";

// Recoger todos los valores del formulario

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

try {
    // Preparar la llamada a la funci�n de PostgreSQL
    $sentencia = $base_de_datos->prepare("SELECT fun_insert_buses(?, ?, ?, ?, ?, ?, ?);");
    // Ejecutar con todos los par�metros en el orden correcto
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
        // �xito: redirigir al listado con mensaje
        header("Location: listar_buses.php?insertado=1");
        exit();
    } else {
        throw new Exception("No fue posible insertar el bus. Verifica datos/reglas de la funcion y que fun_insert_buses este actualizada en la base de datos.");
    }
} catch (Exception $e) {
    // Mostrar el error directamente en pantalla para depuraci�n
    echo "<div style='color: red; font-weight: bold; margin: 2em;'>";
    echo "Error al insertar el bus: " . htmlspecialchars($e->getMessage());
    echo "</div>";
    exit();
}




