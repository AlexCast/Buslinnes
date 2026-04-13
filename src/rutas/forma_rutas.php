<?php
header('Content-Type: text/html; charset=utf-8');

/*
CRUD con PostgreSQL y PHP
Formulario para agregar nuevas rutas
@oto
*/
?>

<?php
// Token CSRF para el submit normal del formulario (no-AJAX)
require_once __DIR__ . '/../../app/SecurityMiddleware.php';
$csrfToken = SecurityMiddleware::generateCSRFToken();
?>

<?php include_once "encab_rutas.php"; ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<main class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h5 mb-0">Agregar Nueva Ruta</h1>
                </div>
                <div class="card-body">
                    <form action="insertar_rutas.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row">
                            <!-- Columna 1 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="id_ruta" class="form-label">ID Ruta</label>
                                    <input name="id_ruta" type="number" id="id_ruta" class="form-control" placeholder="ID de la ruta" min="1" max="2147483647" required onkeydown="return event.key !== 'e' && event.key !== 'E'">
                                    <small class="text-danger" id="error-id-ruta" style="display:none;"></small>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="nom_ruta" class="form-label">Nombre de la Ruta</label>
                                    <input required name="nom_ruta" type="text" id="nom_ruta" class="form-control" placeholder="Nombre de la ruta">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="hora_inicio" class="form-label">Hora de Inicio</label>
                                    <input required name="hora_inicio" type="time" id="hora_inicio" class="form-control" placeholder="HH:MM">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="hora_final" class="form-label">Hora Final</label>
                                    <input required name="hora_final" type="time" id="hora_final" class="form-control" placeholder="HH:MM">
                                </div>
                            </div>
                            <!-- Columna 2 -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="longitud" class="form-label">Distancia (km)</label>
                                    <input required name="longitud" type="text" id="longitud" class="form-control" placeholder="Distancia en kilómetros (ej: 1,5)" pattern="[0-9]+([,.][0-9]+)?" title="Ingrese un número válido, puede usar coma o punto para decimales">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="val_pasaje" class="form-label">Valor Pasaje</label>
                                    <input required name="val_pasaje" type="number" step="1" id="val_pasaje" class="form-control" placeholder="Valor del pasaje" min="1," max="9999" inputmode="numeric">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="inicio_ruta" class="form-label">Inicio de la Ruta</label>
                                    <div style="position: relative;">
                                        <input required name="inicio_ruta" type="text" id="inicio_ruta" class="form-control" placeholder="Ej: Terminal Bucaramanga" autocomplete="off">
                                        <ul id="suggestions-inicio" class="autocomplete-list" style="display:none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; list-style: none; margin: 0; padding: 0; max-height: 200px; overflow-y: auto; z-index: 1001;"></ul>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="fin_ruta" class="form-label">Fin de la Ruta</label>
                                    <div style="position: relative;">
                                        <input required name="fin_ruta" type="text" id="fin_ruta" class="form-control" placeholder="Ej: Girón Centro" autocomplete="off">
                                        <ul id="suggestions-fin" class="autocomplete-list" style="display:none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; list-style: none; margin: 0; padding: 0; max-height: 200px; overflow-y: auto; z-index: 1001;"></ul>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Coordenadas (para rutas por calles)</label>
                                    <div class="btn-group mb-2 rutas-coords-btn-group" role="group">
                                        <button type="button" class="btn btn-outline-success btn-sm rutas-btn-punto-inicio" id="btnPuntoInicio">
                                            <i class="fas fa-map-marker-alt"></i> Marcar inicio
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm rutas-btn-punto-fin" id="btnPuntoFin">
                                            <i class="fas fa-flag-checkered"></i> Marcar fin
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm rutas-btn-agregar-waypoint" id="btnAgregarWaypoint">
                                            <i class="fas fa-plus-circle"></i> Agregar waypoint
                                        </button>
                                    </div>
                                    <div id="coordsStatus" class="small text-muted mb-1 rutas-coords-status">Haz clic en "Marcar inicio" y luego en el mapa. Después "Marcar fin" y clic en el punto final.</div>
                                    
                                    <!-- Búsqueda de Waypoint -->
                                    <div id="waypointSearchContainer" style="display:none; margin-bottom: 15px;">
                                        <div style="position: relative;">
                                            <input type="text" id="waypoint_search" class="form-control" placeholder="Busca un barrio, calle o lugar para waypoint" autocomplete="off" style="margin-bottom: 8px;">
                                            <ul id="suggestions-waypoint" class="autocomplete-list" style="display:none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; list-style: none; margin: 0; padding: 0; max-height: 200px; overflow-y: auto; z-index: 1001;"></ul>
                                        </div>
                                        <small class="text-muted-rutas">O haz clic en el mapa para marcar manualmente</small>
                                    </div>
                                    
                                    <!-- Lista de waypoints -->
                                    <div id="waypointsContainer" class="mb-2" style="display:none;">
                                        <div class="alert alert-info mb-2">
                                            <strong>Waypoints (puntos intermedios):</strong>
                                            <ul id="waypointsList" class="mb-0 mt-2"></ul>
                                        </div>
                                    </div>
                                    
                                    <div id="map-picker" style="height: 420px; border-radius: 8px; border: 1px solid #dee2e6;"></div>
                                    <input type="hidden" name="inicio_lat" id="inicio_lat" value="">
                                    <input type="hidden" name="inicio_lng" id="inicio_lng" value="">
                                    <input type="hidden" name="fin_lat" id="fin_lat" value="">
                                    <input type="hidden" name="fin_lng" id="fin_lng" value="">
                                    <input type="hidden" name="waypoints_json" id="waypoints_json" value="">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="./listar_rutas.php" class="btn btn-warning">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success" id="submitBtn">
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
document.addEventListener('DOMContentLoaded', function() {

    const map = L.map('map-picker').setView([7.1193, -73.1227], 12);
    const lightTiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    });
    const darkTiles = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 19
    });

    let activeBaseLayer = null;
    function applyMapTheme() {
        const useDarkTheme = document.body.classList.contains('dark');
        const nextLayer = useDarkTheme ? darkTiles : lightTiles;

        if (activeBaseLayer === nextLayer) {
            return;
        }

        if (activeBaseLayer && map.hasLayer(activeBaseLayer)) {
            map.removeLayer(activeBaseLayer);
        }

        nextLayer.addTo(map);
        activeBaseLayer = nextLayer;
    }

    applyMapTheme();

    const themeObserver = new MutationObserver(() => {
        applyMapTheme();
    });
    themeObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });

    window.addEventListener('beforeunload', function() {
        themeObserver.disconnect();
    });

    let markerInicio = null, markerFin = null;
    let modo = 'inicio';
    let waypoints = []; // Array para guardar waypoints
    let waypointMarkers = {}; // Diccionario de marcadores de waypoints
    let ultimoCalculoKey = '';
    let recalculoTimer = null;
    
    const btnInicio = document.getElementById('btnPuntoInicio');
    const btnFin = document.getElementById('btnPuntoFin');
    const btnWaypoint = document.getElementById('btnAgregarWaypoint');
    const coordsStatus = document.getElementById('coordsStatus');
    const waypointsContainer = document.getElementById('waypointsContainer');
    const waypointsList = document.getElementById('waypointsList');

    const inicioRutaInput = document.getElementById('inicio_ruta');
    const finRutaInput = document.getElementById('fin_ruta');
    const waypointSearchInput = document.getElementById('waypoint_search');
    const suggestionsInicio = document.getElementById('suggestions-inicio');
    const suggestionsFin = document.getElementById('suggestions-fin');
    const suggestionsWaypoint = document.getElementById('suggestions-waypoint');

    function actualizarEstado() {
        const ilat = document.getElementById('inicio_lat').value;
        const ilng = document.getElementById('inicio_lng').value;
        const flat = document.getElementById('fin_lat').value;
        const flng = document.getElementById('fin_lng').value;
        
        let status = '';
        if (ilat && ilng && flat && flng) {
            status = '✓ Inicio: ' + ilat + ', ' + ilng + ' | Fin: ' + flat + ', ' + flng;
            if (waypoints.length > 0) {
                status += ` (+ ${waypoints.length} waypoint${waypoints.length === 1 ? '' : 's'})`;
            }
            coordsStatus.textContent = status;
            coordsStatus.classList.remove('text-muted-rutas');
            coordsStatus.classList.add('text-success');
        } else if (ilat && ilng) {
            coordsStatus.textContent = 'Inicio listo. Haz clic en "Marcar fin" y luego en el mapa.';
        } else {
            coordsStatus.textContent = 'Haz clic en "Marcar inicio" y luego en el mapa. Después "Marcar fin" y clic en el punto final.';
            coordsStatus.classList.remove('text-success');
            coordsStatus.classList.add('text-muted-rutas');
        }
    }

    function obtenerClaveCalculoActual() {
        const ilat = document.getElementById('inicio_lat').value || '';
        const ilng = document.getElementById('inicio_lng').value || '';
        const flat = document.getElementById('fin_lat').value || '';
        const flng = document.getElementById('fin_lng').value || '';
        const wpKey = waypoints.map(wp => `${wp.lat},${wp.lng}`).join('|');
        return `${ilat};${ilng};${flat};${flng};${wpKey}`;
    }

    function programarRecalculoRuta(forzar = false) {
        const ilat = document.getElementById('inicio_lat').value;
        const ilng = document.getElementById('inicio_lng').value;
        const flat = document.getElementById('fin_lat').value;
        const flng = document.getElementById('fin_lng').value;

        if (!ilat || !ilng || !flat || !flng) {
            return;
        }

        const clave = obtenerClaveCalculoActual();
        if (!forzar && clave === ultimoCalculoKey) {
            return;
        }

        if (recalculoTimer) {
            clearTimeout(recalculoTimer);
        }

        recalculoTimer = setTimeout(() => {
            coordsStatus.textContent = 'Calculando ruta y distancia...';
            coordsStatus.classList.remove('text-muted-rutas', 'text-warning');
            coordsStatus.classList.add('text-info');
            ultimoCalculoKey = clave;
            calcularDistancia();
        }, 120);
    }

    function mostrarWaypoints() {
        waypointsList.innerHTML = '';
        
        if (waypoints.length === 0) {
            waypointsContainer.style.display = 'none';
            return;
        }

        waypointsContainer.style.display = 'block';
        
        waypoints.forEach((wp, index) => {
            const li = document.createElement('li');
            li.className = 'mb-2 rutas-waypoint-item';
            li.innerHTML = `
                <div class="d-flex justify-content-between align-items-start rutas-waypoint-row">
                    <div class="rutas-waypoint-info">
                        <strong>Waypoint ${index + 1}:</strong> ${wp.nombre || wp.lat + ', ' + wp.lng}
                        <br><small class="text-muted-rutas">${wp.lat.toFixed(4)}, ${wp.lng.toFixed(4)}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger rutas-waypoint-btn-delete" onclick="window.eliminarWaypoint(${index})" title="Eliminar waypoint ${index + 1}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            waypointsList.appendChild(li);
        });

        // Guardar waypoints en JSON
        document.getElementById('waypoints_json').value = JSON.stringify(waypoints.map((wp, idx) => ({
            orden: idx + 1,
            lat: wp.lat,
            lng: wp.lng,
            nombre: wp.nombre
        })));

        // Recalcular distancia con OSRM considerando waypoints
        programarRecalculoRuta(true);
    }

    window.eliminarWaypoint = function(index) {
        waypoints.splice(index, 1);
        
        // Remover marcador del mapa
        if (waypointMarkers[index]) {
            map.removeLayer(waypointMarkers[index]);
        }
        
        // Reordenar diccionario de marcadores
        const nuevosDiccionario = {};
        waypoints.forEach((wp, i) => {
            if (waypointMarkers[i]) {
                nuevosDiccionario[i] = waypointMarkers[i];
            }
        });
        waypointMarkers = nuevosDiccionario;
        
        mostrarWaypoints();
    };

    btnInicio.classList.add('active');
    btnInicio.addEventListener('click', function() {
        modo = 'inicio';
        btnInicio.classList.add('active');
        btnFin.classList.remove('active');
        btnWaypoint.classList.remove('active');
        document.getElementById('waypointSearchContainer').style.display = 'none';
        coordsStatus.textContent = 'Modo: Marcar INICIO. Haz clic en el mapa.';
    });
    
    btnFin.addEventListener('click', function() {
        modo = 'fin';
        btnFin.classList.add('active');
        btnInicio.classList.remove('active');
        btnWaypoint.classList.remove('active');
        document.getElementById('waypointSearchContainer').style.display = 'none';
        coordsStatus.textContent = 'Modo: Marcar FIN. Haz clic en el mapa.';
    });

    btnWaypoint.addEventListener('click', function() {
        modo = 'waypoint';
        btnWaypoint.classList.add('active');
        btnInicio.classList.remove('active');
        btnFin.classList.remove('active');
        
        // Mostrar campo de búsqueda
        document.getElementById('waypointSearchContainer').style.display = 'block';
        document.getElementById('waypoint_search').focus();
        coordsStatus.textContent = 'Modo: Agregar WAYPOINT. Busca un lugar o haz clic en el mapa.';
    });

    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        if (modo === 'inicio') {
            document.getElementById('inicio_lat').value = lat.toFixed(6);
            document.getElementById('inicio_lng').value = lng.toFixed(6);
            if (markerInicio) map.removeLayer(markerInicio);
            markerInicio = L.marker(e.latlng, { icon: L.divIcon({ className: 'inicio-marker', html: '<span style="color:green;font-size:24px">●</span>', iconSize: [24, 24] }) }).addTo(map);
            
            // Obtener dirección y autocompletar el input
            getAddressFromCoords(lat, lng).then(address => {
                document.getElementById('inicio_ruta').value = address;
            });
            
            modo = 'fin';
            btnInicio.classList.remove('active');
            btnFin.classList.add('active');
            coordsStatus.textContent = 'Inicio marcado. Ahora marca el FIN. Haz clic en "Marcar fin" y luego en el mapa.';
        } else if (modo === 'fin') {
            document.getElementById('fin_lat').value = lat.toFixed(6);
            document.getElementById('fin_lng').value = lng.toFixed(6);
            if (markerFin) map.removeLayer(markerFin);
            markerFin = L.marker(e.latlng, { icon: L.divIcon({ className: 'fin-marker', html: '<span style="color:red;font-size:24px">●</span>', iconSize: [24, 24] }) }).addTo(map);
            
            // Obtener dirección y autocompletar el input
            getAddressFromCoords(lat, lng).then(address => {
                document.getElementById('fin_ruta').value = address;
            });
            
            modo = null;
            btnFin.classList.remove('active');
            coordsStatus.textContent = 'Inicio y fin marcados. Ahora puedes agregar waypoints o guardar la ruta.';
            
            // Calcular distancia y dibujar ruta
            programarRecalculoRuta(true);
        } else if (modo === 'waypoint') {
            const waypointIndex = waypoints.length;
            waypoints.push({ lat, lng, nombre: null });
            
            // Agregar marcador al mapa (amarillo)
            const waypointMarker = L.marker(e.latlng, { 
                icon: L.divIcon({ 
                    className: 'waypoint-marker', 
                    html: '<span style="color:#FFA500;font-size:20px">◆</span>', 
                    iconSize: [20, 20] 
                }) 
            }).addTo(map).bindPopup(`Waypoint ${waypointIndex + 1}`);
            
            waypointMarkers[waypointIndex] = waypointMarker;
            mostrarWaypoints();
        }
        
        actualizarEstado();
    });

    actualizarEstado();

    // Función para obtener dirección desde coordenadas (reverse geocoding)
    async function getAddressFromCoords(lat, lng) {
        try {
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data && data.display_name) {
                // Extraer nombre simplificado
                let displayName = data.name || data.display_name.split(',')[0];
                
                // Si es muy largo, acortar
                if (displayName.length > 50) {
                    displayName = displayName.substring(0, 47) + '...';
                }
                
                return displayName;
            } else {
                // Fallback a coordenadas si no hay dirección
                return `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
            }
        } catch (error) {
            console.error('Error en reverse geocoding:', error);
            // Fallback a coordenadas
            return `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
        }
    }

    let debounceTimer = {};

    // Función para obtener sugerencias de Nominatim
    async function getSuggestions(query, tipoRuta) {
        if (query.length < 2) {
            const suggestionsList = tipoRuta === 'inicio' ? suggestionsInicio : suggestionsFin;
            suggestionsList.style.display = 'none';
            return;
        }

        const suggestionsList = tipoRuta === 'inicio' ? suggestionsInicio : suggestionsFin;
        suggestionsList.innerHTML = '<li style="padding: 10px 12px; color: #666;">🔍 Buscando...</li>';
        suggestionsList.style.display = 'block';

        try {
            // Límites expandidos de los 4 municipios para cubrir todos los barrios:
            // Bucaramanga: ~7.1,-73.1, Girón: ~7.0,-73.2, Piedecuesta: ~6.8,-73.1, Lebrija: ~6.9,-73.2
            // Viewbox expandido: lon -73.4 a -72.85, lat 6.65 a 7.3
            const viewbox = '-73.4,6.65,-72.85,7.3';
            
            const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=20&viewbox=${viewbox}&dedupe=1`;
            
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            const suggestionsList = tipoRuta === 'inicio' ? suggestionsInicio : suggestionsFin;
            suggestionsList.innerHTML = '';

            if (data.length === 0) {
                const li = document.createElement('li');
                li.style.padding = '10px 12px';
                li.style.color = '#999';
                li.textContent = 'No encontrado en Bucaramanga, Girón, Piedecuesta o Lebrija';
                suggestionsList.appendChild(li);
                suggestionsList.style.display = 'block';
                return;
            }

            // Filtrar solo resultados dentro del área de los 4 municipios (con rango expandido)
            const validResults = data.filter(place => {
                const lat = parseFloat(place.lat);
                const lon = parseFloat(place.lon);
                // Verificar si está dentro del rango expandido
                return lat >= 6.65 && lat <= 7.3 && lon >= -73.4 && lon <= -72.85;
            });

            if (validResults.length === 0) {
                const li = document.createElement('li');
                li.style.padding = '10px 12px';
                li.style.color = '#999';
                li.textContent = 'No encontrado en estos municipios. Intenta otra búsqueda.';
                suggestionsList.appendChild(li);
                suggestionsList.style.display = 'block';
                return;
            }

            validResults.slice(0, 8).forEach((place) => {
                const li = document.createElement('li');
                li.style.padding = '10px 12px';
                li.style.borderBottom = '1px solid #eee';
                li.style.cursor = 'pointer';
                li.style.transition = 'background 0.2s';
                
                // Detectar tipo de lugar por el nombre
                let icon = '📍';
                const displayNameLower = place.display_name.toLowerCase();
                if (displayNameLower.includes('parque')) icon = '🎡';
                if (displayNameLower.includes('universi')) icon = '🎓';
                if (displayNameLower.includes('estación') || displayNameLower.includes('terminal') || displayNameLower.includes('station') || displayNameLower.includes('bus')) icon = '🚌';
                if (displayNameLower.includes('barrio') || displayNameLower.includes('comuna') || displayNameLower.includes('neighborhood')) icon = '🏘️';
                
                li.innerHTML = `
                    <strong>${icon} ${place.name || place.display_name.split(',')[0]}</strong><br>
                    <small style="color: #666;">${place.display_name.substring(0, 65)}</small>
                `;

                li.addEventListener('mouseover', () => {
                    li.style.background = '#f5f5f5';
                });
                li.addEventListener('mouseout', () => {
                    li.style.background = 'white';
                });

                li.addEventListener('click', () => {
                    selectPlace(place, tipoRuta);
                });

                suggestionsList.appendChild(li);
            });

            suggestionsList.style.display = 'block';
        } catch (error) {
            console.error('Error obteniendo sugerencias:', error);
            const suggestionsList = tipoRuta === 'inicio' ? suggestionsInicio : suggestionsFin;
            suggestionsList.innerHTML = '<li style="padding: 10px 12px; color: #999;">⚠️ Error de conexión. Intenta de nuevo.</li>';
            suggestionsList.style.display = 'block';
        }
    }

    async function geocodificarTextoDireccion(query) {
        const texto = (query || '').trim();
        if (texto.length < 3) return null;

        try {
            const viewbox = '-73.4,6.65,-72.85,7.3';
            const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(texto)}&format=json&limit=1&viewbox=${viewbox}&dedupe=1`;
            const response = await fetch(url);
            if (!response.ok) return null;
            const data = await response.json();
            if (!Array.isArray(data) || !data[0]) return null;
            return data[0];
        } catch (error) {
            console.warn('Geocodificación por texto falló:', error);
            return null;
        }
    }

    async function resolverCampoDireccionATexto(tipoRuta) {
        const inputEl = tipoRuta === 'inicio' ? inicioRutaInput : finRutaInput;
        const latEl = document.getElementById(tipoRuta === 'inicio' ? 'inicio_lat' : 'fin_lat');
        const lngEl = document.getElementById(tipoRuta === 'inicio' ? 'inicio_lng' : 'fin_lng');

        if (!inputEl || !latEl || !lngEl) return;
        if (latEl.value && lngEl.value) return;

        const place = await geocodificarTextoDireccion(inputEl.value);
        if (!place) return;
        selectPlace(place, tipoRuta);
    }

    // Función para seleccionar un lugar de la lista
    function selectPlace(place, tipoRuta) {
        const lat = parseFloat(place.lat);
        const lng = parseFloat(place.lon);
        const displayName = place.name || place.display_name.split(',')[0];

        const suggestionsList = tipoRuta === 'inicio' ? suggestionsInicio : suggestionsFin;
        const inputEl = tipoRuta === 'inicio' ? inicioRutaInput : finRutaInput;

        // Actualizar el input
        inputEl.value = displayName;

        // Actualizar campos ocultos
        if (tipoRuta === 'inicio') {
            document.getElementById('inicio_lat').value = lat.toFixed(6);
            document.getElementById('inicio_lng').value = lng.toFixed(6);

            // Remover marcador anterior y crear uno nuevo
            if (markerInicio) map.removeLayer(markerInicio);
            markerInicio = L.marker([lat, lng], { 
                icon: L.divIcon({ 
                    className: 'inicio-marker', 
                    html: '<span style="color:green;font-size:24px">●</span>', 
                    iconSize: [24, 24] 
                }) 
            }).addTo(map).bindPopup(`Inicio: ${displayName}`);
        } else {
            document.getElementById('fin_lat').value = lat.toFixed(6);
            document.getElementById('fin_lng').value = lng.toFixed(6);

            // Remover marcador anterior y crear uno nuevo
            if (markerFin) map.removeLayer(markerFin);
            markerFin = L.marker([lat, lng], { 
                icon: L.divIcon({ 
                    className: 'fin-marker', 
                    html: '<span style="color:red;font-size:24px">●</span>', 
                    iconSize: [24, 24] 
                }) 
            }).addTo(map).bindPopup(`Fin: ${displayName}`);
        }

        map.flyTo([lat, lng], 15);
        suggestionsList.style.display = 'none';
        actualizarEstado();

        // Calcular distancia si ambos puntos están listos
        programarRecalculoRuta(true);
    }

    // Función para obtener sugerencias de Nominatim para waypoints
    async function getSuggestionsWaypoint(query) {
        if (query.length < 2) {
            suggestionsWaypoint.style.display = 'none';
            return;
        }

        suggestionsWaypoint.innerHTML = '<li style="padding: 10px 12px; color: #666;">🔍 Buscando...</li>';
        suggestionsWaypoint.style.display = 'block';

        try {
            const viewbox = '-73.4,6.65,-72.85,7.3';
            const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=20&viewbox=${viewbox}&dedupe=1`;
            
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            suggestionsWaypoint.innerHTML = '';

            if (data.length === 0) {
                const li = document.createElement('li');
                li.style.padding = '10px 12px';
                li.style.color = '#999';
                li.textContent = 'No encontrado en los municipios';
                suggestionsWaypoint.appendChild(li);
                suggestionsWaypoint.style.display = 'block';
                return;
            }

            const validResults = data.filter(place => {
                const lat = parseFloat(place.lat);
                const lon = parseFloat(place.lon);
                return lat >= 6.65 && lat <= 7.3 && lon >= -73.4 && lon <= -72.85;
            });

            if (validResults.length === 0) {
                const li = document.createElement('li');
                li.style.padding = '10px 12px';
                li.style.color = '#999';
                li.textContent = 'No encontrado en estos municipios. Intenta otra búsqueda.';
                suggestionsWaypoint.appendChild(li);
                suggestionsWaypoint.style.display = 'block';
                return;
            }

            validResults.slice(0, 8).forEach((place) => {
                const li = document.createElement('li');
                li.style.padding = '10px 12px';
                li.style.borderBottom = '1px solid #eee';
                li.style.cursor = 'pointer';
                li.style.transition = 'background 0.2s';
                
                let icon = '📍';
                const displayNameLower = place.display_name.toLowerCase();
                if (displayNameLower.includes('parque')) icon = '🎡';
                if (displayNameLower.includes('universi')) icon = '🎓';
                if (displayNameLower.includes('estación') || displayNameLower.includes('terminal') || displayNameLower.includes('station') || displayNameLower.includes('bus')) icon = '🚌';
                if (displayNameLower.includes('barrio') || displayNameLower.includes('comuna') || displayNameLower.includes('neighborhood')) icon = '🏘️';
                
                li.innerHTML = `
                    <strong>${icon} ${place.name || place.display_name.split(',')[0]}</strong><br>
                    <small style="color: #666;">${place.display_name.substring(0, 65)}</small>
                `;

                li.addEventListener('mouseover', () => {
                    li.style.background = '#f5f5f5';
                });
                li.addEventListener('mouseout', () => {
                    li.style.background = 'white';
                });

                li.addEventListener('click', () => {
                    selectWaypoint(place);
                });

                suggestionsWaypoint.appendChild(li);
            });

            suggestionsWaypoint.style.display = 'block';
        } catch (error) {
            console.error('Error obteniendo sugerencias de waypoint:', error);
            suggestionsWaypoint.innerHTML = '<li style="padding: 10px 12px; color: #999;">⚠️ Error de conexión. Intenta de nuevo.</li>';
            suggestionsWaypoint.style.display = 'block';
        }
    }

    // Función para seleccionar un waypoint de la lista
    function selectWaypoint(place) {
        const lat = parseFloat(place.lat);
        const lng = parseFloat(place.lon);
        const displayName = place.name || place.display_name.split(',')[0];

        // Agregar waypoint al array
        const waypointIndex = waypoints.length;
        waypoints.push({ lat, lng, nombre: displayName });
        
        // Agregar marcador al mapa (naranja)
        const waypointMarker = L.marker([lat, lng], { 
            icon: L.divIcon({ 
                className: 'waypoint-marker', 
                html: '<span style="color:#FFA500;font-size:20px">◆</span>', 
                iconSize: [20, 20] 
            }) 
        }).addTo(map).bindPopup(`<strong>${displayName}</strong><br>Waypoint ${waypointIndex + 1}`);
        
        waypointMarkers[waypointIndex] = waypointMarker;
        
        // Limpiar búsqueda
        waypointSearchInput.value = '';
        suggestionsWaypoint.style.display = 'none';
        
        // Actualizar lista y distancia
        mostrarWaypoints();
        map.flyTo([lat, lng], 15);
    }

    // Función para calcular la distancia con waypoints usando OSRM
    function calcularDistanciaHaversineKm(lat1, lon1, lat2, lon2) {
        const R = 6371; // km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function dibujarRutaEnMapa(latlngs) {
        if (!Array.isArray(latlngs) || latlngs.length < 2) {
            return;
        }

        if (window.rutaPolyline) {
            map.removeLayer(window.rutaPolyline);
        }

        window.rutaPolyline = L.polyline(latlngs, {
            color: '#0066ff',
            weight: 3,
            opacity: 0.7,
            lineCap: 'round',
            lineJoin: 'round'
        }).addTo(map);

        map.fitBounds(window.rutaPolyline.getBounds(), { padding: [50, 50] });
    }

    function aplicarFallbackDistanciaYTrazo(ilat, ilng, flat, flng) {
        const puntos = [[Number(ilat), Number(ilng)]];
        waypoints.forEach((wp) => puntos.push([Number(wp.lat), Number(wp.lng)]));
        puntos.push([Number(flat), Number(flng)]);

        let totalKm = 0;
        for (let i = 0; i < puntos.length - 1; i++) {
            const [lat1, lon1] = puntos[i];
            const [lat2, lon2] = puntos[i + 1];
            totalKm += calcularDistanciaHaversineKm(lat1, lon1, lat2, lon2);
        }

        document.getElementById('longitud').value = totalKm.toFixed(2).replace('.', ',');
        dibujarRutaEnMapa(puntos);
        coordsStatus.textContent = 'No se pudo trazar por calles con OSRM. Se mostró una ruta aproximada en línea recta.';
        coordsStatus.classList.remove('text-muted-rutas');
        coordsStatus.classList.add('text-warning');
    }

    async function calcularDistanciaConWaypoints() {
        const ilat = document.getElementById('inicio_lat').value;
        const ilng = document.getElementById('inicio_lng').value;
        const flat = document.getElementById('fin_lat').value;
        const flng = document.getElementById('fin_lng').value;

        // Solo calcular si tenemos al menos inicio y fin
        if (!ilat || !ilng || !flat || !flng) {
            return;
        }

        try {
            // Construir coordinates de OSRM incluyendo waypoints
            let coords = `${ilng},${ilat}`;
            
            // Agregar waypoints en orden
            waypoints.forEach(wp => {
                coords += `;${wp.lng},${wp.lat}`;
            });
            
            // Agregar fin
            coords += `;${flng},${flat}`;

            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${coords}?overview=full&geometries=geojson`;
            const response = await fetch(osrmUrl);
            if (!response.ok) {
                throw new Error(`OSRM HTTP ${response.status}`);
            }
            const data = await response.json();

            if (data.code === 'Ok' && data.routes && data.routes[0]) {
                // La distancia viene en metros, convertir a kilómetros
                const distanciaMetros = data.routes[0].distance;
                const distanciaKm = (distanciaMetros / 1000).toFixed(2).replace('.', ',');
                
                document.getElementById('longitud').value = distanciaKm;
                
                // Dibujar la ruta en el mapa
                if (data.routes[0].geometry && data.routes[0].geometry.coordinates) {
                    const coords = data.routes[0].geometry.coordinates;
                    const latlngs = coords.map(c => [c[1], c[0]]);
                    dibujarRutaEnMapa(latlngs);
                }
            } else {
                aplicarFallbackDistanciaYTrazo(ilat, ilng, flat, flng);
            }
        } catch (error) {
            console.error('Error calculando distancia con waypoints:', error);
            aplicarFallbackDistanciaYTrazo(ilat, ilng, flat, flng);
        }
    }

    // Función para calcular la distancia entre inicio y fin (sin waypoints)
    async function calcularDistancia() {
        if (waypoints.length > 0) {
            // Si hay waypoints, usar la función expandida
            calcularDistanciaConWaypoints();
            return;
        }

        const ilat = document.getElementById('inicio_lat').value;
        const ilng = document.getElementById('inicio_lng').value;
        const flat = document.getElementById('fin_lat').value;
        const flng = document.getElementById('fin_lng').value;

        // Solo calcular si tenemos ambos puntos
        if (!ilat || !ilng || !flat || !flng) {
            return;
        }

        try {
            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${ilng},${ilat};${flng},${flat}?overview=full&geometries=geojson`;
            const response = await fetch(osrmUrl);
            if (!response.ok) {
                throw new Error(`OSRM HTTP ${response.status}`);
            }
            const data = await response.json();

            if (data.code === 'Ok' && data.routes && data.routes[0]) {
                // La distancia viene en metros, convertir a kilómetros
                const distanciaMetros = data.routes[0].distance;
                const distanciaKm = (distanciaMetros / 1000).toFixed(2).replace('.', ',');
                
                document.getElementById('longitud').value = distanciaKm;
                
                // Dibujar la ruta en el mapa
                if (data.routes[0].geometry && data.routes[0].geometry.coordinates) {
                    const coords = data.routes[0].geometry.coordinates;
                    const latlngs = coords.map(c => [c[1], c[0]]);
                    dibujarRutaEnMapa(latlngs);
                }
            } else {
                aplicarFallbackDistanciaYTrazo(ilat, ilng, flat, flng);
            }
        } catch (error) {
            console.error('Error calculando distancia:', error);
            aplicarFallbackDistanciaYTrazo(ilat, ilng, flat, flng);
        }
    }

    // Event listener para "Inicio de Ruta"
    inicioRutaInput.addEventListener('input', function() {
        clearTimeout(debounceTimer.inicio);
        const value = this.value.trim();
        document.getElementById('inicio_lat').value = '';
        document.getElementById('inicio_lng').value = '';

        debounceTimer.inicio = setTimeout(() => {
            getSuggestions(value, 'inicio');
        }, 300);
    });

    // Event listener para "Fin de Ruta"
    finRutaInput.addEventListener('input', function() {
        clearTimeout(debounceTimer.fin);
        const value = this.value.trim();
        document.getElementById('fin_lat').value = '';
        document.getElementById('fin_lng').value = '';

        debounceTimer.fin = setTimeout(() => {
            getSuggestions(value, 'fin');
        }, 300);
    });

    // Si escribe manual y no selecciona sugerencia, resolver al salir del campo
    inicioRutaInput.addEventListener('blur', () => {
        setTimeout(() => resolverCampoDireccionATexto('inicio'), 120);
    });
    finRutaInput.addEventListener('blur', () => {
        setTimeout(() => resolverCampoDireccionATexto('fin'), 120);
    });

    // Event listener para búsqueda de Waypoint
    waypointSearchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer.waypoint);
        const value = this.value.trim();

        debounceTimer.waypoint = setTimeout(() => {
            getSuggestionsWaypoint(value);
        }, 300);
    });

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="inicio_ruta"], [id^="sugg"], [id^="fin_ruta"], [id^="waypoint"]')) {
            suggestionsInicio.style.display = 'none';
            suggestionsFin.style.display = 'none';
            suggestionsWaypoint.style.display = 'none';
        }
    });

    // Permitir cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            suggestionsInicio.style.display = 'none';
            suggestionsFin.style.display = 'none';
            suggestionsWaypoint.style.display = 'none';
        }
    });

    // Validación del formulario para el campo ID Ruta
    const idRutaInput = document.getElementById('id_ruta');
    const errorIdRuta = document.getElementById('error-id-ruta');
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');

    // Rango válido para un entero de 32 bits (Integer en PostgreSQL)
    const MIN_ID = 1;
    const MAX_ID = 2147483647;

    // Debounce para validar contra el servidor
    let idCheckTimeout;
    
    function validateIdFormat(value) {
        if (value === '') return null;
        const numValue = parseInt(value, 10);
        if (isNaN(numValue)) return 'El valor debe ser un número';
        if (numValue < MIN_ID || numValue > MAX_ID) return `Rango válido: ${MIN_ID} a ${MAX_ID}`;
        return null;
    }

    async function checkIdExists(idValue) {
        if (!idValue) return;
        
        try {
            const response = await securityHelper.get(`./check_id.php?id_ruta=${encodeURIComponent(idValue)}`);
            const data = await response.json();
            
            if (data.exists) {
                errorIdRuta.textContent = '❌ El ID no puede repetirse. Este ID ya existe en el sistema.';
                errorIdRuta.style.display = 'block';
                errorIdRuta.style.color = '#dc3545';
                submitBtn.disabled = true;
            } else {
                errorIdRuta.style.display = 'none';
                submitBtn.disabled = false;
            }
        } catch (err) {
            console.error('Error verificando ID:', err);
            errorIdRuta.style.display = 'none';
            submitBtn.disabled = false;
        }
    }

    // Validar en tiempo real mientras el usuario escribe
    idRutaInput.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value === '') {
            errorIdRuta.style.display = 'none';
            submitBtn.disabled = false;
            clearTimeout(idCheckTimeout);
            return;
        }

        // Validar formato primero
        const formatError = validateIdFormat(value);
        if (formatError) {
            errorIdRuta.textContent = '⚠️ ' + formatError;
            errorIdRuta.style.display = 'block';
            errorIdRuta.style.color = '#dc3545';
            submitBtn.disabled = true;
            clearTimeout(idCheckTimeout);
        } else {
            // Debounce: esperar 500ms tras dejar de escribir para verificar en BD
            clearTimeout(idCheckTimeout);
            idCheckTimeout = setTimeout(() => {
                checkIdExists(value);
            }, 500);
        }
    });

    // Validar también en el evento de envío del formulario
    form.addEventListener('submit', function(e) {
        const value = idRutaInput.value.trim();
        const formatError = validateIdFormat(value);
        
        if (formatError) {
            e.preventDefault();
            errorIdRuta.textContent = '⚠️ ' + formatError;
            errorIdRuta.style.display = 'block';
            errorIdRuta.style.color = '#dc3545';
            submitBtn.disabled = true;
            return false;
        }
        
        if (submitBtn.disabled) {
            e.preventDefault();
            return false;
        }
    });

    // Red de seguridad: si por algún motivo no se disparó el evento correcto,
    // detectar coordenadas completas y recalcular automáticamente.
    setInterval(() => {
        const ilat = document.getElementById('inicio_lat').value;
        const ilng = document.getElementById('inicio_lng').value;
        const flat = document.getElementById('fin_lat').value;
        const flng = document.getElementById('fin_lng').value;
        const longitudActual = document.getElementById('longitud').value.trim();
        if (ilat && ilng && flat && flng && !longitudActual) {
            programarRecalculoRuta();
        }
    }, 1500);
});
</script>
<?php include_once "../pie.php"; ?>



