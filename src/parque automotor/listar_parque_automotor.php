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

/* autor: yerson */

// Validar JWT antes de mostrar contenido
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']); // Solo admin puede ver parque automotor

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->query("
    SELECT pa.id_parque_automotor, pa.id_bus, pa.dir_parque_automotor,
           pa.usr_insert, pa.fec_insert, pa.usr_update, pa.fec_update,
        pa.usr_delete, pa.fec_delete
    FROM tab_parque_automotor pa
    ORDER BY pa.fec_insert DESC
");
$parques = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Contar parques eliminados
$parquesEliminados = array_filter($parques, function($parque) {
    return !empty($parque->fec_delete);
});
?>

<?php include_once "encab_parque_automotor.php"; ?>
<main class="main-container">
<div class="row">
    <div class="col-12">
        <h1>Parques Automotores Registrados</h1>
        
        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Total: <?php echo count($parques); ?> parques</span>
            <span class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;">Eliminados: <?php echo count($parquesEliminados); ?></span>
        </div>

        <!-- Modal flotante para parque automotor eliminados -->
        <div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminadosLabel">Registros Eliminados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (count($parquesEliminados) === 0): ?>
                  <div class="alert alert-info">No hay registros eliminados.</div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-bordered">
                      <thead class="table-danger">
                        <tr>
                          <th>ID</th>
                          <th>Eliminado por</th>
                          <th>Fecha Eliminación</th>
                          <th>Acciones</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($parquesEliminados as $registro): ?>
                          <tr>
                            <td><?php echo $registro->id_parque_automotor; ?></td>
                            <td><?php echo htmlspecialchars($registro->usr_delete); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($registro->fec_delete)); ?></td>
                            <td>
                              <form method="POST" action="restore_parque_automotor.php" onsubmit="return confirm('¿Restaurar este registro?');" style="display:inline-block;">
                                <input type="hidden" name="id_parque_automotor" value="<?php echo $registro->id_parque_automotor; ?>">
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
                            <th>Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($parques) === 0): ?>
                               <tr><td colspan="4" class="text-center">No hay parques automotores registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($parques as $parque): 
                                $eliminado = !empty($parque->fec_delete);
                                if ($eliminado) continue; // Ocultar eliminados de la tabla principal
                            ?>
                            <tr>
                                <td><?php echo $parque->id_parque_automotor; ?></td>
                                <td><?php echo htmlspecialchars($parque->id_bus, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($parque->dir_parque_automotor, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="actions-cell">
                                    <?php if (!$eliminado): ?>
                                        <a class="btn btn-warning btn-sm" href="editar_parque_automotor.php?id=<?php echo $parque->id_parque_automotor; ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="eliminar_parque_automotor.php" onsubmit="return confirm('¿Seguro que deseas eliminar este parque automotor?');" style="display:inline-block;">
                                            <input type="hidden" name="id_parque_automotor" value="<?php echo $parque->id_parque_automotor; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="restore_parque_automotor.php" onsubmit="return confirm('¿Restaurar este parque automotor?');" style="display:inline-block;">
                                            <input type="hidden" name="id_parque_automotor" value="<?php echo $parque->id_parque_automotor; ?>">
                                            <button type="submit" class="btn btn-sm btn-restore">
                                                <i class="fas fa-trash-restore"></i> Restaurar
                                            </button>
                                        </form>
                                        <span class="small text-muted d-block mt-1">
                                            <?php 
                                            echo "Eliminado por: " . htmlspecialchars($parque->usr_delete) . "<br>";
                                            echo "Fecha: " . date('d/m/Y H:i', strtotime($parque->fec_delete));
                                            ?>
                                        </span>
                                    <?php endif; ?>
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
                <?php if (count($parques) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay parques automotores registrados.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($parques as $parque): 
                        $eliminado = !empty($parque->fec_delete);
                        if ($eliminado) continue; // Ocultar eliminados de la vista móvil
                    ?>
                    <div class="col-12 mb-3">
                        <div class="card <?php echo $eliminado ? 'registro-eliminado' : ''; ?>">
                            <?php if ($eliminado): ?>
                                <span class="badge-eliminado">ELIMINADO</span>
                            <?php endif; ?>
                            
                            <!-- Card header - Contiene título del parque y botones -->
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Parque #<?php echo $parque->id_parque_automotor; ?></h5>
                                
                                <!-- Botones directamente en el header -->
                                <div class="d-flex gap-2">
                                    <?php if (!$eliminado): ?>
                                        <a class="btn btn-warning btn-sm" href="editar_parque_automotor.php?id=<?php echo $parque->id_parque_automotor; ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="eliminar_parque_automotor.php" onsubmit="return confirm('¿Seguro que deseas eliminar este parque automotor?');" style="display:inline-block;">
                                            <input type="hidden" name="id_parque_automotor" value="<?php echo $parque->id_parque_automotor; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="restore_parque_automotor.php" onsubmit="return confirm('¿Restaurar este parque automotor?');" style="display:inline-block;">
                                            <input type="hidden" name="id_parque_automotor" value="<?php echo $parque->id_parque_automotor; ?>">
                                            <button type="submit" class="btn btn-sm btn-restore">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($eliminado): ?>
                                    <div class="small mt-1">
                                        <i class="fas fa-user-times me-1"></i> <?php echo htmlspecialchars($parque->usr_delete); ?>
                                        <i class="fas fa-clock ms-2 me-1"></i> <?php echo date('d/m/Y', strtotime($parque->fec_delete)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>ID Bus: </strong>
                                        <span><?php echo htmlspecialchars($parque->id_bus, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Dirección: </strong>
                                        <p class="mt-2"><?php echo htmlspecialchars($parque->dir_parque_automotor, ENT_QUOTES, 'UTF-8'); ?></p>
                                    </li>
                                </ul>
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
<!-- Bootstrap JS (asegúrate de que esté presente) -->
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<!-- Script único para el modal de eliminados -->
<script src="../../assets/js/modalEliminados.js"></script>