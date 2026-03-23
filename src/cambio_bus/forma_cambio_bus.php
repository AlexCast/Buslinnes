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
                                    <label for="id_cambio" class="form-label">ID Cambio</label>
                                    <input required name="id_cambio" type="number" id="id_cambio" class="form-control"
                                           placeholder="Identificador único del cambio" min="1" max="999999" step="1">
                                </div>

                                <?php
                                // Buses disponibles
                                $sentencia = $base_de_datos->query("SELECT id_bus, matricula FROM tab_buses WHERE fec_delete IS NULL ORDER BY matricula");
                                $buses = $sentencia->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label for="id_bus_salida" class="form-label">Bus que sale</label>
                                    <select name="id_bus_salida" id="id_bus_salida" class="form-select" required>
                                        <option value="" disabled selected>Seleccione bus de salida</option>
                                        <?php foreach($buses as $bus): ?>
                                            <option value="<?php echo $bus->id_bus ?>">
                                                <?php echo $bus->id_bus . ' - ' . $bus->matricula ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="id_bus_entrada" class="form-label">Bus que entra</label>
                                    <select name="id_bus_entrada" id="id_bus_entrada" class="form-select" required>
                                        <option value="" disabled selected>Seleccione bus de reemplazo</option>
                                        <?php foreach($buses as $bus): ?>
                                            <option value="<?php echo $bus->id_bus ?>">
                                                <?php echo $bus->id_bus . ' - ' . $bus->matricula ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="fecha_cambio" class="form-label">Fecha del Cambio</label>
                                    <input required name="fecha_cambio" type="date" id="fecha_cambio" class="form-control">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="motivo" class="form-label">Motivo</label>
                                    <textarea required name="motivo" id="motivo" class="form-control" rows="3"
                                              placeholder="Describa el motivo del cambio"></textarea>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="usuario" class="form-label">Usuario que registra</label>
                                    <input required name="usuario" type="text" id="usuario" class="form-control"
                                           placeholder="Usuario que realiza el registro" maxlength="50">
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



