<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
autor: alexndrcastt
=================================================================
Formulario para editar usuarios existentes
=================================================================
*/

if (!isset($_GET["id_usuario"])) {
    exit();
}

$id_usuario = $_GET["id_usuario"];
include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT id_usuario, nombre, correo, contrasena FROM tab_usuarios WHERE id_usuario = ?");
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
                        <input type="hidden" name="id_usuario" value="<?php echo $usuario->id_usuario; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nombre" class="form-label">Nombre Completo</label>
                                    <input value="<?php echo $usuario->nombre; ?>" required name="nombre" type="text" id="nombre" class="form-control" placeholder="Nombre del usuario">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="correo" class="form-label">Correo Electrónico</label>
                                    <input value="<?php echo $usuario->correo; ?>" required name="correo" type="email" id="correo" class="form-control" placeholder="correo@ejemplo.com">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="contrasena" class="form-label">Contraseña (Dejar igual si no se cambia)</label>
                                    <input value="<?php echo $usuario->contrasena; ?>" required name="contrasena" type="text" id="contrasena" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8">
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



