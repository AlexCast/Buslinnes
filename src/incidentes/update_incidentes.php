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
if (!isset($_POST["id_incidente"]) || 
    !isset($_POST["titulo_incidente"]) ||
    !isset($_POST["desc_incidente"]) || // descripcion del incidente
    !isset($_POST["id_bus"]) || 
    !isset($_POST["id_conductor"]) ||
    !isset($_POST["tipo_incidente"])) 
{
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

$redirigirConError = static function (string $mensaje): void {
    header("Location: listar_incidentes.php?error=" . urlencode($mensaje));
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
$id_incidente = $validarEntero($_POST["id_incidente"], "id_incidente");
$titulo_incidente = $validarTexto($_POST["titulo_incidente"], "titulo_incidente", 3, 120);
$desc_incidente = $validarTexto($_POST["desc_incidente"], "desc_incidente", 5, 2000);
$id_bus = $validarEntero($_POST["id_bus"], "id_bus");
$id_conductor = $validarEntero($_POST["id_conductor"], "id_conductor");
$tipo_incidente = strtoupper(trim((string) $_POST["tipo_incidente"]));
if (!in_array($tipo_incidente, ['C', 'E', 'D', 'A', 'O'], true)) {
    $redirigirConError("El tipo de incidente no es valido.");
}

$sentencia = $base_de_datos->prepare("SELECT fun_update_incidentes(?,?,?,?,?,?);");
$resultado = $sentencia->execute([
    $id_incidente, 
    $titulo_incidente,
    $desc_incidente, 
    $id_bus, 
    $id_conductor,
    $tipo_incidente
]);

if ($resultado === true) {
    header("Location: listar_incidentes.php");
} else {
    echo "Algo salió mal. Por favor verifica que la tabla exista, así como el ID del incidente";
}


