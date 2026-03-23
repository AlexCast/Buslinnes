<?php
// === Configurar encoding UTF-8 ===
header('Content-Type: text/html; charset=utf-8');
/*
CRUD con PostgreSQL y PHP
@Carlos Eduardo Perez Rueda
2023

Adaptado por
@alexndrcastt
2025
=================================================================
Formulario para agregar nuevos buses al sistema
=================================================================
*/

?>

<?php
include_once "../base_de_datos.php";
include_once "encab_buses.php";
?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Agregar Nuevo Bus</h1>
                </div>
                <div class="card-body">
                    <form action="insertar_buses.php" method="POST">
                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">ID Bus</label>
                                    <input required name="id_bus" type="number" id="id_bus" class="form-control" placeholder="Identificador único del bus" min="1" max="999999" step="1" onkeydown="return event.key !== 'e' && event.key !== 'E'">
                                </div>
                                <?php
                                $sentencia = $base_de_datos->query("SELECT id_conductor, nom_conductor, ape_conductor FROM tab_conductores WHERE fec_delete IS NULL ORDER BY nom_conductor");
                                $conductores = $sentencia->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label for="id_conductor" class="form-label">ID Conductor</label>
                                    <select name="id_conductor" id="id_conductor" class="form-select" required>
                                        <option value="" disabled selected>Seleccione conductor</option>
                                        <?php foreach($conductores as $conductor): ?>
                                            <option value="<?php echo $conductor->id_conductor ?>"><?php echo $conductor->id_conductor . ' - ' . $conductor->nom_conductor . ' ' . $conductor->ape_conductor ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php
                                $sentencia = $base_de_datos->query("SELECT num_chasis FROM tab_buses WHERE fec_delete IS NULL ORDER BY num_chasis");
                                $chasis = $sentencia->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label for="num_chasis" class="form-label">Número de Chasis</label>
                                    <input required name="num_chasis" type="text" id="num_chasis" class="form-control" placeholder="Número de chasis" list="chasis_list" maxlength="17">
                                    <datalist id="chasis_list">
                                        <?php foreach($chasis as $c): ?>
                                            <option value="<?php echo $c->num_chasis ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>

                            </div>
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="anio_fab" class="form-label">Año de Fabricación</label>
                                    <input required name="anio_fab" type="number" id="anio_fab" class="form-control" placeholder="Año de fabricación" min="1950" max="<?php echo date('Y'); ?>">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="capacidad_pasajeros" class="form-label">Capacidad de Pasajeros</label>
                                    <input required name="capacidad_pasajeros" type="number" id="capacidad_pasajeros" class="form-control" placeholder="Capacidad en pasajeros" min="1" max="60">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tipo_bus" class="form-label">Tipo de Bus</label>
                                    <select name="tipo_bus" id="tipo_bus" class="form-select" required>
                                        <option value="" disabled selected>Seleccione un tipo</option>
                                        <option value="U">Urbano</option>
                                        <option value="M">Municipal</option>
                                        <option value="A">Articulado</option>
                                        <option value="E">Especializado</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="gps" class="form-label">GPS Activo</label>
                                    <select name="gps" id="gps" class="form-select" required>
                                        <option value="" disabled selected>Seleccione una opción</option>
                                        <option value="true">Sí</option>
                                        <option value="false">No</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="ind_estado_buses" class="form-label">Estado del Bus</label>
                                    <select name="ind_estado_buses" id="ind_estado_buses" class="form-select" required>
                                        <option value="" disabled selected>Seleccione estado</option>
                                        <option value="L">Libre</option>
                                        <option value="F">Fuera de servicio</option>
                                        <option value="D">Dañado</option>
                                        <option value="S">Suspendido</option>
                                        <option value="T">Taller</option>
                                        <option value="A">Activo</option>
                                    </select>
                                </div>
                                <?php
                                $sentencia = $base_de_datos->query("SELECT matricula FROM tab_buses WHERE fec_delete IS NULL ORDER BY matricula");
                                $matriculas = $sentencia->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label for="matricula" class="form-label">Matrícula</label>
                                    <input required name="matricula" type="text" id="matricula" class="form-control" placeholder="Matrícula del bus" list="matricula_list" maxlength="6">
                                    <datalist id="matricula_list">
                                        <?php foreach($matriculas as $m): ?>
                                            <option value="<?php echo $m->matricula ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="./listar_buses.php" class="btn btn-warning">
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

