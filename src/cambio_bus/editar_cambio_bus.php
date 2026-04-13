<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
Adaptación: editar registro de tab_cambio_bus
*/

if (!isset($_GET["id_cambio_bus"])) {
    echo "No se especificó el cambio de bus a editar";
    exit();
}

$id_cambio_bus_txt = trim((string) $_GET["id_cambio_bus"]);
if (!preg_match('/^[0-9]+$/', $id_cambio_bus_txt) || (int) $id_cambio_bus_txt <= 0) {
    echo "ID de cambio de bus invalido";
    exit();
}
$id_cambio_bus = (int) $id_cambio_bus_txt;
include_once "../base_de_datos.php";

// Obtener el registro del cambio de bus
$sentencia = $base_de_datos->prepare("SELECT id_cambio_bus, id_incidente, id_bus, ubicacion_cambio, usr_insert, fec_insert FROM tab_cambio_bus WHERE id_cambio_bus = ?;");
$sentencia->execute([$id_cambio_bus]);
$cambio = $sentencia->fetchObject();

if (!$cambio) {
    echo "¡No existe ningún cambio de bus con ese ID!";
    exit();
}

// Consultar listas para combos
$buses = $base_de_datos->query("SELECT id_bus FROM tab_buses ORDER BY id_bus;")->fetchAll(PDO::FETCH_OBJ);
$incidentes = $base_de_datos->query("SELECT id_incidente, desc_incidente FROM tab_incidentes ORDER BY id_incidente;")->fetchAll(PDO::FETCH_OBJ);
?>

<?php include_once "encab_cambio_bus.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Cambio de Bus</h1>
                </div>
                <div class="card-body">
                    <form action="update_cambio_bus.php" method="POST">
                        <!-- IDs ocultos -->
                        <input type="hidden" name="id_cambio_bus" value="<?= (int) $cambio->id_cambio_bus; ?>">
                        <input type="hidden" name="usr_insert" value="<?= htmlspecialchars($cambio->usr_insert, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="fec_insert" value="<?= date('Y-m-d H:i:s', strtotime($cambio->fec_insert)); ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Incidente -->
                                <div class="form-group mb-3">
                                    <label for="id_incidente" class="form-label">Incidente</label>
                                    <select name="id_incidente" id="id_incidente" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($incidentes as $i): ?>
                                            <option value="<?= $i->id_incidente ?>" 
                                                <?= $cambio->id_incidente == $i->id_incidente ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($i->desc_incidente, ENT_QUOTES, 'UTF-8') ?> (ID: <?= (int) $i->id_incidente ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Bus -->
                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">Bus</label>
                                    <select name="id_bus" id="id_bus" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($buses as $b): ?>
                                            <option value="<?= $b->id_bus ?>" 
                                                <?= $cambio->id_bus == $b->id_bus ? 'selected' : '' ?>>
                                                Bus #<?= $b->id_bus ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Ubicación de cambio -->
                                <div class="form-group mb-3">
                                    <label for="ubicacion_cambio" class="form-label">Ubicación del Cambio</label>
                                     <input value="<?= htmlspecialchars($cambio->ubicacion_cambio, ENT_QUOTES, 'UTF-8'); ?>" 
                                           required 
                                           name="ubicacion_cambio" 
                                           type="text" 
                                           id="ubicacion_cambio" 
                                           class="form-control" 
                                         minlength="3"
                                           maxlength="255">
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



