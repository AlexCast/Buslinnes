<?php 
header('Content-Type: text/html; charset=utf-8');
include_once "encabezado_propietarios.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Agregar Nuevo Propietario</h1>
                </div>
                <div class="card-body">
                    <form action="insert_propietarios.php" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_propietario" class="form-label">ID Propietario</label>
<input required name="id_propietario" type="text" inputmode="numeric" pattern="[0-9]{6,10}" minlength="6" maxlength="10" title="Debe contener entre 6 y 10 dígitos" id="id_propietario" class="form-control" placeholder="ID del propietario" autocomplete="off" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="nom_propietario" class="form-label">Nombre</label>
<input required name="nom_propietario" type="text" minlength="3" maxlength="50" id="nom_propietario" class="form-control" placeholder="Nombre del propietario" autocomplete="off">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="ape_propietario" class="form-label">Apellido</label>
<input required name="ape_propietario" type="text" minlength="3" maxlength="50" id="ape_propietario" class="form-control" placeholder="Apellido del propietario"  autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tel_propietario" class="form-label">Teléfono</label>
<input required name="tel_propietario" type="number" pattern="\d{10}" title="Debe contener 10 dígitos" id="tel_propietario" class="form-control" placeholder="Teléfono del propietario" min="3000000000" max="3999999999"  >
                                </div>
                                <div class="form-group mb-3">
                                    <label for="email_propietario" class="form-label">Email</label>
<input required name="email_propietario" type="email" id="email_propietario" class="form-control" placeholder="Email del propietario" autocomplete="off">
                                </div>
                                <?php
                                include_once "../base_de_datos.php";
                                $buses = $base_de_datos->query("SELECT id_bus FROM tab_buses WHERE fec_delete IS NULL ORDER BY id_bus")->fetchAll(PDO::FETCH_OBJ);
                                ?>
                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">ID Bus</label>
                                    <select name="id_bus" id="id_bus" class="form-select" required>
                                        <option value="" disabled selected hidden>Seleccione un bus</option>
                                        <?php foreach($buses as $bus): ?>
                                            <option value="<?php echo htmlspecialchars($bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>
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
                                <i class="fas fa-save me-1"></i> Registrar Propietario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once "../pie.php"; ?>





