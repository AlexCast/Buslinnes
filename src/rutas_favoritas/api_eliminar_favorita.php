<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../app/SecurityMiddleware.php';

SecurityMiddleware::protect([
    'csrf' => true,
    'rateLimit' => true,
    'origin' => true,
    'userAgent' => true,
    'securityHeaders' => true
]);

header('Content-Type: application/json; charset=utf-8');

if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
include_once __DIR__ . '/../base_de_datos.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metodo no permitido']);
    exit;
}

try {
    $decoded = validarTokenJWT(['pasajero']);
    $idUsuario = isset($decoded->id_usuario) ? (int)$decoded->id_usuario : 0;

    if ($idUsuario <= 0) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Usuario no valido']);
        exit;
    }

    $raw = SecurityMiddleware::getRawInput();
    $body = json_decode($raw, true);
    if (!is_array($body)) {
        $body = [];
    }

    $idRuta = isset($body['id_ruta']) ? (int)$body['id_ruta'] : 0;
    if ($idRuta <= 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'id_ruta es obligatorio']);
        exit;
    }

    $find = $base_de_datos->prepare(
        'SELECT id_ruta_favorita
         FROM tab_rutas_favoritas
         WHERE id_pasajero = :id_pasajero AND id_ruta = :id_ruta
         LIMIT 1'
    );
    $find->execute([
        ':id_pasajero' => $idUsuario,
        ':id_ruta' => $idRuta
    ]);

    $idRutaFavorita = $find->fetchColumn();
    if (!$idRutaFavorita) {
        echo json_encode([
            'ok' => true,
            'already_removed' => true,
            'id_ruta' => $idRuta
        ]);
        exit;
    }

    $base_de_datos->beginTransaction();
    
    try {
        // Eliminar directo de la tabla (la tabla no tiene soft delete)
        $stmt = $base_de_datos->prepare(
            'DELETE FROM tab_rutas_favoritas 
             WHERE id_ruta_favorita = :id_ruta_favorita'
        );
        $stmt->execute([':id_ruta_favorita' => (int)$idRutaFavorita]);
        
        $base_de_datos->commit();
        
        echo json_encode([
            'ok' => true,
            'id_ruta_favorita' => (int)$idRutaFavorita,
            'id_ruta' => $idRuta
        ]);
    } catch (Exception $txnError) {
        if ($base_de_datos->inTransaction()) {
            $base_de_datos->rollBack();
        }
        throw $txnError;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Error eliminando favorita',
        'detail' => $e->getMessage()
    ]);
}



