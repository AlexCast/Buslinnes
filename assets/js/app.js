document.addEventListener('DOMContentLoaded', () => {
	const API_URL = '/buslinnes/src/rutas/api_listar_rutas.php';
	const colores = ['#2563eb','#dc2626','#16a34a','#ca8a04','#7c3aed','#9333ea','#ea580c','#0891b2'];
	let routes = [];
	let selectedRoute = null;
	let map = null;
	let currentTileLayer = null;
	let userLocation = null;
	let userMarker = null;

	const routesListContainer = document.getElementById('routes-list-container');
	const mapPlaceholder = document.getElementById('map-placeholder');
	const mapContainer = document.getElementById('map-container');

	// ensure the map element is visible right away (hidden placeholder only when no map)
	if (mapContainer) {
		// create map instance early so it exists regardless of route selection
		map = window.buslinnesMap || null;
		if (!map) {
			map = L.map('map-container').setView([7.1193, -73.1227], 13);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap contributors', maxZoom:19 }).addTo(map);
			L.control.scale().addTo(map);
			window.buslinnesMap = map;
		}
		// reveal the container and remove placeholder
		mapContainer.classList.remove('hidden');
		mapContainer.style.minHeight = '600px';
		if (mapPlaceholder) mapPlaceholder.style.display = 'none';
	}

	const mapUrls = {
		light: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
		dark: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
	};

	function updateMapTheme() {
		if (!map) return;
		const isDark = document.body.classList.contains('dark');
		const mapUrl = isDark ? mapUrls.dark : mapUrls.light;
		const attribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' + (isDark ? ' | &copy; <a href="https://carto.com/">CARTO</a>' : '');
		if (currentTileLayer) map.removeLayer(currentTileLayer);
		currentTileLayer = L.tileLayer(mapUrl, { attribution, maxZoom: 19 }).addTo(map);
	}

	const observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			if (mutation.type === 'attributes' && mutation.attributeName === 'class') updateMapTheme();
		});
	});
	observer.observe(document.body, { attributes: true });

	async function fetchRoutes() {
		try {
			const resp = await securityHelper.get(API_URL);
			routes = await resp.json();
			routes = routes.map((r, idx) => ({
				id: r.id_ruta,
				number: String(r.id_ruta),
				name: r.nom_ruta,
				description: `${r.inicio_ruta} → ${r.fin_ruta}`,
				buses: Number(r.buses_count) || 0,
				frequency: '',
				color: colores[idx % colores.length],
				inicio_lat: r.inicio_lat,
				inicio_lng: r.inicio_lng,
				fin_lat: r.fin_lat,
				fin_lng: r.fin_lng,
				waypoints: r.waypoints || []
			}));
			renderRoutes();
		} catch (e) {
			console.error('Error fetching routes:', e);
		}
	}

	function renderRoutes() {
		routesListContainer.innerHTML = '';
		routes.forEach(route => {
			const routeElement = document.createElement('div');
			routeElement.className = 'route-item';
			routeElement.dataset.routeId = route.id;
			routeElement.innerHTML = `
				<div class="route-header">
					<div class="route-info">
						<div class="route-number" style="background-color: ${route.color};">${route.number}</div>
						<div class="route-details">
							<h3>${route.name}</h3>
							<p>${route.description}</p>
						</div>
					</div>
					<div class="route-arrow"><i class="fas fa-chevron-right"></i></div>
				</div>
				<div class="route-stats"><span><span class="route-stat-value">${route.buses}</span> buses</span></div>
			`;
			routeElement.addEventListener('click', () => handleRouteSelect(route));
			routesListContainer.appendChild(routeElement);
		});
	}

	async function handleRouteSelect(route) {
		selectedRoute = route;
		mapPlaceholder.classList.add('hidden');
		mapContainer.classList.remove('hidden');

				if (map) map.remove();
				// Reuse global map if another script already created it
				map = window.buslinnesMap || null;
				if (!map) {
					map = L.map('map-container').setView([7.1193, -73.1227], 13);
					window.buslinnesMap = map;
				// si ya tenemos ubicación, mostrar marcador
				maybeShowUserMarker();
		// Construir URL de OSRM incluyendo waypoints si existen
		let osrmCoords = `${route.inicio_lng},${route.inicio_lat}`;
		if (route.waypoints && route.waypoints.length > 0) {
			route.waypoints.sort((a, b) => a.orden - b.orden);
			route.waypoints.forEach(wp => {
				osrmCoords += `;${wp.lng},${wp.lat}`;
			});
		}
		osrmCoords += `;${route.fin_lng},${route.fin_lat}`;
		const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${osrmCoords}?overview=full&geometries=geojson`;
		try {
			const resp = await fetch(osrmUrl);
			const data = await resp.json();
			let coords;
			if (data.code === 'Ok' && data.routes && data.routes[0]) {
				coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
			} else {
				coords = [[route.inicio_lat, route.inicio_lng], [route.fin_lat, route.fin_lng]];
			}
			const poly = L.polyline(coords, { color: route.color, weight: 5 }).addTo(map);
			map.fitBounds(poly.getBounds().pad(0.1));
			L.circleMarker(coords[0], { radius:6, color:'green', fillColor:'green', weight:2 }).addTo(map);
			L.circleMarker(coords[coords.length-1], { radius:6, color:'red', fillColor:'red', weight:2 }).addTo(map);
			
			// Agregar marcadores para waypoints
			if (route.waypoints && route.waypoints.length > 0) {
				route.waypoints.forEach((wp, idx) => {
					L.circleMarker([wp.lat, wp.lng], { 
						radius: 5, 
						color: '#FFA500', 
						fillColor: '#FFA500', 
						weight: 2 
					}).bindPopup(`<strong>${wp.nombre || 'Waypoint ' + (idx + 1)}</strong>`).addTo(map);
				});
			}
		} catch (e) {
			console.error('OSRM error:', e);
			const coords = [[route.inicio_lat, route.inicio_lng], [route.fin_lat, route.fin_lng]];
			const poly = L.polyline(coords, { color: route.color, weight: 5 }).addTo(map);
			map.fitBounds(poly.getBounds().pad(0.1));
		}

		document.querySelectorAll('.route-item').forEach(item => {
			item.classList.toggle('selected', item.dataset.routeId == route.id);
		});
	}

	// inicialización de geolocalización
	function maybeShowUserMarker() {
		if (!userLocation || !map) return;
		if (!userMarker) {
			userMarker = L.circleMarker([userLocation.lat, userLocation.lng], {
				radius: 8,
				color: '#2e7d32',
				fillColor: '#66bb6a',
				fillOpacity: 0.8
			}).addTo(map).bindPopup('Tu ubicación');
			// centrar la primera vez
			map.setView([userLocation.lat, userLocation.lng], 14);
		} else {
			userMarker.setLatLng([userLocation.lat, userLocation.lng]);
		}
	}

	function requestUserLocation() {
		if (!navigator.geolocation) return;
		navigator.geolocation.getCurrentPosition(pos => {
			userLocation = { lat: pos.coords.latitude, lng: pos.coords.longitude };
			maybeShowUserMarker();
		}, err => { console.warn('No se obtuvo ubicación:', err.message); }, { enableHighAccuracy:true, timeout:5000 });
	}

	requestUserLocation();

	fetchRoutes();
	setInterval(fetchRoutes, 20000);
});


