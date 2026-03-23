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
if (!isset($_POST["titulo_incidente"]) ||
    !isset($_POST["desc_incidente"]) ||
    !isset($_POST["id_bus"])         ||
    !isset($_POST["id_conductor"])    ||
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
        if ($longitud === false) {
            $longitud = strlen($texto);
        }
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

// Calcular el siguiente ID disponible para no pedirlo al conductor.
$sentenciaId = $base_de_datos->query("SELECT COALESCE(MAX(id_incidente), 0) + 1 AS siguiente_id FROM tab_incidentes");
$id_incidente = (int) $sentenciaId->fetch(PDO::FETCH_OBJ)->siguiente_id;

// Recoger todos los valores del formulario
$titulo_incidente     = $validarTexto($_POST["titulo_incidente"], "titulo_incidente", 3, 120);
$desc_incidente       = $validarTexto($_POST["desc_incidente"], "desc_incidente", 5, 2000);
$id_bus               = $validarEntero($_POST["id_bus"], "id_bus");
$id_conductor         = $validarEntero($_POST["id_conductor"], "id_conductor");
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
        $id_conductor,
        $tipo_incidente
    ]);
    
    if ($resultado) {
        // Obtener el id_rol del administrador
        $queryRol = $base_de_datos->query("SELECT id_rol FROM tab_roles WHERE lower(nombre_rol) IN ('admin', 'administrador') AND fec_delete IS NULL ORDER BY id_rol ASC LIMIT 1");
        $rolResult = $queryRol->fetch(PDO::FETCH_OBJ);
        
        if ($rolResult) {
            $id_rol_admin = (int) $rolResult->id_rol;
            
            // Crear notificacion interna para el rol admin
            $tipo_label = [
                'C' => 'Choque',
                'E' => 'Embotellamiento',
                'D' => 'Desviaci�n de ruta',
                'A' => 'Atropello',
                'O' => 'Otros'
            ];
            $tipo_texto = $tipo_label[$tipo_incidente] ?? $tipo_incidente;

            $queryNotificacionId = $base_de_datos->query("SELECT COALESCE(MAX(id_notificacion), 0) + 1 AS siguiente_id FROM tab_notificaciones");
            $id_notificacion = (int) $queryNotificacionId->fetch(PDO::FETCH_OBJ)->siguiente_id;

            $titulo_notificacion = 'Nuevo incidente reportado';
            $descripcion_notificacion = "Se registro un incidente ($tipo_texto), ID: $id_incidente. Revisar en modulo de incidentes.";

            $sentenciaNotificacion = $base_de_datos->prepare("SELECT fun_insert_notificaciones(?, ?, ?, ?, ?);");
            $sentenciaNotificacion->execute([
                $id_notificacion,
                null,
                $id_rol_admin,
                $titulo_notificacion,
                $descripcion_notificacion
            ]);

            $verificarNotificacion = $base_de_datos->prepare("SELECT COUNT(*) AS total FROM tab_notificaciones WHERE id_notificacion = ? AND fec_delete IS NULL");
            $verificarNotificacion->execute([$id_notificacion]);
            $notificacionCreada = (int) ($verificarNotificacion->fetch(PDO::FETCH_OBJ)->total ?? 0) > 0;
            if (!$notificacionCreada) {
                error_log("No se pudo crear la notificacion interna para admin al reportar incidente ID: {$id_incidente}");
            }
            
            // Enviar notificaci�n push al administrador
            $notif_result = enviar_notificacion_onesignal(
                $titulo_notificacion,
                $descripcion_notificacion,
                null,
                $id_rol_admin
            );
        }
        
        // Exito: redirigir al panel del conductor con confirmacion
        header("Location: /buslinnes/templates/driver_interface.html?incidente_reportado=1");
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



