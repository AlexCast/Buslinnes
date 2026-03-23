<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
include_once('../config/database.php');
include_once('../app/userClass.php');

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('Token inválido');
}

// Verificar token
$userClass = new userClass();
$stmt = $userClass->db->prepare("SELECT email FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() LIMIT 1");
$stmt->execute([$token]);
$row = $stmt->fetch();

if (!$row) {
    die('Token inválido o expirado');
}

$email = $row['email'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($newPassword) < 6) {
        $message = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Las contraseñas no coinciden';
    } else {
        // Actualizar contraseña
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $userClass->db->prepare("UPDATE tab_usuarios SET contrasena = ? WHERE correo = ?");
        $stmt->execute([$hashedPassword, $email]);

        // Eliminar token
        $stmt = $userClass->db->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
        $stmt->execute([$token]);

        $message = 'Contraseña actualizada exitosamente. <a href="/buslinnes/public/login.php">Iniciar sesión</a>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Buslinnes</title>
    <link rel="stylesheet" href="./assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/buslinnes/assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>
                <a href="index.html">
                    <img class="logo-light" src="/buslinnes/assets/img/logo.svg" alt="Buslinnes Logo">
                    <img class="logo-dark" src="/buslinnes/assets/img/logomorado.svg" alt="Buslinnes Logo (dark)">
                </a>
            </h1>
            <p>Restablecer Contraseña</p>
        </div>

        <div class="login-body">
            <?php if ($message): ?>
                <div class="confirmation-message">
                    <p><?php echo $message; ?></p>
                </div>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="password" class="form-label">Nueva Contraseña</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" class="form-input" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Restablecer Contraseña</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

