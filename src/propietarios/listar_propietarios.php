<?php
/*
CRUD con PostgreSQL y PHP
@Equipo BNPRO (Alvaro, Jose, Esteban, CEP)
@2023-05-08
=========================================================================================
Este archivo lista todos los datos de la tabla, obteniendo a los mismos como un arreglo
=========================================================================================
*/
// Validar JWT antes de mostrar contenido
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin']); // Solo admin puede ver propietarios
?>
<?php
include_once "../base_de_datos.php";
/*echo "Entro a Listar para saber si está entrando o no....";*/
// Consulta propietarios (agrega fec_delete y usr_delete si existe borrado lógico)
$sentencia = $base_de_datos->query('SELECT id_propietario, nom_propietario, ape_propietario, tel_propietario, email_propietario, id_bus, fec_delete, usr_delete FROM tab_propietarios ORDER BY nom_propietario DESC');
$Propietarios = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Contar propietarios eliminados
$propietariosEliminados = array_filter($Propietarios, function($p) {
	return !empty($p->fec_delete);
});
?>
<!--Recordemos que podemos intercambiar HTML y PHP como queramos-->
<?php include_once "encabezado_propietarios.php" ?>
<main class="main-container">
<div class="row">
	<div class="col-12">
		<h1>Propietarios</h1>
		<div class="d-flex gap-3 mb-4">
			<span class="badge bg-primary p-2">Total: <?php echo count($Propietarios); ?> propietarios</span>
			<span class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;">Eliminados: <?php echo count($propietariosEliminados); ?></span>
		<!-- Modal flotante para propietarios eliminados -->
		<div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
		  <div class="modal-dialog modal-lg">
			<div class="modal-content">
			  <div class="modal-header bg-danger text-white">
				<h5 class="modal-title" id="modalEliminadosLabel">Propietarios Eliminados</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
			  </div>
			  <div class="modal-body">
				<?php if (count($propietariosEliminados) === 0): ?>
				  <div class="alert alert-info">No hay propietarios eliminados.</div>
				<?php else: ?>
				  <div class="table-responsive">
					<table class="table table-bordered">
					  <thead class="table-danger">
						<tr>
						<th>ID</th>
						<th>ID Bus</th>
						  <th>Nombre</th>
						  <th>Apellido</th>
						  <th>Teléfono</th>
						  <th>Email</th>
						  <th>Eliminado por</th>
						  <th>Fecha Eliminación</th>
						  <th>Acciones</th>
						</tr>
					  </thead>
					  <tbody>
						<?php foreach ($propietariosEliminados as $registro): ?>
						  <tr>
							<td><?php echo $registro->id_propietario; ?></td>
							<td><?php echo htmlspecialchars($registro->id_bus, ENT_QUOTES, 'UTF-8'); ?></td>
							<td><?php echo htmlspecialchars($registro->nom_propietario); ?></td>
							<td><?php echo htmlspecialchars($registro->ape_propietario); ?></td>
							<td><?php echo htmlspecialchars($registro->tel_propietario); ?></td>
							<td><?php echo htmlspecialchars($registro->email_propietario); ?></td>
							<td><?php echo htmlspecialchars($registro->usr_delete); ?></td>
							<td><?php echo date('d/m/Y H:i', strtotime($registro->fec_delete)); ?></td>
							<td>
							  <form method="POST" action="restore_propietarios.php" onsubmit="return confirm('¿Restaurar este propietario?');" style="display:inline-block;">
								<input type="hidden" name="id_propietario" value="<?php echo $registro->id_propietario; ?>">
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
		</div>

		<div class="desktop-view">
			<div class="table-responsive">
				<table class="table table-hover">
					<thead class="table-primary">
					<tr>
					<th>ID</th>
					<th>ID Bus</th>
					<th>Nombre</th>
					<th>Apellido</th>
					<th>Teléfono</th>
					<th>Email</th>
					<th>Acciones</th>
					</tr>
					</thead>
<tbody>
						<?php if (count($Propietarios) === 0): ?>
							<tr><td colspan="7" class="text-center">No hay propietarios registrados.</td></tr>
						<?php else: ?>
							<?php foreach ($Propietarios as $p): 
								$eliminado = !empty($p->fec_delete);
								if ($eliminado) continue; // Ocultar eliminados de la tabla principal
							?>
							<tr>
							<td><?php echo $p->id_propietario; ?></td>
							<td><?php echo htmlspecialchars($p->id_bus, ENT_QUOTES, 'UTF-8'); ?></td>
							<td><?php echo htmlspecialchars($p->nom_propietario); ?></td>
							<td><?php echo htmlspecialchars($p->ape_propietario); ?></td>
							<td><?php echo htmlspecialchars($p->tel_propietario); ?></td>
							<td><?php echo htmlspecialchars($p->email_propietario); ?></td>
							<td class="actions-cell">
									<a class="btn btn-warning btn-sm" href="editar_propietarios.php?id_propietario=<?php echo $p->id_propietario; ?>">
										<i class="fas fa-edit"></i>
									</a>
									<form method="POST" action="eliminar_propietarios.php" onsubmit="return confirm('¿Seguro que deseas eliminar este propietario?');" style="display:inline-block;">
										<input type="hidden" name="id_propietario" value="<?php echo $p->id_propietario; ?>">
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
				<?php if (count($Propietarios) === 0): ?>
					<div class="col-12">
						<div class="alert alert-info">No hay propietarios registrados.</div>
					</div>
				<?php else: ?>
					<?php foreach ($Propietarios as $p): 
						$eliminado = !empty($p->fec_delete);
						if ($eliminado) continue; // Ocultar eliminados de la vista móvil
					?>
					<div class="col-12 mb-3">
						<div class="tarjeta-card card <?php echo $eliminado ? 'registro-eliminado' : ''; ?>">
							<?php if ($eliminado): ?>
								<span class="badge-eliminado">ELIMINADO</span>
							<?php endif; ?>
							
							<!-- Card header - Contiene título del propietario y botones -->
							<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
								<h5 class="mb-0">Propietario #<?php echo $p->id_propietario; ?></h5>
								
								<!-- Botones directamente en el header -->
								<div class="d-flex gap-2">
									<?php if (!$eliminado): ?>
										<a class="btn btn-warning btn-sm" href="editar_propietarios.php?id_propietario=<?php echo $p->id_propietario; ?>">
											<i class="fas fa-edit"></i>
										</a>
										<form method="POST" action="eliminar_propietarios.php" onsubmit="return confirm('¿Seguro que deseas eliminar este propietario?');" style="display:inline-block;">
											<input type="hidden" name="id_propietario" value="<?php echo $p->id_propietario; ?>">
											<button type="submit" class="btn btn-danger btn-sm">
												<i class="fas fa-trash"></i>
											</button>
										</form>
									<?php else: ?>
										<form method="POST" action="restore_propietarios.php" onsubmit="return confirm('¿Restaurar este propietario?');" style="display:inline-block;">
											<input type="hidden" name="id_propietario" value="<?php echo $p->id_propietario; ?>">
											<button type="submit" class="btn btn-sm btn-restore">
												<i class="fas fa-trash-restore"></i>
											</button>
										</form>
									<?php endif; ?>
								</div>
								
								<?php if ($eliminado): ?>
									<div class="small mt-1">
										<i class="fas fa-user-times me-1"></i> <?php echo htmlspecialchars($p->usr_delete); ?>
										<i class="fas fa-clock ms-2 me-1"></i> <?php echo date('d/m/Y', strtotime($p->fec_delete)); ?>
									</div>
								<?php endif; ?>
							</div>
							
							<div class="card-body">
								<ul class="list-group list-group-flush">
									<li class="list-group-item d-flex justify-content-between align-items-center">
										<strong>ID Bus: </strong>
											<span><?php echo htmlspecialchars($p->id_bus, ENT_QUOTES, 'UTF-8'); ?></span>
									</li>
									<li class="list-group-item d-flex justify-content-between align-items-center">
										<strong>Nombre: </strong>
										<span><?php echo htmlspecialchars($p->nom_propietario); ?></span>
									</li>
									<li class="list-group-item d-flex justify-content-between align-items-center">
										<strong>Apellido: </strong>
										<span><?php echo htmlspecialchars($p->ape_propietario); ?></span>
									</li>
									<li class="list-group-item d-flex justify-content-between align-items-center">
										<strong>Teléfono: </strong>
										<span><?php echo htmlspecialchars($p->tel_propietario); ?></span>
									</li>
									<li class="list-group-item">
										<strong>Email: </strong>
										<p class="mt-2"><?php echo htmlspecialchars($p->email_propietario); ?></p>
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
<!-- Bootstrap JS (asegúrate de que esté presente) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Script único para el modal de eliminados -->
<script src="../../assets/js/modalEliminados.js"></script>