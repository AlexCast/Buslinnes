<?php
/**
 * Debug OneSignal - Ver logs
 */
if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']);

// Archivo de log de errores de PHP - intentar múltiples ubicaciones
$error_log_file = ini_get('error_log');
$default_apache_log = "C:/Apache24/logs/error.log";

// Usar la ruta por defecto si ini_get no devuelve nada válido
if (!$error_log_file || $error_log_file === 'syslog') {
    $error_log_file = $default_apache_log;
}

// Enviar un log de prueba para verificar que funciona
error_log("[DEBUG " . date('Y-m-d H:i:s') . "] Accediendo a debug_logs.php");

// Diagnosticar el log
$log_exists = false;
$log_size = 0;
$log_content = '';
$all_lines = [];

if ($error_log_file && file_exists($error_log_file)) {
    $log_exists = true;
    $log_size = filesize($error_log_file);
    
    // Leer últimas 150 líneas del archivo completo
    $lines = file($error_log_file);
    $all_lines = array_slice($lines, max(0, count($lines) - 150), 150);
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Debug OneSignal Logs</title>
    <style>
        body { 
            font-family: 'Segoe UI', monospace; 
            padding: 20px; 
            background: #1e1e1e; 
            color: #d4d4d4; 
            line-height: 1.6;
        }
        pre { 
            background: #2d2d2d; 
            padding: 15px; 
            border-radius: 6px; 
            overflow-x: auto;
            border: 1px solid #444;
            max-height: 600px;
            overflow-y: auto;
        }
        h1 { color: #4ec9b0; margin-bottom: 10px; }
        h2 { color: #9cdcfe; margin-top: 25px; margin-bottom: 10px; border-bottom: 1px solid #444; padding-bottom: 5px; }
        .info { 
            background: #2d4a2d; 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 20px;
            border-left: 4px solid #4ec9b0;
        }
        .warning { 
            background: #4a3d2d; 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 20px;
            border-left: 4px solid #dcdcaa;
        }
        .error { 
            background: #4a2d2d; 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 20px;
            border-left: 4px solid #f48771;
        }
        .btn { 
            padding: 10px 20px; 
            background: #007acc; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block;
            margin: 5px 5px 5px 0;
        }
        .btn:hover { background: #005a9e; }
        .btn-secondary { background: #6a6a6a; }
        .btn-secondary:hover { background: #7a7a7a; }
        .stat { display: inline-block; margin-right: 20px; }
        .stat-label { color: #9cdcfe; font-weight: bold; }
        .stat-value { color: #ce9178; }
    </style>
</head>
<body>
    <h1>🔍 Debug OneSignal - Visor de Logs</h1>
    
    <div class="info">
        <h3 style="margin-top: 0; color: #4ec9b0;">📋 Configuración del Sistema</h3>
        <div class="stat">
            <span class="stat-label">Archivo:</span>
            <span class="stat-value"><?php echo htmlspecialchars($error_log_file); ?></span>
        </div><br>
        <div class="stat">
            <span class="stat-label">Existe:</span>
            <span class="stat-value"><?php echo $log_exists ? '✅ Sí' : '❌ No'; ?></span>
        </div><br>
        <div class="stat">
            <span class="stat-label">Tamaño:</span>
            <span class="stat-value"><?php echo $log_exists ? number_format($log_size) . ' bytes' : 'N/A'; ?></span>
        </div><br>
        <div class="stat">
            <span class="stat-label">Líneas cargadas:</span>
            <span class="stat-value"><?php echo count($all_lines); ?></span>
        </div>
    </div>

    <?php
    // Filtrar líneas por tipo
    $onesignal_lines = [];
    $error_lines = [];
    $test_lines = [];
    
    foreach ($all_lines as $line) {
        if (stripos($line, 'OneSignal') !== false) {
            $onesignal_lines[] = $line;
        }
        if (stripos($line, 'ERROR') !== false || stripos($line, 'error') !== false) {
            $error_lines[] = $line;
        }
        if (stripos($line, 'DEBUG') !== false) {
            $test_lines[] = $line;
        }
    }
    ?>

    <!-- Logs de OneSignal -->
    <h2>🚀 Logs de OneSignal (<?php echo count($onesignal_lines); ?>)</h2>
    <?php if (empty($onesignal_lines) && $log_exists): ?>
        <div class="warning">
            ⚠️ No se encontraron logs de OneSignal aún.<br>
            <small>Intenta enviar una notificación con el checkbox ✅ activado y recarga esta página.</small>
        </div>
    <?php elseif (empty($onesignal_lines) && !$log_exists): ?>
        <div class="error">
            ❌ No se pudo acceder al archivo de log. Verifica la ruta de error_log en Apache.
        </div>
    <?php else: ?>
        <pre><?php echo htmlspecialchars(implode('', $onesignal_lines)); ?></pre>
    <?php endif; ?>

    <!-- Logs de ERROR -->
    <h2>⚠️ Líneas con ERROR (<?php echo count($error_lines); ?>)</h2>
    <?php if (!empty($error_lines)): ?>
        <pre><?php echo htmlspecialchars(implode('', array_slice($error_lines, -20))); ?></pre>
    <?php else: ?>
        <p style="color: #6a9955;">✅ No hay errores registrados.</p>
    <?php endif; ?>

    <!-- Logs de prueba -->
    <h2>🧪 Logs de Prueba (<?php echo count($test_lines); ?>)</h2>
    <?php if (!empty($test_lines)): ?>
        <pre><?php echo htmlspecialchars(implode('', array_slice($test_lines, -20))); ?></pre>
    <?php else: ?>
        <p style="color: #6a9955;">Sin logs de DEBUG aún.</p>
    <?php endif; ?>

    <!-- Últimas 20 líneas -->
    <h2>📝 Últimas 20 Líneas del Log</h2>
    <?php if (!empty($all_lines)): ?>
        <pre><?php echo htmlspecialchars(implode('', array_slice($all_lines, -20))); ?></pre>
    <?php else: ?>
        <p style="color: #ce9178;">El archivo de log está vacío o no es accesible.</p>
    <?php endif; ?>

    <hr style="border-color: #444; margin: 30px 0;">
    
    <div style="background: #2d2d2d; padding: 15px; border-radius: 6px;">
        <strong>🔗 Enlaces rápidos:</strong><br><br>
        <a href="forma_notificaciones.php" class="btn">📝 Enviar Notificación</a>
        <a href="listar_notificaciones.php" class="btn">📋 Ver Notificaciones</a>
        <a href="javascript:location.reload()" class="btn btn-secondary">🔄 Recargar</a>
        <a href="?clear=1" class="btn btn-secondary">🗑️ Limpiar (borrar log)</a>
    </div>

</body>
</html>

<?php
// Opcional: Limpiar el log si se solicita
if (isset($_GET['clear']) && $_GET['clear'] == '1' && $log_exists) {
    file_put_contents($error_log_file, "");
    header("Location: debug_logs.php");
    exit;
}
?>


