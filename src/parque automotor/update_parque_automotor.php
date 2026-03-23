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
@yerson
2025
=================================================================
Este archivo guarda los datos del formulario en donde se editan
=================================================================
*/

#Salir si alguno de los datos no está presente
if (!isset($_POST["id_parque_automotor"]) || 
    !isset($_POST["id_bus"]) ||
    !isset($_POST["dir_parque_automotor"])) 
{
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

$redirigirConError = static function (string $mensaje): void {
    header("Location: listar_parque_automotor.php?error=" . urlencode($mensaje));
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

#Si todo va bien, se ejecuta esta parte del código...

include_once "../base_de_datos.php";


$id_parque_automotor = $validarEntero($_POST["id_parque_automotor"], "id_parque_automotor");
$id_bus             = $validarEntero($_POST["id_bus"], "id_bus");
$dir_parque_automotor = $validarTexto($_POST["dir_parque_automotor"], "dir_parque_automotor", 5, 255);



$sentencia = $base_de_datos->prepare("SELECT fun_update_parque_automotor(CAST(? AS INTEGER), CAST(? AS INTEGER), CAST(? AS VARCHAR));");
$resultado = $sentencia->execute([
    $id_parque_automotor,
    $id_bus,
    $dir_parque_automotor
]);

if ($resultado === true) {
    header("Location: listar_parque_automotor.php");
} else {
    echo "Algo salió mal. Por favor verifica que la tabla exista, así como el ID del parque automotor";
}


