<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
// Incluir archivos de configuración y clases
include_once('../config/database.php');
include_once('../app/userClass.php');
if (!defined('JWT_SECRET')) { define('JWT_SECRET', 'TU_CLAVE_SECRETA_AQUI'); }
if (!defined('JWT_ISSUER')) { define('JWT_ISSUER', 'buslinnes'); }
if (!defined('JWT_AUDIENCE')) { define('JWT_AUDIENCE', 'buslinnes_users'); }
if (!defined('JWT_LIFETIME')) { define('JWT_LIFETIME', 1200); }

$jwt_secret = JWT_SECRET;
$jwt_issuer = JWT_ISSUER;
$jwt_audience = JWT_AUDIENCE;
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$userClass = new userClass();
$errorMsgReg = '';
$errorMsgLogin = '';
$successMsg = '';
$activeTab = 'login';
$nameRegValue = '';
$emailRegValue = '';
$termsChecked = '';

// Detectar si viene de un registro exitoso
if (!empty($_GET['success']) && $_GET['success'] == '1') {
    $successMsg = '¡Registro exitoso! Por favor, inicia sesión con tus credenciales.';
}

// Mostrar error cuando se bloquea acceso por intento de abrir herramientas de desarrollador
if (!empty($_GET['blocked']) && $_GET['blocked'] === 'devtools') {
    $errorMsgLogin = 'Acceso bloqueado por seguridad: intento de abrir herramientas de desarrollador.';
    $activeTab = 'login';
}

/* Procesamiento de formulario de login */
if (!empty($_POST['loginSubmit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // Validación de campos vacíos
    if (empty($email) && empty($password)) {
        $errorMsgLogin = "Por favor ingrese su correo electrónico y contraseña.";
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $errorMsgLogin]);
            exit();
        }
    } elseif (empty($email)) {
        $errorMsgLogin = "Por favor ingrese su correo electrónico.";
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $errorMsgLogin]);
            exit();
        }
    } elseif (empty($password)) {
        $errorMsgLogin = "Por favor ingrese su contraseña.";
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $errorMsgLogin]);
            exit();
        }
    } else {
        // Validar formato del correo
        $email_pattern = '~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i';
        if (!preg_match($email_pattern, $email)) {
            $errorMsgLogin = "El formato del correo electrónico no es válido.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $errorMsgLogin]);
                exit();
            }
        } else {
            try {
                // Verificar si el usuario existe
                $stmtCheck = $userClass->db->prepare("SELECT id_usuario FROM tab_usuarios WHERE correo = ? AND fec_delete IS NULL LIMIT 1");
                $stmtCheck->execute([$email]);
                $userExists = $stmtCheck->fetchColumn();

                if (!$userExists) {
                    $errorMsgLogin = "No existe una cuenta registrada con este correo electrónico.";
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => $errorMsgLogin]);
                        exit();
                    }
                } else {
                    // Intentar iniciar sesión
                    $loginResult = $userClass->iniciar_sesion_usuario($email, $password);
                    if ($loginResult) {
                        // Obtener el rol y id_rol del usuario
                        $stmt = $userClass->db->prepare("
                            SELECT r.nombre_rol, ur.id_rol
                            FROM tab_usuarios_roles ur
                            JOIN tab_roles r ON ur.id_rol = r.id_rol
                            WHERE ur.id_usuario = ?
                            AND ur.fec_delete IS NULL
                            AND r.fec_delete IS NULL
                            LIMIT 1
                        ");
                        $stmt->execute([$loginResult]);
                        $rolData = $stmt->fetch(PDO::FETCH_OBJ);
                        $rol = $rolData->nombre_rol ?? null;
                        $id_rol = $rolData->id_rol ?? null;

                        // Obtener el nombre completo del usuario
                        $stmtName = $userClass->db->prepare("SELECT nombre FROM tab_usuarios WHERE id_usuario = ? LIMIT 1");
                        $stmtName->execute([$loginResult]);
                        $nombre_completo = $stmtName->fetchColumn();

                        // Si la petición es AJAX, devolver JWT
                        if ($isAjax) {
                            $payload = [
                                'iss' => $jwt_issuer,
                                'aud' => $jwt_audience,
                                'iat' => time(),
                                'exp' => time() + 1200, // 20 minutos
                                'sub' => $loginResult,
                                'email' => $email,
                                'rol' => $rol,
                                'id_rol' => (int)$id_rol,
                                'id_usuario' => $loginResult,
                                'nombre' => $nombre_completo
                            ];
                            $jwt = JWT::encode($payload, $jwt_secret, 'HS256');
                            setcookie('jwt_token', $jwt, time() + 1200, '/', '', false, true);
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'token' => $jwt, 'rol' => $rol, 'nombre' => $nombre_completo]);
                            exit();
                        }

                        if ($rol === 'admin') {
                            header("Location: /buslinnes/templates/buslinnes_interface.html");
                            exit();
                        } elseif ($rol === 'conductor') {
                            header("Location: /buslinnes/templates/driver_interface.html");
                            exit();
                        } elseif ($rol === 'pasajero') {
                            header("Location: /buslinnes/templates/passenger_interface.html");
                            exit();
                        }
                    } else {
                        $errorMsgLogin = "La contraseña ingresada es incorrecta. Por favor, intente nuevamente.";
                        if ($isAjax) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'error' => $errorMsgLogin]);
                            exit();
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Error en login: " . $e->getMessage());
                $errorMsgLogin = "Error en el sistema. Por favor, intente más tarde.";
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $errorMsgLogin]);
                    exit();
                }
            }
        }
    }
}

/* Procesamiento de formulario de registro */
if (!empty($_POST['signupSubmit'])) {
    $email = trim($_POST['emailReg'] ?? '');
    $password = $_POST['passwordReg'];
    $name = trim($_POST['nameReg'] ?? '');
    $nameRegValue = $name;
    $emailRegValue = $email;
    $termsChecked = !empty($_POST['terms']) ? 'checked' : '';
    
    /* Validación con expresiones regulares */
    $name_check = preg_match('~^[A-Za-z0-9ñÑ ]{3,40}$~u', $name);
    $email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);
    $password_check = preg_match('~^[A-Za-z0-9!@#$%^&*()_]{6,20}$~i', $password);

    // Validar cada campo y mostrar error específico
    if (!$name_check) {
        $errorMsgReg = "El nombre de usuario debe tener entre 3 y 40 caracteres (solo letras, números y espacios).";
        $activeTab = 'register';
    } elseif (!$email_check) {
        $errorMsgReg = "El correo electrónico no tiene un formato válido.";
        $activeTab = 'register';
    } elseif (!$password_check) {
        $errorMsgReg = "La contraseña debe tener entre 6 y 20 caracteres (letras, números y símbolos !@#$%^&*()_).";
        $activeTab = 'register';
    } else {
        // Llamada al método adaptado para PostgreSQL
        $registrationSuccess = $userClass->fun_insert_usuario($password, $email, $name);

        if ($registrationSuccess) {
            // Asignar rol pasajero automáticamente
            try {
                // Obtener el id_usuario recién registrado
                $stmt = $userClass->db->prepare("SELECT id_usuario FROM tab_usuarios WHERE correo = ? LIMIT 1");
                $stmt->execute([$email]);
                $id_usuario = $stmt->fetchColumn();

                // Obtener el id_rol de pasajero
                $stmt2 = $userClass->db->prepare("SELECT id_rol FROM tab_roles WHERE nombre_rol = 'pasajero' AND fec_delete IS NULL LIMIT 1");
                $stmt2->execute();
                $id_rol = $stmt2->fetchColumn();

                // Insertar en tab_usuarios_roles
                if ($id_usuario && $id_rol) {
                    $stmt3 = $userClass->db->prepare("INSERT INTO tab_usuarios_roles (id_usuario, id_rol) VALUES (?, ?)");
                    $stmt3->execute([$id_usuario, $id_rol]);
                }
            } catch (Exception $e) {
                error_log("Error asignando rol pasajero: " . $e->getMessage());
            }
            header("Location: /buslinnes/public/login.php?success=1");
            exit();
        } else {
            if ($userClass->lastErrorCode === 'EMAIL_EXISTS') {
                $errorMsgReg = "El correo electrónico ya está registrado en el sistema.";
            } elseif ($userClass->lastErrorCode === 'INVALID_INPUT') {
                $errorMsgReg = "Hay datos incompletos en el formulario.";
            } else {
                $errorMsgReg = "No fue posible registrar el usuario. Revisa los datos e inténtalo de nuevo.";
            }
            $activeTab = 'register';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buslinnes - Sistema de Gestión de Transporte</title>
    <!--Favicon-->
    <link rel="apple-touch-icon" sizes="180x180" href="/buslinnes/assets/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/buslinnes/assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/buslinnes/assets/img/favicon-16x16.png">
    <link rel="stylesheet" href="/buslinnes/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/buslinnes/assets/css/login.css">
    <script src="/buslinnes/assets/js/darkmode.js" defer></script>
</head>
<body>
    <main class="login-container">
        <a href="/buslinnes/templates/index.html" class="login-back-btn" aria-label="Volver al inicio" title="Volver al inicio">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
        </a>
        <header class="login-header">
            <h1>
                <a href="/buslinnes/index.php">
                    <img class="logo-light" src="/buslinnes/assets/img/logo.svg" alt="Buslinnes Logo">
                    <img class="logo-dark" src="/buslinnes/assets/img/logomorado.svg" alt="Buslinnes Logo (dark)">
                </a>
            </h1>
            <p>Sistema de Gestión de Transporte</p>
        </header>

        <div class="login-body">
            <div class="tabs" role="tablist" aria-label="Opciones de acceso">
                <button type="button" class="tab <?php echo $activeTab === 'login' ? 'active' : ''; ?>" data-tab="login" role="tab" aria-selected="<?php echo $activeTab === 'login' ? 'true' : 'false'; ?>" aria-controls="login-tab" id="tab-login">Iniciar Sesión</button>
                <button type="button" class="tab <?php echo $activeTab === 'register' ? 'active' : ''; ?>" data-tab="register" role="tab" aria-selected="<?php echo $activeTab === 'register' ? 'true' : 'false'; ?>" aria-controls="register-tab" id="tab-register">Registrarse</button>
            </div>

            <!-- Notificación de éxito del registro -->
            <div class="success-message <?php echo !empty($successMsg) ? 'show' : ''; ?>">
                <i class="fas fa-check-circle"></i> <?php echo $successMsg; ?>
            </div>

            <!-- Login Form -->
            <div id="login-tab" class="tab-content <?php echo $activeTab === 'login' ? 'active' : ''; ?>">
                <div class="form-title">
                    Ingresa con tu correo
                </div>

                <!-- Mensaje de error para login -->
                <div class="error-message <?php echo !empty($errorMsgLogin) ? 'show' : ''; ?>">
                    <?php echo $errorMsgLogin; ?>
                </div>

                <form action="" method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-input" placeholder="tu@correo.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                            <button type="button" class="toggle-password" data-target="password" title="Mostrar/Ocultar contraseña">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="forgot-password-container">
                        <a href="/buslinnes/templates/cambio_contraseña.html" class="forgot-password-link">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn btn-primary" name="loginSubmit" value="Acceso">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                    
                    <!-- Botón para inicio con Google -->
                    <button type="button" id="googleSignIn" class="btn btn-google-custom">
                        <i class="fab fa-google btn-google-icon"></i> Continuar con Google
                    </button>
                </form>
            </div>

            <!-- Register Form -->
            <div id="register-tab" class="tab-content <?php echo $activeTab === 'register' ? 'active' : ''; ?>">
                <!-- Mensaje de error para registro -->
                <div class="error-message <?php echo !empty($errorMsgReg) ? 'show' : ''; ?>">
                    <?php echo $errorMsgReg; ?>
                </div>

                <form action="" method="POST" id="registerForm">
                    <div class="form-group">
                        <label for="nameReg" class="form-label">Nombre usuario  </label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="nameReg" name="nameReg" class="form-input" placeholder="Juan Pérez" value="<?php echo htmlspecialchars($nameRegValue, ENT_QUOTES, 'UTF-8'); ?>" required aria-describedby="nameReg-description">
                        </div>
                        <p id="nameReg-description" class="form-text">El nombre de usuario debe tener entre 3 y 40 caracteres (solo letras, números y espacios).</p>
                    </div>

                    <div class="form-group">
                        <label for="emailReg" class="form-label">Correo Electrónico</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="emailReg" name="emailReg" class="form-input" placeholder="tu@correo.com" value="<?php echo htmlspecialchars($emailRegValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="passwordReg" class="form-label">Contraseña</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="passwordReg" name="passwordReg" class="form-input" placeholder="••••••••" required aria-describedby="passwordReg-description">
                            <button type="button" class="toggle-password" data-target="passwordReg" title="Mostrar/Ocultar contraseña">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p id="passwordReg-description" class="form-text">La contraseña debe tener entre 6 y 20 caracteres (letras, números y símbolos !@#$%^&*()_).</p>
                    </div>

                    <div class="form-group">
                        <label for="confirmReg" class="form-label">Confirmar Contraseña</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirmReg" name="confirmReg" class="form-input" placeholder="••••••••" required>
                            <button type="button" class="toggle-password" data-target="confirmReg" title="Mostrar/Ocultar contraseña">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="success-message" id="passwordMatchMessage">
                            <i class="fas fa-check-circle"></i> Las contraseñas coinciden
                        </div>
                    </div>

                    <div class="form-group terms-container">
                        <input type="checkbox" id="terms" name="terms" <?php echo $termsChecked; ?> required>
                        <label for="terms">Acepto los <a href="/buslinnes/templates/terms.html" class="terms-link">Términos y condiciones</a> y <a href="/buslinnes/templates/privacy.html" class="terms-link">Política de Privacidad</a></label>
                    </div>

                    <button type="submit" class="btn btn-primary" name="signupSubmit" value="Enviar">
                        <i class="fas fa-user-plus"></i> Registrarse
                    </button>
                </form>
            </div>
        </div>

        <footer class="login-footer">
            <p>¿Tienes una cuenta? <a href="#" class="switch-tab" data-tab="login">Inicia Sesión</a></p>
            <p>o <a href="/buslinnes/templates/guest_interface.html">Entrar como invitado</a></p>
        </footer>
    </main>

    <script>
        // Tabs functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs and contents
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                tab.classList.add('active');
                document.getElementById(`${tab.dataset.tab}-tab`).classList.add('active');
            });
        });

        // Switch tab from footer link
        document.querySelectorAll('.switch-tab').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = link.dataset.tab;
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                document.querySelector(`.tab[data-tab="${tabName}"]`).classList.add('active');
                document.getElementById(`${tabName}-tab`).classList.add('active');
            });
        });

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = button.getAttribute('data-target');
                const inputField = document.getElementById(targetId);
                const icon = button.querySelector('i');
                
                if (inputField.type === 'password') {
                    inputField.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    inputField.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Check if passwords match in real-time
        const passwordReg = document.getElementById('passwordReg');
        const confirmReg = document.getElementById('confirmReg');
        const matchMessage = document.getElementById('passwordMatchMessage');

        function checkPasswordMatch() {
            if (passwordReg && confirmReg && matchMessage) {
                const password = passwordReg.value;
                const confirm = confirmReg.value;
                
                // Only show message if both fields have values and they match
                if (password && confirm && password === confirm) {
                    matchMessage.classList.add('show');
                } else {
                    matchMessage.classList.remove('show');
                }
            }
        }

        // Add event listeners to both password fields
        if (passwordReg) {
            passwordReg.addEventListener('input', checkPasswordMatch);
        }
        if (confirmReg) {
            confirmReg.addEventListener('input', checkPasswordMatch);
        }

        // Form validation for registration
        const registerForm = document.querySelector('#registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => {
                const password = document.getElementById('passwordReg').value;
                const confirm = document.getElementById('confirmReg').value;
                const terms = document.getElementById('terms').checked;
                
                if (password !== confirm) {
                    e.preventDefault();
                    showError('Las contraseñas no coinciden', 'register');
                    return;
                }
                
                if (!terms) {
                    e.preventDefault();
                    showError('Debes aceptar los términos y condiciones', 'register');
                    return;
                }
            });
        }
        
        // Form validation for login
        const loginForm = document.querySelector('#loginForm');
        if (loginForm) {
                loginForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const email = document.getElementById('email').value;
                    const password = document.getElementById('password').value;
                    if (!email || !password) {
                        showError('Por favor completa todos los campos', 'login');
                        return;
                    }
                    // Enviar login por fetch
                    try {
                        const formData = new FormData();
                        formData.append('email', email);
                        formData.append('password', password);
                        formData.append('loginSubmit', 'Acceso');
                        const res = await fetch('login.php', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });
                        
                        if (!res.ok) {
                            showError('Error en el servidor (HTTP ' + res.status + ')', 'login');
                            return;
                        }

                        let data;
                        try {
                            data = await res.json();
                        } catch (parseError) {
                            console.error('Error al parsear JSON:', parseError);
                            showError('Error en la respuesta del servidor', 'login');
                            return;
                        }

                        if (data.success && data.token) {
                            localStorage.setItem('jwt_token', data.token);
                            // También establecer cookie para que las páginas PHP puedan validar
                            // La cookie expira en 15 minutos (900 segundos)
                            const expirationDate = new Date();
                            expirationDate.setTime(expirationDate.getTime() + (900 * 1000));
                            document.cookie = `jwt_token=${data.token}; expires=${expirationDate.toUTCString()}; path=/`;
                            // Redirigir según el rol
                            if (data.rol === 'admin') {
                                window.location.href = '/buslinnes/templates/buslinnes_interface.html';
                            } else if (data.rol === 'conductor') {
                                window.location.href = '/buslinnes/templates/driver_interface.html';
                            } else if (data.rol === 'pasajero') {
                                window.location.href = '/buslinnes/templates/passenger_interface.html';
                            } else {
                                showError('Rol no reconocido', 'login');
                            }
                        } else if (data.error) {
                            showError(data.error, 'login');
                        } else {
                            showError('Error desconocido en el servidor', 'login');
                        }
                    } catch (err) {
                        console.error('Error en fetch:', err);
                        showError('Error de conexión. Verifique su conexión a internet', 'login');
                    }
                });
        }

        // Handler para el botón de Google (redirecciona a un endpoint que inicia OAuth)
        const googleBtn = document.getElementById('googleSignIn');
        if (googleBtn) {
            googleBtn.addEventListener('click', function(){
                // Cambia esta URL al endpoint que implementes para OAuth con Google
                window.location.href = '/buslinnes/src/auth/google.php';
            });
        }
        
        // Function to show error message
        function showError(message, formType) {
            const errorDiv = document.querySelector(`#${formType}-tab .error-message`);
            errorDiv.textContent = message;
            errorDiv.classList.add('show');
            
            // Hide error after 5 seconds
            setTimeout(() => {
                errorDiv.classList.remove('show');
            }, 5000);
        }

        // Function to hide success message after delay
        function autoHideSuccessMessage() {
            const successDiv = document.querySelector('.success-message');
            if (successDiv && successDiv.classList.contains('show')) {
                setTimeout(() => {
                    successDiv.classList.remove('show');
                }, 5000);
            }
        }

        // Auto-hide success message on page load
        document.addEventListener('DOMContentLoaded', autoHideSuccessMessage);
    </script>
</body>
<!-- Modal para cerrar sesión si hay token -->
<div id="logoutModal" class="modal-overlay">
    <div class="modal-content">
        <h2 class="modal-title">¿Deseas cerrar la sesión?</h2>
        <p class="modal-text">Tu sesión sigue activa. ¿Quieres cerrarla?</p>
        <button id="btnLogoutYes" class="btn-modal btn-modal-yes">Sí</button>
        <button id="btnLogoutNo" class="btn-modal btn-modal-no">No</button>
    </div>
</div>
<script>
function parseJwt(token) {
    try {
        const base64Url = token.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
        return JSON.parse(jsonPayload);
    } catch (e) { return null; }
}
function checkAndShowLogoutModal() {
    const token = localStorage.getItem('jwt_token');
    if (token) {
        const payload = parseJwt(token);
        if (payload && payload.exp && Date.now() / 1000 < payload.exp) {
            document.getElementById('logoutModal').style.display = 'flex';
            document.getElementById('btnLogoutYes').onclick = function() {
                localStorage.removeItem('jwt_token');
                // Forzar recarga para limpiar cualquier estado en memoria
                window.location.replace('/buslinnes/public/logout.php');
                setTimeout(function(){ window.location.reload(true); }, 500);
            };
            document.getElementById('btnLogoutNo').onclick = function() {
                let role = '';
                if (payload.rol) {
                    role = payload.rol.toLowerCase();
                }
                if (role === 'admin' || role === 'administrador') {
                    window.location.replace('/buslinnes/templates/buslinnes_interface.html');
                } else if (role === 'conductor') {
                    window.location.replace('/buslinnes/templates/driver_interface.html');
                } else if (role === 'pasajero') {
                    window.location.replace('/buslinnes/templates/passenger_interface.html');
                } else {
                    window.location.reload();
                }
            };
        }
    }
}
document.addEventListener('DOMContentLoaded', checkAndShowLogoutModal);
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        checkAndShowLogoutModal();
    }
});
</script>
</html>

