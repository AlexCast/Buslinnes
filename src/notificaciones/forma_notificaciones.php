    <?php
header('Content-Type: text/html; charset=utf-8');

/*
Formulario para registrar notificación.
Opción: enviar a un usuario O a un rol específico.
@yerson @2025
*/
if (!defined('VALIDAR_JWT_MANUAL')) define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']);

include_once "../base_de_datos.php";

$sentencia_usuarios = $base_de_datos->query("
    SELECT id_usuario, nombre
    FROM tab_usuarios
    WHERE fec_delete IS NULL
    ORDER BY nombre
");
$usuarios = $sentencia_usuarios->fetchAll(PDO::FETCH_OBJ);

$sentencia_roles = $base_de_datos->query("
    SELECT id_rol, nombre_rol
    FROM tab_roles
    WHERE fec_delete IS NULL
    ORDER BY nombre_rol
");
$roles = $sentencia_roles->fetchAll(PDO::FETCH_OBJ);

// Siguiente ID para nueva notificación (evitar colisiones)
$seq = $base_de_datos->query("SELECT COALESCE(MAX(id_notificacion), 0) + 1 AS next_id FROM tab_notificaciones");
$next_id = (int) $seq->fetch(PDO::FETCH_OBJ)->next_id;
?>

<?php include_once "encab_notificaciones.php"; ?>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Registrar Nueva Notificación</h1>
                    <p class="mb-0 small">Enviar a un usuario o a un rol</p>
                </div>
                <div class="card-body">
                    <?php if (!empty($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    <?php endif; ?>
                    <form action="insertar_notificaciones.php" method="POST" id="formNotif">
                        <input type="hidden" name="id_notificacion" value="<?php echo $next_id; ?>">

                        <div class="mb-4">
                            <label class="form-label fw-bold">Destino de la notificación</label>
                            <div class="d-flex gap-4 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_destino" id="destino_todos" value="todos" checked>
                                    <label class="form-check-label" for="destino_todos"><strong>Todos los suscritos</strong> (Push a todos)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_destino" id="destino_usuario" value="usuario">
                                    <label class="form-check-label" for="destino_usuario">Un usuario</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_destino" id="destino_rol" value="rol">
                                    <label class="form-check-label" for="destino_rol">Un rol</label>
                                </div>
                            </div>
                        </div>

                        <div id="bloque_usuario" class="mb-3" style="display: none;">
                            <label for="id_usuario" class="form-label">Usuario</label>
                            <select name="id_usuario" id="id_usuario" class="form-select">
                                <option value="">-- Seleccione usuario --</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?php echo (int) $u->id_usuario; ?>"><?php echo htmlspecialchars($u->nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="bloque_rol" class="mb-3" style="display: none;">
                            <label for="id_rol" class="form-label">Rol</label>
                            <select name="id_rol" id="id_rol" class="form-select">
                                <option value="">-- Seleccione rol --</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?php echo (int) $r->id_rol; ?>"><?php echo htmlspecialchars($r->nombre_rol); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="titulo_notificacion" class="form-label">Título</label>
                            <input type="text" name="titulo_notificacion" id="titulo_notificacion" class="form-control" required
                                placeholder="Título de la notificación">
                        </div>
                        <div class="mb-3">
                            <label for="descr_notificacion" class="form-label">Descripción</label>
                            <textarea name="descr_notificacion" id="descr_notificacion" class="form-control" rows="5" required
                                placeholder="Describa la notificación"></textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enviar_push" name="enviar_push" value="1" checked>
                                <label class="form-check-label" for="enviar_push">
                                    <strong>📱 Enviar notificación push</strong>
                                    <small class="d-block text-muted">Se enviará una notificación al navegador de los usuarios seleccionados</small>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="listar_notificaciones.php" class="btn btn-secondary text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Registrar Notificación
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
    var destinoTodos = document.getElementById('destino_todos');
    var destinoUsuario = document.getElementById('destino_usuario');
    var destinoRol = document.getElementById('destino_rol');
    var bloqueUsuario = document.getElementById('bloque_usuario');
    var bloqueRol = document.getElementById('bloque_rol');
    var selectUsuario = document.getElementById('id_usuario');
    var selectRol = document.getElementById('id_rol');

    function actualizarBloques() {
        if (destinoTodos.checked) {
            bloqueUsuario.style.display = 'none';
            bloqueRol.style.display = 'none';
            selectUsuario.value = '';
            selectRol.value = '';
            selectUsuario.removeAttribute('required');
            selectRol.removeAttribute('required');
        } else if (destinoUsuario.checked) {
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
    destinoTodos.addEventListener('change', actualizarBloques);
    destinoUsuario.addEventListener('change', actualizarBloques);
    destinoRol.addEventListener('change', actualizarBloques);
    actualizarBloques();
});
</script>
<?php include_once "../pie.php"; ?>



