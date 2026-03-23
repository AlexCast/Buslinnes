<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
@yerson
@2025
=================================================================
Formulario para agregar nuevos pasajeros al sistema
=================================================================
*/
?>

<?php include_once "encab_pasajeros.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Registro de Pasajeros</h1>
                </div>
                <div class="card-body">
                    <form action="insertar_pasajeros.php" method="POST">
                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_pasajero" class="form-label">ID Pasajero</label>
<input type="number" name="id_pasajero" id="id_pasajero" class="form-control" 
       required pattern="\d{10}" title="10 dígitos numéricos"
       placeholder="Ingrese el ID de 10 dígitos" min="1000000000" max="9999999999" onkeydown="return event.key !== 'e' && event.key !== 'E'">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="nom_pasajero" class="form-label">Nombre</label>
<input type="text" name="nom_pasajero" id="nom_pasajero" class="form-control"
       required minlength="3" maxlength="50"
       placeholder="Nombre completo del pasajero" >
                                </div>
                            </div>
                            
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="ape_pasajero" class="form-label">Apellido</label>
<input type="text" name="ape_pasajero" id="ape_pasajero" class="form-control"
       required minlength="3" maxlength="50"
       placeholder="Apellido del pasajero" >
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tel_pasajero" class="form-label">Teléfono</label>
<input type="number" name="tel_pasajero" id="tel_pasajero" class="form-control"
       required pattern="\d{10}" title="10 dígitos numéricos"
       placeholder="Número de teléfono" min="3000000000" ma="3999999999">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="javascript:history.back()" class="btn btn-warning">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-plus me-1"></i> Registrar Pasajero
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once "../pie.php"; ?>


