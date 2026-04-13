<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../app/SecurityMiddleware.php';

SecurityMiddleware::protect([
    'csrf' => false,
    'rateLimit' => true,
    'origin' => true,
    'userAgent' => true,
    'securityHeaders' => true
]);

define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
$jwtData = validarTokenJWT(['admin', 'conductor']);

require_once __DIR__ . '/../base_de_datos.php';

function hasColumn(PDO $db, string $tableName, string $columnName): bool {
    $stmt = $db->prepare(
        "SELECT 1
         FROM information_schema.columns
         WHERE table_schema = 'public'
           AND table_name = :table_name
           AND column_name = :column_name
         LIMIT 1"
    );
    $stmt->execute([
        ':table_name' => $tableName,
        ':column_name' => $columnName
    ]);
    return (bool) $stmt->fetchColumn();
}

function buildNotDeletedCondition(PDO $db, string $tableName, string $alias): string {
    if (hasColumn($db, $tableName, 'fec_delete')) {
        return " AND {$alias}.fec_delete IS NULL";
    }
    if (hasColumn($db, $tableName, 'usr_delete')) {
        return " AND {$alias}.usr_delete IS NULL";
    }
    return '';
}

try {
    $idUsuario = isset($_GET['id_usuario']) ? trim((string) $_GET['id_usuario']) : '';
    $idConductor = isset($_GET['id_conductor']) ? trim((string) $_GET['id_conductor']) : '';
    $idObjetivo = $idUsuario !== '' ? $idUsuario : $idConductor;

    // Si no envían ID y el token es conductor, usar su propio id_usuario.
    if ($idObjetivo === '') {
        $rolJwt = strtolower((string) ($jwtData->rol ?? ''));
        $idDesdeJwt = $jwtData->id_usuario ?? $jwtData->sub ?? null;
        if ($rolJwt === 'conductor' && $idDesdeJwt !== null && $idDesdeJwt !== '') {
            $idObjetivo = (string) $idDesdeJwt;
        }
    }

    if ($idObjetivo === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Debes enviar id_usuario o id_conductor.']);
        exit;
    }

    $idNumerico = (int) $idObjetivo;
    if ($idNumerico <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'id_usuario/id_conductor inválido.']);
        exit;
    }

    // Regla solicitada: id_usuario del usuario == id del conductor
    $conductorNotDeleted = buildNotDeletedCondition($base_de_datos, 'tab_conductores', 'c');
    $stmt = $base_de_datos->prepare("
            SELECT
                c.id_usuario,
                c.nom_conductor,
                c.ape_conductor,
                c.genero_conductor,
                c.email_conductor,
                c.licencia_conductor,
                c.tipo_licencia,
                c.fec_venc_licencia,
                c.estado_conductor,
                c.fec_nacimiento,
                c.tipo_sangre,
                c.perfil_completdo
            FROM tab_conductores c
            WHERE c.id_usuario = :id_usuario
              {$conductorNotDeleted}
            LIMIT 1
    ");
    $stmt->execute([':id_usuario' => $idNumerico]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        $profile = [
            'id_usuario' => $idNumerico,
            'nom_conductor' => null,
            'ape_conductor' => null,
            'genero_conductor' => null,
            'email_conductor' => null,
            'licencia_conductor' => null,
            'tipo_licencia' => null,
            'fec_venc_licencia' => null,
            'estado_conductor' => null,
            'fec_nacimiento' => null,
            'tipo_sangre' => null,
            'perfil_completdo' => false,
            'existe_perfil' => false
        ];
        $profile['bus_asignado'] = null;
        $profile['ruta_asignada'] = null;
        $profile['waypoints_ruta'] = [];

        http_response_code(200);
        echo json_encode($profile);
        exit;
    }

    $profile['existe_perfil'] = true;

    // Bus asignado al conductor (tab_buses.id_usuario -> tab_conductores.id_usuario)
    $busNotDeleted = buildNotDeletedCondition($base_de_datos, 'tab_buses', 'b');
    $stmtBus = $base_de_datos->prepare("
        SELECT
            b.id_bus,
            b.capacidad_pasajeros,
            b.ind_estado_buses
        FROM tab_buses b
        WHERE b.id_usuario = :id_usuario
          {$busNotDeleted}
        LIMIT 1
    ");
    $stmtBus->execute([':id_usuario' => $idNumerico]);
    $bus = $stmtBus->fetch(PDO::FETCH_ASSOC);

    $ruta = null;
    $waypoints = [];

    if ($bus && !empty($bus['id_bus'])) {
        // Ruta asignada al bus (tab_ruta_bus.id_bus -> tab_rutas.id_ruta)
        $rutaBusNotDeleted = buildNotDeletedCondition($base_de_datos, 'tab_ruta_bus', 'rb');
        $rutasNotDeleted = buildNotDeletedCondition($base_de_datos, 'tab_rutas', 'r');
        $stmtRuta = $base_de_datos->prepare("
            SELECT
                r.id_ruta,
                r.nom_ruta,
                r.inicio_ruta,
                r.fin_ruta,
                r.longitud,
                r.val_pasaje,
                r.inicio_lat::float AS inicio_lat,
                r.inicio_lng::float AS inicio_lng,
                r.fin_lat::float AS fin_lat,
                r.fin_lng::float AS fin_lng
            FROM tab_ruta_bus rb
            INNER JOIN tab_rutas r ON r.id_ruta = rb.id_ruta
            WHERE rb.id_bus = :id_bus
              {$rutaBusNotDeleted}
              {$rutasNotDeleted}
            ORDER BY rb.id_ruta_bus DESC
            LIMIT 1
        ");
        $stmtRuta->execute([':id_bus' => $bus['id_bus']]);
        $ruta = $stmtRuta->fetch(PDO::FETCH_ASSOC);

        if ($ruta && !empty($ruta['id_ruta'])) {
            $waypointsNotDeleted = buildNotDeletedCondition($base_de_datos, 'tab_ruta_waypoints', 'w');
            $stmtWp = $base_de_datos->prepare("
                SELECT id_waypoint, orden, lat, lng, nombre
                FROM tab_ruta_waypoints w
                WHERE id_ruta = :id_ruta
                  {$waypointsNotDeleted}
                ORDER BY orden ASC
            ");
            $stmtWp->execute([':id_ruta' => $ruta['id_ruta']]);
            $waypoints = $stmtWp->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    }

    $profile['bus_asignado'] = $bus ?: null;
    $profile['ruta_asignada'] = $ruta ?: null;
    $profile['waypoints_ruta'] = $waypoints;

    http_response_code(200);
    echo json_encode($profile);
} catch (Throwable $e) {
    error_log('api_perfil_conductor.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor al consultar la ficha profesional.']);
}
?>