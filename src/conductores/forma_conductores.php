<?php
header('Content-Type: text/html; charset=utf-8');
$fechaMinimaLicencia = date('Y-m-d', strtotime('+1 day'));

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
                                    <label for="id_usuario" class="form-label">Cédula Conductor</label>
                                    <input type="number" name="id_usuario" id="id_usuario" class="form-control" 
                                         required pattern="\d{10}" title="10 dígitos numéricos"
                                           placeholder="Ingrese la cédula de 10 dígitos"
                                           min="1000000000" max="9999999999" onkeydown="return event.key !== 'e' && event.key !== 'E'">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="nom_conductor" class="form-label">Nombre</label>
                                    <input type="text" name="nom_conductor" id="nom_conductor" class="form-control"
                                         required minlength="3" maxlength="60"
                                           placeholder="Nombre completo del conductor">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="ape_conductor" class="form-label">Apellido</label>
                                    <input type="text" name="ape_conductor" id="ape_conductor" class="form-control"
                                         required minlength="3" maxlength="60"
                                           placeholder="Apellido del conductor">
                                </div>
                            </div>
                            
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tel_conductor" class="form-label">Teléfono</label>
                                    <input type="number" name="tel_conductor" id="tel_conductor" class="form-control"
                                           required pattern="\d{10}" title="10 dígitos numéricos"
                                         placeholder="Número de teléfono" minlength="10" maxlength="10"
                                         min="3000000000" max="3999999999" onkeydown="return event.key !== 'e' && event.key !== 'E'">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="licencia_conductor" class="form-label">Licencia</label>
                                    <input type="text" name="licencia_conductor" id="licencia_conductor" 
                                           class="form-control" required minlength="7" maxlength="10" pattern="[0-9]{7,10}" inputmode="numeric"
                                           placeholder="Número de licencia">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="email_conductor" class="form-label">Email</label>
                                    <input type="email" name="email_conductor" id="email_conductor" class="form-control" 
                                         required maxlength="120" placeholder="correo@ejemplo.com">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tipo_licencia" class="form-label">Tipo de licencia</label>
                                    <select name="tipo_licencia" id="tipo_licencia" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                        <option value="C2">C2 (camiones/buses)</option>
                                        <option value="C3">C3 (pesados/articulados)</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="fec_venc_licencia" class="form-label">Vencimiento licencia</label>
                                    <input type="date" name="fec_venc_licencia" id="fec_venc_licencia" class="form-control" 
                                           min="<?php echo $fechaMinimaLicencia; ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="estado_conductor" class="form-label">Estado</label>
                                    <select name="estado_conductor" id="estado_conductor" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                        <option value="A">A (Activo)</option>
                                        <option value="S">S (Suspendido)</option>
                                        <option value="R">R (Retirado)</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="edad" class="form-label">Edad</label>
                                    <input type="number" name="edad" id="edad" class="form-control" required min="18" max="100"
                                           placeholder="Edad">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tipo_sangre" class="form-label">Tipo de sangre</label>
                                    <select name="tipo_sangre" id="tipo_sangre" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
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



