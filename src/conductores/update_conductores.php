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
    !isset($_POST["id_usuario"]) ||
    !isset($_POST["nom_conductor"]) ||
    !isset($_POST["ape_conductor"]) ||
    !isset($_POST["email_conductor"]) ||
    !isset($_POST["licencia_conductor"]) ||
    !isset($_POST["tipo_licencia"]) ||
    !isset($_POST["fec_venc_licencia"]) ||
    !isset($_POST["estado_conductor"]) ||
    !isset($_POST["edad"]) ||
    !isset($_POST["tipo_sangre"])
) {
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
$id_usuario = $validarEntero($_POST["id_usuario"], 'id_usuario', 1, 2147483647);
$nom_conductor = $validarTexto($_POST["nom_conductor"], 'nom_conductor', 3, 60);
$ape_conductor = $validarTexto($_POST["ape_conductor"], 'ape_conductor', 3, 60);
$email_conductor = trim((string) $_POST["email_conductor"]);
if (!filter_var($email_conductor, FILTER_VALIDATE_EMAIL)) {
    $salirConError('El email del conductor no es valido.');
}
$licencia_conductor = trim((string) $_POST["licencia_conductor"]);
if (!preg_match('/^[0-9]{7,10}$/', $licencia_conductor)) {
    $salirConError('La licencia del conductor debe tener entre 7 y 10 digitos numericos.');
}
$tipo_licencia = strtoupper(trim((string) $_POST["tipo_licencia"]));
if (!in_array($tipo_licencia, ['C2', 'C3'], true)) {
    $salirConError('Tipo de licencia invalido.');
}
$fec_venc_licencia = trim((string) $_POST["fec_venc_licencia"]);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fec_venc_licencia)) {
    $salirConError('Fecha de vencimiento invalida.');
}
$hoy = new DateTimeImmutable('today');
$fechaVenc = DateTimeImmutable::createFromFormat('Y-m-d', $fec_venc_licencia);
if ($fechaVenc === false || $fechaVenc < $hoy) {
    $salirConError('La fecha de vencimiento de la licencia no puede ser anterior a hoy.');
}
$estado_conductor = strtoupper(trim((string) $_POST["estado_conductor"]));
if (!in_array($estado_conductor, ['A', 'S', 'R', 'P'], true)) {
    $salirConError('Estado del conductor invalido.');
}
$edad = $validarEntero($_POST["edad"], 'edad', 18, 99);
$tipo_sangre = strtoupper(trim((string) $_POST["tipo_sangre"]));
if (!in_array($tipo_sangre, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'PENDIENTE'], true)) {
    $salirConError('Tipo de sangre invalido.');
}

$genero_conductor = strtoupper(trim((string) ($_POST["genero_conductor"] ?? 'O')));
if (!in_array($genero_conductor, ['M', 'F', 'O'], true)) {
    $genero_conductor = 'O';
}
$fec_nacimiento = (new DateTimeImmutable('today'))->modify("-{$edad} years")->format('Y-m-d');

$sentencia = $base_de_datos->prepare("SELECT fun_update_conductores(?::int, ?::varchar, ?::varchar, ?::char(1), ?::varchar, ?::varchar, ?::char(2), ?::date, ?::char(1), ?::date, ?::varchar) AS resultado;");

$sentencia->execute([
    $id_usuario,
    $nom_conductor,
    $ape_conductor,
    $genero_conductor,
    $email_conductor,
    $licencia_conductor,
    $tipo_licencia,
    $fec_venc_licencia,
    $estado_conductor,
    $fec_nacimiento,
    $tipo_sangre
]);
$respuesta = $sentencia->fetch(PDO::FETCH_ASSOC);
$mensaje = $respuesta['resultado'] ?? '';

$resultadoOk = $mensaje === true || $mensaje === 1 || $mensaje === '1' || $mensaje === 't' || $mensaje === 'true';
if ($resultadoOk || (is_string($mensaje) && stripos($mensaje, 'actualizado correctamente') !== false)) {
    $marcarPerfil = $base_de_datos->prepare("UPDATE tab_conductores SET perfil_completdo = TRUE WHERE id_usuario = ? AND fec_delete IS NULL;");
    $marcarPerfil->execute([$id_usuario]);
    header("Location: listar_conductores.php");
} else {
    echo "Algo salió mal: " . htmlspecialchars($mensaje);
}



