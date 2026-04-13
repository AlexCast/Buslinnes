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
validarTokenJWT(['admin']); // Solo admin puede ver usuarios roles

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->query("
    SELECT ur.id_usuario,
      u.nom_usuario AS nombre,
      ur.id_rol,
      r.nombre_rol,
           ur.fec_insert, ur.usr_insert, ur.fec_delete, ur.usr_delete
    FROM tab_usuarios_roles ur
    JOIN tab_usuarios u ON ur.id_usuario = u.id_usuario
    JOIN tab_roles r ON ur.id_rol = r.id_rol
    ORDER BY ur.fec_insert DESC
");
$usuarios_roles = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Contar eliminados
$eliminados = array_filter($usuarios_roles, function($ur) {
    return !empty($ur->fec_delete);
});
?>

<?php include_once "encab_usuarios_roles.php"; ?>
<main class="main-container">
<div class="row">
    <div class="col-12">
        <!-- Encabezado principal (H1) mantenido para jerarquía -->
        <h1>Asignación de Roles a Usuarios</h1>
        
        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Total: <?php echo count($usuarios_roles); ?> asignaciones</span>
          <!-- Botón accesible para abrir modal de eliminados -->
          <button type="button" class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;" aria-haspopup="dialog" aria-controls="modalEliminados">Eliminados: <?php echo count($eliminados); ?></button>
        </div>
        
        <!-- Modal flotante para eliminados -->
        <div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <!-- Jerarquía: H2 con clase h5 para mantener estilo visual -->
                <h2 class="modal-title h5" id="modalEliminadosLabel">Asignaciones Eliminadas</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (count($eliminados) === 0): ?>
                  <div class="alert alert-info">No hay asignaciones eliminadas.</div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-bordered" aria-labelledby="modalEliminadosLabel">
                      <caption style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de asignaciones de usuario a rol eliminadas</caption>
                      <thead class="table-danger">
                        <tr>
                          <th scope="col">Usuario</th>
                          <th scope="col">Rol</th>
                          <th scope="col">Fecha Eliminación</th>
                          <th scope="col">Usuario Eliminó</th>
                          <th scope="col">Acciones</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($eliminados as $ur): ?>
                        <tr>
                          <td data-label="Usuario"><?php echo $ur->nombre ?></td>
                          <td data-label="Rol"><?php echo $ur->nombre_rol ?></td>
                          <td data-label="Fecha Eliminación"><?php echo $ur->fec_delete ?></td>
                          <td data-label="Usuario Eliminó"><?php echo htmlspecialchars($ur->usr_delete) ?></td>
                          <td class="actions-cell" data-label="Acciones">
                            <form method="POST" action="restore_usuarios_roles.php" onsubmit="return confirm('¿Restaurar esta asignación?');" style="display:inline-block;">
                                <input type="hidden" name="id_usuario" value="<?php echo (int) $ur->id_usuario; ?>">
                                <input type="hidden" name="id_rol" value="<?php echo (int) $ur->id_rol; ?>">
                                <button type="submit" class="btn btn-warning btn-sm" aria-label="Restaurar asignación del usuario <?php echo htmlspecialchars($ur->nombre); ?> al rol <?php echo htmlspecialchars($ur->nombre_rol); ?>">
                                    <i class="fa fa-undo" aria-hidden="true"></i> Restaurar
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
            <table class="table table-bordered table-striped" aria-describedby="tablaUsuariosRolesCaption">
              <caption id="tablaUsuariosRolesCaption" style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de asignaciones de roles a usuarios</caption>
                  <thead class="thead-dark">
                      <tr>
                  <th scope="col">Usuario</th>
                  <th scope="col">Rol</th>
                  <th scope="col">Fecha Inserción</th>
                  <th scope="col">Usuario Insertó</th>
                  <th scope="col">Acciones</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php if (count($usuarios_roles) === 0 || count($usuarios_roles) === count($eliminados)): ?>
                          <tr><td colspan="5" class="text-center">No hay asignaciones registradas</td></tr>
                      <?php else: ?>
                          <?php foreach($usuarios_roles as $ur): ?>
                              <?php if(empty($ur->fec_delete)): ?>
                              <tr>
                        <td data-label="Usuario"><?php echo htmlspecialchars($ur->nombre) ?></td>
                        <td data-label="Rol"><?php echo htmlspecialchars($ur->nombre_rol) ?></td>
                        <td data-label="Fecha Inserción"><?php echo $ur->fec_insert ?></td>
                        <td data-label="Usuario Insertó"><?php echo htmlspecialchars($ur->usr_insert) ?></td>
                        <td class="actions-cell" data-label="Acciones">
                          <a class="btn btn-warning btn-sm" href="editar_usuarios_roles.php?id_usuario=<?php echo $ur->id_usuario; ?>&id_rol=<?php echo $ur->id_rol; ?>" aria-label="Editar asignación del usuario <?php echo htmlspecialchars($ur->nombre); ?> al rol <?php echo htmlspecialchars($ur->nombre_rol); ?>">
                            <i class="fas fa-edit" aria-hidden="true"></i>
                                      </a>
                                      <form method="POST" action="eliminar_usuarios_roles.php" onsubmit="return confirm('¿Seguro que deseas eliminar esta asignación?');" style="display:inline-block;">
                                          <input type="hidden" name="id_usuario" value="<?php echo $ur->id_usuario; ?>">
                                          <input type="hidden" name="id_rol" value="<?php echo $ur->id_rol; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar asignación del usuario <?php echo htmlspecialchars($ur->nombre); ?> al rol <?php echo htmlspecialchars($ur->nombre_rol); ?>">
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
                <?php if (count($usuarios_roles) === 0 || count($usuarios_roles) === count($eliminados)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay asignaciones registradas</div>
                    </div>
                <?php else: ?>
                    <?php foreach($usuarios_roles as $ur): 
                        if (!empty($ur->fec_delete)) continue;
                    ?>
                    <div class="col-12 mb-3">
                        <div class="ur-card card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h3 class="mb-0 h5"><?php echo htmlspecialchars($ur->nombre); ?> - <?php echo htmlspecialchars($ur->nombre_rol); ?></h3>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-warning btn-sm" href="editar_usuarios_roles.php?id_usuario=<?php echo $ur->id_usuario; ?>&id_rol=<?php echo $ur->id_rol; ?>" aria-label="Editar asignación">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="eliminar_usuarios_roles.php" onsubmit="return confirm('¿Seguro que deseas eliminar esta asignación?');" style="display:inline-block;">
                                        <input type="hidden" name="id_usuario" value="<?php echo $ur->id_usuario; ?>">
                                        <input type="hidden" name="id_rol" value="<?php echo $ur->id_rol; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar asignación">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Fecha Inserción:</strong>
                                        <span><?php echo $ur->fec_insert; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Usuario Insertó:</strong>
                                        <span><?php echo htmlspecialchars($ur->usr_insert); ?></span>
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
