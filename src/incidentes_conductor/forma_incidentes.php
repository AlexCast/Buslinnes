<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
@Carlos Eduardo Perez Rueda
2023

Adaptado por
@yerson
2025
=================================================================
Formulario para agregar nuevos incidentes al sistema
=================================================================
*/
?>

<?php include_once "encab_incidentes.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Registrar Nuevo Incidente</h1>
                </div>
                <div class="card-body">
                    <form action="insertar_incidentes.php" method="POST">
                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="titulo_incidente" class="form-label">Título del Incidente</label>
                                    <input type="text" id="titulo_incidente" name="titulo_incidente" class="form-control" placeholder="Ingrese un título breve" required minlength="3" maxlength="120">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="desc_incidente" class="form-label">Descripción del Incidente</label>
                                    <textarea required name="desc_incidente" id="desc_incidente" class="form-control" rows="5" placeholder="Describa detalladamente el incidente ocurrido"></textarea>
                                </div>
                            </div>
                            
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_bus" class="form-label">Bus Involucrado</label>
                                    <select name="id_bus" id="id_bus" class="form-select" required>
                                        <option value="" disabled selected>Seleccione un bus</option>
                                        <?php
                                        include_once "../base_de_datos.php";
                                        $sentencia = $base_de_datos->query("SELECT id_bus FROM tab_buses WHERE fec_delete IS NULL ORDER BY id_bus");
                                        $buses = $sentencia->fetchAll(PDO::FETCH_OBJ);
                                        foreach($buses as $bus): ?>
                                            <option value="<?php echo $bus->id_bus ?>"><?php echo $bus->id_bus ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="id_usuario" class="form-label">Conductor</label>
                                    <select name="id_usuario" id="id_usuario" class="form-select" required>
                                        <option value="" disabled selected>Seleccione un conductor</option>
                                        <?php
                                        $sentencia = $base_de_datos->query("SELECT id_usuario, nom_conductor, ape_conductor FROM tab_conductores WHERE fec_delete IS NULL ORDER BY nom_conductor, ape_conductor");
                                        $conductores = $sentencia->fetchAll(PDO::FETCH_OBJ);
                                        foreach($conductores as $conductor): ?>
                                            <option value="<?php echo $conductor->id_usuario ?>"><?php echo $conductor->nom_conductor . ' ' . $conductor->ape_conductor . ' (#' . $conductor->id_usuario . ')' ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="tipo_incidente" class="form-label">Tipo de Incidente</label>
                                    <select name="tipo_incidente" id="tipo_incidente" class="form-select" required>
                                        <option value="" disabled selected>Seleccione un tipo</option>
                                        <option value="C">Choque</option>
                                        <option value="E">Embotellamiento</option>
                                        <option value="D">Desviación de ruta</option>
                                        <option value="A">Atropello</option>
                                        <option value="O">Otros</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="fec_insert" class="form-label">Fecha del Incidente</label>
                                    <input type="datetime-local" name="fec_insert" id="fec_insert" class="form-control" required max="<?php echo date('Y-m-d\TH:i'); ?>" value="<?php echo date('Y-m-d\TH:i'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="/buslinnes/templates/driver_interface.html" class="btn btn-warning">
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





