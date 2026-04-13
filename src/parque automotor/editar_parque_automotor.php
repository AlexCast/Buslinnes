<?php

header('Content-Type: text/html; charset=utf-8');
/*
CRUD con PostgreSQL y PHP
Autor: yerson
*/
// Permitir id_parque_automotor por GET o id por compatibilidad
if (isset($_GET["id_parque_automotor"])) {
    $id_parque = $_GET["id_parque_automotor"];
} elseif (isset($_GET["id"])) {
    $id_parque = $_GET["id"];
} else {
    echo "No se especificó el parque automotor a editar";
    exit();
}

$id_parque_txt = trim((string) $id_parque);
if (!preg_match('/^[0-9]+$/', $id_parque_txt) || (int) $id_parque_txt <= 0) {
    echo "ID de parque automotor invalido";
    exit();
}
$id_parque = (int) $id_parque_txt;
include_once "../base_de_datos.php";


// Obtener datos del parque automotor
$sentencia = $base_de_datos->prepare("
    SELECT pa.*
    FROM tab_parque_automotor pa
    WHERE pa.id_parque_automotor = ?
      AND pa.fec_delete IS NULL
");
$sentencia->execute([$id_parque]);
$parque = $sentencia->fetchObject();

// Obtener lista de buses para el select
$buses = $base_de_datos->query("SELECT id_bus FROM tab_buses WHERE fec_delete IS NULL ORDER BY id_bus")->fetchAll(PDO::FETCH_OBJ);

if (!$parque) {
    echo "¡No existe ningún parque automotor con ese ID!";
    exit();
}
?>

<?php include_once "encab_parque_automotor.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Parque Automotor</h1>
                </div>
                <div class="card-body">
                    <form action="update_parque_automotor.php" method="POST">
                        <input type="hidden" name="id_parque_automotor" value="<?php echo htmlspecialchars((string) $parque->id_parque_automotor, ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">ID Bus</label>
                                    <select name="id_bus" id="id_bus" class="form-select" required>
                                        <option value="" disabled>Seleccione un bus</option>
                                        <?php foreach($buses as $bus): ?>
                                            <option value="<?php echo htmlspecialchars($bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>" <?php if((string) $bus->id_bus === (string) $parque->id_bus) echo 'selected'; ?>>
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
                                    <input value="<?php echo htmlspecialchars($parque->dir_parque_automotor); ?>" 
                                        required name="dir_parque_automotor" type="text" id="dir_parque_automotor" 
                                        minlength="5" maxlength="255"
                                        class="form-control" placeholder="Dirección completa">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="./listar_parque_automotor.php" class="btn btn-warning">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once "../pie.php"; ?>




