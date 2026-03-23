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
autor: yerson
==================================================================
Este archivo inserta los datos enviados a trav�s de forma_parque_automotor.php
==================================================================
*/
if (!isset($_POST["id_parque_automotor"])   ||
    !isset($_POST["id_bus"]) ||
    !isset($_POST["dir_parque_automotor"])) 
{
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

$redirigirConError = static function (string $mensaje): void {
    header("Location: forma_parque_automotor.php?error=" . urlencode($mensaje));
    exit();
};

$validarEntero = static function ($valor, string $campo, int $min = 1, int $max = 2147483647) use ($redirigirConError): int {
    $valor = trim((string) $valor);
    if (!preg_match('/^[0-9]+$/', $valor)) {
        $redirigirConError("El campo {$campo} debe ser numerico entero.");
    }
    $numero = (int) $valor;
    if ($numero < $min || $numero > $max) {
        $redirigirConError("El campo {$campo} debe estar entre {$min} y {$max}.");
    }
    return $numero;
};

$validarTexto = static function ($valor, string $campo, int $min, int $max) use ($redirigirConError): string {
    $texto = trim((string) $valor);
    $longitud = mb_strlen($texto);
    if ($longitud < $min || $longitud > $max) {
        $redirigirConError("El campo {$campo} debe tener entre {$min} y {$max} caracteres.");
    }
    return $texto;
};

include_once "../base_de_datos.php";

// Recoger todos los valores del formulario
$id_parque_automotor  = $validarEntero($_POST["id_parque_automotor"], "id_parque_automotor");
$id_bus               = $validarEntero($_POST["id_bus"], "id_bus");
$dir_parque_automotor = $validarTexto($_POST["dir_parque_automotor"], "dir_parque_automotor", 5, 255);

try {
    // Preparar la llamada a la funci�n de PostgreSQL
    $sentencia = $base_de_datos->prepare("SELECT fun_insert_parque_automotor(?, ?, ?);");
    // Ejecutar con todos los par�metros
    $resultado = $sentencia->execute([
        $id_parque_automotor,
        $id_bus,
        $dir_parque_automotor
    ]);
    
    if ($resultado) {
        // �xito: redirigir al listado con mensaje
        header("Location: listar_parque_automotor.php?insertado=1");
        exit();
    } else {
        // Error en la ejecuci�n
        $errorInfo = $sentencia->errorInfo();
        throw new Exception("Error al insertar: " . $errorInfo[2]);
    }
} catch (Exception $e) {
    // Manejo de errores m�s detallado
    $mensajeError = "Error: " . $e->getMessage();
    
    // Redirigir al formulario con mensaje de error
    header("Location: forma_parque_automotor.php?error=" . urlencode($mensajeError));
    exit();
}



