<?php
require_once __DIR__ . '/../../app/SecurityMiddleware.php';

SecurityMiddleware::protect([
    'csrf' => false,
    'rateLimit' => true,
    'origin' => true,
    'userAgent' => true,
    'securityHeaders' => true
]);

header('Content-Type: application/json; charset=utf-8');

define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
include_once __DIR__ . '/../base_de_datos.php';

try {
    $decoded = validarTokenJWT(['pasajero']);
    $idUsuario = isset($decoded->id_usuario) ? (int)$decoded->id_usuario : 0;

    if ($idUsuario <= 0) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Usuario no valido']);
        exit;
    }

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

    echo json_encode([
        'ok' => true,
        'id_pasajero' => $idUsuario,
        'route_ids' => $routeIds,
        'items' => $rows
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Error listando favoritos',
        'detail' => $e->getMessage()
    ]);
}
