<?php
/**
 * Cloudinary Helper - Maneja uploads a Cloudinary API
 * Sin dependencias externas (usa file_get_contents + stream context)
 */

function loadEnvConfig(): array
{
    $envFile = __DIR__ . '/../.env';
    $config = [
        'CLOUDINARY_CLOUD_NAME' => '',
        'CLOUDINARY_API_KEY' => '',
        'CLOUDINARY_API_SECRET' => ''
    ];

    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remover comillas si existen (simple, doble o none)
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }
                
                if (isset($config[$key])) {
                    $config[$key] = $value;
                }
            }
        }
    }

    return $config;
}

function uploadToCloudinary(string $filePath, string $resourceType = 'image', ?string $publicId = null): ?array
{
    if (!file_exists($filePath)) {
        throw new Exception("Archivo no encontrado: $filePath");
    }

    $config = loadEnvConfig();
    
    if (empty($config['CLOUDINARY_CLOUD_NAME']) || empty($config['CLOUDINARY_API_KEY'])) {
        throw new Exception('Credenciales de Cloudinary no configuradas en .env');
    }

    $cloudName = $config['CLOUDINARY_CLOUD_NAME'];
    $apiKey = $config['CLOUDINARY_API_KEY'];
    $apiSecret = $config['CLOUDINARY_API_SECRET'];

    // Timestamp para evitar colisiones
    $timestamp = (int)(microtime(true) * 1000);
    
    // Generar public_id único si no se proporciona
    if (!$publicId) {
        $publicId = 'buslinnes/' . $resourceType . '_' . date('YmdHis') . '_' . substr(md5(uniqid()), 0, 8);
    }

    // Generar firma: string a firmar = "public_id={publicId}&timestamp={timestamp}{apiSecret}"
    $toSign = "public_id=$publicId&timestamp=$timestamp" . $apiSecret;
    $signature = hash('sha256', $toSign);

    // Leer contenido del archivo como binario
    $fileContent = file_get_contents($filePath);
    if ($fileContent === false) {
        throw new Exception("No se puede leer el archivo: $filePath");
    }

    // Construir multipart form data manualmente
    $boundary = '----CloudinaryUploadBoundary' . uniqid();
    $body = '';

    // Agregar campos en orden específico para Cloudinary
    $fields = [
        'file' => null, // Se agregará después
        'public_id' => $publicId,
        'timestamp' => (string)$timestamp,
        'api_key' => $apiKey,
        'signature' => $signature,
    ];

    foreach ($fields as $name => $value) {
        if ($name === 'file') continue; // Skip file por ahora
        
        $body .= "--$boundary\r\n";
        $body .= "Content-Disposition: form-data; name=\"$name\"\r\n\r\n";
        $body .= "$value\r\n";
    }

    // Agregar archivo
    $fileName = basename($filePath);
    $body .= "--$boundary\r\n";
    $body .= "Content-Disposition: form-data; name=\"file\"; filename=\"$fileName\"\r\n";
    $body .= "Content-Type: application/octet-stream\r\n\r\n";
    $body .= $fileContent . "\r\n";
    $body .= "--$boundary--\r\n";

    // Crear stream context
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: multipart/form-data; boundary=' . $boundary,
                'Connection: close',
            ],
            'content' => $body,
            'timeout' => 60,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ]
    ];

    $context = stream_context_create($options);
    $url = "https://api.cloudinary.com/v1_1/$cloudName/$resourceType/upload";

    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        $error = error_get_last();
        $errorMsg = ($error['message'] ?? 'Error desconocido');
        // Obtener headers para más detalles
        $headers = $http_response_header ?? [];
        throw new Exception("Error en upload Cloudinary: $errorMsg. Headers: " . implode("; ", $headers));
    }

    $responseData = json_decode($response, true);
    
    if (!isset($responseData['secure_url'])) {
        $errorMsg = $responseData['error']['message'] ?? 'Error desconocido en respuesta';
        throw new Exception("Error en upload Cloudinary: $errorMsg");
    }
    
    return [
        'secure_url' => $responseData['secure_url'] ?? null,
        'public_id' => $responseData['public_id'] ?? $publicId,
        'format' => $responseData['format'] ?? null,
        'width' => $responseData['width'] ?? null,
        'height' => $responseData['height'] ?? null,
    ];
}

function deleteFromCloudinary(string $publicId, string $resourceType = 'image'): bool
{
    $config = loadEnvConfig();
    
    if (empty($config['CLOUDINARY_CLOUD_NAME']) || empty($config['CLOUDINARY_API_KEY'])) {
        throw new Exception('Credenciales de Cloudinary no configuradas en .env');
    }

    $cloudName = $config['CLOUDINARY_CLOUD_NAME'];
    $apiKey = $config['CLOUDINARY_API_KEY'];
    $apiSecret = $config['CLOUDINARY_API_SECRET'];
    $timestamp = (int)(microtime(true) * 1000);

    // Generar firma
    $paramsString = "public_id=$publicId&timestamp=$timestamp";
    $signature = hash('sha256', $paramsString . $apiSecret);

    // Construir form data
    $boundary = '----CloudinaryDeleteBoundary' . uniqid();
    $body = '';

    $fields = [
        'public_id' => $publicId,
        'timestamp' => $timestamp,
        'api_key' => $apiKey,
        'signature' => $signature,
    ];

    foreach ($fields as $name => $value) {
        $body .= "--$boundary\r\n";
        $body .= "Content-Disposition: form-data; name=\"$name\"\r\n\r\n";
        $body .= "$value\r\n";
    }
    
    $body .= "--$boundary--\r\n";

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: multipart/form-data; boundary=' . $boundary,
                'Connection: close',
            ],
            'content' => $body,
            'timeout' => 30,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ]
    ];

    $context = stream_context_create($options);
    $url = "https://api.cloudinary.com/v1_1/$cloudName/$resourceType/destroy";

    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        return ($data['result'] ?? null) === 'ok';
    }

    return false;
}

