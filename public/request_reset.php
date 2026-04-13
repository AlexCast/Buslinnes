<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

header('Content-Type: application/json');

// Este endpoint es público (para usuarios no autenticados que olvidaron contraseña)
// Por lo tanto, NO validamos CSRF ni sesión, solo aplicamos headers de seguridad
try {
    require_once('../app/SecurityMiddleware.php');
    // protect(false) = solo headers de seguridad, sin CSRF/rate limit validation
    SecurityMiddleware::protect([
        'csrf' => false,
        'rateLimit' => false,
        'origin' => false,
        'userAgent' => false,
        'securityHeaders' => true,
        'jwt' => false
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de seguridad']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Support both application/json and form-urlencoded bodies
// Use SecurityMiddleware::getRawInput() — php://input can only be read once
$email = trim($_POST['email'] ?? '');
if (empty($email)) {
    $rawInput = SecurityMiddleware::getRawInput();
    if ($rawInput) {
        $jsonData = json_decode($rawInput, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['email'])) {
            $email = trim($jsonData['email']);
        }
    }
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Correo electrónico inválido']);
    exit();
}

// DB is best-effort: failure should not block sending the reset email
$dbAvailable = false;
try {
    include_once('../config/database.php');
    include_once('../app/userClass.php');
    $dbAvailable = true;
} catch (\Throwable $e) {
    error_log("request_reset: DB no disponible: " . $e->getMessage());
}

// Generate and store token
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+12 hours'));

if ($dbAvailable) {
    try {
        $userClass = new userClass();
        // Delete any existing token for this email, then insert fresh one
        $userClass->db->prepare("DELETE FROM password_reset_tokens WHERE email_usuario = ?")->execute([$email]);
        $stmt = $userClass->db->prepare(
            "INSERT INTO password_reset_tokens (email_usuario, token, expires_at) VALUES (?, ?, ?)"
        );
        $stmt->execute([$email, $token, $expires]);
    } catch (\Throwable $e) {
        error_log("Error guardando token de reset: " . $e->getMessage());
        // Don't nullify the token — keep it so the link is still valid if DB recovers
    }
} else {
    $token = null;
}

// Send email — always return a generic 200 response regardless of outcome
// (security: do not reveal whether the email is registered or if sending failed)
$result = sendResetEmail($email, $token);
if (!$result['success']) {
    error_log("Error enviando correo de reset a $email: " . $result['message']);
}

echo json_encode([
    'success' => true,
    'message' => 'Si el correo está registrado, recibirás las instrucciones en breve.'
]);
exit();

function sendResetEmail($to, $token = null) {
    // Configurar PHPMailer (similar a enviar_correo_simple.php)
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        return ['success' => false, 'message' => 'PHPMailer no instalado'];
    }

    if (!defined('GMAIL_USERNAME') || !defined('GMAIL_APP_PASSWORD')) {
        return ['success' => false, 'message' => 'Credenciales de Gmail no configuradas'];
    }

    require_once __DIR__ . '/../vendor/autoload.php';

    // Credenciales de Gmail desde config
    $gmail = GMAIL_USERNAME;
    $password = GMAIL_APP_PASSWORD;

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $gmail;
        $mail->Password = $password;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 10; // Abort if SMTP doesn't respond in 10s

        $mail->setFrom($gmail, 'Buslinnes');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de Contraseña - Buslinnes';
        
        // Construir el enlace de recuperación usando el host real de la petición
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $resetLink = "$scheme://$host/buslinnes/templates/verificacion_correo.html?token=" . urlencode($token ?? '');
        
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; padding: 15px 30px; background: #8059d4ff; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Buslinnes</h1>
                    <p>Recuperación de Contraseña</p>
                </div>
                <div class='content'>
                    <h2>Hola,</h2>
                    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta asociada al correo: <strong>$to</strong></p>
                    <p>Para establecer una nueva contraseña, haz clic en el siguiente botón:</p>
                    <p style='text-align: center;'>
                        <a href='$resetLink' class='button'>Restablecer Contraseña</a>
                    </p>
                    <p>O copia y pega el siguiente enlace en tu navegador:</p>
                    <p style='word-break: break-all; background: #fff; padding: 10px; border-radius: 5px;'>$resetLink</p>
                    <p><strong>Este enlace expirará en 12 horas.</strong></p>
                    <p>Si no solicitaste este cambio, puedes ignorar este correo de forma segura.</p>
                </div>
                <div class='footer'>
                    <p>© 2026 Buslinnes - Sistema de Transporte Público</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->send();
        return ['success' => true, 'message' => 'Correo enviado exitosamente'];
    } catch (\Exception $e) {
        error_log("Error enviando correo: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error enviando correo: ' . ($mail->ErrorInfo ?? $e->getMessage())];
    }
}
?></content>
<parameter name="filePath">c:\Apache24\htdocs\buslinnes\request_reset.php

