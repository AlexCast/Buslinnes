/**
 * Helper para proteger endpoints del lado del cliente
 * Maneja tokens CSRF y headers de seguridad
 */

class SecurityHelper {
    constructor() {
        this.csrfToken = null;
        this.initPromise = this.init();
    }

    /**
     * Inicializar - obtener token CSRF
     */
    async init() {
        try {
            const response = await fetch('/buslinnes/app/get_csrf_token.php', {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Cache-Control': 'no-cache'
                }
            });
            
            if (!response.ok) {
                console.error(`Failed to fetch CSRF token - HTTP ${response.status}: ${response.statusText}`);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (!data.csrf_token) {
                console.error('CSRF token endpoint returned empty token:', data);
                throw new Error('Empty CSRF token in response');
            }
            
            this.csrfToken = data.csrf_token;
            
            // Guardar en meta tag para fácil acceso
            let metaTag = document.querySelector('meta[name="csrf-token"]');
            if (!metaTag) {
                metaTag = document.createElement('meta');
                metaTag.name = 'csrf-token';
                document.head.appendChild(metaTag);
            }
            metaTag.content = this.csrfToken;
            
            return this.csrfToken;
            
        } catch (error) {
            console.error('Error obteniendo CSRF token:', error);
            return null;
        }
    }

    /**
     * Realizar petición segura con protección CSRF
     */
    async secureRequest(url, options = {}) {
        // Asegurar que la inicialización ha terminado
        if (this.initPromise) {
            await this.initPromise;
        }

        // Si aún no tenemos token después de init, intentar obtener de nuevo
        if (!this.csrfToken) {
            console.warn('Token CSRF no disponible, intentando obtener nuevamente...');
            const token = await this.init();
            if (!token) {
                console.error('CSRF token initialization failed - request will likely fail');
            }
        }

        // Configuración por defecto
        const defaultOptions = {
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        // Agregar X-CSRF-Token si está disponible
        if (this.csrfToken) {
            defaultOptions.headers['X-CSRF-Token'] = this.csrfToken;
        }
        
        // Agregar JWT token si está disponible
        const jwtToken = localStorage.getItem('jwt_token');
        if (jwtToken) {
            defaultOptions.headers['Authorization'] = `Bearer ${jwtToken}`;
        }

        // Merge de opciones
        options = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...(options.headers || {})
            }
        };

        // Always overwrite with the latest CSRF token AFTER merge
        // (options.headers from a previous call may carry a stale X-CSRF-Token)
        if (this.csrfToken) {
            options.headers['X-CSRF-Token'] = this.csrfToken;
        }

        // Para POST/PUT/DELETE agregar CSRF token al body si es FormData
        if (['POST', 'PUT', 'DELETE'].includes(options.method?.toUpperCase())) {
            if (options.body instanceof FormData) {
                options.body.append('csrf_token', this.csrfToken);
            } else if (options.headers['Content-Type'] === 'application/json') {
                // Para JSON, agregar al body parseado
                try {
                    const jsonBody = JSON.parse(options.body);
                    jsonBody.csrf_token = this.csrfToken;
                    options.body = JSON.stringify(jsonBody);
                } catch (e) {
                    console.error('Error parseando JSON body:', e);
                }
            } else if (typeof options.body === 'string') {
                // Para form-urlencoded
                const separator = options.body.length > 0 ? '&' : '';
                options.body += `${separator}csrf_token=${encodeURIComponent(this.csrfToken)}`;
            } else if (!options.body) {
                // Si no hay body, crear uno con el token
                options.body = `csrf_token=${encodeURIComponent(this.csrfToken)}`;
                options.headers['Content-Type'] = 'application/x-www-form-urlencoded';
            }
            
        }

        try {
            const response = await fetch(url, options);
            
            // Refrescar token automáticamente si el servidor envía uno nuevo
            const newJwtToken = response.headers.get('X-New-JWT') || response.headers.get('x-new-jwt');
            if (newJwtToken) {
                localStorage.setItem('jwt_token', newJwtToken);
                const expirationDate = new Date();
                expirationDate.setTime(expirationDate.getTime() + (1200 * 1000));
                document.cookie = `jwt_token=${newJwtToken}; expires=${expirationDate.toUTCString()}; path=/`;
            }

            // Manejar expiración del lado del servidor
            if (response.status === 401) {
                // Forzar re-login: token vencido o inválido
                window.location.replace('/buslinnes/public/login.php?expired=1');
                throw new Error('No autorizado. Por favor inicie sesión nuevamente.');
            }

            // Manejar errores de seguridad
            if (response.status === 403) {
                const data = await response.json();
                if (data.error && data.error.includes('CSRF')) {
                    // Token mismatch — fetch a fresh one y retry
                    await this.init();
                    if (options.headers) delete options.headers['X-CSRF-Token'];
                    return this.secureRequest(url, options);
                }
                throw new Error(data.error || 'Acceso denegado');
            }
            
            if (response.status === 429) {
                throw new Error('Demasiadas peticiones. Espera un momento.');
            }
            
            return response;
        } catch (error) {
            console.error('Error en petición segura:', error);
            throw error;
        }
    }

    /**
     * Métodos convenience para GET, POST, PUT, DELETE
     */
    async get(url, options = {}) {
        return this.secureRequest(url, { ...options, method: 'GET' });
    }

    async post(url, data, options = {}) {
        const body = data instanceof FormData ? data : JSON.stringify(data);
        const headers = data instanceof FormData 
            ? {} 
            : { 'Content-Type': 'application/json' };
        
        return this.secureRequest(url, {
            ...options,
            method: 'POST',
            body,
            headers: { ...headers, ...(options.headers || {}) }
        });
    }

    async put(url, data, options = {}) {
        const body = data instanceof FormData ? data : JSON.stringify(data);
        const headers = data instanceof FormData 
            ? {} 
            : { 'Content-Type': 'application/json' };
        
        return this.secureRequest(url, {
            ...options,
            method: 'PUT',
            body,
            headers: { ...headers, ...(options.headers || {}) }
        });
    }

    async delete(url, options = {}) {
        return this.secureRequest(url, { ...options, method: 'DELETE' });
    }

    /**
     * Obtener el token CSRF actual
     */
    getCSRFToken() {
        return this.csrfToken;
    }
}

// Crear instancia global
const securityHelper = new SecurityHelper();

// Ejemplo de uso:
/*
// GET request
const response = await securityHelper.get('/buslinnes/src/buses/listar_buses.php');
const buses = await response.json();

// POST request
const newBus = { placa: 'ABC123', modelo: '2024' };
const response = await securityHelper.post('/buslinnes/src/buses/insertar_buses.php', newBus);

// PUT request
const updateData = { id_bus: 1, estado: 'A' };
await securityHelper.put('/buslinnes/src/buses/update_buses.php', updateData);

// DELETE request
await securityHelper.delete('/buslinnes/src/buses/eliminar_buses.php?id=1');
*/

// ======= Auto sesión JWT (inactividad y refresh) =======
(function(){
    if (window.securityTokenManagerInitialized) {
        return;
    }
    window.securityTokenManagerInitialized = true;

    const INACTIVITY_TIMEOUT = 15 * 60 * 1000; // 15 min
    const CHECK_INTERVAL = 10 * 1000; // 10 seg
    const REFRESH_MARGIN = 30 * 1000; // 30 seg antes de expirar
    const ACTIVITY_THRESHOLD = 5 * 60 * 1000; // 5 min de actividad reciente

    let lastActivityTime = Date.now();
    let isRefreshing = false;

    const events = ['mousedown','mousemove','keydown','scroll','touchstart','click','focus'];
    events.forEach(evt => document.addEventListener(evt, () => { lastActivityTime = Date.now(); }, { passive: true }));

    function getJwt() {
        return localStorage.getItem('jwt_token') || null;
    }

    function setJwt(token) {
        if (!token) return;
        localStorage.setItem('jwt_token', token);
        const expires = new Date(Date.now() + 900 * 1000).toUTCString();
        document.cookie = `jwt_token=${token}; expires=${expires}; path=/`;
    }

    function removeJwt() {
        localStorage.removeItem('jwt_token');
        document.cookie = 'jwt_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    }

    function decodePayload(token) {
        try {
            const part = token.split('.')[1];
            if (!part) return null;
            const json = decodeURIComponent(atob(part.replace(/-/g, '+').replace(/_/g, '/')).split('').map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join(''));
            return JSON.parse(json);
        } catch (e) {
            console.error('Error al decodificar JWT:', e);
            return null;
        }
    }

    function redirectLogin() {
        removeJwt();
        window.location.replace('/buslinnes/public/login.php?expired=1');
    }

    async function refreshJwt() {
        if (isRefreshing) return;
        isRefreshing = true;
        try {
            const token = getJwt();
            if (!token) {
                redirectLogin();
                return;
            }
            const res = await fetch('/buslinnes/public/renovar_token.php', {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.success && data.token) {
                    setJwt(data.token);
                } else {
                    redirectLogin();
                }
            } else {
                if (res.status === 401) {
                    redirectLogin();
                }
            }
        } catch (e) {
            console.error('Error al renovar token:', e);
            redirectLogin();
        } finally {
            isRefreshing = false;
        }
    }

    async function checkSession() {
        const token = getJwt();
        if (!token) {
            return;
        }

        const payload = decodePayload(token);
        if (!payload || !payload.exp) {
            redirectLogin();
            return;
        }

        const now = Math.floor(Date.now() / 1000);
        const expMs = payload.exp * 1000;

        if (payload.exp <= now) {
            redirectLogin();
            return;
        }

        if (Date.now() - lastActivityTime >= INACTIVITY_TIMEOUT) {
            redirectLogin();
            return;
        }

        const remaining = expMs - Date.now();
        const timeFromLastActivity = Date.now() - lastActivityTime;

        if (remaining <= REFRESH_MARGIN && timeFromLastActivity < ACTIVITY_THRESHOLD) {
            await refreshJwt();
        }
    }

    setInterval(checkSession, CHECK_INTERVAL);

})();

