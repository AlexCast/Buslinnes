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
=========================================================================================
Este archivo lista todos los datos de la tabla tab_ruta_bus, obteniendo los mismos como un arreglo
=========================================================================================
*/
// Validar JWT antes de mostrar contenido
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']); // Admin y conductor pueden ver rutas de buses

include_once "../base_de_datos.php";
$sentencia = $base_de_datos->query('SELECT id_ruta_bus, id_ruta, id_bus, fec_delete, usr_delete FROM tab_ruta_bus ORDER BY id_ruta_bus DESC');
$rutas_buses = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Contar rutas-bus eliminadas
$rutasEliminadas = array_filter($rutas_buses, function($r) {
    return !empty($r->fec_delete);
});
?>
<?php include_once "encabezado_rutas_buses.php"; ?>
<main class="main-container">
<div class="row">
    <div class="col-12">
                <!-- Encabezado principal (H1) mantenido para jerarquía -->
                <h1>Rutas de Buses</h1>
        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Total: <?php echo count($rutas_buses); ?> rutas-bus</span>
                        <!-- Botón accesible para abrir modal de eliminados -->
                        <button type="button" class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;" aria-haspopup="dialog" aria-controls="modalEliminados">Eliminadas: <?php echo count($rutasEliminadas); ?></button>
            <!-- Modal flotante para rutas-bus eliminadas -->
            <div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header bg-danger text-white">
                                        <!-- Jerarquía: H2 con clase h5 para mantener estilo visual -->
                                        <h2 class="modal-title h5" id="modalEliminadosLabel">Rutas-Bus Eliminadas</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                  </div>
                  <div class="modal-body">
                    <?php if (count($rutasEliminadas) === 0): ?>
                      <div class="alert alert-info">No hay rutas-bus eliminadas.</div>
                    <?php else: ?>
                      <div class="table-responsive">
                                                <table class="table table-bordered" aria-labelledby="modalEliminadosLabel">
                                                    <caption style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de rutas de buses eliminadas</caption>
                          <thead class="table-danger">
                            <tr>
                                                            <th scope="col">ID Ruta Bus</th>
                                                            <th scope="col">ID Ruta</th>
                                                            <th scope="col">ID Bus</th>
                                                            <th scope="col">Eliminada por</th>
                                                            <th scope="col">Fecha Eliminación</th>
                                                            <th scope="col">Acciones</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($rutasEliminadas as $registro): ?>
                              <tr>
                                                                <td data-label="ID Ruta Bus"><?php echo $registro->id_ruta_bus; ?></td>
                                                                <td data-label="ID Ruta"><?php echo $registro->id_ruta; ?></td>
                                                                <td data-label="ID Bus"><?php echo htmlspecialchars($registro->id_bus, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td data-label="Eliminada por"><?php echo htmlspecialchars($registro->usr_delete); ?></td>
                                                                <td data-label="Fecha Eliminación"><?php echo date('d/m/Y H:i', strtotime($registro->fec_delete)); ?></td>
                                                                <td data-label="Acciones">
                                  <form method="POST" action="restore_rutas_buses.php" onsubmit="return confirm('¿Restaurar esta ruta-bus?');" style="display:inline-block;">
                                    <input type="hidden" name="id_ruta_bus" value="<?php echo $registro->id_ruta_bus; ?>">
                                                                        <button type="submit" class="btn btn-sm btn-restore" aria-label="Restaurar ruta-bus <?php echo $registro->id_ruta_bus; ?>">
                                                                            <i class="fas fa-trash-restore" aria-hidden="true"></i> Restaurar
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
        </div>

        <div class="desktop-view">
            <div class="table-responsive">
                <table class="table table-hover" aria-describedby="tablaRutasBusesCaption">
                    <caption id="tablaRutasBusesCaption" style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de rutas de buses activas</caption>
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">ID Ruta Bus</th>
                            <th scope="col">ID Ruta</th>
                            <th scope="col">ID Bus</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rutasNormales = array_filter($rutas_buses, function($r) { return empty($r->fec_delete); });
                        if (count($rutasNormales) === 0): ?>
                            <tr><td colspan="4" class="text-center">No hay rutas-bus registradas.</td></tr>
                        <?php else: ?>
                            <?php foreach($rutasNormales as $ruta): ?>
                            <tr>
                                <td data-label="ID Ruta Bus"><?php echo $ruta->id_ruta_bus; ?></td>
                                <td data-label="ID Ruta"><?php echo $ruta->id_ruta; ?></td>
                                <td data-label="ID Bus"><?php echo htmlspecialchars($ruta->id_bus, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="actions-cell" data-label="Acciones">
                                    <a class="btn btn-warning btn-sm" href="editar_rutas_buses.php?id_ruta_bus=<?php echo $ruta->id_ruta_bus; ?>" aria-label="Editar ruta-bus <?php echo $ruta->id_ruta_bus; ?>">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="eliminar_rutas_buses.php" onsubmit="return confirm('¿Seguro que deseas eliminar esta ruta-bus?');" style="display:inline-block;">
                                        <input type="hidden" name="id_ruta_bus" value="<?php echo $ruta->id_ruta_bus; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar ruta-bus <?php echo $ruta->id_ruta_bus; ?>">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
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
                <?php if (count($rutas_buses) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay rutas-bus registradas.</div>
                    </div>
                <?php else: ?>
                    <?php foreach($rutas_buses as $ruta): 
                        $eliminada = !empty($ruta->fec_delete);
                        if ($eliminada) continue; // Ocultar eliminadas de la vista móvil
                    ?>
                    <div class="col-12 mb-3">
                        <div class="tarjeta-card card <?php echo $eliminada ? 'registro-eliminado' : ''; ?>">
                            <?php if ($eliminada): ?>
                                <span class="badge-eliminado">ELIMINADA</span>
                            <?php endif; ?>
                            
                            <!-- Card header - Contiene título de la ruta-bus y botones -->
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h3 class="mb-0 h5">Ruta-Bus #<?php echo $ruta->id_ruta_bus; ?></h3>
                                
                                <!-- Botones directamente en el header -->
                                <div class="d-flex gap-2">
                                    <?php if (!$eliminada): ?>
                                        <a class="btn btn-warning btn-sm" href="editar_rutas_buses.php?id_ruta_bus=<?php echo $ruta->id_ruta_bus; ?>" aria-label="Editar ruta-bus <?php echo $ruta->id_ruta_bus; ?>">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </a>
                                        <form method="POST" action="eliminar_rutas_buses.php" onsubmit="return confirm('¿Seguro que deseas eliminar esta ruta-bus?');" style="display:inline-block;">
                                            <input type="hidden" name="id_ruta_bus" value="<?php echo $ruta->id_ruta_bus; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar ruta-bus <?php echo $ruta->id_ruta_bus; ?>">
                                                <i class="fas fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="restore_rutas_buses.php" onsubmit="return confirm('¿Restaurar esta ruta-bus?');" style="display:inline-block;">
                                            <input type="hidden" name="id_ruta_bus" value="<?php echo $ruta->id_ruta_bus; ?>">
                                            <button type="submit" class="btn btn-sm btn-restore" aria-label="Restaurar ruta-bus <?php echo $ruta->id_ruta_bus; ?>">
                                                <i class="fas fa-trash-restore" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($eliminada): ?>
                                    <div class="small mt-1">
                                        <i class="fas fa-user-times me-1"></i> <?php echo htmlspecialchars($ruta->usr_delete); ?>
                                        <i class="fas fa-clock ms-2 me-1"></i> <?php echo date('d/m/Y', strtotime($ruta->fec_delete)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>ID Ruta: </strong>
                                        <span><?php echo $ruta->id_ruta; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>ID Bus: </strong>
                                        <span><?php echo htmlspecialchars($ruta->id_bus, ENT_QUOTES, 'UTF-8'); ?></span>
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