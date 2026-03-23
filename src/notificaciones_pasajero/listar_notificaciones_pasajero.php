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

// Listado de notificaciones visibles para el pasajero autenticado.
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
$jwt = validarTokenJWT(['pasajero']);

$idUsuario = isset($jwt->id_usuario) ? (int) $jwt->id_usuario : 0;
$idRol = isset($jwt->id_rol) ? (int) $jwt->id_rol : 0;

include_once __DIR__ . '/../base_de_datos.php';

// Traer notificaciones dirigidas al usuario o a su rol.
$sentencia = $base_de_datos->prepare('
    SELECT n.id_notificacion,
           n.id_usuario,
           n.id_rol,
           n.titulo_notificacion,
           n.descr_notificacion,
           n.fec_insert,
           u.nombre AS nom_usuario,
           r.nombre_rol
    FROM tab_notificaciones n
    LEFT JOIN tab_usuarios u ON n.id_usuario = u.id_usuario
    LEFT JOIN tab_roles r ON n.id_rol = r.id_rol
    WHERE n.fec_delete IS NULL
      AND (
            n.id_usuario = :id_usuario
         OR (:id_rol > 0 AND n.id_rol = :id_rol)
      )
    ORDER BY n.id_notificacion DESC
');
$sentencia->execute([
    ':id_usuario' => $idUsuario,
    ':id_rol' => $idRol,
]);
$notificaciones = $sentencia->fetchAll(PDO::FETCH_OBJ);
?>

<?php include_once __DIR__ . '/encab_notificaciones_pasajero.php'; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-1">Mis notificaciones</h1>
                    <span class="badge bg-primary p-2">Total: <?php echo count($notificaciones); ?></span>
                </div>
                <div class="card-body">
                    <?php if (count($notificaciones) === 0): ?>
                        <div class="alert alert-info mb-0">
                            No tienes notificaciones pendientes.
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($notificaciones as $notif): ?>
                                <div class="list-group-item list-group-item-action mb-3 rounded shadow-sm border-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h4 class="text-primary" style="font-weight:700; margin: 0 0 2px 0;">
                                                <?php echo htmlspecialchars($notif->titulo_notificacion ?? ''); ?>
                                            </h4>
                                            <p class="text-muted" style="white-space:pre-line; margin: 0 0 2px 0; line-height: 1.35;">
                                                <?php echo htmlspecialchars($notif->descr_notificacion ?? ''); ?>
                                            </p>
                                            <div class="d-flex flex-wrap gap-3 small text-secondary">
                                                <?php if (!empty($notif->fec_insert)): ?>
                                                    <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notif->fec_insert)); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../pie.php'; ?>
