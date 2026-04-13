<?php
header('Content-Type: text/html; charset=utf-8');

// === SEGURIDAD: Proteccion anti-scraping y CSRF ===
require_once __DIR__ . '/../../app/SecurityMiddleware.php';

SecurityMiddleware::protect([
    'csrf' => false,  // GET no requiere CSRF
    'rateLimit' => true,
    'origin' => true,
    'userAgent' => true,
    'securityHeaders' => true
]);
// === FIN SEGURIDAD ===

/*
CRUD con PostgreSQL y PHP
Autor: alexndrcastt
*/
if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']);

if (!isset($_GET["id_bus"])) {
    echo "No se especificó el bus a editar";
    exit();
}

$id_bus_txt = strtoupper(trim((string) $_GET["id_bus"]));
$id_bus_compacto = preg_replace('/\s+/', '', $id_bus_txt);
if (!preg_match('/^[A-Z]{3}[0-9]{3}$/', $id_bus_compacto)) {
    echo "ID de bus invalido. Formato esperado: AAA 123";
    exit();
}
$id_bus = $id_bus_compacto;
include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("SELECT id_bus, id_usuario, anio_fab, capacidad_pasajeros, tipo_bus, gps, ind_estado_buses, usr_insert, fec_insert FROM tab_buses WHERE id_bus = ?;");
$sentencia->execute([$id_bus]);
$bus = $sentencia->fetchObject();

if (!$bus) {
    echo "¡No existe ningún bus con ese ID!";
    exit();
}

// Consultar listas para autocompletar
$conductores = $base_de_datos->query("SELECT id_usuario, nom_conductor FROM tab_conductores WHERE fec_delete IS NULL ORDER BY nom_conductor;")->fetchAll(PDO::FETCH_OBJ);
?>

<?php include_once "encab_buses.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Información del Bus</h1>
                </div>
                <div class="card-body">
                    <!-- Corrección en la acción del formulario -->
                    <form action="update_buses.php" method="POST">
                        <input type="hidden" name="id_bus" value="<?php echo htmlspecialchars((string) $bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="usr_insert" value="<?php echo htmlspecialchars($bus->usr_insert, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="fec_insert" value="<?php echo date('Y-m-d H:i:s', strtotime($bus->fec_insert)); ?>">

                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">

                                <div class="form-group mb-3">
                                    <label for="id_usuario" class="form-label">Conductor</label>
                                    <select name="id_usuario" id="id_usuario" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($conductores as $c): ?>
                                            <option value="<?= (int) $c->id_usuario ?>" <?= (int) $bus->id_usuario === (int) $c->id_usuario ? 'selected' : '' ?>><?= htmlspecialchars($c->nom_conductor, ENT_QUOTES, 'UTF-8') ?> (ID: <?= (int) $c->id_usuario ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="anio_fab" class="form-label">Año de Fabricación</label>
                                    <input value="<?php echo (int) $bus->anio_fab; ?>" required name="anio_fab" type="number" id="anio_fab" class="form-control" min="1950" max="<?php echo date('Y'); ?>">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="capacidad_pasajeros" class="form-label">Capacidad de Pasajeros</label>
                                    <input value="<?php echo (int) $bus->capacidad_pasajeros; ?>" required name="capacidad_pasajeros" type="number" id="capacidad_pasajeros" class="form-control" min="1" max="60">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tipo_bus" class="form-label">Tipo de Bus</label>
                                    <select name="tipo_bus" id="tipo_bus" class="form-select" required>
                                        <option value="U" <?= $bus->tipo_bus == 'U' ? 'selected' : '' ?>>Urbano</option>
                                        <option value="M" <?= $bus->tipo_bus == 'M' ? 'selected' : '' ?>>Municipal</option>
                                        <option value="A" <?= $bus->tipo_bus == 'A' ? 'selected' : '' ?>>Articulado</option>
                                        <option value="E" <?= $bus->tipo_bus == 'E' ? 'selected' : '' ?>>Especializado</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="gps" class="form-label">GPS Activo</label>
                                    <select name="gps" id="gps" class="form-select" required>
                                        <option value="true" <?= $bus->gps ? 'selected' : '' ?>>Sí</option>
                                        <option value="false" <?= !$bus->gps ? 'selected' : '' ?>>No</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="ind_estado_buses" class="form-label">Estado del Bus</label>
                                    <select name="ind_estado_buses" id="ind_estado_buses" class="form-select" required>
                                        <option value="L" <?= $bus->ind_estado_buses == 'L' ? 'selected' : '' ?>>Libre</option>
                                        <option value="F" <?= $bus->ind_estado_buses == 'F' ? 'selected' : '' ?>>Fuera de servicio</option>
                                        <option value="D" <?= $bus->ind_estado_buses == 'D' ? 'selected' : '' ?>>Dañado</option>
                                        <option value="S" <?= $bus->ind_estado_buses == 'S' ? 'selected' : '' ?>>Suspendido</option>
                                        <option value="T" <?= $bus->ind_estado_buses == 'T' ? 'selected' : '' ?>>Taller</option>
                                        <option value="A" <?= $bus->ind_estado_buses == 'A' ? 'selected' : '' ?>>Activo</option>
                                    </select>
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




