<?php
header('Content-Type: application/json; charset=utf-8');

// === SEGURIDAD: Proteccion anti-scraping y CSRF ===
require_once __DIR__ . '/../../app/SecurityMiddleware.php';

SecurityMiddleware::protect([
    'csrf' => false,  // GET no requiere CSRF
    'rateLimit' => true,
    'origin' => true,
    'userAgent' => true,
    'securityHeaders' => true
]);
// === FIN SEGURIDAD ===

/**
 * API que devuelve la ruta asignada a un conductor específico
 * Parámetros GET: id_usuario
 */
include_once __DIR__ . '/../base_de_datos.php';

$idConductor = null;
$conductoreEmail = null;

if (isset($_GET['id_usuario']) && trim($_GET['id_usuario']) !== '') {
    $idConductor = intval($_GET['id_usuario']);
}

if (isset($_GET['email_conductor']) && trim($_GET['email_conductor']) !== '') {
    $conductoreEmail = trim($_GET['email_conductor']);
}

if (!$idConductor && !$conductoreEmail) {
    http_response_code(400);
    echo json_encode(['error' => 'id_usuario o email_conductor requerido']);
    exit;
}

try {
    // Resolver id_usuario por email si no se dio id
    if (!$idConductor && $conductoreEmail) {
        $stmt = $base_de_datos->prepare(
            "SELECT id_usuario FROM tab_conductores WHERE LOWER(email_conductor) = LOWER(:email_conductor) AND fec_delete IS NULL LIMIT 1"
        );
        $stmt->execute([':email_conductor' => $conductoreEmail]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            http_response_code(404);
            echo json_encode(['error' => 'Conductor no encontrado por email']);
            exit;
        }
        $idConductor = intval($result['id_usuario']);
    }

    if ($idConductor <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'id_usuario inválido']);
        exit;
    }

    // Paso 1: Verificar que el conductor existe y está activo
    $stmt = $base_de_datos->prepare(
        "SELECT id_usuario FROM tab_conductores 
         WHERE id_usuario = :id_usuario 
         AND fec_delete IS NULL 
         LIMIT 1"
    );
    $stmt->execute([':id_usuario' => $idConductor]);
    $conductor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$conductor) {
        http_response_code(404);
        echo json_encode(['error' => 'Conductor no encontrado o eliminado']);
        exit;
    }
    
    // Paso 2: Obtener el bus asignado al conductor
    $stmt = $base_de_datos->prepare(
        "SELECT id_bus FROM tab_buses 
         WHERE id_usuario = :id_usuario 
         AND fec_delete IS NULL 
         LIMIT 1"
    );
    $stmt->execute([':id_usuario' => $idConductor]);
    $bus = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bus) {
        http_response_code(404);
        echo json_encode(['error' => 'No hay bus asignado a este conductor']);
        exit;
    }
    
    $idBus = $bus['id_bus'];
    
    // Paso 3: Obtener la ruta asignada a este bus
    $stmt = $base_de_datos->prepare(
        "SELECT rb.id_ruta FROM tab_ruta_bus rb 
         WHERE rb.id_bus = :id_bus 
         AND rb.fec_delete IS NULL 
         LIMIT 1"
    );
    $stmt->execute([':id_bus' => $idBus]);
    $rutaBus = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$rutaBus) {
        http_response_code(404);
        echo json_encode(['error' => 'No hay ruta asignada al bus de este conductor']);
        exit;
    }
    
    $idRuta = $rutaBus['id_ruta'];
    
    // Paso 4: Obtener los detalles completos de la ruta
    $queryRuta = "SELECT 
                    r.id_ruta, 
                    r.nom_ruta, 
                    r.inicio_ruta, 
                    r.fin_ruta, 
                    r.longitud, 
                    r.val_pasaje,
                    r.inicio_lat::float AS inicio_lat,
                    r.inicio_lng::float AS inicio_lng,
                    r.fin_lat::float AS fin_lat,
                    r.fin_lng::float AS fin_lng,
                    (SELECT COUNT(*) FROM tab_ruta_bus rb 
                     JOIN tab_buses b ON rb.id_bus = b.id_bus 
                     WHERE rb.id_ruta = r.id_ruta 
                     AND rb.fec_delete IS NULL 
                     AND b.fec_delete IS NULL) AS buses_count
                 FROM tab_rutas r
                 WHERE r.id_ruta = :id_ruta
                 AND r.fec_delete IS NULL";
    
    $stmt = $base_de_datos->prepare($queryRuta);
    $stmt->execute([':id_ruta' => $idRuta]);
    $ruta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ruta) {
        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada o eliminada']);
        exit;
    }
    
    // Paso 5: Obtener los waypoints de la ruta
    $stmt = $base_de_datos->prepare(
        "SELECT id_waypoint, orden, lat, lng, nombre 
         FROM tab_ruta_waypoints 
         WHERE id_ruta = :id_ruta 
         AND fec_delete IS NULL 
         ORDER BY orden ASC"
    );
    $stmt->execute([':id_ruta' => $idRuta]);
    $waypoints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Paso 6: Obtener información del bus para contexto adicional
    $stmt = $base_de_datos->prepare(
        "SELECT id_bus, capacidad_pasajeros, ind_estado_buses 
         FROM tab_buses 
         WHERE id_bus = :id_bus 
         AND fec_delete IS NULL"
    );
    $stmt->execute([':id_bus' => $idBus]);
    $busInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Construir respuesta
    $ruta['waypoints'] = $waypoints ? $waypoints : [];
    $ruta['bus_info'] = $busInfo ? $busInfo : [];
    
    echo json_encode($ruta);
    http_response_code(200);

} catch (PDOException $e) {
    error_log('Error al obtener ruta del conductor: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
?>


