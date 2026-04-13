<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
@yerson
@2025
=================================================================
Formulario para editar mantenimientos.
=================================================================
*/

include_once "../base_de_datos.php";

if (!isset($_GET["id_mantenimiento"])) {
    echo "No existe el mantenimiento a editar";
    exit();
}

$id_mantenimiento = trim((string) $_GET["id_mantenimiento"]);
if (!preg_match('/^[0-9]+$/', $id_mantenimiento)) {
    echo "ID de mantenimiento invalido";
    exit();
}

$sentencia = $base_de_datos->prepare("
    SELECT id_mantenimiento, id_bus, descripcion, fecha_mantenimiento, 
           costo_mantenimiento 
    FROM tab_mantenimiento 
    WHERE id_mantenimiento = ?
      AND fec_delete IS NULL
");
$sentencia->execute([$id_mantenimiento]);
$mantenimiento = $sentencia->fetchObject();

// Obtener los buses existentes para el select
$buses = $base_de_datos->query('SELECT id_bus FROM tab_buses WHERE fec_delete IS NULL ORDER BY id_bus')->fetchAll(PDO::FETCH_OBJ);

if (!$mantenimiento) {
    echo "¡No existe el mantenimiento con ese ID!";
    exit();
}
?>

<?php include_once "./encab_mantenimiento.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Mantenimiento</h1>
                </div>
                <div class="card-body">
                    <form action="update_mantenimiento.php" method="POST">
                        <input type="hidden" name="id_mantenimiento" value="<?php echo htmlspecialchars((string) $mantenimiento->id_mantenimiento, ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">ID Bus</label>
                                    <select name="id_bus" id="id_bus" class="form-control" required>
                                        <option value="" disabled>Seleccione un bus</option>
                                        <?php foreach($buses as $bus): ?>
                                            <option value="<?php echo htmlspecialchars($bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>" <?php if((string) $bus->id_bus === (string) $mantenimiento->id_bus) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="descripcion" class="form-label">Descripción del Mantenimiento</label>
                                    <input value="<?php echo htmlspecialchars($mantenimiento->descripcion); ?>" 
                                           required name="descripcion" type="text" id="descripcion" minlength="10" maxlength="500"
                                           placeholder="Descripción del mantenimiento" class="form-control">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="fecha_mantenimiento" class="form-label">Fecha del Mantenimiento</label>
                                    <input value="<?php echo htmlspecialchars(date('Y-m-d\\TH:i', strtotime($mantenimiento->fecha_mantenimiento))); ?>" 
                                           required name="fecha_mantenimiento" type="datetime-local" id="fecha_mantenimiento" 
                                           max="<?php echo date('Y-m-d\\TH:i'); ?>"
                                           class="form-control">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="costo_mantenimiento" class="form-label">Costo del Mantenimiento</label>
                                    <input value="<?php echo htmlspecialchars($mantenimiento->costo_mantenimiento); ?>" 
                                           required name="costo_mantenimiento" type="number" id="costo_mantenimiento" 
                                           step="1" min="0" max="9999999999"
                                           placeholder="Costo del mantenimiento" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="./listar_mantenimiento.php" class="btn btn-warning">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button class="btn btn-primary"><i class="fas fa-rocket"></i> Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once "../pie.php"; ?>




