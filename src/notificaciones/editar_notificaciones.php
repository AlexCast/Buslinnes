<?php
header('Content-Type: text/html; charset=utf-8');

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
Editar notificación. Destino: un usuario O un rol.
@yerson @2025
*/
if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']);

if (!isset($_GET["id_notificacion"])) {
    header("Location: listar_notificaciones.php");
    exit();
}

$id_notificacion_txt = trim((string) $_GET["id_notificacion"]);
if (!preg_match('/^[0-9]+$/', $id_notificacion_txt) || (int) $id_notificacion_txt <= 0) {
    header("Location: listar_notificaciones.php?error=ID%20invalido");
    exit();
}
$id_notificacion = (int) $id_notificacion_txt;
include_once "../base_de_datos.php";

$sentencia = $base_de_datos->prepare("
    SELECT n.*, u.nom_usuario, r.nombre_rol
    FROM tab_notificaciones n
    LEFT JOIN tab_usuarios u ON n.id_usuario = u.id_usuario
    LEFT JOIN tab_roles r ON n.id_rol = r.id_rol
    WHERE n.id_notificacion = ? AND n.fec_delete IS NULL
");
$sentencia->execute([$id_notificacion]);
$notificacion = $sentencia->fetchObject();

if (!$notificacion) {
    echo "No existe la notificación o está eliminada.";
    exit();
}

$tiene_usuario = !empty($notificacion->id_usuario);
$tiene_rol     = !empty($notificacion->id_rol);

$sentencia_usuarios = $base_de_datos->query("
    SELECT id_usuario, nom_usuario
    FROM tab_usuarios
    WHERE fec_delete IS NULL
    ORDER BY nom_usuario
");
$usuarios = $sentencia_usuarios->fetchAll(PDO::FETCH_OBJ);

$sentencia_roles = $base_de_datos->query("
    SELECT id_rol, nombre_rol
    FROM tab_roles
    WHERE fec_delete IS NULL
    ORDER BY nombre_rol
");
$roles = $sentencia_roles->fetchAll(PDO::FETCH_OBJ);
?>

<?php include_once "encab_notificaciones.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Notificación</h1>
                    <p class="mb-0 small">ID: <?php echo (int) $notificacion->id_notificacion; ?></p>
                </div>
                <div class="card-body">
                    <form action="update_notificaciones.php" method="POST" id="formNotif">
                        <input type="hidden" name="id_notificacion" value="<?php echo (int) $notificacion->id_notificacion; ?>">

                        <div class="mb-4">
                            <label class="form-label fw-bold">Destino de la notificación</label>
                            <div class="d-flex flex-wrap" style="column-gap: 2rem; row-gap: 0.75rem;">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_destino" id="destino_usuario" value="usuario" <?php echo $tiene_usuario ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="destino_usuario">Un usuario</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_destino" id="destino_rol" value="rol" <?php echo $tiene_rol ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="destino_rol">Un rol</label>
                                </div>
                            </div>
                        </div>

                        <div id="bloque_usuario" class="mb-3" style="display: <?php echo $tiene_usuario ? 'block' : 'none'; ?>;">
                            <label for="id_usuario" class="form-label">Usuario</label>
                            <select name="id_usuario" id="id_usuario" class="form-select">
                                <option value="">-- Seleccione usuario --</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?php echo (int) $u->id_usuario; ?>" <?php echo ($tiene_usuario && (int)$u->id_usuario === (int)$notificacion->id_usuario) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($u->nom_usuario); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="bloque_rol" class="mb-3" style="display: <?php echo $tiene_rol ? 'block' : 'none'; ?>;">
                            <label for="id_rol" class="form-label">Rol</label>
                            <select name="id_rol" id="id_rol" class="form-select">
                                <option value="">-- Seleccione rol --</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?php echo (int) $r->id_rol; ?>" <?php echo ($tiene_rol && (int)$r->id_rol === (int)$notificacion->id_rol) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($r->nombre_rol); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="titulo_notificacion" class="form-label">Título</label>
                            <input type="text" name="titulo_notificacion" id="titulo_notificacion" class="form-control" required minlength="3" maxlength="120"
                                value="<?php echo htmlspecialchars($notificacion->titulo_notificacion ?? ''); ?>"
                                placeholder="Título de la notificación">
                        </div>
                        <div style="margin-bottom: 1.25rem;">
                            <label for="descr_notificacion" class="form-label">Descripción</label>
                            <textarea name="descr_notificacion" id="descr_notificacion" class="form-control" rows="5" required minlength="5" maxlength="2000"
                                placeholder="Descripción de la notificación"><?php echo htmlspecialchars($notificacion->descr_notificacion ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2" style="margin-top: 1.25rem;">
                            <a href="listar_notificaciones.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var destinoUsuario = document.getElementById('destino_usuario');
    var destinoRol = document.getElementById('destino_rol');
    var bloqueUsuario = document.getElementById('bloque_usuario');
    var bloqueRol = document.getElementById('bloque_rol');
    var selectUsuario = document.getElementById('id_usuario');
    var selectRol = document.getElementById('id_rol');

    function actualizarBloques() {
        if (destinoUsuario.checked) {
            bloqueUsuario.style.display = 'block';
            bloqueRol.style.display = 'none';
            selectRol.value = '';
            selectUsuario.removeAttribute('required');
            selectRol.removeAttribute('required');
            selectUsuario.setAttribute('required', 'required');
        } else {
            bloqueUsuario.style.display = 'none';
            bloqueRol.style.display = 'block';
            selectUsuario.value = '';
            selectUsuario.removeAttribute('required');
            selectRol.removeAttribute('required');
            selectRol.setAttribute('required', 'required');
        }
    }
    destinoUsuario.addEventListener('change', actualizarBloques);
    destinoRol.addEventListener('change', actualizarBloques);
    actualizarBloques();
});
</script>
<?php include_once "../pie.php"; ?>



