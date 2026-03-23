<?php
/**
 * ARCHIVO DE PRUEBA: Enviar Correo Simple
 * 
 * Este archivo te permite probar si puedes enviar correos.
 * 
 * CÓMO USARLO:
 * 1. Configura tus datos de Gmail abajo (líneas 15-16)
 * 2. Abre en el navegador: http://localhost/buslinnes/enviar_correo_simple.php
 * 3. Ingresa un correo de destino y haz clic en "Enviar"
 */

// ============================================
// PASO 1: CONFIGURA TUS DATOS DE GMAIL AQUÍ
// ============================================
$mi_correo_gmail = 'tu-email@gmail.com';  // ⬅️ CAMBIA ESTO: Tu correo Gmail
$mi_password_gmail = 'tu-contraseña-app';  // ⬅️ CAMBIA ESTO: Contraseña de aplicación (ver abajo)

// ============================================
// NO TOCAR NADA DE AQUÍ HACIA ABAJO
// ============================================

$mensaje_resultado = '';
$tipo_mensaje = '';

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar'])) {
    $correo_destino = trim($_POST['correo_destino'] ?? '');
    
    if (empty($correo_destino) || !filter_var($correo_destino, FILTER_VALIDATE_EMAIL)) {
        $mensaje_resultado = 'Por favor ingresa un correo válido';
        $tipo_mensaje = 'error';
    } else {
        // Intentar enviar el correo
        $resultado = enviarCorreoSimple($mi_correo_gmail, $mi_password_gmail, $correo_destino);
        
        if ($resultado['exito']) {
            $mensaje_resultado = '¡Correo enviado exitosamente a: ' . $correo_destino . '!';
            $tipo_mensaje = 'exito';
        } else {
            $mensaje_resultado = 'Error: ' . $resultado['mensaje'];
            $tipo_mensaje = 'error';
        }
    }
}

/**
 * Función simple para enviar correo usando Gmail
 */
function enviarCorreoSimple($correo_gmail, $password_gmail, $correo_destino) {
    // Verificar si PHPMailer está instalado
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        return [
            'exito' => false,
            'mensaje' => 'PHPMailer no está instalado. Ejecuta: composer require phpmailer/phpmailer'
        ];
    }
    
    require_once __DIR__ . '/vendor/autoload.php';
    
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configuración del servidor SMTP de Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $correo_gmail;
        $mail->Password = $password_gmail;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        // Remitente y destinatario
        $mail->setFrom($correo_gmail, 'Buslinnes');
        $mail->addAddress($correo_destino);
        
        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Prueba de Correo - Buslinnes';
        $mail->Body = '
        <h2>¡Hola!</h2>
        <p>Este es un correo de prueba desde Buslinnes.</p>
        <p>Si recibes este correo, significa que la configuración está funcionando correctamente.</p>
        <p>Fecha: ' . date('d/m/Y H:i:s') . '</p>
        ';
        
        $mail->send();
        
        return ['exito' => true, 'mensaje' => 'Correo enviado'];
    } catch (\Exception $e) {
        return ['exito' => false, 'mensaje' => $mail->ErrorInfo];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Envío de Correo</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #8059d4;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .pasos {
            background-color: #f8f9fa;
            border-left: 4px solid #8059d4;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        .pasos h3 {
            color: #8059d4;
            margin-bottom: 15px;
        }
        .pasos ol {
            margin-left: 20px;
            color: #333;
        }
        .pasos li {
            margin: 10px 0;
            line-height: 1.6;
        }
        .pasos code {
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            color: #d63384;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        input[type="email"]:focus {
            outline: none;
            border-color: #8059d4;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #8059d4;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #6a4c93;
        }
        .mensaje {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .mensaje.exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .advertencia {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .advertencia strong {
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Prueba de Envío de Correo</h1>
        <p class="subtitle">Paso 1: Verificar que puedes enviar correos</p>

        <div class="pasos">
            <h3>📋 Pasos para configurar:</h3>
            <ol>
                <li><strong>Instalar PHPMailer:</strong> Abre PowerShell y ejecuta:<br>
                    <code>cd c:\Apache24\htdocs\buslinnes</code><br>
                    <code>composer require phpmailer/phpmailer</code>
                </li>
                <li><strong>Configurar Gmail:</strong>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <li>Ve a: <a href="https://myaccount.google.com/security" target="_blank">Google Seguridad</a></li>
                        <li>Activa "Verificación en 2 pasos"</li>
                        <li>Ve a: <a href="https://myaccount.google.com/apppasswords" target="_blank">Contraseñas de aplicación</a></li>
                        <li>Crea una contraseña de aplicación para "Correo"</li>
                        <li>Copia la contraseña de 16 caracteres</li>
                    </ul>
                </li>
                <li><strong>Editar este archivo:</strong> Abre <code>enviar_correo_simple.php</code> y cambia las líneas 15-16 con tus datos</li>
                <li><strong>Probar:</strong> Ingresa un correo abajo y haz clic en "Enviar"</li>
            </ol>
        </div>

        <?php if ($mensaje_resultado): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje_resultado); ?>
            </div>
        <?php endif; ?>

        <div class="advertencia">
            <strong>⚠️ Importante:</strong> Antes de probar, asegúrate de haber configurado tus datos de Gmail en las líneas 15-16 de este archivo PHP.
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="correo_destino">Correo de Destino (donde quieres recibir el correo):</label>
                <input 
                    type="email" 
                    id="correo_destino" 
                    name="correo_destino" 
                    placeholder="correo@ejemplo.com" 
                    required
                    value="<?php echo isset($_POST['correo_destino']) ? htmlspecialchars($_POST['correo_destino']) : ''; ?>"
                >
            </div>
            <button type="submit" name="enviar" class="btn">
                📤 Enviar Correo de Prueba
            </button>
        </form>
    </div>
</body>
</html>


