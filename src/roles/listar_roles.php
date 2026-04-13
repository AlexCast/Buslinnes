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
validarTokenJWT(['admin']); // Solo admin puede ver roles

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->query("
    SELECT id_rol, nombre_rol, fec_insert, usr_insert, 
           usr_delete, fec_delete
    FROM tab_roles
    ORDER BY fec_insert DESC
");
$roles = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Contar roles eliminados
$rolesEliminados = array_filter($roles, function($rol) {
    return !empty($rol->fec_delete);
});
?>

<?php include_once "encab_roles.php"; ?>
<main class="main-container">
<div class="row">
    <div class="col-12">
        <!-- Encabezado principal (H1) mantenido para jerarquía -->
        <h1>Roles Registrados</h1>
        
        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Total: <?php echo count($roles); ?> roles</span>
          <!-- Botón accesible para abrir modal de eliminados -->
          <button type="button" class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;" aria-haspopup="dialog" aria-controls="modalEliminados">Eliminados: <?php echo count($rolesEliminados); ?></button>
        </div>
        
        <!-- Modal flotante para roles eliminados -->
        <div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <!-- Jerarquía: H2 con clase h5 para mantener estilo visual -->
                <h2 class="modal-title h5" id="modalEliminadosLabel">Roles Eliminados</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (count($rolesEliminados) === 0): ?>
                  <div class="alert alert-info">No hay roles eliminados.</div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-bordered" aria-labelledby="modalEliminadosLabel">
                      <caption style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de roles eliminados</caption>
                      <thead class="table-danger">
                        <tr>
                          <th scope="col">ID</th>
                          <th scope="col">Nombre Rol</th>
                          <th scope="col">Fecha Eliminación</th>
                          <th scope="col">Usuario Eliminó</th>
                          <th scope="col">Acciones</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($rolesEliminados as $rol): ?>
                        <tr>
                          <td data-label="ID"><?php echo $rol->id_rol ?></td>
                          <td data-label="Nombre Rol"><?php echo $rol->nombre_rol ?></td>
                          <td data-label="Fecha Eliminación"><?php echo $rol->fec_delete ?></td>
                          <td data-label="Usuario Eliminó"><?php echo htmlspecialchars($rol->usr_delete) ?></td>
                          <td class="actions-cell" data-label="Acciones">
                            <a class="btn btn-warning btn-sm" href="<?php echo "restore_roles.php?id_rol=" . $rol->id_rol?>" aria-label="Restaurar rol <?php echo $rol->id_rol; ?>">
                              <i class="fa fa-undo" aria-hidden="true"></i> Restaurar
                            </a>
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
            <table class="table table-bordered table-striped" aria-describedby="tablaRolesCaption">
              <caption id="tablaRolesCaption" style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de roles registrados</caption>
                  <thead class="thead-dark">
                      <tr>
                  <th scope="col">ID</th>
                  <th scope="col">Nombre Rol</th>
                  <th scope="col">Fecha Inserción</th>
                  <th scope="col">Usuario Insertó</th>
                  <th scope="col">Acciones</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php if (count($roles) === 0 || count($roles) === count($rolesEliminados)): ?>
                          <tr><td colspan="5" class="text-center">No hay roles registrados</td></tr>
                      <?php else: ?>
                          <?php foreach($roles as $rol): ?>
                              <?php if(empty($rol->fec_delete)): ?>
                              <tr>
                                <td data-label="ID"><?php echo $rol->id_rol ?></td>
                                <td data-label="Nombre Rol"><?php echo htmlspecialchars($rol->nombre_rol) ?></td>
                                <td data-label="Fecha Inserción"><?php echo $rol->fec_insert ?></td>
                                <td data-label="Usuario Insertó"><?php echo htmlspecialchars($rol->usr_insert) ?></td>
                                <td class="actions-cell" data-label="Acciones">
                                  <a class="btn btn-warning btn-sm" href="editar_roles.php?id_rol=<?php echo $rol->id_rol; ?>" aria-label="Editar rol <?php echo $rol->id_rol; ?>">
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                      </a>
                                      <form method="POST" action="eliminar_roles.php" onsubmit="return confirm('¿Seguro que deseas eliminar este rol?');" style="display:inline-block;">
                                          <input type="hidden" name="id_rol" value="<?php echo $rol->id_rol; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar rol <?php echo $rol->id_rol; ?>">
                                      <i class="fas fa-trash" aria-hidden="true"></i>
                                          </button>
                                      </form>
                                  </td>
                              </tr>
                              <?php endif; ?>
                          <?php endforeach; ?>
                      <?php endif; ?>
                  </tbody>
              </table>
          </div>
        </div>

        <div class="mobile-view">
            <div class="row">
                <?php if (count($roles) === 0 || count($roles) === count($rolesEliminados)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay roles registrados</div>
                    </div>
                <?php else: ?>
                    <?php foreach($roles as $rol): 
                        if (!empty($rol->fec_delete)) continue;
                    ?>
                    <div class="col-12 mb-3">
                        <div class="rol-card card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h3 class="mb-0 h5"><?php echo htmlspecialchars($rol->nombre_rol); ?></h3>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-warning btn-sm" href="editar_roles.php?id_rol=<?php echo $rol->id_rol; ?>" aria-label="Editar rol <?php echo $rol->id_rol; ?>">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="eliminar_roles.php" onsubmit="return confirm('¿Seguro que deseas eliminar este rol?');" style="display:inline-block;">
                                        <input type="hidden" name="id_rol" value="<?php echo $rol->id_rol; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar rol <?php echo $rol->id_rol; ?>">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>ID:</strong>
                                        <span><?php echo $rol->id_rol; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Fecha Inserción:</strong>
                                        <span><?php echo $rol->fec_insert; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Usuario Insertó:</strong>
                                        <span><?php echo htmlspecialchars($rol->usr_insert); ?></span>
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
<?php include_once "../pie.php" ?>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/modalEliminados.js"></script>
