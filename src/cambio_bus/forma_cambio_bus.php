<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
@Carlos Eduardo Perez Rueda
2023

Adaptado por
@alexndrcastt
2025
=================================================================
Formulario para registrar cambio de bus
Tabla: tab_cambio_bus
=================================================================
*/
?>

<?php
include_once "../base_de_datos.php";
include_once "encab_cambio_bus.php";
?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Registrar Cambio de Bus</h1>
                </div>
                <div class="card-body">
                    <form action="insertar_cambio_bus.php" method="POST">
                        <div class="row">

                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_cambio_bus" class="form-label">ID Cambio</label>
                                    <input required name="id_cambio_bus" type="number" id="id_cambio_bus" class="form-control"
                                         placeholder="Identificador único del cambio" min="1" max="999999" step="1" onkeydown="return event.key !== 'e' && event.key !== 'E'">
                                </div>

                                <?php
                                // Buses disponibles
                                $sentencia = $base_de_datos->query("SELECT id_bus FROM tab_buses WHERE fec_delete IS NULL ORDER BY id_bus");
                                $buses = $sentencia->fetchAll(PDO::FETCH_OBJ);
                                $sentencia = $base_de_datos->query("SELECT id_incidente, desc_incidente FROM tab_incidentes WHERE fec_delete IS NULL ORDER BY id_incidente");
                                $incidentes = $sentencia->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label for="id_incidente" class="form-label">Incidente</label>
                                    <select name="id_incidente" id="id_incidente" class="form-select" required>
                                        <option value="" disabled selected>Seleccione incidente</option>
                                        <?php foreach($incidentes as $incidente): ?>
                                            <option value="<?php echo $incidente->id_incidente ?>">
                                                <?php echo '#' . $incidente->id_incidente . ' - ' . htmlspecialchars($incidente->desc_incidente) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">Bus</label>
                                    <select name="id_bus" id="id_bus" class="form-select" required>
                                        <option value="" disabled selected>Seleccione bus</option>
                                        <?php foreach($buses as $bus): ?>
                                            <option value="<?php echo $bus->id_bus ?>">
                                                <?php echo 'Bus #' . $bus->id_bus ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="ubicacion_cambio" class="form-label">Ubicación del Cambio</label>
                                    <input required name="ubicacion_cambio" type="text" id="ubicacion_cambio" class="form-control"
                                         placeholder="Ubicación donde se hará el cambio" minlength="3" maxlength="255">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="./listar_cambio_bus.php" class="btn btn-warning">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once "../pie.php"; ?>



