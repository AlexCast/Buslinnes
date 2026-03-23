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
@Carlos Eduardo Perez Rueda
@Marzo de 2023
==================================================================
Este archivo inserta los datos enviados a través de formulario.php
==================================================================
*/
?>
<?php
if (!isset($_POST["id_ruta"])      ||
    !isset($_POST["nom_ruta"])     ||
    !isset($_POST["hora_inicio"])  || 
    !isset($_POST["hora_final"])   ||
    !isset($_POST["inicio_ruta"])  || 
    !isset($_POST["fin_ruta"])     ||
    !isset($_POST["longitud"])     ||
    !isset($_POST["val_pasaje"]))
    {
    exit();
    }

$redirigirConError = static function (string $mensaje): void {
    header("Location: forma_rutas.php?error=" . urlencode($mensaje));
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
#Si todo va bien, se ejecuta esta parte del código..., si no, nos jodimos

include_once "../base_de_datos.php";
$id_ruta       = $validarEntero($_POST["id_ruta"], "id_ruta", 1, 2147483647);
$nom_ruta      = $validarTexto($_POST["nom_ruta"], "nom_ruta", 3, 255);
$hora_inicio   = $validarHora($_POST["hora_inicio"], "hora_inicio");
$hora_final    = $validarHora($_POST["hora_final"], "hora_final");
$inicio_ruta   = $validarTexto($_POST["inicio_ruta"], "inicio_ruta", 3, 255);
$fin_ruta      = $validarTexto($_POST["fin_ruta"], "fin_ruta", 3, 255);
$longitud      = $validarDecimal($_POST["longitud"], "longitud", 0.1, 99999);
$val_pasaje    = $validarEntero($_POST["val_pasaje"], "val_pasaje", 1, 9999);
$inicio_lat    = $validarCoordenada($_POST["inicio_lat"] ?? null, "inicio_lat", -90, 90);
$inicio_lng    = $validarCoordenada($_POST["inicio_lng"] ?? null, "inicio_lng", -180, 180);
$fin_lat       = $validarCoordenada($_POST["fin_lat"] ?? null, "fin_lat", -90, 90);
$fin_lng       = $validarCoordenada($_POST["fin_lng"] ?? null, "fin_lng", -180, 180);
// SecurityMiddleware sanitiza $_POST con htmlspecialchars, lo cual puede romper JSON (comillas -> &quot;)
$waypoints_json = !empty($_POST["waypoints_json"])
    ? html_entity_decode((string)$_POST["waypoints_json"], ENT_QUOTES | ENT_HTML5, 'UTF-8')
    : null;

if ($waypoints_json !== null) {
    $waypointsValidacion = json_decode($waypoints_json, true);
    if ($waypointsValidacion !== null && !is_array($waypointsValidacion)) {
        $redirigirConError("El formato de waypoints no es valido.");
    }
}

// Verificar si el ID de ruta ya existe antes de intentar insertar
$checkStmt = $base_de_datos->prepare("SELECT id_ruta FROM tab_rutas WHERE id_ruta = ? LIMIT 1");
$checkStmt->execute([$id_ruta]);
if ($checkStmt->fetchColumn()) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - ID Duplicado</title>
        <link rel="stylesheet" href="/buslinnes/assets/fontawesome/css/all.min.css">
        <style>
            body { font-family: Arial, sans-serif; background: #f5f7fa; margin: 0; padding: 20px; }
            .error-container { max-width: 500px; margin: 100px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
            .error-icon { font-size: 60px; color: #dc3545; margin-bottom: 20px; }
            h1 { color: #333; margin: 20px 0; font-size: 24px; }
            p { color: #666; line-height: 1.6; margin: 15px 0; }
            .btn { display: inline-block; margin-top: 20px; padding: 10px 25px; background: #8059d4; color: white; border: none; border-radius: 6px; text-decoration: none; cursor: pointer; font-size: 16px; }
            .btn:hover { background: #6a47b5; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h1>❌ Error: ID Duplicado</h1>
            <p><strong>El ID de ruta <?php echo htmlspecialchars($id_ruta); ?> ya existe en el sistema.</strong></p>
            <p>No se pueden repetir IDs. Por favor, intenta con un ID diferente.</p>
            <a href="./forma_rutas.php" class="btn">← Volver al formulario</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Insertar la ruta principal usando la función de base de datos
$sentencia = $base_de_datos->prepare("SELECT fun_insert_rutas(?, ?, ?, ?, ?, ?, ?, ?);");
$sentencia->execute([
    $id_ruta,
    $nom_ruta,
    $hora_inicio,
    $hora_final,
    $inicio_ruta,
    $fin_ruta,
    $longitud,
    $val_pasaje
]);
$resultado = (bool) $sentencia->fetchColumn();

if ($resultado) {
    // Guardar coordenadas si llegaron desde el formulario de creación
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
            error_log("Error al guardar coordenadas de ruta: " . $e->getMessage());
        }
    }

    // Si hay waypoints, insertarlos en la tabla tab_ruta_waypoints
    if ($waypoints_json) {
        try {
            $waypoints = json_decode($waypoints_json, true);
            
            if (is_array($waypoints) && count($waypoints) > 0) {
                // Evitar duplicados (si ya existe para la ruta y el orden, no insertar)
                $existsStmt = $base_de_datos->prepare("
                    SELECT 1
                    FROM tab_ruta_waypoints
                    WHERE id_ruta = ? AND orden = ?
                    LIMIT 1
                ");

                // Preparar INSERT compatible (primero intentamos el formato "completo")
                // En varios modelos del proyecto usr_insert/fec_insert son NOT NULL.
                try {
                    $sentencia_wp = $base_de_datos->prepare("
                        INSERT INTO tab_ruta_waypoints (id_ruta, orden, lat, lng, nombre, usr_insert, fec_insert)
                        VALUES (?, ?, ?, ?, ?, current_user, CURRENT_TIMESTAMP)
                    ");
                } catch (PDOException $e) {
                    // Fallback para instalaciones donde esas columnas no existan
                    $sentencia_wp = $base_de_datos->prepare("
                        INSERT INTO tab_ruta_waypoints (id_ruta, orden, lat, lng, nombre)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                }

                foreach ($waypoints as $waypoint) {
                    // Asegurarse de que lat/lng/orden existan
                    $orden = isset($waypoint['orden']) ? $waypoint['orden'] : null;
                    $lat = isset($waypoint['lat']) ? $waypoint['lat'] : null;
                    $lng = isset($waypoint['lng']) ? $waypoint['lng'] : null;
                    $nombre = isset($waypoint['nombre']) ? $waypoint['nombre'] : null;
                    if ($orden === null || $lat === null || $lng === null) continue;

                    if (!is_numeric($orden) || (int) $orden < 1 || !is_numeric($lat) || !is_numeric($lng)) {
                        continue;
                    }
                    $lat = (float) $lat;
                    $lng = (float) $lng;
                    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                        continue;
                    }
                    $orden = (int) $orden;

                    $existsStmt->execute([$id_ruta, $orden]);
                    if ($existsStmt->fetchColumn()) {
                        continue;
                    }

                    $sentencia_wp->execute([
                        $id_ruta,
                        $orden,
                        $lat,
                        $lng,
                        $nombre
                    ]);
                }
            }
        } catch (Exception $e) {
            // Si falla la inserción de waypoints, la ruta ya se creó
            // Continuamos sin error crítico
            error_log("Error al insertar waypoints (id_ruta={$id_ruta}): " . $e->getMessage());
            error_log("waypoints_json recibido: " . (string)$waypoints_json);
        }
    }
    
    header("Location: listar_rutas.php");
    exit();
} else {
    echo "Error al insertar. Verifica que el ID de ruta no exista y que los datos sean correctos.";
}
?>


