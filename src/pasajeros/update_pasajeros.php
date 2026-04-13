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
if (!isset($_POST["id_pasajero"]) || !isset($_POST["nom_pasajero"]) ||
    (!isset($_POST["tel_pasajero"]) && !isset($_POST["email_pasajero"]))) {
    echo "Faltan campos obligatorios";
    exit();
}

$salirConError = static function (string $mensaje): void {
    echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    exit();
};

$validarEntero = static function ($valor, string $campo, int $min, int $max) use ($salirConError): int {
    $texto = trim((string) $valor);
    if (!preg_match('/^[0-9]+$/', $texto)) {
        $salirConError("El campo {$campo} debe ser numerico entero.");
    }
    $numero = (int) $texto;
    if ($numero < $min || $numero > $max) {
        $salirConError("El campo {$campo} esta fuera de rango.");
    }
    return $numero;
};

$validarTexto = static function ($valor, string $campo, int $min, int $max) use ($salirConError): string {
    $texto = trim((string) $valor);
    if (function_exists('mb_strlen')) {
        $longitud = mb_strlen($texto);
    } elseif (function_exists('iconv_strlen')) {
        $longitud = iconv_strlen($texto, 'UTF-8');
        if ($longitud === false) {
            $longitud = strlen($texto);
        }
    } else {
        $longitud = strlen($texto);
    }
    if ($longitud < $min || $longitud > $max) {
        $salirConError("El campo {$campo} debe tener entre {$min} y {$max} caracteres.");
    }
    return $texto;
};

#Si todo va bien, se ejecuta esta parte del código...

include_once "../base_de_datos.php";
$id_pasajero = $validarEntero($_POST["id_pasajero"], 'id_pasajero', 1, 2147483647);
$nom_pasajero = $validarTexto($_POST["nom_pasajero"], 'nom_pasajero', 3, 80);
$email_pasajero = trim((string) ($_POST["email_pasajero"] ?? $_POST["tel_pasajero"]));
if (!filter_var($email_pasajero, FILTER_VALIDATE_EMAIL)) {
    $salirConError('El email del pasajero no es valido.');
}

$sentencia = $base_de_datos->prepare("SELECT fun_update_pasajeros(?,?,?) AS resultado;");

$sentencia->execute([$id_pasajero, $nom_pasajero, $email_pasajero]);
$respuesta = $sentencia->fetch(PDO::FETCH_ASSOC);
$mensaje = $respuesta['resultado'] ?? '';

if (stripos($mensaje, 'funcion') !== false || stripos($mensaje, 'actualiz') !== false) {
    header("Location: listar_pasajeros.php");
} else {
    echo "Algo salió mal: " . htmlspecialchars($mensaje);
}


