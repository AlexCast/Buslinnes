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
autor: alexndrcastt
=================================================================
Actualiza registros en la tabla tab_cambio_bus
=================================================================
*/

# Verificar que todos los campos obligatorios estén presentes
if (
    !isset($_POST["id_cambio_bus"])      ||
    !isset($_POST["id_incidente"])      ||
    !isset($_POST["id_bus"])         ||
    !isset($_POST["ubicacion_cambio"])
) {
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

$salirConError = static function (string $mensaje): void {
    echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    exit();
};

$validarEntero = static function ($valor, string $campo) use ($salirConError): int {
    $texto = trim((string) $valor);
    if (!preg_match('/^[0-9]+$/', $texto)) {
        $salirConError("El campo {$campo} debe ser un entero positivo.");
    }
    $numero = (int) $texto;
    if ($numero <= 0) {
        $salirConError("El campo {$campo} debe ser mayor a cero.");
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

include_once "../base_de_datos.php";

/* 
   Sanitizar / convertir datos.
   Ajusta los tipos según tu estructura real de tab_cambio_bus
*/
$id_cambio_bus = $validarEntero($_POST["id_cambio_bus"], 'id_cambio_bus');
$id_incidente = $validarEntero($_POST["id_incidente"], 'id_incidente');
$id_bus = $validarEntero($_POST["id_bus"], 'id_bus');
$ubicacion_cambio = $validarTexto($_POST["ubicacion_cambio"], 'ubicacion_cambio', 3, 255);

try {
    // Llamada a la función que actualiza en PostgreSQL
    $sentencia = $base_de_datos->prepare("
        SELECT fun_update_cambio_bus(?, ?, ?, ?);
    ");

    $resultado = $sentencia->execute([
        $id_cambio_bus,
        $id_incidente,
        $id_bus,
        $ubicacion_cambio
    ]);

    if ($resultado) {
        // Redirigir a la lista de cambios con mensaje de éxito
        header("Location: listar_cambio_bus.php?actualizado=1");
        exit();
    } else {
        $errorInfo = $sentencia->errorInfo();
        throw new Exception("Error al actualizar: " . $errorInfo[2]);
    }
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold; margin: 2em;'>";
    echo "Error al actualizar el registro de cambio de bus: " . htmlspecialchars($e->getMessage());
    echo "</div>";
    exit();
}



