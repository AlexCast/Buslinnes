<?php
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

/* autor: alexndrcastt | Adaptado para tab_cambio_bus */

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->query("
    SELECT id_cambio, id_bus_salida, id_bus_entrada, fecha_cambio,
           motivo, usr_insert, fec_insert, usr_delete, fec_delete
    FROM tab_cambio_bus
    ORDER BY fec_insert DESC
");
$cambios = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Contar cambios eliminados (soft delete)
$cambiosEliminados = array_filter($cambios, fn($c) => !empty($c->fec_delete));
?>

<?php include_once "encab_cambio_bus.php"; ?>
<main class="main-container">
<div class="row">
    <div class="col-12">
        <h1>Cambios de Bus Registrados</h1>

        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Total: <?php echo count($cambios); ?> cambios</span>
            <span class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;">
                Eliminados: <?php echo count($cambiosEliminados); ?>
            </span>
        </div>

        <!-- Modal para cambios eliminados -->
        <div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminadosLabel">Cambios Eliminados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (count($cambiosEliminados) === 0): ?>
                  <div class="alert alert-info">No hay cambios eliminados.</div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-bordered">
                      <thead class="table-danger">
                        <tr>
                          <th>ID</th>
                          <th>Bus Salida</th>
                          <th>Bus Entrada</th>
                          <th>Fecha Cambio</th>
                          <th>Motivo</th>
                          <th>Eliminado por</th>
                          <th>Fecha</th>
                          <th>Acciones</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($cambiosEliminados as $c): ?>
                          <tr>
                            <td><?php echo $c->id_cambio; ?></td>
                            <td><?php echo $c->id_bus_salida; ?></td>
                            <td><?php echo $c->id_bus_entrada; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($c->fecha_cambio)); ?></td>
                            <td><?php echo htmlspecialchars($c->motivo); ?></td>
                            <td><?php echo htmlspecialchars($c->usr_delete); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($c->fec_delete)); ?></td>
                            <td>
                              <form method="POST" action="restore_cambio_bus.php" onsubmit="return confirm('¿Restaurar este registro?');" style="display:inline-block;">
                                <input type="hidden" name="id_cambio" value="<?php echo $c->id_cambio; ?>">
                                <button type="submit" class="btn btn-sm btn-restore">
                                  <i class="fas fa-trash-restore"></i> Restaurar
                                </button>
                              </form>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Vista de escritorio -->
        <div class="desktop-view">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Bus Salida</th>
                            <th>Bus Entrada</th>
                            <th>Fecha Cambio</th>
                            <th>Motivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($cambios) === 0): ?>
                            <tr><td colspan="6" class="text-center">No hay cambios registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($cambios as $c):
                                if (!empty($c->fec_delete)) continue; // ocultar eliminados
                            ?>
                            <tr>
                                <td><?php echo $c->id_cambio; ?></td>
                                <td><?php echo $c->id_bus_salida; ?></td>
                                <td><?php echo $c->id_bus_entrada; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($c->fecha_cambio)); ?></td>
                                <td><?php echo htmlspecialchars($c->motivo); ?></td>
                                <td>
                                    <a class="btn btn-warning btn-sm" href="editar_cambio_bus.php?id_cambio=<?php echo $c->id_cambio; ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="eliminar_cambio_bus.php" onsubmit="return confirm('¿Eliminar este cambio?');" style="display:inline-block;">
                                        <input type="hidden" name="id_cambio" value="<?php echo $c->id_cambio; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vista móvil -->
        <div class="mobile-view">
            <div class="row">
                <?php if (count($cambios) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay cambios registrados.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($cambios as $c):
                        if (!empty($c->fec_delete)) continue;
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="card cambio-card">
                            <div class="card-header bg-primary text-white">
                                Cambio #<?php echo $c->id_cambio; ?>
                            </div>
                            <div class="card-body">
                                <p><strong>Bus Salida:</strong> <?php echo $c->id_bus_salida; ?></p>
                                <p><strong>Bus Entrada:</strong> <?php echo $c->id_bus_entrada; ?></p>
                                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($c->fecha_cambio)); ?></p>
                                <p><strong>Motivo:</strong> <?php echo htmlspecialchars($c->motivo); ?></p>
                            </div>
                            <div class="card-footer d-flex justify-content-end gap-2">
                                <a class="btn btn-warning btn-sm" href="editar_cambio_bus.php?id_cambio=<?php echo $c->id_cambio; ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="eliminar_cambio_bus.php" onsubmit="return confirm('¿Eliminar este cambio?');" style="display:inline-block;">
                                    <input type="hidden" name="id_cambio" value="<?php echo $c->id_cambio; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
</main>
<?php include_once "../pie.php"; ?>

<!-- Bootstrap JS y script para modal de eliminados -->
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/modalEliminados.js"></script>
