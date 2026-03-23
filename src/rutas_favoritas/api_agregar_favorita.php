<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../app/SecurityMiddleware.php';

// Temporalmente desactivamos CSRF para debug
SecurityMiddleware::protect([
    'csrf' => false,
    'rateLimit' => true,
    'origin' => true,
    'userAgent' => true,
    'securityHeaders' => true
]);

header('Content-Type: application/json; charset=utf-8');

if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
include_once __DIR__ . '/../base_de_datos.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
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
        $body = $_POST;
    }

    $idRuta = isset($body['id_ruta']) ? (int)$body['id_ruta'] : 0;
    if ($idRuta <= 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'id_ruta es obligatorio']);
        exit;
    }

    $check = $base_de_datos->prepare(
        'SELECT id_ruta_favorita
         FROM tab_rutas_favoritas
         WHERE id_pasajero = :id_pasajero AND id_ruta = :id_ruta
         LIMIT 1'
    );
    $check->execute([
        ':id_pasajero' => $idUsuario,
        ':id_ruta' => $idRuta
    ]);
    $existingId = $check->fetchColumn();

    if ($existingId) {
        echo json_encode([
            'ok' => true,
            'already_exists' => true,
            'id_ruta_favorita' => (int)$existingId,
            'id_ruta' => $idRuta
        ]);
        exit;
    }

    $base_de_datos->beginTransaction();

    try {
        // Log de inicio
        error_log("DEBUG: Iniciando insert favorita - user: $idUsuario, ruta: $idRuta");

        // Obtener el próximo ID de forma segura (sin FOR UPDATE para evitar problemas)
        $result = $base_de_datos->query('SELECT COALESCE(MAX(id_ruta_favorita), 0) + 1 FROM tab_rutas_favoritas');
        $nextId = (int)$result->fetchColumn();
        error_log("DEBUG: nextId obtenido: $nextId");

        error_log("DEBUG: Insertando directamente - nextId=$nextId, user=$idUsuario, ruta=$idRuta");
        
        // Insertar directamente en la tabla (sin función)
        $stmt = $base_de_datos->prepare(
            'INSERT INTO tab_rutas_favoritas (id_ruta_favorita, id_pasajero, id_ruta)
             VALUES (:id_ruta_favorita, :id_pasajero, :id_ruta)'
        );
        
        $result = $stmt->execute([
            ':id_ruta_favorita' => $nextId,
            ':id_pasajero' => $idUsuario,
            ':id_ruta' => $idRuta
        ]);
        
        error_log("DEBUG: Insert ejecutado, resultado: " . ($result ? 'true' : 'false'));

        $base_de_datos->commit();
        error_log("DEBUG: Transacción confirmada");

        echo json_encode([
            'ok' => true,
            'id_ruta_favorita' => $nextId,
            'id_ruta' => $idRuta
        ]);
    } catch (Exception $txnError) {
        error_log("DEBUG: Excepción en transacción: " . $txnError->getMessage());
        if ($base_de_datos->inTransaction()) {
            $base_de_datos->rollBack();
        }
        throw $txnError;
    }
} catch (Exception $e) {
    error_log("FINAL ERROR: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Error agregando favorita',
        'detail' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString()
    ]);
}




