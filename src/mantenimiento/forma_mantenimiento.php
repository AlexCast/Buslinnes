<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
@yerson
@2025
=================================================================
Formulario para agregar nuevos mantenimientos al sistema
=================================================================
*/
?>

<?php include_once "encab_mantenimiento.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Registro de Mantenimientos</h1>
                </div>
                <div class="card-body">
                    <form action="insertar_mantenimiento.php" method="POST">
                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">

                                <div class="form-group mb-3">
                                    <label for="id_mantenimiento" class="form-label">ID Mantenimiento</label>
                                    <input type="number" name="id_mantenimiento" id="id_mantenimiento" class="form-control" 
                                           required min="1" max="2147483647" step="1" title="Solo números, mínimo 1 dígito"
                                           placeholder="Ingrese el ID" onkeydown="return event.key !== 'e' && event.key !== 'E'">
                                </div>

                                <?php
                                // Obtener los buses existentes
                                include_once '../base_de_datos.php';
                                $buses = $base_de_datos->query('SELECT id_bus FROM tab_buses WHERE fec_delete IS NULL ORDER BY id_bus')->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">ID Bus</label>
                                    <select name="id_bus" id="id_bus" class="form-control" required>
                                        <option value="" disabled selected>Seleccione un bus</option>
                                        <?php foreach($buses as $bus): ?>
                                            <option value="<?php echo htmlspecialchars($bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <input type="text" name="descripcion" id="descripcion" class="form-control"
                                           required minlength="10" maxlength="500"
                                           placeholder="Descripción del mantenimiento">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="fecha_mantenimiento" class="form-label">Fecha del Mantenimiento</label>
                                    <input type="datetime-local" name="fecha_mantenimiento" id="fecha_mantenimiento" class="form-control"
                                           required max="<?php echo date('Y-m-d\TH:i'); ?>" value="<?php echo date('Y-m-d\TH:i'); ?>">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="costo" class="form-label">Costo del Mantenimiento</label>
                                     <input type="number" name="costo_mantenimiento" id="costo" class="form-control"
                                         required step="1" min="0" max="9999999999"
                                           placeholder="Costo del mantenimiento">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="javascript:history.back()" class="btn btn-warning">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-plus me-1"></i> Registrar Mantenimiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once "../pie.php"; ?>




