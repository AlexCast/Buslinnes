<?php
require_once __DIR__ . '/../../app/SecurityMiddleware.php';

SecurityMiddleware::protect([
    'csrf' => false,
    'rateLimit' => false,  
    'origin' => false,     
    'userAgent' => false,  
    'securityHeaders' => true
]);

header('Content-Type: application/json; charset=utf-8');

try {
    // Sin requetir JWT - simplemente retornar array vacío como default
    define('VALIDAR_JWT_MANUAL', true);
    require_once __DIR__ . '/../validar_jwt.php';
    include_once __DIR__ . '/../base_de_datos.php';
    
    $decoded = null;
    $idUsuario = 0;
    
    try {
        $decoded = validarTokenJWT(['pasajero']);
        $idUsuario = isset($decoded->id_usuario) ? (int)$decoded->id_usuario : 0;
    } catch (Exception $tokenError) {
        // Sin JWT válido - ok, retornar favoritos vacíos
    }

    if ($idUsuario > 0 && isset($base_de_datos)) {
        try {
            $stmt = $base_de_datos->prepare(
                'SELECT id_ruta_favorita, id_ruta
                 FROM tab_rutas_favoritas
                 WHERE id_pasajero = :id_pasajero
                 ORDER BY id_ruta_favorita DESC'
            );
            $stmt->execute([':id_pasajero' => $idUsuario]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $routeIds = array_map(static function ($row) {
                return (int)$row['id_ruta'];
            }, $rows);
        } catch (Exception $dbError) {
            $routeIds = [];
            $rows = [];
        }
    } else {
        $routeIds = [];
        $rows = [];
    }

    echo json_encode([
        'ok' => true,
        'id_pasajero' => $idUsuario,
        'route_ids' => $routeIds,
        'items' => $rows
    ]);
} catch (Exception $e) {
    // Fallback final - retornar favoritos vacíos
    http_response_code(200);
    echo json_encode([
        'ok' => true,
        'id_pasajero' => 0,
        'route_ids' => [],
        'items' => []
    ]);
}
