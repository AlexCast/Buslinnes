<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
@yerson
@2025
=================================================================
Formulario para editar pasajeros.
=================================================================
*/

include_once "../base_de_datos.php";

if (!isset($_GET["id_pasajero"])) {
    echo "No existe el pasajero a editar";
    exit();
}

$id_pasajero = $_GET["id_pasajero"];
$sentencia = $base_de_datos->prepare("
    SELECT id_usuario AS id_pasajero,
           nom_pasajero,
           '' AS ape_pasajero,
           email_pasajero AS tel_pasajero
    FROM tab_pasajeros 
    WHERE id_usuario = ? AND fec_delete IS NULL
");
$sentencia->execute([$id_pasajero]);
$pasajero = $sentencia->fetchObject();

if (!$pasajero) {
    echo "¡No existe el pasajero con ese ID o ha sido eliminado!";
    exit();
}
?>

<?php include_once "encab_pasajeros.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Pasajero</h1>
                </div>
                <div class="card-body">
                    <form action="update_pasajeros.php" method="POST">
                        <input type="hidden" name="id_pasajero" value="<?php echo $pasajero->id_pasajero; ?>">

                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nom_pasajero" class="form-label">Nombre</label>
                                    <input value="<?php echo htmlspecialchars($pasajero->nom_pasajero); ?>" 
                                           required name="nom_pasajero" type="text" id="nom_pasajero" 
                                           placeholder="Nombre del pasajero" class="form-control"
                                           minlength="3" maxlength="50">
                                </div>

                                <div class="form-group mb-3">
                                     <label for="ape_pasajero" class="form-label">Apellido (opcional)</label>
                                    <input value="<?php echo htmlspecialchars($pasajero->ape_pasajero); ?>" 
                                         name="ape_pasajero" type="text" id="ape_pasajero" 
                                           placeholder="Apellido del pasajero" class="form-control"
                                         maxlength="50">
                                </div>
                            </div>
                            
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                     <label for="tel_pasajero" class="form-label">Correo</label>
                                    <input value="<?php echo htmlspecialchars($pasajero->tel_pasajero); ?>" 
                                         required name="tel_pasajero" type="email" id="tel_pasajero" 
                                         placeholder="Correo del pasajero" class="form-control">
                                </div>

                                <!-- Campo de usuario que modifica eliminado -->
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="./listar_pasajeros.php" class="btn btn-warning">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once "../pie.php"; ?>


