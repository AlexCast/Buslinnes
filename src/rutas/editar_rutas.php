<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP

============================================================================================
Este archivo muestra un formulario que se envía a insertar.php, el cual guardará los datos
============================================================================================
*/

include_once "../base_de_datos.php";

// Verificar que los parámetros existan en la URL
if (!isset($_GET["id_ruta"])) {
    echo "No existe la ruta a editar";
    exit();
}

$id_ruta = $_GET["id_ruta"];

// Seleccionar el ruta por ID (agregar coordenadas inicio y fin)
$sentencia = $base_de_datos->prepare("SELECT id_ruta, nom_ruta, hora_inicio, hora_final, inicio_ruta, fin_ruta, longitud, val_pasaje, inicio_lat, inicio_lng, fin_lat, fin_lng FROM tab_rutas WHERE id_ruta = ?;");
$sentencia->execute([$id_ruta]);
$cli = $sentencia->fetchObject();
if (!$cli) {
    echo "¡No existe la ruta con ese ID!";
    exit();
}

// Obtener los waypoints asociados a esta ruta
$stmtWaypoints = $base_de_datos->prepare("SELECT id_waypoint, nombre, lat, lng, orden FROM tab_ruta_waypoints WHERE id_ruta = ? ORDER BY orden ASC");
$stmtWaypoints->execute([$id_ruta]);
$waypoints = $stmtWaypoints->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include_once "encab_rutas.php"; ?>
<head>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .suggestions-list {
            position: absolute;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            width: 100%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .suggestions-list li {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .suggestions-list li:hover {
            background: #f8f9fa;
        }
        .suggestions-list li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Editar Ruta</h1>
                </div>
                <div class="card-body">
                    <!-- Mapa sincronizado -->
                    <div id="ruta-map" style="width:100%;height:450px;margin-bottom:20px;border:2px solid #8059d4;border-radius:6px;overflow:hidden;"></div>
                    
                    <form action="update_rutas.php" method="POST" id="formRuta">
                        <input type="hidden" name="id_ruta" value="<?php echo $cli->id_ruta; ?>">
                        <input type="hidden" name="waypoints_json" id="waypoints_json" value="">
                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nom_ruta" class="form-label">Nombre de la Ruta</label>
                                    <input value="<?php echo htmlspecialchars($cli->nom_ruta); ?>" required name="nom_ruta" type="text" id="nom_ruta" class="form-control" placeholder="Nombre de la ruta">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="hora_inicio" class="form-label">Hora de Inicio</label>
                                    <input value="<?php echo htmlspecialchars(substr($cli->hora_inicio,0,5)); ?>" required name="hora_inicio" type="time" id="hora_inicio" class="form-control" placeholder="HH:MM">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="hora_final" class="form-label">Hora Final</label>
                                    <input value="<?php echo htmlspecialchars(substr($cli->hora_final,0,5)); ?>" required name="hora_final" type="time" id="hora_final" class="form-control" placeholder="HH:MM">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="inicio_ruta" class="form-label">Inicio de la Ruta</label>
                                    <input value="<?php echo htmlspecialchars($cli->inicio_ruta); ?>" required name="inicio_ruta" type="text" id="inicio_ruta" class="form-control" placeholder="Punto de inicio">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="fin_ruta" class="form-label">Fin de la Ruta</label>
                                    <input value="<?php echo htmlspecialchars($cli->fin_ruta); ?>" required name="fin_ruta" type="text" id="fin_ruta" class="form-control" placeholder="Punto final">
                                </div>
                            </div>
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="longitud" class="form-label">Distancia (km)</label>
                                    <input value="<?php echo htmlspecialchars($cli->longitud); ?>" required name="longitud" type="text" id="longitud" class="form-control" placeholder="Distancia en kilómetros (ej: 1,5 o 1.5)" pattern="[0-9]+([,.][0-9]+)?" title="Ingrese un número válido, puede usar coma o punto para decimales">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="val_pasaje" class="form-label">Valor Pasaje</label>
                                    <input value="<?php echo htmlspecialchars($cli->val_pasaje); ?>" required name="val_pasaje" type="number" step="0.01" id="val_pasaje" class="form-control" placeholder="Valor del pasaje">
                                </div>
                            </div>
                        </div>

                        <!-- Coordenadas de Inicio -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h6 class="text-success mb-3">📍 Punto de Inicio (Verde)</h6>
                                <div class="form-group mb-3">
                                    <label for="inicio_ruta" class="form-label">Dirección de Inicio</label>
                                    <input value="<?php echo htmlspecialchars($cli->inicio_ruta); ?>" name="inicio_ruta" type="text" id="inicio_ruta" class="form-control" placeholder="Buscar dirección de inicio">
                                    <div id="suggestions-inicio" class="suggestions-list" style="display: none;"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="inicio_lat" class="form-label">Latitud Inicio</label>
                                    <input value="<?php echo htmlspecialchars($cli->inicio_lat ?? ''); ?>" name="inicio_lat" type="number" step="0.000001" id="inicio_lat" class="form-control map-sync-input" placeholder="Latitud">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="inicio_lng" class="form-label">Longitud Inicio</label>
                                    <input value="<?php echo htmlspecialchars($cli->inicio_lng ?? ''); ?>" name="inicio_lng" type="number" step="0.000001" id="inicio_lng" class="form-control map-sync-input" placeholder="Longitud">
                                </div>
                                <button type="button" class="btn btn-success btn-sm" id="btnEditarInicio">
                                    <i class="fas fa-map-marker-alt me-1"></i> Editar Inicio
                                </button>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-danger mb-3">📍 Punto de Fin (Rojo)</h6>
                                <div class="form-group mb-3">
                                    <label for="fin_ruta" class="form-label">Dirección de Fin</label>
                                    <input value="<?php echo htmlspecialchars($cli->fin_ruta); ?>" name="fin_ruta" type="text" id="fin_ruta" class="form-control" placeholder="Buscar dirección de fin">
                                    <div id="suggestions-fin" class="suggestions-list" style="display: none;"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="fin_lat" class="form-label">Latitud Fin</label>
                                    <input value="<?php echo htmlspecialchars($cli->fin_lat ?? ''); ?>" name="fin_lat" type="number" step="0.000001" id="fin_lat" class="form-control map-sync-input" placeholder="Latitud">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="fin_lng" class="form-label">Longitud Fin</label>
                                    <input value="<?php echo htmlspecialchars($cli->fin_lng ?? ''); ?>" name="fin_lng" type="number" step="0.000001" id="fin_lng" class="form-control map-sync-input" placeholder="Longitud">
                                </div>
                                <button type="button" class="btn btn-danger btn-sm" id="btnEditarFin">
                                    <i class="fas fa-map-marker-alt me-1"></i> Editar Fin
                                </button>
                            </div>
                        </div>

                        <!-- Waypoints -->
                        <div class="mt-4">
                            <h6 class="mb-3">🛑 Waypoints (Paradas Intermedias - Naranja)</h6>
                            <div class="form-group mb-3">
                                <label for="waypoint_search" class="form-label">Buscar Waypoint</label>
                                <input type="text" id="waypoint_search" class="form-control" placeholder="Buscar dirección para waypoint" style="display: none;">
                                <div id="suggestions-waypoint" class="suggestions-list" style="display: none;"></div>
                            </div>
                            <div id="waypointsContainer">
                                <?php foreach ($waypoints as $index => $wp): ?>
                                    <div class="waypoint-item card mb-3 p-3" data-waypoint-index="<?php echo $index; ?>">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Nombre</label>
                                                <input type="text" class="form-control waypoint-nombre map-sync-input" value="<?php echo htmlspecialchars($wp['nombre'] ?? ''); ?>" placeholder="Nombre del waypoint">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Orden</label>
                                                <input type="number" class="form-control waypoint-orden map-sync-input" value="<?php echo htmlspecialchars($wp['orden'] ?? $index); ?>" placeholder="Orden">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Latitud</label>
                                                <input type="number" step="0.000001" class="form-control waypoint-lat map-sync-input" value="<?php echo htmlspecialchars($wp['lat'] ?? ''); ?>" placeholder="Latitud">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Longitud</label>
                                                <input type="number" step="0.000001" class="form-control waypoint-lng map-sync-input" value="<?php echo htmlspecialchars($wp['lng'] ?? ''); ?>" placeholder="Longitud">
                                            </div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="button" class="btn btn-warning btn-sm me-1 btn-editar-waypoint">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm btn-delete-waypoint">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-secondary" id="btnAgregarWaypoint">
                                <i class="fas fa-plus me-1"></i> Agregar Waypoint
                            </button>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="./listar_rutas.php" class="btn btn-warning">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Función para recolectar waypoints y enviar al formulario
document.getElementById('formRuta').addEventListener('submit', function(e) {
    const waypoints = [];
    document.querySelectorAll('.waypoint-item').forEach((wpDiv, index) => {
        const nombre = wpDiv.querySelector('.waypoint-nombre').value.trim();
        const lat = parseFloat(wpDiv.querySelector('.waypoint-lat').value);
        const lng = parseFloat(wpDiv.querySelector('.waypoint-lng').value);
        const orden = parseInt(wpDiv.querySelector('.waypoint-orden').value) || (index + 1);
        if (nombre && !isNaN(lat) && !isNaN(lng)) {
            waypoints.push({
                orden: orden,
                lat: lat,
                lng: lng,
                nombre: nombre
            });
        }
    });
    document.getElementById('waypoints_json').value = JSON.stringify(waypoints);
});

if (typeof L !== 'undefined') {
    let map;
    let startMarker, endMarker;
    const waypointMarkers = {};
    let routePolyline; // Variable para la línea de la ruta
    let modo = null; // 'inicio', 'fin', 'waypoint', 'edit_waypoint'
    let editingWaypointDiv = null;

    function initMap() {
        console.log('Inicializando mapa...');
        map = L.map('ruta-map').setView([7.119, -73.122], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        console.log('Mapa inicializado');
    }

    async function updateMapMarkers() {
        const inicioLat = parseFloat(document.getElementById('inicio_lat').value);
        const inicioLng = parseFloat(document.getElementById('inicio_lng').value);
        const finLat = parseFloat(document.getElementById('fin_lat').value);
        const finLng = parseFloat(document.getElementById('fin_lng').value);

        // Actualizar marcador de inicio (verde)
        if (isFinite(inicioLat) && isFinite(inicioLng)) {
            if (startMarker) {
                startMarker.setLatLng([inicioLat, inicioLng]);
            } else {
                startMarker = L.marker([inicioLat, inicioLng], {
                    icon: L.divIcon({ className: 'inicio-marker', html: '<span style="color:green;font-size:24px">●</span>', iconSize: [24, 24] }),
                    draggable: true
                }).addTo(map).bindPopup('Inicio');
                startMarker.on('dragend', function(e) {
                    const latlng = e.target.getLatLng();
                    document.getElementById('inicio_lat').value = latlng.lat.toFixed(6);
                    document.getElementById('inicio_lng').value = latlng.lng.toFixed(6);
                    updateMapMarkers();
                });
            }
        }

        // Actualizar marcador de fin (rojo)
        if (isFinite(finLat) && isFinite(finLng)) {
            if (endMarker) {
                endMarker.setLatLng([finLat, finLng]);
            } else {
                endMarker = L.marker([finLat, finLng], {
                    icon: L.divIcon({ className: 'fin-marker', html: '<span style="color:red;font-size:24px">●</span>', iconSize: [24, 24] }),
                    draggable: true
                }).addTo(map).bindPopup('Fin');
                endMarker.on('dragend', function(e) {
                    const latlng = e.target.getLatLng();
                    document.getElementById('fin_lat').value = latlng.lat.toFixed(6);
                    document.getElementById('fin_lng').value = latlng.lng.toFixed(6);
                    updateMapMarkers();
                });
            }
        }

        // Actualizar waypoints (naranja)
        document.querySelectorAll('.waypoint-item').forEach((wpDiv, index) => {
            const lat = parseFloat(wpDiv.querySelector('.waypoint-lat').value);
            const lng = parseFloat(wpDiv.querySelector('.waypoint-lng').value);
            const nombre = wpDiv.querySelector('.waypoint-nombre').value;

            if (isFinite(lat) && isFinite(lng)) {
                const wpKey = `wp_${index}`;
                if (waypointMarkers[wpKey]) {
                    waypointMarkers[wpKey].setLatLng([lat, lng]);
                } else {
                    waypointMarkers[wpKey] = L.marker([lat, lng], {
                        icon: L.divIcon({ className: 'waypoint-marker', html: '<span style="color:orange;font-size:20px">●</span>', iconSize: [20, 20] }),
                        draggable: true
                    }).addTo(map).bindPopup(`Waypoint: ${nombre || 'Sin nombre'}`);
                    waypointMarkers[wpKey].on('dragend', function(e) {
                        const latlng = e.target.getLatLng();
                        wpDiv.querySelector('.waypoint-lat').value = latlng.lat.toFixed(6);
                        wpDiv.querySelector('.waypoint-lng').value = latlng.lng.toFixed(6);
                        updateMapMarkers();
                    });
                }
            }
        });

        // Dibujar la línea de la ruta
        await updateRoutePolyline();

        // Ajustar vista del mapa
        const bounds = L.latLngBounds();
        if (startMarker) bounds.extend(startMarker.getLatLng());
        if (endMarker) bounds.extend(endMarker.getLatLng());
        Object.values(waypointMarkers).forEach(m => bounds.extend(m.getLatLng()));

        if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    async function updateRoutePolyline() {
        // Remover la polilínea anterior si existe
        if (routePolyline) {
            map.removeLayer(routePolyline);
        }

        // Recolectar todos los puntos en orden
        const routePoints = [];

        // Agregar punto de inicio
        const inicioLat = parseFloat(document.getElementById('inicio_lat').value);
        const inicioLng = parseFloat(document.getElementById('inicio_lng').value);
        if (isFinite(inicioLat) && isFinite(inicioLng)) {
            routePoints.push([inicioLat, inicioLng]);
        }

        // Agregar waypoints ordenados por el campo orden
        const waypoints = [];
        document.querySelectorAll('.waypoint-item').forEach((wpDiv) => {
            const lat = parseFloat(wpDiv.querySelector('.waypoint-lat').value);
            const lng = parseFloat(wpDiv.querySelector('.waypoint-lng').value);
            const orden = parseInt(wpDiv.querySelector('.waypoint-orden').value) || 0;
            if (isFinite(lat) && isFinite(lng)) {
                waypoints.push({ lat, lng, orden });
            }
        });

        // Ordenar waypoints por orden
        waypoints.sort((a, b) => a.orden - b.orden);

        // Agregar waypoints ordenados
        waypoints.forEach(wp => {
            routePoints.push([wp.lat, wp.lng]);
        });

        // Agregar punto de fin
        const finLat = parseFloat(document.getElementById('fin_lat').value);
        const finLng = parseFloat(document.getElementById('fin_lng').value);
        if (isFinite(finLat) && isFinite(finLng)) {
            routePoints.push([finLat, finLng]);
        }

        // Si hay al menos 2 puntos, intentar obtener ruta de OSRM
        if (routePoints.length >= 2) {
            // Construir coordenadas para OSRM
            let coordsStr = `${routePoints[0][1]},${routePoints[0][0]}`;
            for (let i = 1; i < routePoints.length; i++) {
                coordsStr += `;${routePoints[i][1]},${routePoints[i][0]}`;
            }
            const url = `https://router.project-osrm.org/route/v1/driving/${coordsStr}?overview=full&geometries=geojson`;

            try {
                const resp = await fetch(url);
                const data = await resp.json();
                if (data.code === 'Ok' && data.routes && data.routes[0]) {
                    const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                    routePolyline = L.polyline(coords, {
                        color: 'blue',
                        weight: 4,
                        opacity: 0.7
                    }).addTo(map);
                } else {
                    // Fallback a línea recta
                    routePolyline = L.polyline(routePoints, {
                        color: 'blue',
                        weight: 4,
                        opacity: 0.7
                    }).addTo(map);
                }
            } catch (e) {
                // Fallback a línea recta en caso de error
                routePolyline = L.polyline(routePoints, {
                    color: 'blue',
                    weight: 4,
                    opacity: 0.7
                }).addTo(map);
            }
        }
    }

    // Función para obtener dirección desde coordenadas (reverse geocoding)
    async function getAddressFromCoords(lat, lng) {
        try {
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
            const response = await fetch(url);
            const data = await response.json();
            
            if (data && data.display_name) {
                return data.display_name;
            } else {
                return `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
            }
        } catch (error) {
            console.error('Error en reverse geocoding:', error);
            return `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
        }
    }

    // Función para obtener sugerencias de Nominatim
    async function getSuggestions(query, tipoRuta) {
        if (query.length < 2) {
            const suggestionsList = tipoRuta === 'inicio' ? document.getElementById('suggestions-inicio') : document.getElementById('suggestions-fin');
            suggestionsList.style.display = 'none';
            return;
        }

        const suggestionsList = tipoRuta === 'inicio' ? document.getElementById('suggestions-inicio') : document.getElementById('suggestions-fin');
        suggestionsList.innerHTML = '<li style="padding: 10px 12px; color: #666;">🔍 Buscando...</li>';
        suggestionsList.style.display = 'block';

        try {
            const viewbox = '-73.4,6.65,-72.85,7.3';
            const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=20&viewbox=${viewbox}&dedupe=1`;
            
            const response = await fetch(url);
            const data = await response.json();

            suggestionsList.innerHTML = '';

            if (data.length === 0) {
                suggestionsList.innerHTML = '<li style="padding: 10px 12px; color: #999;">No se encontraron resultados.</li>';
                suggestionsList.style.display = 'block';
                return;
            }

            const validResults = data.filter(place => {
                const lat = parseFloat(place.lat);
                const lon = parseFloat(place.lon);
                return lat >= 6.65 && lat <= 7.3 && lon >= -73.4 && lon <= -72.85;
            });

            if (validResults.length === 0) {
                suggestionsList.innerHTML = '<li style="padding: 10px 12px; color: #999;">No se encontraron resultados en la zona.</li>';
                suggestionsList.style.display = 'block';
                return;
            }

            validResults.slice(0, 8).forEach((place) => {
                const li = document.createElement('li');
                li.innerHTML = `<strong>${place.display_name.split(',')[0]}</strong><br><small>${place.display_name}</small>`;
                li.onclick = () => selectPlace(place, tipoRuta);
                suggestionsList.appendChild(li);
            });

            suggestionsList.style.display = 'block';
        } catch (error) {
            console.error('Error obteniendo sugerencias:', error);
            suggestionsList.innerHTML = '<li style="padding: 10px 12px; color: #999;">⚠️ Error de conexión. Intenta de nuevo.</li>';
            suggestionsList.style.display = 'block';
        }
    }

    // Función para seleccionar un lugar de la lista
    function selectPlace(place, tipoRuta) {
        const lat = parseFloat(place.lat);
        const lng = parseFloat(place.lon);
        const displayName = place.display_name;

        const suggestionsList = tipoRuta === 'inicio' ? document.getElementById('suggestions-inicio') : document.getElementById('suggestions-fin');
        const inputEl = tipoRuta === 'inicio' ? document.getElementById('inicio_ruta') : document.getElementById('fin_ruta');

        inputEl.value = displayName;
        if (tipoRuta === 'inicio') {
            document.getElementById('inicio_lat').value = lat.toFixed(6);
            document.getElementById('inicio_lng').value = lng.toFixed(6);
        } else {
            document.getElementById('fin_lat').value = lat.toFixed(6);
            document.getElementById('fin_lng').value = lng.toFixed(6);
        }

        suggestionsList.style.display = 'none';
        updateMapMarkers();
    }

    // Función para obtener sugerencias de Nominatim para waypoints
    async function getSuggestionsWaypoint(query) {
        if (query.length < 2) {
            document.getElementById('suggestions-waypoint').style.display = 'none';
            return;
        }

        const suggestionsList = document.getElementById('suggestions-waypoint');
        suggestionsList.innerHTML = '<li style="padding: 10px 12px; color: #666;">🔍 Buscando...</li>';
        suggestionsList.style.display = 'block';

        try {
            const viewbox = '-73.4,6.65,-72.85,7.3';
            const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=20&viewbox=${viewbox}&dedupe=1`;
            
            const response = await fetch(url);
            const data = await response.json();
            suggestionsList.innerHTML = '';

            if (data.length === 0) {
                suggestionsList.innerHTML = '<li style="padding: 10px 12px; color: #999;">No se encontraron resultados.</li>';
                suggestionsList.style.display = 'block';
                return;
            }

            const validResults = data.filter(place => {
                const lat = parseFloat(place.lat);
                const lon = parseFloat(place.lon);
                return lat >= 6.65 && lat <= 7.3 && lon >= -73.4 && lon <= -72.85;
            });

            if (validResults.length === 0) {
                suggestionsList.innerHTML = '<li style="padding: 10px 12px; color: #999;">No se encontraron resultados en la zona.</li>';
                suggestionsList.style.display = 'block';
                return;
            }

            validResults.slice(0, 8).forEach((place) => {
                const li = document.createElement('li');
                li.innerHTML = `<strong>${place.display_name.split(',')[0]}</strong><br><small>${place.display_name}</small>`;
                li.onclick = () => selectWaypoint(place);
                suggestionsList.appendChild(li);
            });

            suggestionsList.style.display = 'block';
        } catch (error) {
            console.error('Error obteniendo sugerencias de waypoint:', error);
            suggestionsList.innerHTML = '<li style="padding: 10px 12px; color: #999;">⚠️ Error de conexión. Intenta de nuevo.</li>';
            suggestionsList.style.display = 'block';
        }
    }

    // Función para seleccionar un waypoint de la lista
    function selectWaypoint(place) {
        const lat = parseFloat(place.lat);
        const lng = parseFloat(place.lon);
        const displayName = place.display_name;

        if (modo === 'waypoint') {
            // Agregar nuevo waypoint
            const container = document.getElementById('waypointsContainer');
            const wpIndex = container.querySelectorAll('.waypoint-item').length;

            const wpDiv = document.createElement('div');
            wpDiv.className = 'waypoint-item card mb-3 p-3';
            wpDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control waypoint-nombre map-sync-input" value="${displayName.split(',')[0]}" placeholder="Nombre del waypoint">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Orden</label>
                        <input type="number" class="form-control waypoint-orden map-sync-input" value="${wpIndex + 1}" placeholder="Orden">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Latitud</label>
                        <input type="number" step="0.000001" class="form-control waypoint-lat map-sync-input" value="${lat.toFixed(6)}" placeholder="Latitud">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Longitud</label>
                        <input type="number" step="0.000001" class="form-control waypoint-lng map-sync-input" value="${lng.toFixed(6)}" placeholder="Longitud">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-warning btn-sm me-1 btn-editar-waypoint">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm btn-delete-waypoint">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            container.appendChild(wpDiv);

            // Agregar listeners
            wpDiv.querySelectorAll('.map-sync-input').forEach(input => {
                input.addEventListener('change', updateMapMarkers);
                input.addEventListener('input', updateMapMarkers);
            });

            wpDiv.querySelector('.btn-delete-waypoint').addEventListener('click', function() {
                wpDiv.remove();
                updateMapMarkers();
            });

            wpDiv.querySelector('.btn-editar-waypoint').addEventListener('click', function() {
                editingWaypointDiv = wpDiv;
                modo = 'edit_waypoint';
                document.getElementById('waypoint_search').style.display = 'block';
                document.getElementById('waypoint_search').focus();
            });

            updateMapMarkers();
        } else if (modo === 'edit_waypoint' && editingWaypointDiv) {
            // Editar waypoint existente
            editingWaypointDiv.querySelector('.waypoint-nombre').value = displayName.split(',')[0];
            editingWaypointDiv.querySelector('.waypoint-lat').value = lat.toFixed(6);
            editingWaypointDiv.querySelector('.waypoint-lng').value = lng.toFixed(6);
            updateMapMarkers();
        }

        document.getElementById('waypoint_search').value = '';
        document.getElementById('suggestions-waypoint').style.display = 'none';
        modo = null;
        editingWaypointDiv = null;
    }

    // Sincronizar mapa cuando cambian los inputs
    document.querySelectorAll('.map-sync-input').forEach(input => {
        input.addEventListener('change', updateMapMarkers);
        input.addEventListener('input', updateMapMarkers);
    });

    // Botones para editar inicio y fin
    document.getElementById('btnEditarInicio').addEventListener('click', function() {
        modo = 'inicio';
        this.classList.add('active');
        document.getElementById('btnEditarFin').classList.remove('active');
        // Ocultar búsqueda de waypoint
        document.getElementById('waypoint_search').style.display = 'none';
    });

    document.getElementById('btnEditarFin').addEventListener('click', function() {
        modo = 'fin';
        this.classList.add('active');
        document.getElementById('btnEditarInicio').classList.remove('active');
        document.getElementById('waypoint_search').style.display = 'none';
    });

    // Agregar nuevo waypoint
    document.getElementById('btnAgregarWaypoint').addEventListener('click', function() {
        modo = 'waypoint';
        document.getElementById('btnEditarInicio').classList.remove('active');
        document.getElementById('btnEditarFin').classList.remove('active');
        document.getElementById('waypoint_search').style.display = 'block';
        document.getElementById('waypoint_search').focus();
    });

    // Listeners para editar waypoints existentes
    document.querySelectorAll('.btn-editar-waypoint').forEach(btn => {
        btn.addEventListener('click', function() {
            editingWaypointDiv = this.closest('.waypoint-item');
            modo = 'edit_waypoint';
            document.getElementById('btnEditarInicio').classList.remove('active');
            document.getElementById('btnEditarFin').classList.remove('active');
            document.getElementById('waypoint_search').style.display = 'block';
            document.getElementById('waypoint_search').focus();
        });
    });

    // Listeners para eliminar waypoints existentes
    document.querySelectorAll('.btn-delete-waypoint').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.waypoint-item').remove();
            updateMapMarkers();
        });
    });

    // Event listeners para búsqueda
    let debounceTimer = {};

    document.getElementById('inicio_ruta').addEventListener('input', function() {
        clearTimeout(debounceTimer.inicio);
        const value = this.value.trim();
        debounceTimer.inicio = setTimeout(() => {
            getSuggestions(value, 'inicio');
        }, 300);
    });

    document.getElementById('fin_ruta').addEventListener('input', function() {
        clearTimeout(debounceTimer.fin);
        const value = this.value.trim();
        debounceTimer.fin = setTimeout(() => {
            getSuggestions(value, 'fin');
        }, 300);
    });

    document.getElementById('waypoint_search').addEventListener('input', function() {
        clearTimeout(debounceTimer.waypoint);
        const value = this.value.trim();
        debounceTimer.waypoint = setTimeout(() => {
            getSuggestionsWaypoint(value);
        }, 300);
    });

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#inicio_ruta, #suggestions-inicio, #fin_ruta, #suggestions-fin, #waypoint_search, #suggestions-waypoint')) {
            document.getElementById('suggestions-inicio').style.display = 'none';
            document.getElementById('suggestions-fin').style.display = 'none';
            document.getElementById('suggestions-waypoint').style.display = 'none';
        }
    });

    // Permitir cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('suggestions-inicio').style.display = 'none';
            document.getElementById('suggestions-fin').style.display = 'none';
            document.getElementById('suggestions-waypoint').style.display = 'none';
        }
    });

    // Inicializar mapa y markers
    try {
        initMap();
        updateMapMarkers();

        // Clic en el mapa
        map.on('click', async function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            if (modo === 'inicio') {
                document.getElementById('inicio_lat').value = lat.toFixed(6);
                document.getElementById('inicio_lng').value = lng.toFixed(6);
                const address = await getAddressFromCoords(lat, lng);
                document.getElementById('inicio_ruta').value = address;
                updateMapMarkers();
                modo = null;
                document.getElementById('btnEditarInicio').classList.remove('active');
            } else if (modo === 'fin') {
                document.getElementById('fin_lat').value = lat.toFixed(6);
                document.getElementById('fin_lng').value = lng.toFixed(6);
                const address = await getAddressFromCoords(lat, lng);
                document.getElementById('fin_ruta').value = address;
                updateMapMarkers();
                modo = null;
                document.getElementById('btnEditarFin').classList.remove('active');
            } else if (modo === 'waypoint') {
                const address = await getAddressFromCoords(lat, lng);
                selectWaypoint({ lat: lat.toFixed(6), lon: lng.toFixed(6), display_name: address });
            } else if (modo === 'edit_waypoint' && editingWaypointDiv) {
                editingWaypointDiv.querySelector('.waypoint-lat').value = lat.toFixed(6);
                editingWaypointDiv.querySelector('.waypoint-lng').value = lng.toFixed(6);
                const address = await getAddressFromCoords(lat, lng);
                editingWaypointDiv.querySelector('.waypoint-nombre').value = address.split(',')[0];
                updateMapMarkers();
                modo = null;
                editingWaypointDiv = null;
            }
        });
    } catch (e) {
        console.error('Error initializing map or markers:', e);
    }
} else {
    console.error('Leaflet library not loaded');
}</script>

<?php include_once "../pie.php"; ?>



