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
Formulario para agregar nuevos usuarios al sistema
=================================================================
*/
?>

<?php
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']);

include_once "../base_de_datos.php";
include_once "encab_usuarios.php";
?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Agregar Nuevo Usuario</h1>
                </div>
                <div class="card-body">
                    <form action="insertar_usuarios.php" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tipo_doc" class="form-label">Tipo de Documento</label>
                                    <select required name="tipo_doc" id="tipo_doc" class="form-select">
                                        <option value="CD" selected>Cédula de ciudadanía (CD)</option>
                                        <option value="TI">Tarjeta de identidad (TI)</option>
                                        <option value="CE">Cédula de extranjería (CE)</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="id_usuario" class="form-label">Número de Documento / ID Usuario</label>
                                    <input required name="id_usuario" type="text" id="id_usuario" class="form-control" pattern="[0-9]+" minlength="6" maxlength="10" inputmode="numeric" placeholder="Ej: 1001234567">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="nombre" class="form-label">Nombre Completo</label>
                                    <input required name="nombre" type="text" id="nombre" class="form-control" minlength="3" maxlength="120" placeholder="Nombre del usuario">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="correo" class="form-label">Correo Electrónico</label>
                                    <input required name="correo" type="email" id="correo" class="form-control" maxlength="120" placeholder="correo@ejemplo.com">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="contrasena" class="form-label">Contraseña</label>
                                    <input required name="contrasena" type="password" id="contrasena" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8" maxlength="72">
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar
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



