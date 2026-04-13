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
Formulario para asignar roles a usuarios
=================================================================
*/
?>

<?php
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']);

include_once "../base_de_datos.php";
include_once "encab_usuarios_roles.php";
?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Asignar Rol a Usuario</h1>
                </div>
                <div class="card-body">
                    <form action="insertar_usuarios_roles.php" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <?php
                                $sentencia = $base_de_datos->query("SELECT id_usuario, nom_usuario AS nombre FROM tab_usuarios WHERE fec_delete IS NULL ORDER BY nom_usuario");
                                $usuarios = $sentencia->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label for="id_usuario" class="form-label">Usuario</label>
                                    <select name="id_usuario" id="id_usuario" class="form-select" required>
                                        <option value="" disabled selected>Seleccione usuario</option>
                                        <?php foreach($usuarios as $usuario): ?>
                                            <option value="<?php echo (int) $usuario->id_usuario ?>"><?php echo htmlspecialchars($usuario->nombre, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <?php
                                $sentencia = $base_de_datos->query("SELECT id_rol, nombre_rol FROM tab_roles WHERE fec_delete IS NULL ORDER BY nombre_rol");
                                $roles = $sentencia->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label for="id_rol" class="form-label">Rol</label>
                                    <select name="id_rol" id="id_rol" class="form-select" required>
                                        <option value="" disabled selected>Seleccione rol</option>
                                        <?php foreach($roles as $rol): ?>
                                            <option value="<?php echo (int) $rol->id_rol ?>"><?php echo htmlspecialchars($rol->nombre_rol, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="listar_usuarios_roles.php" class="btn btn-warning">
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



