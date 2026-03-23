<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
autor: alexndrcastt
=================================================================
Formulario para asignar roles a usuarios
=================================================================
*/
?>

<?php
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
                                $sentencia = $base_de_datos->query("SELECT id_usuario, nombre FROM tab_usuarios WHERE fec_delete IS NULL ORDER BY nombre");
                                $usuarios = $sentencia->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label for="id_usuario" class="form-label">Usuario</label>
                                    <select name="id_usuario" id="id_usuario" class="form-select" required>
                                        <option value="" disabled selected>Seleccione usuario</option>
                                        <?php foreach($usuarios as $usuario): ?>
                                            <option value="<?php echo $usuario->id_usuario ?>"><?php echo $usuario->nombre ?></option>
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
                                            <option value="<?php echo $rol->id_rol ?>"><?php echo $rol->nombre_rol ?></option>
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



