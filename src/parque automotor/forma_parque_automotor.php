<?php
header('Content-Type: text/html; charset=utf-8');

/*
Formulario para agregar nuevos parques automotores al sistema
@yerson
2025
=================================================================
*/
?>

<?php include_once "encab_parque_automotor.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Agregar Nuevo Parque Automotor</h1>
                </div>
                <div class="card-body">
                    <form action="insertar_parque_automotor.php" method="POST">
                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_parque_automotor" class="form-label">ID Parque Automotor</label>
                                    <input type="number" name="id_parque_automotor" id="id_parque_automotor" class="form-control" min="1" max="2147483647" step="1" inputmode="numeric" required placeholder="ID parque automotor" onkeydown="return event.key !== 'e' && event.key !== 'E'">
                                </div>
                                <?php
                                include_once "../base_de_datos.php";
                                $buses = $base_de_datos->query("SELECT id_bus FROM tab_buses WHERE fec_delete IS NULL ORDER BY id_bus")->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">ID Bus</label>
                                    <select name="id_bus" id="id_bus" class="form-select" required>
                                        <option value="" disabled selected>Seleccione un bus</option>
                                        <?php foreach($buses as $bus): ?>
                                            <option value="<?php echo htmlspecialchars($bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="dir_parque_automotor" class="form-label">Dirección del Parque</label>
                                    <input required name="dir_parque_automotor" type="text" id="dir_parque_automotor" class="form-control" placeholder="Dirección completa del parque" minlength="5" maxlength="255">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="./listar_parque_automotor.php" class="btn btn-warning">
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




