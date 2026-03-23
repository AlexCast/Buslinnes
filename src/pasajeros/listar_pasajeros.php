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
@yerson
@2025
=================================================================
Listado de pasajeros.
=================================================================
*/
// Validar JWT antes de mostrar contenido
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']); // Solo admin puede ver pasajeros

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->query('
    SELECT id_usuario AS id_pasajero,
           nom_pasajero,
           email_pasajero,
           usr_delete,
           fec_delete
    FROM tab_pasajeros 
    ORDER BY nom_pasajero DESC
');
$pasajeros = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Contar pasajeros eliminados
$pasajerosEliminados = array_filter($pasajeros, function($pasajero) {
    return !empty($pasajero->fec_delete);
});
?>

<?php include_once "encab_pasajeros.php"; ?>
<main class="main-container">
<div class="row">
    <div class="col-12">
        <!-- Encabezado principal (H1) mantenido para jerarquía -->
        <h1>Pasajeros Registrados</h1>
        
        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Total: <?php echo count($pasajeros); ?> pasajeros</span>
            <!-- Botón accesible para abrir modal de eliminados -->
            <button type="button" class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;" aria-haspopup="dialog" aria-controls="modalEliminados">Eliminados: <?php echo count($pasajerosEliminados); ?></button>
        </div>

        <!-- Modal flotante para pasajeros eliminados -->
        <div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <!-- Jerarquía: H2 con clase h5 para mantener estilo visual -->
                                <h2 class="modal-title h5" id="modalEliminadosLabel">Pasajeros Eliminados</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (count($pasajerosEliminados) === 0): ?>
                  <div class="alert alert-info">No hay pasajeros eliminados.</div>
                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" aria-labelledby="modalEliminadosLabel">
                                            <caption style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de pasajeros eliminados</caption>
                      <thead class="table-danger">
                        <tr>
                                                    <th scope="col">ID</th>
                                                    <th scope="col">Nombre</th>
                                                    <th scope="col">Correo</th>
                                                    <th scope="col">Eliminado por</th>
                                                    <th scope="col">Fecha Eliminación</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($pasajerosEliminados as $registro): ?>
                          <tr>
                                                        <td data-label="ID"><?php echo $registro->id_pasajero; ?></td>
                                                        <td data-label="Nombre"><?php echo htmlspecialchars($registro->nom_pasajero); ?></td>
                                                        <td data-label="Correo"><?php echo $registro->email_pasajero; ?></td>
                                                        <td data-label="Eliminado por"><?php echo htmlspecialchars($registro->usr_delete); ?></td>
                                                        <td data-label="Fecha Eliminación"><?php echo date('d/m/Y H:i', strtotime($registro->fec_delete)); ?></td>
                                                        <td data-label="Acciones">
                                                            <form method="POST" action="restore_pasajeros.php" onsubmit="return confirm('¿Restaurar este pasajero?');" style="display:inline-block;">
                                                                <input type="hidden" name="id_pasajero" value="<?php echo $registro->id_pasajero; ?>">
                                                                <button type="submit" class="btn btn-sm btn-restore" aria-label="Restaurar pasajero <?php echo htmlspecialchars($registro->nom_pasajero . ' ' . $registro->email_pasajero); ?>">
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
                <table class="table table-hover" aria-describedby="tablaPasajerosCaption">
                    <caption id="tablaPasajerosCaption" style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de pasajeros registrados</caption>
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Correo</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pasajeros) === 0): ?>
                            <tr><td colspan="5" class="text-center">No hay pasajeros registrados</td></tr>
                        <?php else: ?>
                            <?php foreach($pasajeros as $pasajero): 
                                $eliminado = !empty($pasajero->fec_delete);
                                if ($eliminado) continue; // Ocultar eliminados de la tabla principal
                            ?>
                            <tr>
                                <td data-label="ID"><?php echo $pasajero->id_pasajero; ?></td>
                                <td data-label="Nombre"><?php echo htmlspecialchars($pasajero->nom_pasajero); ?></td>
                                <td data-label="Correo">
                                    <span class="badge bg-info"><?php echo $pasajero->email_pasajero; ?></span>
                                </td>
                                <td class="actions-cell" data-label="Acciones">
                                    <a class="btn btn-warning btn-sm" href="editar_pasajeros.php?id_pasajero=<?php echo $pasajero->id_pasajero; ?>" aria-label="Editar pasajero <?php echo htmlspecialchars($pasajero->nom_pasajero . ' ' . $pasajero->email_pasajero); ?>">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="eliminar_pasajeros.php" onsubmit="return confirm('¿Seguro que deseas eliminar este pasajero?');" style="display: inline-block;">
                                        <input type="hidden" name="id_pasajero" value="<?php echo $pasajero->id_pasajero; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar pasajero <?php echo htmlspecialchars($pasajero->nom_pasajero . ' ' . $pasajero->email_pasajero); ?>">
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
                <?php if (count($pasajeros) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay pasajeros registrados</div>
                    </div>
                <?php else: ?>
                    <?php foreach($pasajeros as $pasajero): 
                        $eliminado = !empty($pasajero->fec_delete);
                    ?>
                    <div class="col-12 mb-3">
                        <div class="pasajero-card card <?php echo $eliminado ? 'registro-eliminado' : ''; ?>">
                            <?php if ($eliminado): ?>
                                <span class="badge-eliminado">ELIMINADO</span>
                            <?php endif; ?>
                            
                            <!-- Card header - Contiene título del pasajero y botones -->
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h3 class="mb-0 h5"><?php echo htmlspecialchars($pasajero->nom_pasajero . ' ' . $pasajero->email_pasajero); ?></h3>
                                
                                <!-- Botones directamente en el header -->
                                <div class="d-flex gap-2">
                                    <?php if (!$eliminado): ?>
                                        <a class="btn btn-warning btn-sm" href="editar_pasajeros.php?id_pasajero=<?php echo $pasajero->id_pasajero; ?>" aria-label="Editar pasajero <?php echo htmlspecialchars($pasajero->nom_pasajero . ' ' . $pasajero->email_pasajero); ?>">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </a>
                                        <form method="POST" action="eliminar_pasajeros.php" onsubmit="return confirm('¿Seguro que deseas eliminar este pasajero?');" style="display:inline-block;">
                                            <input type="hidden" name="id_pasajero" value="<?php echo $pasajero->id_pasajero; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar pasajero <?php echo htmlspecialchars($pasajero->nom_pasajero . ' ' . $pasajero->email_pasajero); ?>">
                                                <i class="fas fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="restore_pasajeros.php" onsubmit="return confirm('¿Restaurar este pasajero?');" style="display:inline-block;">
                                            <input type="hidden" name="id_pasajero" value="<?php echo $pasajero->id_pasajero; ?>">
                                            <button type="submit" class="btn btn-sm btn-restore" aria-label="Restaurar pasajero <?php echo htmlspecialchars($pasajero->nom_pasajero . ' ' . $pasajero->email_pasajero); ?>">
                                                <i class="fas fa-trash-restore" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($eliminado): ?>
                                    <div class="small mt-1">
                                        <i class="fas fa-user-times me-1"></i> <?php echo htmlspecialchars($pasajero->usr_delete); ?>
                                        <i class="fas fa-clock ms-2 me-1"></i> <?php echo date('d/m/Y', strtotime($pasajero->fec_delete)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>ID: </strong>
                                        <span><?php echo $pasajero->id_pasajero; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Nombre: </strong>
                                        <span><?php echo htmlspecialchars($pasajero->nom_pasajero); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Correo: </strong>
                                        <span class="badge bg-info"><?php echo $pasajero->email_pasajero; ?></span>
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