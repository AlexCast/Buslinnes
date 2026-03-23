<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
@alexndrcastt
@2025
=================================================================
Formulario para agregar nuevos conductores al sistema
=================================================================
*/
?>

<?php include_once "encab_conductores.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Registro de Conductores</h1>
                </div>
                <div class="card-body">
                    <form action="insertar_conductores.php" method="POST">
                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_conductor" class="form-label">cedula Conductor</label>
                                    <input type="number" name="id_conductor" id="id_conductor" class="form-control" 
                                           required pattern="\d{10}" " title="10 dígitos numéricos"
                                           placeholder="Ingrese la cedula de 10 dígitos"
                                           min="1000000000" max="9999999999" onkeydown="return event.key !== 'e' && event.key !== 'E'">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="nom_conductor" class="form-label">Nombre</label>
                                    <input type="text" name="nom_conductor" id="nom_conductor" class="form-control"
                                           required minlength="3" 
                                           placeholder="Nombre completo del conductor">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="ape_conductor" class="form-label">Apellido</label>
                                    <input type="text" name="ape_conductor" id="ape_conductor" class="form-control"
                                           required minlength="3"
                                           placeholder="Apellido del conductor">
                                </div>
                            </div>
                            
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tel_conductor" class="form-label">Teléfono</label>
                                    <input type="number" name="tel_conductor" id="tel_conductor" class="form-control"
                                           required pattern="\d{10}" title="10 dígitos numéricos"
                                           placeholder="Número de teléfono"
                                           min="3000000000" max="3999999999">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="licencia_conductor" class="form-label">Licencia</label>
                                    <input type="text" name="licencia_conductor" id="licencia_conductor" 
                                           class="form-control" required maxlength="6"
                                           placeholder="Número de licencia">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="javascript:history.back()" class="btn btn-warning">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-plus me-1"></i> Registrar Conductor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once "../pie.php"; ?>


