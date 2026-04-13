<?php

header('Content-Type: text/html; charset=utf-8');
/*
CRUD con PostgreSQL y PHP
Autor: yerson
*/
if (!isset($_GET["id_incidente"])) {
    echo "No se especificó el incidente a editar";
    exit();
}

$id_incidente_txt = trim((string) $_GET["id_incidente"]);
if (!preg_match('/^[0-9]+$/', $id_incidente_txt) || (int) $id_incidente_txt <= 0) {
    echo "ID de incidente invalido";
    exit();
}
$id_incidente = (int) $id_incidente_txt;
include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT i.id_incidente, 'Incidente #' || i.id_incidente AS titulo_incidente, i.desc_incidente, i.id_bus, b.id_usuario, i.tipo_incidente, i.usr_insert, i.fec_insert FROM tab_incidentes i LEFT JOIN tab_buses b ON i.id_bus = b.id_bus WHERE i.id_incidente = ?;");
$sentencia->execute([$id_incidente]);
$incidente = $sentencia->fetchObject();

if (!$incidente) {
    echo "¡No existe ningún incidente con ese ID!";
    exit();
}
?>

<?php include_once "encab_incidentes.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Información del Incidente</h1>
                </div>
                <div class="card-body">
                    <!-- Corrección en la acción del formulario -->
                    <form action="update_incidentes.php" method="POST">
                        <input type="hidden" name="id_incidente" value="<?php echo $incidente->id_incidente; ?>">
                        <input type="hidden" name="usr_insert" value="<?php echo $incidente->usr_insert; ?>">
                        <input type="hidden" name="fec_insert" value="<?php echo date('Y-m-d H:i:s', strtotime($incidente->fec_insert)); ?>">

                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="titulo_incidente" class="form-label">Título del Incidente</label>
                                    <input required type="text" name="titulo_incidente" id="titulo_incidente" class="form-control" minlength="3" maxlength="120" value="<?php echo htmlspecialchars($incidente->titulo_incidente); ?>">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="desc_incidente" class="form-label">Descripción del Incidente</label>
                                    <textarea required name="desc_incidente" id="desc_incidente" class="form-control" rows="3" minlength="5" maxlength="2000"><?php echo htmlspecialchars($incidente->desc_incidente, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">Bus Involucrado</label>
                                    <select name="id_bus" id="id_bus" class="form-select" required>
                                        <option value="" disabled>Seleccione un bus</option>
                                        <?php
                                        include_once "../base_de_datos.php";
                                        $sentencia_buses = $base_de_datos->query("SELECT id_bus FROM tab_buses WHERE fec_delete IS NULL ORDER BY id_bus");
                                        $buses = $sentencia_buses->fetchAll(PDO::FETCH_OBJ);
                                        foreach($buses as $bus): ?>
                                            <option value="<?php echo $bus->id_bus; ?>" <?php echo ($bus->id_bus == $incidente->id_bus) ? 'selected' : ''; ?>><?php echo $bus->id_bus; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_usuario" class="form-label">Conductor</label>
                                    <select name="id_usuario" id="id_usuario" class="form-select" required>
                                        <option value="" disabled>Seleccione un conductor</option>
                                        <?php
                                        $sentencia_conductores = $base_de_datos->query("SELECT id_usuario, nom_conductor, ape_conductor FROM tab_conductores WHERE fec_delete IS NULL ORDER BY nom_conductor, ape_conductor");
                                        $conductores = $sentencia_conductores->fetchAll(PDO::FETCH_OBJ);
                                        foreach($conductores as $conductor): ?>
                                            <option value="<?php echo $conductor->id_usuario; ?>" <?php echo ($conductor->id_usuario == $incidente->id_usuario) ? 'selected' : ''; ?>><?php echo $conductor->nom_conductor . ' ' . $conductor->ape_conductor . ' (#' . $conductor->id_usuario . ')'; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="tipo_incidente" class="form-label">Tipo de Incidente</label>
                                    <select name="tipo_incidente" id="tipo_incidente" class="form-select" required>
                                        <option value="C" <?= $incidente->tipo_incidente == 'C' ? 'selected' : '' ?>>Choque</option>
                                        <option value="E" <?= $incidente->tipo_incidente == 'E' ? 'selected' : '' ?>>Embotellamiento</option>
                                        <option value="D" <?= $incidente->tipo_incidente == 'D' ? 'selected' : '' ?>>Desviación de ruta</option>
                                        <option value="A" <?= $incidente->tipo_incidente == 'A' ? 'selected' : '' ?>>Atropello</option>
                                        <option value="O" <?= $incidente->tipo_incidente == 'O' ? 'selected' : '' ?>>Otros</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="./listar_incidentes.php" class="btn btn-warning">
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





