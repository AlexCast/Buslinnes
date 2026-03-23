<?php

header('Content-Type: text/html; charset=utf-8');
/*
CRUD con PostgreSQL y PHP
@Carlos Eduardo Perez Rueda
@Marzo de 2023
============================================================================================
Este archivo muestra un formulario que se envía a insertar.php, el cual guardará los datos
============================================================================================
*/

include_once "../base_de_datos.php";

// Verificar que los parámetros existan en la URL
if (!isset($_GET["id_ruta_bus"])) {
    echo "No existe la ruta a editar";
    exit();
}

$id_ruta_bus = $_GET["id_ruta_bus"];

// Seleccionar la ruta por ID
$sentencia = $base_de_datos->prepare("SELECT id_ruta_bus, id_ruta, id_bus FROM tab_ruta_bus WHERE id_ruta_bus = ?;");
$sentencia->execute([$id_ruta_bus]);
$ruta = $sentencia->fetchObject();
if (!$ruta) {
    echo "¡No existe la ruta con ese ID!";
    exit();
}

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
?>
<?php include_once "encabezado_rutas_buses.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Ruta-Bus</h1>
                </div>
                <div class="card-body">
                    <form action="update_rutas_buses.php" method="POST">
                        <input type="hidden" name="id_ruta_bus" value="<?php echo $ruta->id_ruta_bus; ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_ruta" class="form-label">ID Ruta</label>
                                    <select name="id_ruta" id="id_ruta" class="form-select" required>
                                        <option value="" disabled>Seleccione ruta</option>
                                        <?php if (empty($rutas)): ?>
                                            <option value="" disabled>No hay rutas disponibles</option>
                                        <?php else: ?>
                                            <?php foreach($rutas as $r): ?>
                                                <option value="<?php echo $r->id_ruta; ?>" <?php echo ($ruta->id_ruta == $r->id_ruta) ? 'selected' : ''; ?>><?php echo $r->id_ruta . ' - ' . $r->nom_ruta; ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">ID Bus</label>
                                    <select name="id_bus" id="id_bus" class="form-select" required>
                                        <option value="" disabled>Seleccione bus</option>
                                        <?php if (empty($buses)): ?>
                                            <option value="" disabled>No hay buses disponibles</option>
                                        <?php else: ?>
                                            <?php foreach($buses as $b): ?>
                                                <option value="<?php echo $b->id_bus; ?>" <?php echo ($ruta->id_bus == $b->id_bus) ? 'selected' : ''; ?>><?php echo $b->id_bus . ' - ' . $b->matricula; ?></option>
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


