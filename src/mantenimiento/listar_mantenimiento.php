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

/*
CRUD con PostgreSQL y PHP
@alexndrcastt
@2025
=================================================================
Listado de mantenimientos.
=================================================================
*/
// Validar JWT antes de mostrar contenido
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']); // Solo admin puede ver mantenimiento

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->query('
    SELECT id_mantenimiento, id_bus, descripcion, fecha_mantenimiento, 
           costo_mantenimiento, usr_delete, fec_delete
    FROM tab_mantenimiento
    ORDER BY fecha_mantenimiento DESC
');
$mantenimientos = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Contar mantenimientos eliminados
$mantenimientosEliminados = array_filter($mantenimientos, function($mantenimiento) {
    return !empty($mantenimiento->fec_delete);
});
?>

<?php include_once "encab_mantenimiento.php"; ?>
<main class="main-container">
<div class="row">
    <div class="col-12">
        <h1>Mantenimientos Registrados</h1>
        
        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Total: <?php echo count($mantenimientos); ?> mantenimientos</span>
            <span class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;">Eliminados: <?php echo count($mantenimientosEliminados); ?></span>
        </div>

        <!-- Modal flotante para mantenimientos eliminados -->
        <div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminadosLabel">Mantenimientos Eliminados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (count($mantenimientosEliminados) === 0): ?>
                  <div class="alert alert-info">No hay mantenimientos eliminados.</div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-bordered">
                      <thead class="table-danger">
                        <tr>
                          <th>ID</th>
                          <th>ID Bus</th>
                          <th>Descripción</th>
                          <th>Fecha</th>
                          <th>Costo</th>
                          <th>Eliminado por</th>
                          <th>Fecha Eliminación</th>
                          <th>Acciones</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($mantenimientosEliminados as $mantenimiento): ?>
                          <tr>
                            <td><?php echo $mantenimiento->id_mantenimiento; ?></td>
                                                        <td><?php echo htmlspecialchars($mantenimiento->id_bus, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars(substr($mantenimiento->descripcion, 0, 50) . (strlen($mantenimiento->descripcion) > 50 ? '...' : ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($mantenimiento->fecha_mantenimiento)); ?></td>
                            <td>$<?php echo number_format($mantenimiento->costo_mantenimiento, 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($mantenimiento->usr_delete); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($mantenimiento->fec_delete)); ?></td>
                            <td>
                              <form method="POST" action="restore_mantenimiento.php" onsubmit="return confirm('¿Restaurar este mantenimiento?');" style="display:inline-block;">
                                <input type="hidden" name="id_mantenimiento" value="<?php echo $mantenimiento->id_mantenimiento; ?>">
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

        <div class="desktop-view">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>ID Bus</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th>Costo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($mantenimientos) === 0): ?>
                            <tr><td colspan="6" class="text-center">No hay mantenimientos registrados</td></tr>
                        <?php else: ?>
                            <?php foreach($mantenimientos as $mantenimiento): 
                                $eliminado = !empty($mantenimiento->fec_delete);
                                if ($eliminado) continue; // Ocultar eliminados de la tabla principal
                            ?>
                            <tr>
                                <td><?php echo $mantenimiento->id_mantenimiento; ?></td>
                                <td><?php echo htmlspecialchars($mantenimiento->id_bus, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(substr($mantenimiento->descripcion, 0, 50) . (strlen($mantenimiento->descripcion) > 50 ? '...' : ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($mantenimiento->fecha_mantenimiento)); ?></td>
                                <td>$<?php echo number_format($mantenimiento->costo_mantenimiento, 0, ',', '.'); ?></td>
                                <td class="actions-cell">
                                    <a class="btn btn-warning btn-sm" href="editar_mantenimiento.php?id_mantenimiento=<?php echo $mantenimiento->id_mantenimiento; ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="eliminar_mantenimiento.php" onsubmit="return confirm('¿Seguro que deseas eliminar este mantenimiento?');" style="display: inline-block;">
                                        <input type="hidden" name="id_mantenimiento" value="<?php echo $mantenimiento->id_mantenimiento; ?>">
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
        
        <div class="mobile-view">
            <div class="row">
                <?php if (count($mantenimientos) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay mantenimientos registrados</div>
                    </div>
                <?php else: ?>
                    <?php foreach($mantenimientos as $mantenimiento): 
                        $eliminado = !empty($mantenimiento->fec_delete);
                        if ($eliminado) continue; // Ocultar eliminados de la vista móvil
                    ?>
                    <div class="col-12 mb-3">
                        <div class="mantenimiento-card card <?php echo $eliminado ? 'registro-eliminado' : ''; ?>">
                            <?php if ($eliminado): ?>
                                <span class="badge-eliminado">ELIMINADO</span>
                            <?php endif; ?>
                            
                            <!-- Card header - Contiene título del mantenimiento y botones -->
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Mantenimiento #<?php echo $mantenimiento->id_mantenimiento; ?></h5>
                                
                                <!-- Botones directamente en el header -->
                                <div class="d-flex gap-2">
                                    <?php if (!$eliminado): ?>
                                        <a class="btn btn-warning btn-sm" href="editar_mantenimiento.php?id_mantenimiento=<?php echo $mantenimiento->id_mantenimiento; ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="eliminar_mantenimiento.php" onsubmit="return confirm('¿Seguro que deseas eliminar este mantenimiento?');" style="display:inline-block;">
                                            <input type="hidden" name="id_mantenimiento" value="<?php echo $mantenimiento->id_mantenimiento; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="restore_mantenimiento.php" onsubmit="return confirm('¿Restaurar este mantenimiento?');" style="display:inline-block;">
                                            <input type="hidden" name="id_mantenimiento" value="<?php echo $mantenimiento->id_mantenimiento; ?>">
                                            <button type="submit" class="btn btn-sm btn-restore">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($eliminado): ?>
                                    <div class="small mt-1">
                                        <i class="fas fa-user-times me-1"></i> <?php echo htmlspecialchars($mantenimiento->usr_delete); ?>
                                        <i class="fas fa-clock ms-2 me-1"></i> <?php echo date('d/m/Y', strtotime($mantenimiento->fec_delete)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>ID Bus: </strong>
                                        <span><?php echo htmlspecialchars($mantenimiento->id_bus, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Fecha: </strong>
                                        <span><?php echo date('d/m/Y', strtotime($mantenimiento->fecha_mantenimiento)); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Costo: </strong>
                                        <span class="badge bg-success">$<?php echo number_format($mantenimiento->costo_mantenimiento, 0, ',', '.'); ?></span>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Descripción: </strong>
                                        <p class="mt-2"><?php echo htmlspecialchars($mantenimiento->descripcion, ENT_QUOTES, 'UTF-8'); ?></p>
                                    </li>
                                </ul>
                            </div>
                            <!-- Removed card-footer as buttons have been moved to header -->
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
<!-- Bootstrap JS (asegúrate de que esté presente) -->
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<!-- Script único para el modal de eliminados -->
<script src="../../assets/js/modalEliminados.js"></script>