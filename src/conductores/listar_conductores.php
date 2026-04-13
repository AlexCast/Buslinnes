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
Listado de conductores.
=================================================================
*/
// Validar JWT antes de mostrar contenido
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']); // Solo admin y conductor pueden ver conductores

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->query('
    SELECT id_usuario,
           nom_conductor,
           ape_conductor,
           email_conductor AS tel_conductor,
           licencia_conductor,
           usr_delete,
           fec_delete
    FROM tab_conductores 
    ORDER BY nom_conductor DESC
');
$conductores = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Contar conductores eliminados
$conductoresEliminados = array_filter($conductores, function($conductor) {
    return !empty($conductor->fec_delete);
});
?>

<?php include_once "encab_conductores.php"; ?>
<main class="main-container">
<div class="row">
    <div class="col-12">
        <h1>Conductores Registrados</h1>
        
        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Total: <?php echo count($conductores); ?> conductores</span>
            <span class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;">Eliminados: <?php echo count($conductoresEliminados); ?></span>
        </div>

        <!-- Modal flotante para conductores eliminados -->
        <div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminadosLabel">Conductores Eliminados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (count($conductoresEliminados) === 0): ?>
                  <div class="alert alert-info">No hay conductores eliminados.</div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-bordered">
                      <thead class="table-danger">
                        <tr>
                          <th>ID</th>
                          <th>Nombre</th>
                          <th>Apellido</th>
                          <th>Teléfono</th>
                          <th>Licencia</th>
                          <th>Eliminado por</th>
                          <th>Fecha</th>
                          <th>Acción</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($conductoresEliminados as $conductor): ?>
                          <tr>
                            <td><?php echo $conductor->id_usuario; ?></td>
                            <td><?php echo $conductor->nom_conductor; ?></td>
                            <td><?php echo $conductor->ape_conductor; ?></td>
                            <td><?php echo $conductor->tel_conductor; ?></td>
                            <td><?php echo $conductor->licencia_conductor; ?></td>
                            <td><?php echo htmlspecialchars($conductor->usr_delete); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($conductor->fec_delete)); ?></td>
                            <td>
                              <form method="POST" action="restore_conductores.php" onsubmit="return confirm('¿Restaurar este conductor?');" style="display:inline-block;">
                                <input type="hidden" name="id_usuario" value="<?php echo $conductor->id_usuario; ?>">
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
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Teléfono</th>
                            <th>Licencia</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($conductores) === 0): ?>
                            <tr><td colspan="6" class="text-center">No hay conductores registrados</td></tr>
                        <?php else: ?>
                            <?php foreach($conductores as $conductor): 
                                $eliminado = !empty($conductor->fec_delete);
                                if ($eliminado) continue; // Ocultar eliminados de la tabla principal
                            ?>
                            <tr>
                                <td><?php echo $conductor->id_usuario; ?></td>
                                <td><?php echo $conductor->nom_conductor; ?></td>
                                <td><?php echo $conductor->ape_conductor; ?></td>
                                <td><?php echo $conductor->tel_conductor; ?></td>
                                <td><span class="badge bg-info"><?php echo $conductor->licencia_conductor; ?></span></td>
                                <td class="actions-cell">
                                    <a class="btn btn-warning btn-sm" href="editar_conductores.php?id_usuario=<?php echo $conductor->id_usuario; ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="eliminar_conductores.php" onsubmit="return confirm('¿Seguro que deseas eliminar este conductor?');" style="display: inline-block;">
                                        <input type="hidden" name="id_usuario" value="<?php echo $conductor->id_usuario; ?>">
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
                <?php if (count($conductores) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay conductores registrados</div>
                    </div>
                <?php else: ?>
                    <?php foreach($conductores as $conductor): 
                        $eliminado = !empty($conductor->fec_delete);
                        if ($eliminado) continue; // Ocultar eliminados de la vista móvil
                    ?>
                    <div class="col-12 mb-3">
                        <div class="conductor-card card">
                            
                            <!-- Card header - Contiene título del conductor y botones -->
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo $conductor->nom_conductor . ' ' . $conductor->ape_conductor; ?></h5>
                                
                                <!-- Botones directamente en el header -->
                                <div class="d-flex gap-2">
                                    <a class="btn btn-warning btn-sm" href="editar_conductores.php?id_usuario=<?php echo $conductor->id_usuario; ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="eliminar_conductores.php" onsubmit="return confirm('¿Seguro que deseas eliminar este conductor?');" style="display:inline-block;">
                                        <input type="hidden" name="id_usuario" value="<?php echo $conductor->id_usuario; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>ID: </strong>
                                        <span><?php echo $conductor->id_usuario; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Teléfono: </strong>
                                        <span><?php echo $conductor->tel_conductor; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Licencia: </strong>
                                        <span class="badge bg-info"><?php echo $conductor->licencia_conductor; ?></span>
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

