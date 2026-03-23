<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
autor: alexndrcastt
=================================================================
Formulario para agregar nuevos usuarios al sistema
=================================================================
*/
?>

<?php
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
                                    <label for="nombre" class="form-label">Nombre Completo</label>
                                    <input required name="nombre" type="text" id="nombre" class="form-control" placeholder="Nombre del usuario">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="correo" class="form-label">Correo Electrónico</label>
                                    <input required name="correo" type="email" id="correo" class="form-control" placeholder="correo@ejemplo.com">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="contrasena" class="form-label">Contraseña</label>
                                    <input required name="contrasena" type="password" id="contrasena" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8">
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



