<?php
header('Content-Type: text/html; charset=utf-8');

// === SEGURIDAD: Proteccion anti-scraping y CSRF ===
require_once __DIR__ . '/../../app/SecurityMiddleware.php';

SecurityMiddleware::protect([
    'csrf' => false,  // GET no requiere CSRF
    'rateLimit' => true,
    'origin' => true,
    'userAgent' => true,
    'securityHeaders' => true
]);
// === FIN SEGURIDAD ===

/*
CRUD con PostgreSQL y PHP
autor: alexndrcastt
=================================================================
Formulario para editar usuarios existentes
=================================================================
*/

define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']);

if (!isset($_GET["id_usuario"])) {
    exit();
}

$id_usuario_txt = trim((string) $_GET["id_usuario"]);
if (!preg_match('/^[0-9]+$/', $id_usuario_txt) || (int) $id_usuario_txt <= 0) {
    echo "ID de usuario invalido";
    exit();
}
$id_usuario = (int) $id_usuario_txt;
include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT id_usuario, nom_usuario AS nombre, email_usuario AS correo, contrasena FROM tab_usuarios WHERE id_usuario = ?");
$sentencia->execute([$id_usuario]);
$usuario = $sentencia->fetch(PDO::FETCH_OBJ);

if ($usuario === false) {
    echo "¡No existe algún usuario con ese ID!";
    exit();
}

include_once "encab_usuarios.php";
?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Usuario</h1>
                </div>
                <div class="card-body">
                    <form action="update_usuarios.php" method="POST">
                        <input type="hidden" name="id_usuario" value="<?php echo (int) $usuario->id_usuario; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nombre" class="form-label">Nombre Completo</label>
                                    <input value="<?php echo htmlspecialchars($usuario->nombre, ENT_QUOTES, 'UTF-8'); ?>" required name="nombre" type="text" id="nombre" class="form-control" minlength="3" maxlength="120" placeholder="Nombre del usuario">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="correo" class="form-label">Correo Electrónico</label>
                                    <input value="<?php echo htmlspecialchars($usuario->correo, ENT_QUOTES, 'UTF-8'); ?>" required name="correo" type="email" id="correo" class="form-control" maxlength="120" placeholder="correo@ejemplo.com">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="contrasena" class="form-label">Contraseña (Dejar igual si no se cambia)</label>
                                    <input value="<?php echo htmlspecialchars($usuario->contrasena, ENT_QUOTES, 'UTF-8'); ?>" required name="contrasena" type="text" id="contrasena" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8" maxlength="72">
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="listar_usuarios.php" class="btn btn-warning">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once "../pie.php" ?>



