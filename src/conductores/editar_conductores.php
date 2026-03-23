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
@alexndrcastt
@2025
=================================================================
Formulario para editar conductores.
=================================================================
*/

include_once "../base_de_datos.php";

if (!isset($_GET["id_conductor"])) {
    echo "No existe el conductor a editar";
    exit();
}

$id_conductor = $_GET["id_conductor"];
$sentencia = $base_de_datos->prepare("
    SELECT id_conductor,
           nom_conductor,
           ape_conductor,
           email_conductor AS tel_conductor,
           licencia_conductor,
           tipo_licencia,
           fec_venc_licencia,
           estado_conductor,
           edad,
           tipo_sangre
    FROM tab_conductores 
    WHERE id_conductor = ?
");
$sentencia->execute([$id_conductor]);
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

                        <input type="hidden" name="id_conductor" value="<?php echo ($conductor->id_conductor); ?>">
                        <input type="hidden" name="tipo_licencia" value="<?php echo htmlspecialchars($conductor->tipo_licencia); ?>">
                        <input type="hidden" name="fec_venc_licencia" value="<?php echo htmlspecialchars($conductor->fec_venc_licencia); ?>">
                        <input type="hidden" name="estado_conductor" value="<?php echo htmlspecialchars($conductor->estado_conductor); ?>">
                        <input type="hidden" name="edad" value="<?php echo htmlspecialchars($conductor->edad); ?>">
                        <input type="hidden" name="tipo_sangre" value="<?php echo htmlspecialchars($conductor->tipo_sangre); ?>">

                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nom_conductor" class="form-label">Nombre</label>
                     <input value="<?php echo ($conductor->nom_conductor); ?>" 
                         required name="nom_conductor" type="text" id="nom_conductor" 
                         minlength="3"
                         placeholder="Nombre completo del conductor" class="form-control">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="ape_conductor" class="form-label">Apellido</label>
                     <input value="<?php echo ($conductor->ape_conductor); ?>" 
                         required name="ape_conductor" type="text" id="ape_conductor" 
                         minlength="3"
                         placeholder="Apellido del conductor" class="form-control">
                                </div>
                            </div>
                            
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tel_conductor" class="form-label">Correo</label>
                     <input value="<?php echo ($conductor->tel_conductor); ?>" 
                         required name="tel_conductor" type="email" id="tel_conductor" 
                         placeholder="Correo del conductor" class="form-control">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="licencia_conductor" class="form-label">Licencia</label>
                     <input value="<?php echo ($conductor->licencia_conductor); ?>" 
                         required name="licencia_conductor" type="text" id="licencia_conductor" 
                         maxlength="6"
                         placeholder="Número de licencia" class="form-control">
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



