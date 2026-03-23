<?php
/**
 * Helper para enviar notificaciones push vía OneSignal.
 */
if (!defined('ONESIGNAL_APP_ID') && file_exists(__DIR__ . '/../config/database.php')) {
    require_once __DIR__ . '/../config/database.php';
}

/**
 * Requiere que config/database.php defina ONESIGNAL_APP_ID y ONESIGNAL_REST_API_KEY.
 *
 * Para "enviar a usuario": el frontend debe llamar OneSignal.login(id_usuario) al iniciar sesión.
 * Para "enviar a rol": los usuarios deben tener el tag "rol_id" con su id_rol (ej: "2", "3").
 *
 * @param string $titulo Título de la notificación
 * @param string $mensaje Cuerpo del mensaje
 * @param int|null $id_usuario Si se envía a un usuario concreto
 * @param int|null $id_rol Si se envía a todos los usuarios con ese rol. Si ambos null = Subscribed Users
 * @return array ['ok' => bool, 'error' => string|null, 'response' => mixed]
 */
function enviar_notificacion_onesignal(string $titulo, string $mensaje, ?int $id_usuario, ?int $id_rol): array {
    if (!defined('ONESIGNAL_APP_ID') || !defined('ONESIGNAL_REST_API_KEY')) {
        return ['ok' => false, 'error' => 'OneSignal no configurado en config/database.php', 'response' => null];
    }
    $apiKey = ONESIGNAL_REST_API_KEY;
    if (empty($apiKey)) {
        return ['ok' => false, 'error' => 'ONESIGNAL_REST_API_KEY vacía. Configúrala en config/database.php', 'response' => null];
    }

    $appId = ONESIGNAL_APP_ID;

    $body = [
        'app_id' => $appId,
        'contents' => ['en' => $mensaje, 'es' => $mensaje],
        'headings' => ['en' => $titulo, 'es' => $titulo],
    ];

    if ($id_usuario !== null) {
        // OneSignal v16: usar aliases para external_id + target_channel
        $body['include_aliases'] = [
            'external_id' => [(string) $id_usuario]
        ];
        $body['target_channel'] = 'push';
    } elseif ($id_rol !== null) {
        // Enviar a todos los que tengan el tag rol_id = id_rol
        $body['filters'] = [
            ['field' => 'tag', 'key' => 'rol_id', 'relation' => '=', 'value' => (string) $id_rol],
        ];
    } else {
        // Enviar a TODOS los suscritos
        $body['included_segments'] = ['All'];
    }

    $url = 'https://api.onesignal.com/notifications';
    $response = false;
    $httpCode = 0;

    // Usar stream_context en lugar de curl (curl no está disponible)
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAuthorization: Bearer " . $apiKey . "\r\n",
            'content' => json_encode($body),
            'timeout' => 15,
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    
    $response = @file_get_contents($url, false, $ctx);
    
    if (isset($http_response_header[0]) && preg_match('/\d{3}/', $http_response_header[0], $m)) {
        $httpCode = (int) $m[0];
    }

    $decoded = $response ? json_decode($response, true) : null;

    // Log de diagnóstico para verificar respuesta real de OneSignal
    error_log('OneSignal HTTP: ' . $httpCode . ' RESP: ' . ($response ?: 'null'));

    $is2xx = ($httpCode >= 200 && $httpCode < 300);
    $hasErrors = is_array($decoded) && !empty($decoded['errors']);
    $recipients = is_array($decoded) && isset($decoded['recipients']) ? (int) $decoded['recipients'] : null;
    $hasId = is_array($decoded) && !empty($decoded['id']);

    if ($is2xx && !$hasErrors && $hasId && ($recipients === null || $recipients > 0)) {
        return ['ok' => true, 'error' => null, 'response' => $decoded];
    }

    if ($is2xx && !$hasErrors && $hasId && $recipients === 0) {
        return [
            'ok' => false,
            'error' => 'OneSignal aceptó la solicitud, pero recipients=0 (no hay suscriptores que coincidan con el destino).',
            'response' => $decoded
        ];
    }

    $errMsg = $decoded['errors'][0] ?? $decoded['errors'] ?? $response ?? 'Error desconocido';
    if (is_array($errMsg)) {
        $errMsg = json_encode($errMsg);
    }
    return ['ok' => false, 'error' => (string) $errMsg, 'response' => $decoded];
}


