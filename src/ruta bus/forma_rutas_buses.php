<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
Formulario para agregar nuevas rutas-buses
@oto
*/
include_once "../base_de_datos.php";
?>

<?php
// Obtener IDs y nombres de rutas
$rutas = [];
try {
    $sentenciaRutas = $base_de_datos->query('SELECT id_ruta, nom_ruta FROM tab_rutas ORDER BY nom_ruta');
    $rutas = $sentenciaRutas->fetchAll(PDO::FETCH_OBJ);
} catch(Exception $e) {}
// Obtener IDs y placas de buses
$buses = [];
try {
    $sentenciaBuses = $base_de_datos->query('SELECT id_bus, matricula FROM tab_buses ORDER BY id_bus');
    $buses = $sentenciaBuses->fetchAll(PDO::FETCH_OBJ);
} catch(Exception $e) {}
// Obtener IDs y nombres de empresas
$empresas = [];
try {
    $sentenciaEmpresas = $base_de_datos->query('SELECT id_empresa, nom_empresa FROM tab_empresas ORDER BY nom_empresa');
    $empresas = $sentenciaEmpresas->fetchAll(PDO::FETCH_OBJ);
} catch(Exception $e) {}
?>
<?php include_once "encabezado_rutas_buses.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Agregar Nueva Ruta-Bus</h1>
                </div>
                <div class="card-body">
                    <form action="insertar_rutas_buses.php" method="POST">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="id_ruta_bus" class="form-label">ID Ruta Bus</label>
                                    <input required name="id_ruta_bus" type="number" id="id_ruta_bus" class="form-control" placeholder="ID de la ruta-bus" min="1" onkeydown="return event.key !== 'e' && event.key !== 'E'">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="id_ruta" class="form-label">ID Ruta</label>
                                    <select name="id_ruta" id="id_ruta" class="form-select" required>
                                        <option value="" disabled selected>Seleccione ruta</option>
                                        <?php if (empty($rutas)): ?>
                                            <option value="" disabled>No hay rutas disponibles</option>
                                        <?php else: ?>
                                            <?php foreach($rutas as $r): ?>
                                                <option value="<?php echo $r->id_ruta; ?>"><?php echo $r->id_ruta . ' - ' . $r->nom_ruta; ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">ID Bus</label>
                                    <select name="id_bus" id="id_bus" class="form-select" required>
                                        <option value="" disabled selected>Seleccione bus</option>
                                        <?php if (empty($buses)): ?>
                                            <option value="" disabled>No hay buses disponibles</option>
                                        <?php else: ?>
                                            <?php foreach($buses as $b): ?>
                                                <option value="<?php echo $b->id_bus; ?>"><?php echo $b->id_bus . ' - ' . $b->matricula; ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="./listar_rutas_buses.php" class="btn btn-warning">
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



