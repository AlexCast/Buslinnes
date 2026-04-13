<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
autor: alexndrcastt
=================================================================
Formulario para agregar nuevos roles al sistema
=================================================================
*/
?>

<?php
include_once "../base_de_datos.php";
include_once "encab_roles.php";
?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Agregar Nuevo Rol</h1>
                </div>
                <div class="card-body">
                    <form action="insertar_roles.php" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nombre_rol" class="form-label">Nombre del Rol</label>
                                    <input required name="nombre_rol" type="text" id="nombre_rol" class="form-control" minlength="3" maxlength="40" pattern="[A-Za-z0-9_\-\s]+" placeholder="Nombre del rol">
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar
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



