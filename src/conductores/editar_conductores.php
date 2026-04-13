<?php
header('Content-Type: text/html; charset=utf-8');
$fechaMinimaLicencia = date('Y-m-d', strtotime('+1 day'));

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
@alexndrcastt
@2025
=================================================================
Formulario para editar conductores.
=================================================================
*/

include_once "../base_de_datos.php";

if (!isset($_GET["id_usuario"])) {
    echo "No existe el conductor a editar";
    exit();
}

$id_usuario_txt = trim((string) $_GET["id_usuario"]);
if (!preg_match('/^[0-9]+$/', $id_usuario_txt) || (int) $id_usuario_txt <= 0) {
    echo "ID de conductor invalido";
    exit();
}
$id_usuario = (int) $id_usuario_txt;
$sentencia = $base_de_datos->prepare("
    SELECT id_usuario,
           nom_conductor,
           ape_conductor,
           email_conductor,
           licencia_conductor,
           tipo_licencia,
           fec_venc_licencia,
           estado_conductor,
           COALESCE(EXTRACT(YEAR FROM AGE(CURRENT_DATE, fec_nacimiento))::int, 18) AS edad,
           tipo_sangre
    FROM tab_conductores 
    WHERE id_usuario = ?
");
$sentencia->execute([$id_usuario]);
$conductor = $sentencia->fetchObject();

if (!$conductor) {
    echo "¡No existe el conductor con ese ID!";
    exit();
}
?>

<?php include_once "./encab_conductores.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Conductor</h1>
                </div>
                <div class="card-body">
                    <form action="update_conductores.php" method="POST">

                        <input type="hidden" name="id_usuario" value="<?php echo ($conductor->id_usuario); ?>">

                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nom_conductor" class="form-label">Nombre</label>
                     <input value="<?php echo htmlspecialchars($conductor->nom_conductor); ?>" 
                         required name="nom_conductor" type="text" id="nom_conductor" 
                         minlength="3" maxlength="60"
                         placeholder="Nombre completo del conductor" class="form-control">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="ape_conductor" class="form-label">Apellido</label>
                     <input value="<?php echo htmlspecialchars($conductor->ape_conductor); ?>" 
                         required name="ape_conductor" type="text" id="ape_conductor" 
                         minlength="3" maxlength="60"
                         placeholder="Apellido del conductor" class="form-control">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="email_conductor" class="form-label">Email</label>
                     <input value="<?php echo htmlspecialchars($conductor->email_conductor); ?>" 
                         required name="email_conductor" type="email" id="email_conductor" 
                         maxlength="120"
                         placeholder="Correo del conductor" class="form-control">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="licencia_conductor" class="form-label">Licencia</label>
                     <input value="<?php echo htmlspecialchars($conductor->licencia_conductor); ?>" 
                         required name="licencia_conductor" type="text" id="licencia_conductor" 
                         minlength="7" maxlength="10" pattern="[0-9]{7,10}" inputmode="numeric"
                         placeholder="Número de licencia" class="form-control">
                                </div>
                            </div>
                            
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tipo_licencia" class="form-label">Tipo de licencia</label>
                                    <select name="tipo_licencia" id="tipo_licencia" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                        <option value="C2" <?php echo $conductor->tipo_licencia === 'C2' ? 'selected' : ''; ?>>C2 (camiones/buses)</option>
                                        <option value="C3" <?php echo $conductor->tipo_licencia === 'C3' ? 'selected' : ''; ?>>C3 (pesados/articulados)</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="fec_venc_licencia" class="form-label">Vencimiento licencia</label>
                     <input value="<?php echo htmlspecialchars($conductor->fec_venc_licencia); ?>" 
                         required name="fec_venc_licencia" type="date" id="fec_venc_licencia" 
                         min="<?php echo $fechaMinimaLicencia; ?>"
                         class="form-control">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="estado_conductor" class="form-label">Estado</label>
                                    <select name="estado_conductor" id="estado_conductor" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                        <option value="A" <?php echo $conductor->estado_conductor === 'A' ? 'selected' : ''; ?>>A (Activo)</option>
                                        <option value="S" <?php echo $conductor->estado_conductor === 'S' ? 'selected' : ''; ?>>S (Suspendido)</option>
                                        <option value="R" <?php echo $conductor->estado_conductor === 'R' ? 'selected' : ''; ?>>R (Retirado)</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="edad" class="form-label">Edad</label>
                     <input value="<?php echo htmlspecialchars($conductor->edad); ?>" 
                         required name="edad" type="number" id="edad" min="18" max="100"
                         placeholder="Edad" class="form-control">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tipo_sangre" class="form-label">Tipo de sangre</label>
                                    <select name="tipo_sangre" id="tipo_sangre" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                        <option value="A+" <?php echo $conductor->tipo_sangre === 'A+' ? 'selected' : ''; ?>>A+</option>
                                        <option value="A-" <?php echo $conductor->tipo_sangre === 'A-' ? 'selected' : ''; ?>>A-</option>
                                        <option value="B+" <?php echo $conductor->tipo_sangre === 'B+' ? 'selected' : ''; ?>>B+</option>
                                        <option value="B-" <?php echo $conductor->tipo_sangre === 'B-' ? 'selected' : ''; ?>>B-</option>
                                        <option value="AB+" <?php echo $conductor->tipo_sangre === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                        <option value="AB-" <?php echo $conductor->tipo_sangre === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                        <option value="O+" <?php echo $conductor->tipo_sangre === 'O+' ? 'selected' : ''; ?>>O+</option>
                                        <option value="O-" <?php echo $conductor->tipo_sangre === 'O-' ? 'selected' : ''; ?>>O-</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="./listar_conductores.php" class="btn btn-warning">
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




