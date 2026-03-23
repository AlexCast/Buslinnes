<?php
/**
 * Diagnóstico del sistema de logging
 */
if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']);

header('Content-Type: text/html; charset=utf-8');

// Obtener información del error_log
$error_log_ini = ini_get('error_log');
$default_apache_log = "C:/Apache24/logs/error.log";
$log_path = ($error_log_ini && $error_log_ini !== 'syslog') ? $error_log_ini : $default_apache_log;

// Escribir un log de prueba
error_log("[DIAGNOSTICO " . date('Y-m-d H:i:s') . "] Test de error_log");

// Verificar permisos si el archivo existe
$log_writable = false;
$log_exists = false;
$log_size = 0;

if (file_exists($log_path)) {
    $log_exists = true;
    $log_writable = is_writable($log_path);
    $log_size = filesize($log_path);
} else {
    // Verificar si el directorio existe y es escribible
    $dir = dirname($log_path);
    $log_writable = is_dir($dir) && is_writable($dir);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Diagnóstico de Logging</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial; 
            padding: 20px; 
            background: #f5f5f5;
            line-height: 1.6;
        }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 3px solid #007acc; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 25px; }
        .section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .item:last-child { border-bottom: none; }
        .label { font-weight: bold; color: #333; width: 200px; display: inline-block; }
        .value { color: #666; font-family: monospace; }
        .ok { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #17a2b8; }
        .code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #007acc;
            margin: 10px 0;
            font-family: monospace;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px 10px 0;
            background: #007acc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }
        .btn:hover { background: #005a9e; }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success-box {
            background: #d4edda;
            border: 1px solid #28a745;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Diagnóstico del Sistema de Logging</h1>
        
        <!-- Configuración de PHP -->
        <div class="section">
            <h2>⚙️ Configuración de PHP</h2>
            
            <div class="item">
                <span class="label">error_log configurado:</span>
                <span class="value"><?php echo $error_log_ini ? htmlspecialchars($error_log_ini) : '<span class="info">Usando defecto: ' . htmlspecialchars($default_apache_log) . '</span>'; ?></span>
            </div>
            
            <div class="item">
                <span class="label">Ruta utilizada:</span>
                <span class="value"><?php echo htmlspecialchars($log_path); ?></span>
            </div>
        </div>

        <!-- Estado del Log -->
        <div class="section">
            <h2>📋 Estado del Log</h2>
            
            <div class="item">
                <span class="label">Ruta:</span>
                <span class="value"><?php echo htmlspecialchars($log_path); ?></span>
            </div>
            
            <div class="item">
                <span class="label">Existe:</span>
                <span class="value"><?php echo $log_exists ? '<span class="ok">✅ Sí</span>' : '<span class="error">❌ No</span>'; ?></span>
            </div>
            
            <div class="item">
                <span class="label">Escribible:</span>
                <span class="value"><?php echo $log_writable ? '<span class="ok">✅ Sí</span>' : '<span class="error">❌ No</span>'; ?></span>
            </div>
            
            <div class="item">
                <span class="label">Tamaño:</span>
                <span class="value"><?php echo $log_exists ? number_format($log_size) . ' bytes' : 'N/A'; ?></span>
            </div>
        </div>

        <!-- Recomendaciones -->
        <div class="section">
            <h2>💡 Estado</h2>
            
            <?php if (!$log_exists || !$log_writable): ?>
                <div class="warning-box">
                    <strong>⚠️ Problema detectado:</strong> El error_log no es accesible o no es escribible.
                </div>
            <?php else: ?>
                <div class="success-box">
                    ✅ El error_log está correctamente configurado y es escribible.<br>
                    Los logs de error_log() aparecerán en: <strong><?php echo htmlspecialchars($log_path); ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <!-- Lectura del Log -->
        <div class="section">
            <h2>📖 Últimas líneas del Log</h2>
            
            <?php 
            if (file_exists($log_path)) {
                $lines = file($log_path);
                $last_100 = array_slice($lines, -100);
                
                echo "<p><strong>Total de líneas: " . count($lines) . "</strong></p>";
                echo "<p><strong>Leyendo desde: " . htmlspecialchars($log_path) . "</strong></p>";
                echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 4px; max-height: 500px; overflow-y: auto; border-left: 4px solid #007acc;'>";
                echo htmlspecialchars(implode('', $last_100));
                echo "</pre>";
            } else {
                echo "<p class='error'>❌ No se pudo acceder al archivo de log: " . htmlspecialchars($log_path) . "</p>";
            }
            ?>
        </div>

        <!-- Acciones -->
        <div class="section">
            <h2>🔄 Acciones</h2>
            <a href="forma_notificaciones.php" class="btn">Enviar Notificación</a>
        </div>
    </div>
</body>
</html>


