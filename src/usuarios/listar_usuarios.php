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
validarTokenJWT(['admin']); // Solo admin puede ver usuarios

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->query("
    SELECT id_usuario, nombre, correo, fec_insert, usr_insert, 
           usr_delete, fec_delete
    FROM tab_usuarios
    ORDER BY fec_insert DESC
");
$usuarios = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Contar usuarios eliminados
$usuariosEliminados = array_filter($usuarios, function($usuario) {
    return !empty($usuario->fec_delete);
});
?>

<?php include_once "encab_usuarios.php"; ?>
<main class="main-container">
<div class="row">
    <div class="col-12">
        <!-- Encabezado principal (H1) mantenido para jerarquía clara -->
        <h1>Usuarios Registrados</h1>
        
        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Total: <?php echo count($usuarios); ?> usuarios</span>
          <!-- Botón accesible para abrir modal de eliminados -->
          <button type="button" class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;" aria-haspopup="dialog" aria-controls="modalEliminados">Eliminados: <?php echo count($usuariosEliminados); ?></button>
        </div>
        
        <!-- Modal flotante para usuarios eliminados -->
        <div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <!-- Jerarquía accesible: H2 con clase para conservar estilo -->
                <h2 class="modal-title h5" id="modalEliminadosLabel">Usuarios Eliminados</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (count($usuariosEliminados) === 0): ?>
                  <div class="alert alert-info">No hay usuarios eliminados.</div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-bordered" aria-labelledby="modalEliminadosLabel">
                      <caption style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de usuarios eliminados</caption>
                      <thead class="table-danger">
                        <tr>
                          <th scope="col">ID</th>
                          <th scope="col">Nombre</th>
                          <th scope="col">Correo</th>
                          <th scope="col">Fecha Eliminación</th>
                          <th scope="col">Usuario Eliminó</th>
                          <th scope="col">Acciones</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($usuariosEliminados as $usuario): ?>
                        <tr>
                          <td data-label="ID"><?php echo $usuario->id_usuario ?></td>
                          <td data-label="Nombre"><?php echo $usuario->nombre ?></td>
                          <td data-label="Correo"><?php echo $usuario->correo ?></td>
                          <td data-label="Fecha Eliminación"><?php echo $usuario->fec_delete ?></td>
                          <td data-label="Usuario Eliminó"><?php echo $usuario->usr_delete ?></td>
                          <td data-label="Acciones">
                            <a class="btn btn-warning btn-sm" href="<?php echo "restore_usuarios.php?id_usuario=" . $usuario->id_usuario?>" aria-label="Restaurar usuario <?php echo $usuario->id_usuario; ?>">
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

        <div class="table-responsive">
          <table class="table table-bordered table-striped" aria-describedby="tablaUsuariosCaption">
            <caption id="tablaUsuariosCaption" style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de usuarios registrados</caption>
            <thead class="thead-dark">
                    <tr>
                <th scope="col">ID</th>
                <th scope="col">Nombre</th>
                <th scope="col">Correo</th>
                <th scope="col">Fecha Inserción</th>
                <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $usuario): ?>
                        <?php if(empty($usuario->fec_delete)): ?>
                        <tr>
                  <td data-label="ID"><?php echo $usuario->id_usuario ?></td>
                  <td data-label="Nombre"><?php echo $usuario->nombre ?></td>
                  <td data-label="Correo"><?php echo $usuario->correo ?></td>
                  <td data-label="Fecha Inserción"><?php echo $usuario->fec_insert ?></td>
                  <td class="actions-cell" data-label="Acciones">
                    <a class="btn btn-warning btn-sm" href="editar_usuarios.php?id_usuario=<?php echo $usuario->id_usuario; ?>" aria-label="Editar usuario <?php echo $usuario->id_usuario; ?>">
                      <i class="fas fa-edit" aria-hidden="true"></i>
                                </a>
                                <form method="POST" action="eliminar_usuarios.php" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');" style="display:inline-block;">
                                    <input type="hidden" name="id_usuario" value="<?php echo $usuario->id_usuario; ?>">
                      <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar usuario <?php echo $usuario->id_usuario; ?>">
                        <i class="fas fa-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>
<?php include_once "../pie.php" ?>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/modalEliminados.js"></script>
