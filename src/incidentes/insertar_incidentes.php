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
Este archivo inserta los datos enviados a trav�s de forma_buses.php
==================================================================
*/
if (!isset($_POST["id_incidente"])   ||
    !isset($_POST["titulo_incidente"]) ||
    !isset($_POST["desc_incidente"]) ||
    !isset($_POST["id_bus"])         ||
    !isset($_POST["id_usuario"])    ||
    !isset($_POST["tipo_incidente"])) 
{
    echo "Faltan campos obligatorios en el formulario";
    exit();
}

$redirigirConError = static function (string $mensaje): void {
    header("Location: forma_incidentes.php?error=" . urlencode($mensaje));
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
    if (function_exists('mb_strlen')) {
        $longitud = mb_strlen($texto);
    } elseif (function_exists('iconv_strlen')) {
        $longitud = iconv_strlen($texto, 'UTF-8');
    } else {
        $longitud = strlen($texto);
    }
    if ($longitud < $min || $longitud > $max) {
        $redirigirConError("El campo {$campo} debe tener entre {$min} y {$max} caracteres.");
    }
    return $texto;
};

include_once "../base_de_datos.php";
include_once "../onesignal_helper.php";

// Recoger todos los valores del formulario
$id_incidente         = $validarEntero($_POST["id_incidente"], "id_incidente");
$titulo_incidente     = $validarTexto($_POST["titulo_incidente"], "titulo_incidente", 3, 120);
$desc_incidente       = $validarTexto($_POST["desc_incidente"], "desc_incidente", 5, 2000);
$id_bus               = $validarEntero($_POST["id_bus"], "id_bus");
$id_usuario         = $validarEntero($_POST["id_usuario"], "id_usuario");
$tipo_incidente       = strtoupper(trim((string) $_POST["tipo_incidente"]));
if (!in_array($tipo_incidente, ['C', 'E', 'D', 'A', 'O'], true)) {
    $redirigirConError("El tipo de incidente no es valido.");
}
try {
    // Preparar la llamada a la funci�n de PostgreSQL
    $sentencia = $base_de_datos->prepare("SELECT fun_insert_incidentes(?, ?, ?, ?, ?, ?);");

    // Ejecutar con todos los par�metros
    $resultado = $sentencia->execute([
        $id_incidente,
        $titulo_incidente,
        $desc_incidente,
        $id_bus,
        $id_usuario,
        $tipo_incidente
    ]);
    
    if ($resultado) {
        // Obtener el id_rol del administrador
        $queryRol = $base_de_datos->query("SELECT id_rol FROM tab_roles WHERE nombre_rol = 'admin' AND fec_delete IS NULL LIMIT 1");
        $rolResult = $queryRol->fetch(PDO::FETCH_OBJ);
        
        if ($rolResult) {
            $id_rol_admin = $rolResult->id_rol;
            
            // Enviar notificaci�n al administrador
            $tipo_label = [
                'C' => 'Choque',
                'E' => 'Embotellamiento',
                'D' => 'Desviaci�n de ruta',
                'A' => 'Atropello',
                'O' => 'Otros'
            ];
            $tipo_texto = $tipo_label[$tipo_incidente] ?? $tipo_incidente;
            
            $notif_result = enviar_notificacion_onesignal(
                'Nuevo Incidente Reportado',
                "Se ha registrado un incidente de tipo: $tipo_texto. ID: $id_incidente",
                null,
                $id_rol_admin
            );
        }
        
        // �xito: redirigir al listado con mensaje
        header("Location: listar_incidentes.php?insertado=1");
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
    header("Location: forma_incidentes.php?error=" . urlencode($mensajeError));
    exit();
}




