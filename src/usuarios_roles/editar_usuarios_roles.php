<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
autor: alexndrcastt
=================================================================
Formulario para editar asignación de roles
=================================================================
*/

if (!isset($_GET["id_usuario"]) || !isset($_GET["id_rol"])) {
    exit();
}

$id_usuario = $_GET["id_usuario"];
$id_rol = $_GET["id_rol"];
include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT id_usuario, id_rol FROM tab_usuarios_roles WHERE id_usuario = ? AND id_rol = ?");
$sentencia->execute([$id_usuario, $id_rol]);
$usuario_rol = $sentencia->fetch(PDO::FETCH_OBJ);

if ($usuario_rol === false) {
    echo "¡No existe esa asignación!";
    exit();
}

include_once "encab_usuarios_roles.php";
?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Asignación</h1>
                </div>
                <div class="card-body">
                    <form action="update_usuarios_roles.php" method="POST">
                        <input type="hidden" name="id_usuario" value="<?php echo $usuario_rol->id_usuario; ?>">
                        <input type="hidden" name="id_rol_old" value="<?php echo $usuario_rol->id_rol; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <?php
                                $sentencia = $base_de_datos->prepare("SELECT nombre FROM tab_usuarios WHERE id_usuario = ?");
                                $sentencia->execute([$id_usuario]);
                                $usuario = $sentencia->fetch(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label class="form-label">Usuario</label>
                                    <input type="text" class="form-control" value="<?php echo $usuario->nombre; ?>" disabled>
                                </div>

                                <?php
                                $sentencia = $base_de_datos->query("SELECT id_rol, nombre_rol FROM tab_roles WHERE fec_delete IS NULL ORDER BY nombre_rol");
                                $roles = $sentencia->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label for="id_rol_new" class="form-label">Rol</label>
                                    <select name="id_rol_new" id="id_rol_new" class="form-select" required>
                                        <?php foreach($roles as $rol): ?>
                                            <option value="<?php echo $rol->id_rol ?>" <?php if($rol->id_rol == $usuario_rol->id_rol) echo "selected" ?>>
                                                <?php echo $rol->nombre_rol ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar Cambios
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



