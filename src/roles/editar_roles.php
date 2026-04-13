<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
autor: alexndrcastt
=================================================================
Formulario para editar roles existentes
=================================================================
*/

if (!isset($_GET["id_rol"])) {
    exit();
}

$id_rol_txt = trim((string) $_GET["id_rol"]);
if (!preg_match('/^[0-9]+$/', $id_rol_txt) || (int) $id_rol_txt <= 0) {
    echo "ID de rol invalido";
    exit();
}
$id_rol = (int) $id_rol_txt;
include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT id_rol, nombre_rol FROM tab_roles WHERE id_rol = ?");
$sentencia->execute([$id_rol]);
$rol = $sentencia->fetch(PDO::FETCH_OBJ);

if ($rol === false) {
    echo "¡No existe algún rol con ese ID!";
    exit();
}

include_once "encab_roles.php";
?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Rol</h1>
                </div>
                <div class="card-body">
                    <form action="update_roles.php" method="POST">
                        <input type="hidden" name="id_rol" value="<?php echo (int) $rol->id_rol; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nombre_rol" class="form-label">Nombre del Rol</label>
                                    <input value="<?php echo htmlspecialchars($rol->nombre_rol, ENT_QUOTES, 'UTF-8'); ?>" required name="nombre_rol" type="text" id="nombre_rol" class="form-control" minlength="3" maxlength="40" pattern="[A-Za-z0-9_\-\s]+" placeholder="Nombre del rol">
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="listar_roles.php" class="btn btn-warning">
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



