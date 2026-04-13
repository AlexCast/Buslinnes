<?php
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../src/validar_jwt.php';
require_once __DIR__ . '/../src/branding_config/save_branding.php';
require_once __DIR__ . '/../src/cloudinary_helper.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit;
}

if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
}

$jwtPayload = validarTokenJWT(['admin']);
if (!$jwtPayload) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$defaultColor = '#8059d4ff';
$defaultLogo = '/buslinnes/assets/img/logomorado.svg';
$defaultFavicon = '/buslinnes/mkcert/favicon.ico';
$maxBytesLogo = 2 * 1024 * 1024;
$maxBytesFavicon = 512 * 1024; // 500KB para favicon
$allowedMimesLogo = [
    'image/png' => 'png',
    'image/jpeg' => 'jpg',
    'image/webp' => 'webp'
];
$allowedMimesFavicon = [
    'image/x-icon' => 'ico',
    'image/vnd.microsoft.icon' => 'ico',
    'image/png' => 'png',
    'image/webp' => 'webp'
];

function detectUploadedMime(string $tmpPath): ?string
{
    $mime = null;

    if (function_exists('finfo_open')) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $detected = @finfo_file($finfo, $tmpPath);
            if ($detected) {
                $mime = strtolower(trim($detected));
            }
            @finfo_close($finfo);
        }
    }

    if (!$mime && function_exists('mime_content_type')) {
        $detected = @mime_content_type($tmpPath);
        if ($detected) {
            $mime = strtolower(trim($detected));
        }
    }

    if (!$mime && function_exists('getimagesize')) {
        $img = @getimagesize($tmpPath);
        if (is_array($img) && !empty($img['mime'])) {
            $mime = strtolower(trim($img['mime']));
        }
    }

    return $mime ?: null;
}

function normalizeColor(string $value): string
{
    return strtolower(trim($value));
}

function normalizeUploadedFilePermissions(string $filePath): void
{
    // Best-effort normalization: prevents Windows ACL edge-cases on files moved from temp upload dir.
    if (DIRECTORY_SEPARATOR === '\\') {
        if (function_exists('exec')) {
            $safePath = str_replace('"', '""', $filePath);
            @exec('icacls "' . $safePath . '" /inheritance:e >NUL 2>&1');
            @exec('icacls "' . $safePath . '" /grant *S-1-5-11:(M) >NUL 2>&1');
            @exec('icacls "' . $safePath . '" /grant *S-1-5-32-545:(RX) >NUL 2>&1');
        }
        return;
    }

    @chmod($filePath, 0644);
}

$primaryColor = isset($_POST['primary_color']) ? normalizeColor((string)$_POST['primary_color']) : null;
if (!$primaryColor) {
    $primaryColor = $defaultColor;
}

$resetBranding = isset($_POST['reset_branding']) && in_array(strtolower((string)$_POST['reset_branding']), ['1', 'true', 'yes', 'si'], true);
if ($resetBranding) {
    $primaryColor = $defaultColor;
}

if (!preg_match('/^#(?:[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $primaryColor)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Color invalido. Use #RRGGBB o #RRGGBBAA']);
    exit;
}

$logoUrlToSave = null;
if (!$resetBranding && !empty($_FILES['logo']) && isset($_FILES['logo']['error']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['logo'];

    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Error al subir el logo']);
        exit;
    }

    if ((int)$file['size'] <= 0 || (int)$file['size'] > $maxBytesLogo) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'El logo debe pesar maximo 2MB']);
        exit;
    }

    $tmpPath = (string)$file['tmp_name'];
    $mime = detectUploadedMime($tmpPath);
    if (!$mime || !isset($allowedMimesLogo[$mime])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Formato de logo no permitido. Use png, jpg o webp']);
        exit;
    }

    try {
        $uploadedLogo = uploadToCloudinary($tmpPath, 'image', 'buslinnes/logo_' . date('YmdHis') . '_' . substr(md5(uniqid()), 0, 8));
        $logoUrlToSave = $uploadedLogo['secure_url'];
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al guardar logo en Cloudinary: ' . $e->getMessage()]);
        exit;
    }
}

$faviconUrlToSave = null;
if (!$resetBranding && !empty($_FILES['favicon']) && isset($_FILES['favicon']['error']) && $_FILES['favicon']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['favicon'];

    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Error al subir el favicon']);
        exit;
    }

    if ((int)$file['size'] <= 0 || (int)$file['size'] > $maxBytesFavicon) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'El favicon debe pesar maximo 500KB']);
        exit;
    }

    $tmpPath = (string)$file['tmp_name'];
    $mime = detectUploadedMime($tmpPath);
    if (!$mime || !isset($allowedMimesFavicon[$mime])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Formato de favicon no permitido. Use ico, png o webp']);
        exit;
    }

    try {
        $uploadedFavicon = uploadToCloudinary($tmpPath, 'image', 'buslinnes/favicon_' . date('YmdHis') . '_' . substr(md5(uniqid()), 0, 8));
        $faviconUrlToSave = $uploadedFavicon['secure_url'];
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al guardar favicon en Cloudinary: ' . $e->getMessage()]);
        exit;
    }
}

try {
    if ($resetBranding) {
        $saved = brandingConfigResetPayload();
        $message = 'Branding restablecido a valores predeterminados';
    } else {
        $saved = brandingConfigSavePayload($primaryColor, $logoUrlToSave, $faviconUrlToSave);
        $message = 'Branding actualizado';
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => [
            'primary_color' => $saved['primary_color'] ?? $primaryColor,
            'logo_url' => $saved['logo_url'] ?? ($logoUrlToSave ?: $defaultLogo),
            'favicon_url' => $saved['favicon_url'] ?? ($faviconUrlToSave ?: $defaultFavicon),
            'updated_at' => $saved['updated_at'] ?? null
        ]
    ]);
} catch (Throwable $e) {
    error_log('Branding save error: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo guardar la configuracion de marca: ' . $e->getMessage()
    ]);
}
