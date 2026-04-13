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
@yerson @2025
=================================================================
Listado de notificaciones de usuarios.
=================================================================
*/

define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']);

include_once "../base_de_datos.php";

function obtener_destino_notificacion(object $notificacion): string {
    if (!empty($notificacion->id_usuario)) {
        return 'Usuario: ' . ($notificacion->nom_usuario ?? '#' . $notificacion->id_usuario);
    }
    if (!empty($notificacion->id_rol)) {
        return 'Rol: ' . ($notificacion->nombre_rol ?? '#' . $notificacion->id_rol);
    }
    return 'Todos los suscritos';
}

$sentencia = $base_de_datos->query("
    SELECT n.id_notificacion, n.id_usuario, n.id_rol, n.titulo_notificacion, n.descr_notificacion,
           n.usr_delete, n.fec_delete,
        u.nom_usuario,
           r.nombre_rol
    FROM tab_notificaciones n
    LEFT JOIN tab_usuarios u ON n.id_usuario = u.id_usuario
    LEFT JOIN tab_roles r ON n.id_rol = r.id_rol
    ORDER BY n.id_notificacion DESC
");
$notificaciones = $sentencia->fetchAll(PDO::FETCH_OBJ);

$notificacionesEliminadas = array_filter($notificaciones, function($n) {
    return !empty($n->fec_delete);
});
$notificacionesActivas = array_filter($notificaciones, function($n) {
    return empty($n->fec_delete);
});
?>

<?php include_once "encab_notificaciones.php"; ?>
<main class="main-container">
<div class="row">
    <div class="col-12">
        <h1>Notificaciones de Usuarios</h1>

        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Activas: <?php echo count($notificacionesActivas); ?></span>
            <span class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;">Eliminadas: <?php echo count($notificacionesEliminadas); ?></span>
        </div>

        <!-- Modal flotante para notificaciones eliminadas -->
        <div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminadosLabel">Notificaciones Eliminadas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (count($notificacionesEliminadas) === 0): ?>
                  <div class="alert alert-info">No hay notificaciones eliminadas.</div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-bordered">
                      <thead class="table-danger">
                        <tr>
                          <th>ID</th>
                          <th>Destino</th>
                          <th>Título</th>
                          <th>Descripción</th>
                          <th>Eliminado por</th>
                          <th>Fecha</th>
                          <th>Acciones</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($notificacionesEliminadas as $notificacion):
                            $destino = obtener_destino_notificacion($notificacion);
                        ?>
                          <tr>
                            <td><?php echo (int)$notificacion->id_notificacion; ?></td>
                            <td><?php echo htmlspecialchars($destino); ?></td>
                            <td><?php echo htmlspecialchars(substr($notificacion->titulo_notificacion ?? '', 0, 30)) . (strlen($notificacion->titulo_notificacion ?? '') > 30 ? '...' : ''); ?></td>
                            <td><?php echo htmlspecialchars(substr($notificacion->descr_notificacion ?? '', 0, 40)) . (strlen($notificacion->descr_notificacion ?? '') > 40 ? '...' : ''); ?></td>
                            <td><?php echo htmlspecialchars($notificacion->usr_delete ?? ''); ?></td>
                            <td><?php echo $notificacion->fec_delete ? date('d/m/Y H:i', strtotime($notificacion->fec_delete)) : '-'; ?></td>
                            <td>
                              <form method="POST" action="restore_notificaciones.php" onsubmit="return confirm('¿Restaurar esta notificación?');" style="display:inline-block;">
                                <input type="hidden" name="id_notificacion" value="<?php echo $notificacion->id_notificacion; ?>">
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
                            <th>Destino</th>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($notificacionesActivas) === 0): ?>
                            <tr><td colspan="5" class="text-center">No hay notificaciones registradas</td></tr>
                        <?php else: ?>
                            <?php foreach($notificacionesActivas as $notificacion):
                                $destino = obtener_destino_notificacion($notificacion);
                            ?>
                            <tr>
                                <td><?php echo (int)$notificacion->id_notificacion; ?></td>
                                <td><?php echo htmlspecialchars($destino); ?></td>
                                <td><?php echo htmlspecialchars(substr($notificacion->titulo_notificacion ?? '', 0, 35)) . (strlen($notificacion->titulo_notificacion ?? '') > 35 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars(substr($notificacion->descr_notificacion ?? '', 0, 50)) . (strlen($notificacion->descr_notificacion ?? '') > 50 ? '...' : ''); ?></td>
                                <td class="actions-cell">
                                    <a class="btn btn-warning btn-sm" href="editar_notificaciones.php?id_notificacion=<?php echo $notificacion->id_notificacion; ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="eliminar_notificaciones.php" onsubmit="return confirm('¿Seguro que deseas eliminar esta notificación?');" style="display: inline-block;">
                                        <input type="hidden" name="id_notificacion" value="<?php echo $notificacion->id_notificacion; ?>">
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
                <?php if (count($notificacionesActivas) === 0 && count($notificacionesEliminadas) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay notificaciones registradas</div>
                    </div>
                <?php else: ?>
                    <?php foreach($notificacionesActivas as $notificacion): ?>
                    <div class="col-12 mb-3">
                        <div class="notificacion-card card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($notificacion->titulo_notificacion ?? 'Notificación #'.$notificacion->id_notificacion); ?></h5>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-warning btn-sm" href="editar_notificaciones.php?id_notificacion=<?php echo (int)$notificacion->id_notificacion; ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="eliminar_notificaciones.php" onsubmit="return confirm('¿Seguro que deseas eliminar esta notificación?');" style="display:inline-block;">
                                        <input type="hidden" name="id_notificacion" value="<?php echo (int)$notificacion->id_notificacion; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Destino:</strong>
                                        <span><?php echo htmlspecialchars(obtener_destino_notificacion($notificacion)); ?></span>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Descripción:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($notificacion->descr_notificacion ?? '')); ?>
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
<!-- Script para los Toasts de notificaciones -->
<script src="../../assets/js/notificaciones_toast.js"></script>