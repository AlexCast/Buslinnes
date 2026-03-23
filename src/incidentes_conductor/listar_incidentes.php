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
Listado de incidentes.
=================================================================
*/
// Validar JWT antes de mostrar contenido
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor']); // Admin y conductor pueden ver incidentes

include_once "../base_de_datos.php";

$sentencia = $base_de_datos->query('
    SELECT i.id_incidente, i.titulo_incidente, i.desc_incidente, i.tipo_incidente, i.fec_insert,
           b.id_bus, b.matricula,
           c.id_conductor, c.nom_conductor, c.ape_conductor,
           i.usr_delete, i.fec_delete
    FROM tab_incidentes i
    JOIN tab_buses b ON i.id_bus = b.id_bus
    JOIN tab_conductores c ON i.id_conductor = c.id_conductor
    ORDER BY i.id_incidente DESC
');
$incidentes = $sentencia->fetchAll(PDO::FETCH_OBJ);
?>

<?php include_once "encab_incidentes.php"; ?>
<main class="main-container">
<div class="row">
    <div class="col-12">
        <h1>Incidentes reportados</h1>
        
        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Total: <?php echo count($incidentes); ?> incidentes</span>
        </div>

        <div class="desktop-view">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Bus (Matrícula)</th>
                            <th>Conductor</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($incidentes) === 0): ?>
                            <tr><td colspan="7" class="text-center">No hay incidentes registrados</td></tr>
                        <?php else: ?>
                            <?php foreach($incidentes as $incidente): 
                                $eliminado = !empty($incidente->fec_delete);
                                if ($eliminado) continue; // Ocultar eliminados de la tabla principal
                            ?>
                            <tr>
                                <td><?php echo $incidente->id_incidente; ?></td>
                                <td><?php echo htmlspecialchars($incidente->titulo_incidente); ?></td>
                                <td><?php echo substr($incidente->desc_incidente, 0, 50) . (strlen($incidente->desc_incidente) > 50 ? '...' : ''); ?></td>
                                <td>
                                    <?php 
                                    $tipo = [
                                        'C' => 'Choque',
                                        'E' => 'Embotellamiento',
                                        'D' => 'Desviación',
                                        'A' => 'Atropello',
                                        'O' => 'Otros'
                                    ];
                                    echo $tipo[$incidente->tipo_incidente] ?? 'Desconocido';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $incidente->matricula; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($incidente->nom_conductor . ' ' . $incidente->ape_conductor); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($incidente->fec_insert)); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mobile-view">
            <div class="row">
                <?php if (count($incidentes) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay incidentes registrados</div>
                    </div>
                <?php else: ?>
                    <?php foreach($incidentes as $incidente): 
                        $eliminado = !empty($incidente->fec_delete);
                        if ($eliminado) continue; // Ocultar eliminados de la vista móvil
                    ?>
                    <div class="col-12 mb-3">
                        <div class="incidente-card card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Incidente #<?php echo $incidente->id_incidente; ?></h5>
                            </div>
                            
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Título: </strong>
                                        <span><?php echo htmlspecialchars($incidente->titulo_incidente); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Bus: </strong>
                                        <span class="badge bg-secondary"><?php echo $incidente->matricula; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Conductor: </strong>
                                        <span><?php echo htmlspecialchars($incidente->nom_conductor . ' ' . $incidente->ape_conductor); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Tipo: </strong>
                                        <span>
                                            <?php 
                                            $tipo = [
                                                'C' => 'Choque',
                                                'E' => 'Embotellamiento',
                                                'D' => 'Desviación',
                                                'A' => 'Atropello',
                                                'O' => 'Otros'
                                            ];
                                            echo $tipo[$incidente->tipo_incidente] ?? 'Desconocido';
                                            ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Fecha: </strong>
                                        <span><?php echo date('d/m/Y H:i', strtotime($incidente->fec_insert)); ?></span>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Descripción: </strong>
                                        <p class="mt-2"><?php echo $incidente->desc_incidente; ?></p>
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
