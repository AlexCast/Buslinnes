<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// === SEGURIDAD: Solo validaciones básicas, permitir acceso público para GET ===
require_once __DIR__ . '/../../app/SecurityMiddleware.php';

SecurityMiddleware::protect([
    'csrf' => false,      // GET no requiere CSRF
    'rateLimit' => false, // Permitir acceso público
    'origin' => false,    // No validar origen para GET público
    'userAgent' => false, // No filtrar user agents para acceso público
    'securityHeaders' => true,
    'jwt' => false        // No requerir JWT para GET públic
]);
// === FIN SEGURIDAD ===

/**
 * API que devuelve las rutas no eliminadas con waypoints en JSON
 */
include_once __DIR__ . '/../base_de_datos.php';

$queries = [
    // Variante completa: coordenadas casteadas + conteo de buses activos.
    "SELECT r.id_ruta, r.nom_ruta, r.inicio_ruta, r.fin_ruta, r.longitud, r.val_pasaje, r.usr_delete, r.fec_delete,
            r.inicio_lat::float AS inicio_lat,
            r.inicio_lng::float AS inicio_lng,
            r.fin_lat::float AS fin_lat,
            r.fin_lng::float AS fin_lng,
            (SELECT COUNT(*) FROM tab_ruta_bus rb JOIN tab_buses b ON rb.id_bus = b.id_bus WHERE rb.id_ruta = r.id_ruta AND rb.fec_delete IS NULL AND b.fec_delete IS NULL) AS buses_count
     FROM tab_rutas r
     WHERE r.fec_delete IS NULL
     ORDER BY r.nom_ruta DESC",

    // Si falla el casteo de coordenadas, devolverlas crudas.
    "SELECT r.id_ruta, r.nom_ruta, r.inicio_ruta, r.fin_ruta, r.longitud, r.val_pasaje, r.usr_delete, r.fec_delete,
            r.inicio_lat, r.inicio_lng, r.fin_lat, r.fin_lng,
            (SELECT COUNT(*) FROM tab_ruta_bus rb JOIN tab_buses b ON rb.id_bus = b.id_bus WHERE rb.id_ruta = r.id_ruta AND rb.fec_delete IS NULL AND b.fec_delete IS NULL) AS buses_count
     FROM tab_rutas r
     WHERE r.fec_delete IS NULL
     ORDER BY r.nom_ruta DESC",

    // Si no existe tab_ruta_bus o su estructura no coincide, seguir sin buses_count real.
    "SELECT r.id_ruta, r.nom_ruta, r.inicio_ruta, r.fin_ruta, r.longitud, r.val_pasaje, r.usr_delete, r.fec_delete,
            r.inicio_lat::float AS inicio_lat,
            r.inicio_lng::float AS inicio_lng,
            r.fin_lat::float AS fin_lat,
            r.fin_lng::float AS fin_lng,
            0 AS buses_count
     FROM tab_rutas r
     WHERE r.fec_delete IS NULL
     ORDER BY r.nom_ruta DESC",

    // Fallback final para instalaciones heterog�neas.
    "SELECT r.id_ruta, r.nom_ruta, r.inicio_ruta, r.fin_ruta, r.longitud, r.val_pasaje, r.usr_delete, r.fec_delete,
            r.inicio_lat, r.inicio_lng, r.fin_lat, r.fin_lng,
            0 AS buses_count
     FROM tab_rutas r
     WHERE r.fec_delete IS NULL
     ORDER BY r.nom_ruta DESC"
];

$rutas = [];
$lastDbError = null;
foreach ($queries as $sql) {
    try {
        $sentencia = $base_de_datos->query($sql);
        $rutas = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        $lastDbError = null;
        break;
    } catch (PDOException $e) {
        $lastDbError = $e;
    }
}

if ($lastDbError !== null && empty($rutas)) {
    error_log('api_listar_rutas.php: no se pudo ejecutar ninguna variante de consulta: ' . $lastDbError->getMessage());
}

// Obtener waypoints para cada ruta
foreach ($rutas as &$ruta) {
    try {
        // La tabla tab_ruta_waypoints en algunas versiones no tiene columna fec_delete,
        // por eso no filtramos por esa columna aqu� para evitar errores.
        $sentencia_wp = $base_de_datos->prepare("SELECT lat, lng, nombre, orden FROM tab_ruta_waypoints WHERE id_ruta = ? ORDER BY orden ASC");
        $sentencia_wp->execute([$ruta['id_ruta']]);
        $waypoints = $sentencia_wp->fetchAll(PDO::FETCH_ASSOC);
        $ruta['waypoints'] = array_map(function($wp) {
            return [
                'lat' => (float)$wp['lat'],
                'lng' => (float)$wp['lng'],
                'nombre' => $wp['nombre'],
                'orden' => (int)$wp['orden']
            ];
        }, $waypoints);
    } catch (Exception $e) {
        $ruta['waypoints'] = [];
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(array_values($rutas));
exit;



