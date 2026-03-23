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
@Equipo BNPRO (Alvaro, Jose, Esteban, CEP)
@2023‑05‑08
*/
// Validar JWT antes de mostrar contenido
define('VALIDAR_JWT_MANUAL', true);
require_once __DIR__ . '/../validar_jwt.php';
validarTokenJWT(['admin', 'conductor', 'pasajero']); // Todos pueden ver rutas

include_once "../base_de_datos.php";

/*
  Coordenadas: si existen inicio_lat, inicio_lng, fin_lat, fin_lng en la tabla se usan.
  Si no existen, se intentará geocodificar las direcciones en el cliente.
*/
try {
    $sentencia = $base_de_datos->query('
        SELECT  id_ruta, nom_ruta, inicio_ruta, fin_ruta, longitud, val_pasaje, usr_delete, fec_delete,
                inicio_lat::float AS inicio_lat,
                inicio_lng::float AS inicio_lng,
                fin_lat::float AS fin_lat,
                fin_lng::float AS fin_lng
        FROM tab_rutas
        ORDER BY nom_ruta DESC
    ');
} catch (PDOException $e) {
    // En caso de que la base de datos tenga datos no numéricos en las columnas de coordenadas
    // devolvemos los valores tal cual y el cliente los validará.
    $sentencia = $base_de_datos->query('
        SELECT  id_ruta, nom_ruta, inicio_ruta, fin_ruta, longitud, val_pasaje, usr_delete, fec_delete,
                inicio_lat, inicio_lng, fin_lat, fin_lng
        FROM tab_rutas
        ORDER BY nom_ruta DESC
    ');
}
$rutas = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Separar rutas eliminadas y normales (mover aquí)
$rutasEliminadas = array_filter($rutas, function($r) { return !empty($r->fec_delete); });
$rutasNormales = array_filter($rutas, function($r) { return empty($r->fec_delete); });

// Cargar waypoints para cada ruta (si existe la tabla)
try {
    $stmtWp = $base_de_datos->prepare('SELECT lat, lng, nombre, orden FROM tab_ruta_waypoints WHERE id_ruta = ? ORDER BY orden ASC');
    foreach ($rutas as $r) {
        $stmtWp->execute([$r->id_ruta]);
        $wps = $stmtWp->fetchAll(PDO::FETCH_ASSOC);
        // Añadir propiedad waypoints al objeto ruta
        $r->waypoints = array_map(function($wp) {
            return [
                'lat' => (float)$wp['lat'],
                'lng' => (float)$wp['lng'],
                'nombre' => $wp['nombre'],
                'orden' => (int)$wp['orden']
            ];
        }, $wps);
    }
} catch (Exception $e) {
    // Si falla, dejamos las rutas sin waypoints
}
?>

<?php include_once "encab_rutas.php" ?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<main class="main-container">
<div class="row">
    <div class="col-12">
        <!-- Encabezado principal (H1) mantenido para jerarquía -->
        <h1>Listado de Rutas</h1>

        <div class="d-flex gap-3 mb-4">
            <span class="badge bg-primary p-2">Total: <?php echo count($rutas); ?> rutas</span>
            <!-- Botón accesible para abrir modal de eliminadas -->
            <button type="button" class="badge bg-danger p-2" id="btnEliminados" style="cursor:pointer;" aria-haspopup="dialog" aria-controls="modalEliminados">Eliminadas: <?php echo count($rutasEliminadas); ?></button>
        </div>

        <!-- Modal flotante para rutas eliminadas -->
        <div class="modal fade" id="modalEliminados" tabindex="-1" aria-labelledby="modalEliminadosLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <!-- Jerarquía: H2 con clase h5 para mantener estilo visual -->
                                <h2 class="modal-title h5" id="modalEliminadosLabel">Rutas Eliminadas</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (count($rutasEliminadas) === 0): ?>
                  <div class="alert alert-info">No hay rutas eliminadas.</div>
                <?php else: ?>
                  <div class="table-responsive">
                                        <table class="table table-bordered" aria-labelledby="modalEliminadosLabel">
                                            <caption style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de rutas eliminadas</caption>
                      <thead class="table-danger">
                        <tr>
                                                    <th scope="col">ID ruta</th><th scope="col">Nombre ruta</th><th scope="col">Inicio</th><th scope="col">Fin</th><th scope="col">Distancia (km)</th><th scope="col">Pasaje ($)</th><th scope="col">Eliminado por</th><th scope="col">Fecha Eliminación</th><th scope="col">Acciones</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($rutasEliminadas as $r): ?>
                        <tr>
                                                    <td data-label="ID ruta"><?php echo $r->id_ruta; ?></td>
                                                    <td data-label="Nombre ruta"><?php echo $r->nom_ruta; ?></td>
                                                    <td data-label="Inicio"><?php echo $r->inicio_ruta; ?></td>
                                                    <td data-label="Fin"><?php echo $r->fin_ruta; ?></td>
                                                    <td data-label="Distancia (km)"><?php echo $r->longitud; ?></td>
                                                    <td data-label="Pasaje ($)"><?php echo $r->val_pasaje; ?></td>
                                                    <td data-label="Eliminado por"><?php echo htmlspecialchars($r->usr_delete ?? ''); ?></td>
                                                    <td data-label="Fecha Eliminación"><?php echo !empty($r->fec_delete) ? date('d/m/Y H:i', strtotime($r->fec_delete)) : ''; ?></td>
                                                    <td data-label="Acciones">
                                                        <form method="POST" action="restore_rutas.php" onsubmit="return confirm('¿Restaurar esta ruta?');" style="display:inline-block;">
                                                            <input type="hidden" name="id_ruta" value="<?php echo $r->id_ruta; ?>">
                                                            <button type="submit" class="btn btn-sm btn-restore" aria-label="Restaurar ruta <?php echo $r->nom_ruta; ?>">
                                                                <i class="fas fa-trash-restore" aria-hidden="true"></i> Restaurar
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

        <div class="map-container mb-4" style="position: relative; z-index: 1;">
            <h3>Mapa de Rutas (DEMOSTRACIÓN)</h3>
            <div id="map" style="height:400px;width:100%;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,.15);position: relative; z-index: 1;"></div>
        </div>

        <div class="desktop-view">
            <div class="table-responsive">
                <table class="table table-hover" aria-describedby="tablaRutasCaption">
                    <caption id="tablaRutasCaption" style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0;">Listado de rutas activas</caption>
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">ID ruta</th>
                            <th scope="col">Nombre ruta</th>
                            <th scope="col">Inicio</th>
                            <th scope="col">Fin</th>
                            <th scope="col">Distancia (km)</th>
                            <th scope="col">Pasaje ($)</th>
                            <th scope="col" class="actions-cell">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rutasNormales as $mun): ?>
                        <tr>
                            <td data-label="ID ruta"><?= $mun->id_ruta ?></td>
                            <td data-label="Nombre ruta"><?= $mun->nom_ruta ?></td>
                            <td data-label="Inicio"><?= $mun->inicio_ruta ?></td>
                            <td data-label="Fin"><?= $mun->fin_ruta ?></td>
                            <td data-label="Distancia (km)"><?= $mun->longitud ?></td>
                            <td data-label="Pasaje ($)"><?= number_format($mun->val_pasaje,0,',','.') ?></td>
                            <td class="actions-cell" data-label="Acciones">
                                <a class="btn btn-warning btn-sm" href="editar_rutas.php?id_ruta=<?= $mun->id_ruta ?>" aria-label="Editar ruta <?= $mun->nom_ruta ?>">
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                </a>
                                <form method="POST" action="eliminar_rutas.php" onsubmit="return confirm('¿Seguro que deseas eliminar esta ruta?');" style="display:inline-block;">
                                    <input type="hidden" name="id_ruta" value="<?= $mun->id_ruta ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar ruta <?= $mun->nom_ruta ?>">
                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mobile-view">
            <div class="row">
                <?php if (count($rutasNormales) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No hay rutas registradas.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($rutasNormales as $mun): ?>
                    <div class="col-12 mb-3">
                        <div class="bus-card card">
                            <!-- Card header - Contiene título de la ruta y botones -->
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h3 class="mb-0 h5">Ruta #<?= $mun->id_ruta ?></h3>
                                
                                <!-- Botones directamente en el header -->
                                <div class="d-flex gap-2">
                                    <a class="btn btn-warning btn-sm" href="editar_rutas.php?id_ruta=<?= $mun->id_ruta ?>" aria-label="Editar ruta <?= $mun->nom_ruta ?>">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="eliminar_rutas.php" onsubmit="return confirm('¿Seguro que deseas eliminar esta ruta?');" style="display:inline-block;">
                                        <input type="hidden" name="id_ruta" value="<?= $mun->id_ruta ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar ruta <?= $mun->nom_ruta ?>">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Nombre: </strong>
                                        <span class="badge bg-light text-primary"><?= $mun->nom_ruta ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Inicio: </strong>
                                        <span><?= $mun->inicio_ruta ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Fin: </strong>
                                        <span><?= $mun->fin_ruta ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Distancia: </strong>
                                        <span><?= $mun->longitud ?> km</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong>Pasaje: </strong>
                                        <span class="badge bg-success">$<?= number_format($mun->val_pasaje,0,',','.') ?></span>
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

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Pasar al front-end sólo las rutas no eliminadas
    const rutas = <?= json_encode(array_values($rutasNormales)) ?>;   // datos PHP → JS

    // Asegurarse de trabajar con números (no strings) para las coordenadas
    rutas.forEach(r => {
        r.inicio_lat = parseFloat(r.inicio_lat);
        r.inicio_lng = parseFloat(r.inicio_lng);
        r.fin_lat = parseFloat(r.fin_lat);
        r.fin_lng = parseFloat(r.fin_lng);
        if (Array.isArray(r.waypoints)) {
            r.waypoints = r.waypoints.map(wp => ({
                ...wp,
                lat: parseFloat(wp.lat),
                lng: parseFloat(wp.lng)
            }));
        }
    });

    const map = L.map('map').setView([7.1193,-73.1227],13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
        attribution:'© OpenStreetMap contributors',
        maxZoom:19
    }).addTo(map);

    const colores = ['#2563eb','#dc2626','#16a34a','#ca8a04','#7c3aed'];
    let colorIdx = 0;
    const boundsCoords = [];

    function dibujarRuta(r, pathCoords) {
        pathCoords.forEach(p => boundsCoords.push(p));
        const color = colores[colorIdx % colores.length];
        colorIdx++;
        const line = L.polyline(pathCoords, {
            color: color,
            weight: 5,
            opacity: 0.85
        }).addTo(map);
        line.bindPopup(`<strong>${r.nom_ruta}</strong><br>${r.inicio_ruta} → ${r.fin_ruta}`);
        L.circleMarker(pathCoords[0], { radius: 6, color: 'green', fillColor: 'green', weight: 2 }).addTo(map);
        L.circleMarker(pathCoords[pathCoords.length - 1], { radius: 6, color: 'red', fillColor: 'red', weight: 2 }).addTo(map);
    }

    function dibujarWaypoints(r) {
        if (!r.waypoints || !Array.isArray(r.waypoints) || r.waypoints.length === 0) return;
        r.waypoints.forEach((wp, idx) => {
            if (typeof wp.lat !== 'number' || typeof wp.lng !== 'number') return;
            const marker = L.circleMarker([wp.lat, wp.lng], { radius: 5, color: '#FFA500', fillColor: '#FFA500', weight: 2 })
                .addTo(map)
                .bindPopup(`<strong>${wp.nombre || 'Waypoint ' + (idx + 1)}</strong>`);
            boundsCoords.push([wp.lat, wp.lng]);
        });
    }

    function isValidLatLng(lat, lng) {
        return typeof lat === 'number' && typeof lng === 'number' &&
            !Number.isNaN(lat) && !Number.isNaN(lng) &&
            lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
    }

    async function geocodeAddress(address) {
        if (!address) return null;
        try {
            const q = encodeURIComponent(address);
            const resp = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${q}`, {
                headers: { 'Accept-Language': 'es' }
            });
            const data = await resp.json();
            if (Array.isArray(data) && data[0]) {
                return { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) };
            }
        } catch (e) {
            // Ignore geocoding failures, we'll fallback to defaults below
        }
        return null;
    }

    async function dibujarRutasPorCalles() {
        for (const r of rutas) {
            // Si no hay coordenadas guardadas, intentamos geocodificar las direcciones escritas
            if (!isValidLatLng(r.inicio_lat, r.inicio_lng)) {
                const geo = await geocodeAddress(r.inicio_ruta);
                if (geo) {
                    r.inicio_lat = geo.lat;
                    r.inicio_lng = geo.lng;
                }
            }
            if (!isValidLatLng(r.fin_lat, r.fin_lng)) {
                const geo = await geocodeAddress(r.fin_ruta);
                if (geo) {
                    r.fin_lat = geo.lat;
                    r.fin_lng = geo.lng;
                }
            }

            // Si aún no tenemos coords válidas, no intentamos dibujar la ruta (evita errores con valores null/NaN)
            if (!isValidLatLng(r.inicio_lat, r.inicio_lng) || !isValidLatLng(r.fin_lat, r.fin_lng)) {
                console.warn('Coordenadas inválidas para la ruta', r.id_ruta, r.nom_ruta, r.inicio_lat, r.inicio_lng, r.fin_lat, r.fin_lng);
                continue;
            }

            // Construir coordenadas para OSRM incluyendo waypoints si existen
            let coordsStr = `${r.inicio_lng},${r.inicio_lat}`;
            if (r.waypoints && r.waypoints.length > 0) {
                // waypoints ya están en orden en el servidor
                r.waypoints.forEach(wp => {
                    coordsStr += `;${wp.lng},${wp.lat}`;
                });
            }
            coordsStr += `;${r.fin_lng},${r.fin_lat}`;
            const url = `https://router.project-osrm.org/route/v1/driving/${coordsStr}?overview=full&geometries=geojson`;
            try {
                const resp = await fetch(url);
                const data = await resp.json();
                if (data.code === 'Ok' && data.routes && data.routes[0]) {
                    const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                    dibujarRuta(r, coords);
                    dibujarWaypoints(r);
                } else {
                    dibujarRuta(r, [[r.inicio_lat, r.inicio_lng], [r.fin_lat, r.fin_lng]]);
                    dibujarWaypoints(r);
                }
            } catch (e) {
                dibujarRuta(r, [[r.inicio_lat, r.inicio_lng], [r.fin_lat, r.fin_lng]]);
                dibujarWaypoints(r);
            }
        }
        if (boundsCoords.length > 0) {
            map.fitBounds(boundsCoords, { padding: [30, 30], maxZoom: 15 });
        }
    }

    dibujarRutasPorCalles();

    L.control.scale().addTo(map);
</script>

<?php include_once "../pie.php"; ?>
<!-- Bootstrap JS (asegúrate de que esté presente) -->
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<!-- Script único para el modal de eliminados -->
<script src="../../assets/js/modalEliminados.js"></script>
