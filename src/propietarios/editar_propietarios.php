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
if (!isset($_GET["id_propietario"])) {
    echo "No existe el propietario a editar";
    exit();
}

$id_propietario = $_GET["id_propietario"];

// Seleccionar el cliente por ID
// Obtener datos del propietario
$sentencia = $base_de_datos->prepare("SELECT id_propietario, nom_propietario, ape_propietario, tel_propietario, email_propietario, id_bus FROM tab_propietarios WHERE id_propietario = ?;");
$sentencia->execute([$id_propietario]);
$cli = $sentencia->fetchObject();
// Obtener lista de buses para el select
$buses = $base_de_datos->query("SELECT id_bus, matricula FROM tab_buses ORDER BY id_bus")->fetchAll(PDO::FETCH_OBJ);
if (!$cli) {
    echo "¡No existe el propietario con ese ID!";
    exit();
}

?>
<?php include_once "encabezado_propietarios.php" ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Propietario</h1>
                </div>
                <div class="card-body">
                    <form id="editForm" action="update_propietarios.php" method="POST">
                        <input type="hidden" name="id_propietario" value="<?php echo $cli->id_propietario; ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nom_propietario" class="form-label">Nombre</label>
<input value="<?php echo htmlspecialchars($cli->nom_propietario); ?>" required name="nom_propietario" type="text" id="nom_propietario" placeholder="Nombre" class="form-control" minlength="3" maxlength="50" autocomplete="off">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="ape_propietario" class="form-label">Apellido</label>
<input value="<?php echo htmlspecialchars($cli->ape_propietario); ?>" required name="ape_propietario" type="text" id="ape_propietario" placeholder="Apellido" class="form-control" minlength="3" maxlength="50" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tel_propietario" class="form-label">Teléfono</label>
<input value="<?php echo htmlspecialchars($cli->tel_propietario); ?>" required name="tel_propietario" type="text" id="tel_propietario" placeholder="Teléfono" class="form-control" pattern="\d{10}" minlength="10" maxlength="10" title="Debe contener 10 dígitos" autocomplete="off">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="email_propietario" class="form-label">Email</label>
<input value="<?php echo htmlspecialchars($cli->email_propietario); ?>" required name="email_propietario" type="email" id="email_propietario" placeholder="Email" class="form-control" autocomplete="off">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">ID Bus</label>
                                    <select name="id_bus" id="id_bus" class="form-select" required>
                                        <option value="" disabled>Seleccione un bus</option>
                                        <?php foreach($buses as $bus): ?>
                                            <option value="<?php echo $bus->id_bus; ?>" <?php if($bus->id_bus == $cli->id_bus) echo 'selected'; ?>>
                                                <?php echo $bus->id_bus . ' - ' . htmlspecialchars($bus->matricula); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="./listar_propietarios.php" class="btn btn-warning">
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
<?php include_once "../pie.php" ?>
<?php include_once "../pie.php" ?>



