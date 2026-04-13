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
=================================================================
Este archivo guarda los datos del formulario en donde se editan
=================================================================
*/
?>
<?php
/*
CRUD con PostgreSQL y PHP
@Carlos Eduardo Perez Rueda
@Marzo de 2023
=================================================================
Este archivo guarda los datos del formulario en donde se editan
=================================================================
*/
?>
<?php

# Validar campos obligatorios
if (
    !isset($_POST["id_ruta"]) ||
    !isset($_POST["nom_ruta"]) ||
    !isset($_POST["hora_inicio"]) ||
    !isset($_POST["hora_final"]) ||
    !isset($_POST["inicio_ruta"]) ||
    !isset($_POST["fin_ruta"]) ||
    !isset($_POST["longitud"]) ||
    !isset($_POST["val_pasaje"])
) {
    echo "Faltan campos obligatorios";
    exit();
}

$redirigirConError = static function (string $mensaje): void {
    header("Location: listar_rutas.php?error=" . urlencode($mensaje));
    exit();
};

$validarEntero = static function ($valor, string $campo, int $min, int $max) use ($redirigirConError): int {
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

$obtenerLongitudTexto = static function (string $texto): int {
    if (function_exists('mb_strlen')) {
        return mb_strlen($texto, 'UTF-8');
    }

    if (function_exists('iconv_strlen')) {
        $longitud = iconv_strlen($texto, 'UTF-8');
        if ($longitud !== false) {
            return $longitud;
        }
    }

    return strlen($texto);
};

$validarTexto = static function ($valor, string $campo, int $min, int $max) use ($redirigirConError, $obtenerLongitudTexto): string {
    $texto = trim((string) $valor);
    $longitud = $obtenerLongitudTexto($texto);
    if ($longitud < $min || $longitud > $max) {
        $redirigirConError("El campo {$campo} debe tener entre {$min} y {$max} caracteres.");
    }
    return $texto;
};

$validarHora = static function ($valor, string $campo) use ($redirigirConError): string {
    $hora = trim((string) $valor);
    if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $hora)) {
        $redirigirConError("El campo {$campo} tiene un formato invalido.");
    }
    return strlen($hora) === 5 ? ($hora . ':00') : $hora;
};

$validarDecimal = static function ($valor, string $campo, float $min, float $max) use ($redirigirConError): float {
    $texto = trim((string) $valor);
    // Permitir coma o punto como separador decimal
    $texto = str_replace(',', '.', $texto);
    if (!preg_match('/^[0-9]+(\.[0-9]+)?$/', $texto)) {
        $redirigirConError("El campo {$campo} debe ser un número válido.");
    }
    $numero = (float) $texto;
    if ($numero < $min || $numero > $max) {
        $redirigirConError("El campo {$campo} debe estar entre {$min} y {$max}.");
    }
    return $numero;
};

$validarCoordenada = static function ($valor, string $campo, float $min, float $max) use ($redirigirConError): ?float {
    if ($valor === null || $valor === '') {
        return null;
    }
    $texto = trim((string) $valor);
    if (!preg_match('/^-?[0-9]+(\.[0-9]+)?$/', $texto)) {
        $redirigirConError("El campo {$campo} debe ser numerico.");
    }
    $numero = (float) $texto;
    if ($numero < $min || $numero > $max) {
        $redirigirConError("El campo {$campo} esta fuera de rango.");
    }
    return $numero;
};

include_once "../base_de_datos.php";
$id_ruta     = $validarEntero($_POST["id_ruta"], "id_ruta", 1, 2147483647);
$nom_ruta    = $validarTexto($_POST["nom_ruta"], "nom_ruta", 3, 255);
$hora_inicio = $validarHora($_POST["hora_inicio"], "hora_inicio");
$hora_final  = $validarHora($_POST["hora_final"], "hora_final");
$inicio_ruta = $validarTexto($_POST["inicio_ruta"], "inicio_ruta", 3, 255);
$fin_ruta    = $validarTexto($_POST["fin_ruta"], "fin_ruta", 3, 255);
$longitud    = $validarDecimal($_POST["longitud"], "longitud", 0.1, 99999);
$val_pasaje  = $validarEntero($_POST["val_pasaje"], "val_pasaje", 1, 9999);
$inicio_lat  = $validarCoordenada($_POST["inicio_lat"] ?? null, "inicio_lat", -90, 90);
$inicio_lng  = $validarCoordenada($_POST["inicio_lng"] ?? null, "inicio_lng", -180, 180);
$fin_lat     = $validarCoordenada($_POST["fin_lat"] ?? null, "fin_lat", -90, 90);
$fin_lng     = $validarCoordenada($_POST["fin_lng"] ?? null, "fin_lng", -180, 180);

error_log("=== DEBUG UPDATE_RUTAS ===");
error_log("POST keys: " . implode(', ', array_keys($_POST)));
error_log("waypoints_json CRUDO Post: " . ($_POST["waypoints_json"] ?? 'NO EXISTE'));

// SecurityMiddleware sanitiza $_POST con htmlspecialchars, lo cual puede romper JSON (comillas -> &quot;)
// Primero decodificar el HTML
$waypoints_json = !empty($_POST["waypoints_json"])
    ? html_entity_decode((string)$_POST["waypoints_json"], ENT_QUOTES | ENT_HTML5, 'UTF-8')
    : null;

error_log("waypoints_json DECODIFICADO: " . ($waypoints_json ?? 'null'));

// Validar que sea JSON válido
if ($waypoints_json !== null && $waypoints_json !== '[]') {
    $decoded = json_decode($waypoints_json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("ERROR JSON: " . json_last_error_msg());
    } else {
        error_log("JSON VÁLIDO: " . count($decoded) . " waypoints");
    }
}

if ($waypoints_json !== null) {
    $waypointsValidacion = json_decode($waypoints_json, true);
    if ($waypointsValidacion !== null && !is_array($waypointsValidacion)) {
        $redirigirConError("El formato de waypoints no es valido.");
    }
}

$sentencia = $base_de_datos->prepare("SELECT fun_update_rutas(?,?,?,?,?,?,?,?);");
$resultado = $sentencia->execute([
    $id_ruta,
    $nom_ruta,
    $hora_inicio,
    $hora_final,
    $inicio_ruta,
    $fin_ruta,
    $longitud,
    $val_pasaje
]);

if ($resultado === true) {
    // Guardar coordenadas si llegaron desde el formulario de edición
    if ($inicio_lat !== null && $inicio_lng !== null && $fin_lat !== null && $fin_lng !== null) {
        try {
            $updateCoords = $base_de_datos->prepare(
                "UPDATE tab_rutas SET inicio_lat = ?, inicio_lng = ?, fin_lat = ?, fin_lng = ? WHERE id_ruta = ?"
            );
            $updateCoords->execute([
                $inicio_lat,
                $inicio_lng,
                $fin_lat,
                $fin_lng,
                $id_ruta
            ]);
        } catch (Exception $e) {
            error_log("Error al guardar coordenadas de ruta (actualizar): " . $e->getMessage());
        }
    }

    // Manejar waypoints: eliminar existentes e insertar nuevos
    if ($waypoints_json !== null) {
        try {
            $waypoints = json_decode($waypoints_json, true);
            error_log("Procesando waypoints - JSON decodificado: " . print_r($waypoints, true));
            
            if (is_array($waypoints)) {
                // Eliminar waypoints existentes para esta ruta
                $deleteStmt = $base_de_datos->prepare("DELETE FROM tab_ruta_waypoints WHERE id_ruta = ?");
                $deleteStmt->execute([$id_ruta]);
                error_log("Eliminados waypoints existentes para ruta $id_ruta");
                
                if (count($waypoints) > 0) {
                    error_log("Insertando " . count($waypoints) . " waypoints nuevos");
                    // Preparar INSERT
                    try {
                        $insertStmt = $base_de_datos->prepare("
                            INSERT INTO tab_ruta_waypoints (id_ruta, orden, lat, lng, nombre, usr_insert, fec_insert)
                            VALUES (?, ?, ?, ?, ?, current_user, CURRENT_TIMESTAMP)
                        ");
                    } catch (PDOException $e) {
                        // Fallback si no hay usr_insert/fec_insert
                        error_log("Usando INSERT sin usr_insert/fec_insert");
                        $insertStmt = $base_de_datos->prepare("
                            INSERT INTO tab_ruta_waypoints (id_ruta, orden, lat, lng, nombre)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                    }
                    
                    $insertados = 0;
                    foreach ($waypoints as $i => $waypoint) {
                        $orden = isset($waypoint['orden']) ? $waypoint['orden'] : null;
                        $lat = isset($waypoint['lat']) ? $waypoint['lat'] : null;
                        $lng = isset($waypoint['lng']) ? $waypoint['lng'] : null;
                        $nombre = isset($waypoint['nombre']) ? $waypoint['nombre'] : null;
                        
                        error_log("Procesando waypoint $i: orden=$orden, lat=$lat, lng=$lng, nombre=$nombre");
                        
                        if (
                            $orden !== null &&
                            $lat !== null &&
                            $lng !== null &&
                            $nombre !== null &&
                            is_numeric($orden) &&
                            (int) $orden >= 1 &&
                            is_numeric($lat) &&
                            is_numeric($lng)
                        ) {
                            $lat = (float) $lat;
                            $lng = (float) $lng;
                            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                                error_log("Waypoint $i RECHAZADO: coordenadas fuera de rango");
                                continue;
                            }
                            try {
                                $insertStmt->execute([$id_ruta, $orden, $lat, $lng, $nombre]);
                                $insertados++;
                                error_log("Waypoint $i INSERTADO exitosamente");
                            } catch (Exception $ex) {
                                error_log("ERROR insertando waypoint $i: " . $ex->getMessage());
                            }
                        } else {
                            error_log("Waypoint $i RECHAZADO: datos inválidos");
                        }
                    }
                    error_log("TOTAL INSERTADOS: $insertados waypoints");
                } else {
                    error_log("No hay waypoints para insertar");
                }
            }
        } catch (Exception $e) {
            error_log("Error al actualizar waypoints (id_ruta={$id_ruta}): " . $e->getMessage());
            error_log("waypoints_json recibido: " . (string)$waypoints_json);
        }
    } else {
        error_log("waypoints_json es null - no se procesarán waypoints");
    }

    header("Location: listar_rutas.php");
    exit();
} else {
    echo "Algo salió mal. Por favor verifica que la tabla exista, así como el ID de la ruta";
}

