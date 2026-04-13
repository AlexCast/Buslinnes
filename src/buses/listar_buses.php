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


/* autor: alexndrcastt */

// Validar JWT antes de mostrar contenido
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor', 'pasajero']); // Permitir todos los roles para ver buses

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->query("
    SELECT id_bus, id_usuario,
        anio_fab, capacidad_pasajeros, tipo_bus,
           gps, ind_estado_buses, fec_insert, usr_insert, 
           usr_delete, fec_delete  -- Asegúrate de incluir estos campos
    FROM tab_buses
    ORDER BY fec_insert DESC
");
$buses = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Contar buses eliminados
$busesEliminados = array_filter($buses, function($bus) {
    return !empty($bus->fec_delete);
});
?>

<?php include_once "encab_buses.php"; ?>
<main class="main-container">
<div class="row">
    <div class="col-12">
        <!-- Encabezado principal ajustado para mantener jerarquía clara (nivel H1) -->
        <h1>Buses Registrados</h1>
        
        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Total: <?php echo count($buses); ?> buses</span>
            <!-- Botón accesible en lugar de span para permitir foco y activación por teclado -->
            <button type="button" class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;" aria-haspopup="dialog" aria-controls="modalEliminados">Eliminados: <?php echo count($busesEliminados); ?></button>
        </div>
        
        <!-- Modal flotante para buses eliminados -->
        <div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <!-- Ajuste de jerarquía: usar H2 con clase para mantener estilo visual -->
                                <h2 class="modal-title h5" id="modalEliminadosLabel">Buses Eliminados</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (count($busesEliminados) === 0): ?>
                  <div class="alert alert-info">No hay buses eliminados.</div>
                <?php else: ?>
                  <div class="table-responsive">
                                        <!-- Tabla con caption descriptivo y encabezados con scope -->
                                        <table class="table table-bordered" aria-labelledby="modalEliminadosLabel">
                                            <caption style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de buses eliminados</caption>
                      <thead class="table-danger">
                        <tr>
                                                    <th scope="col">ID</th>
                                                    <th scope="col">Conductor</th>
                                                    <th scope="col">Capacidad</th>
                                                    <th scope="col">Año</th>
                                                    <th scope="col">Tipo</th>
                                                    <th scope="col">Estado</th>
                                                    <th scope="col">Eliminado por</th>
                                                    <th scope="col">Fecha</th>
                                                    <th scope="col">Acciones</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($busesEliminados as $bus): ?>
                                                    <tr>
                                                        <td data-label="ID"><?php echo $bus->id_bus; ?></td>
                                                        <td data-label="Conductor"><?php echo $bus->id_usuario; ?></td>
                                                        <td data-label="Capacidad"><?php echo $bus->capacidad_pasajeros; ?></td>
                                                        <td data-label="Año"><?php echo $bus->anio_fab; ?></td>
                                                        <td data-label="Tipo"><?php 
                              $tipos = ['U'=>'Urbano','M'=>'Municipal','A'=>'Articulado','E'=>'Especializado'];
                              echo $tipos[$bus->tipo_bus] ?? 'Desconocido';
                            ?></td>
                                                        <td data-label="Estado"><?php 
                              $estados = ['L'=>'Libre','F'=>'Fuera de servicio','D'=>'Dañado','S'=>'Suspendido','T'=>'Taller','A'=>'Activo'];
                              echo $estados[$bus->ind_estado_buses] ?? 'Desconocido';
                            ?></td>
                                                        <td data-label="Eliminado por"><?php echo htmlspecialchars($bus->usr_delete); ?></td>
                                                        <td data-label="Fecha"><?php echo date('d/m/Y H:i', strtotime($bus->fec_delete)); ?></td>
                                                        <td data-label="Acciones">
                              <form method="POST" action="restore_buses.php" onsubmit="return confirm('¿Restaurar este bus?');" style="display:inline-block;">
                                                                <input type="hidden" name="id_bus" value="<?php echo htmlspecialchars((string) $bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <button type="submit" class="btn btn-sm btn-restore" aria-label="Restaurar bus <?php echo $bus->id_bus; ?>">
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

        <div class="desktop-view">
            <div class="table-responsive">
                <!-- Tabla principal con caption y encabezados con scope -->
                <table class="table table-hover" aria-describedby="tablaBusesCaption">
                    <caption id="tablaBusesCaption" style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de buses registrados</caption>
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Conductor</th>
                            <th scope="col">Capacidad</th>
                            <th scope="col">Año</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($buses) === 0): ?>
                            <tr><td colspan="7" class="text-center">No hay buses registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($buses as $bus): 
                                $eliminado = !empty($bus->fec_delete);
                                if ($eliminado) continue; // Ocultar eliminados de la tabla principal
                            ?>
                            <tr class="<?php echo $eliminado ? 'tr-eliminada' : ''; ?>">
                                <td data-label="ID"><?php echo $bus->id_bus; ?></td>
                                <td data-label="Conductor"><?php echo $bus->id_usuario; ?></td>
                                <td data-label="Capacidad"><?php echo $bus->capacidad_pasajeros; ?> pasajeros</td>
                                <td data-label="Año"><?php echo $bus->anio_fab; ?></td>
                                <td data-label="Tipo">
                                    <?php 
                                    $tipos = [
                                        'U' => 'Urbano',
                                        'M' => 'Municipal',
                                        'A' => 'Articulado',
                                        'E' => 'Especializado'
                                    ];
                                    echo $tipos[$bus->tipo_bus] ?? 'Desconocido';
                                    ?>
                                </td>
                                <td data-label="Estado">
                                    <span class="bus-status <?php echo $bus->ind_estado_buses === 'A' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php 
                                        $estados = [
                                            'L' => 'Libre',
                                            'F' => 'Fuera de servicio',
                                            'D' => 'Dañado',
                                            'S' => 'Suspendido',
                                            'T' => 'Taller',
                                            'A' => 'Activo'
                                        ];
                                        echo $estados[$bus->ind_estado_buses] ?? 'Desconocido';
                                        ?>
                                    </span>
                                </td>
                                <td class="actions-cell" data-label="Acciones">
                                    <?php if (!$eliminado): ?>
                                        <a class="btn btn-warning btn-sm" href="editar_buses.php?id_bus=<?php echo urlencode((string) $bus->id_bus); ?>" aria-label="Editar bus <?php echo $bus->id_bus; ?>">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </a>
                                        <form method="POST" action="eliminar_buses.php" onsubmit="return confirm('¿Seguro que deseas eliminar este bus?');" style="display:inline-block;">
                                            <input type="hidden" name="id_bus" value="<?php echo htmlspecialchars((string) $bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar bus <?php echo $bus->id_bus; ?>">
                                                <i class="fas fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="restore_buses.php" onsubmit="return confirm('¿Restaurar este bus?');" style="display:inline-block;">
                                            <input type="hidden" name="id_bus" value="<?php echo htmlspecialchars((string) $bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-sm btn-restore" aria-label="Restaurar bus <?php echo $bus->id_bus; ?>">
                                                <i class="fas fa-trash-restore" aria-hidden="true"></i> Restaurar
                                            </button>
                                        </form>
                                        <span class="small text-muted d-block mt-1">
                                            <?php 
                                            echo "Eliminado por: " . htmlspecialchars($bus->usr_delete) . "<br>";
                                            echo "Fecha: " . date('d/m/Y H:i', strtotime($bus->fec_delete));
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
                <?php if (count($buses) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay buses registrados.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($buses as $bus): 
                        $eliminado = !empty($bus->fec_delete);
                        if ($eliminado) continue; // Ocultar eliminados de la vista móvil
                    ?>
                    <div class="col-12 mb-3">
                        <div class="bus-card card <?php echo $eliminado ? 'registro-eliminado' : ''; ?>">
                            <?php if ($eliminado): ?>
                                <span class="badge-eliminado">ELIMINADO</span>
                            <?php endif; ?>
                            
                            <!-- Card header - Contiene título del bus y botones -->
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <!-- Jerarquía: usar H3 con clase h5 para mantener estilo visual -->
                                <h3 class="mb-0 h5">Bus #<?php echo $bus->id_bus; ?></h3>
                                
                                <!-- Botones directamente en el header -->
                                <div class="d-flex gap-2">
                                    <?php if (!$eliminado): ?>
                                        <a class="btn btn-warning btn-sm" href="editar_buses.php?id_bus=<?php echo urlencode((string) $bus->id_bus); ?>" aria-label="Editar bus <?php echo $bus->id_bus; ?>">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </a>
                                        <form method="POST" action="eliminar_buses.php" onsubmit="return confirm('¿Seguro que deseas eliminar este bus?');" style="display:inline-block;">
                                            <input type="hidden" name="id_bus" value="<?php echo htmlspecialchars((string) $bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar bus <?php echo $bus->id_bus; ?>">
                                                <i class="fas fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="restore_buses.php" onsubmit="return confirm('¿Restaurar este bus?');" style="display:inline-block;">
                                            <input type="hidden" name="id_bus" value="<?php echo htmlspecialchars((string) $bus->id_bus, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-sm btn-restore" aria-label="Restaurar bus <?php echo $bus->id_bus; ?>">
                                                <i class="fas fa-trash-restore" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($eliminado): ?>
                                    <div class="small mt-1">
                                        <i class="fas fa-user-times me-1"></i> <?php echo htmlspecialchars($bus->usr_delete); ?>
                                        <i class="fas fa-clock ms-2 me-1"></i> <?php echo date('d/m/Y H:i', strtotime($bus->fec_delete)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Conductor: </strong>
                                        <span><?php echo $bus->id_usuario; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Capacidad: </strong>
                                        <span><?php echo $bus->capacidad_pasajeros; ?> pasajeros</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Año: </strong>
                                        <span><?php echo $bus->anio_fab; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Tipo: </strong>
                                        <span>
                                            <?php 
                                            $tipos = [
                                                'U' => 'Urbano',
                                                'M' => 'Municipal',
                                                'A' => 'Articulado',
                                                'E' => 'Especializado'
                                            ];
                                            echo $tipos[$bus->tipo_bus] ?? 'Desconocido';
                                            ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Estado: </strong>
                                        <span class="<?php echo $bus->ind_estado_buses === 'A' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php 
                                            $estados = [
                                                'L' => 'Libre',
                                                'F' => 'Fuera de servicio',
                                                'D' => 'Dañado',
                                                'S' => 'Suspendido',
                                                'T' => 'Taller',
                                                'A' => 'Activo'
                                            ];
                                            echo $estados[$bus->ind_estado_buses] ?? 'Desconocido';
                                            ?>
                                        </span>
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

