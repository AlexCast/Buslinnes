/**
 * Gestor de tokens JWT con renovación automática y detección de inactividad
 * Renueva el token cada 10 minutos si hay actividad
 * Cierra sesión después de 60 minutos de inactividad
 */

(function() {
    'use strict';
    
    const TOKEN_REFRESH_INTERVAL = 10 * 60 * 1000; // 10 minutos en milisegundos
    const INACTIVITY_TIMEOUT = 60 * 60 * 1000; // 60 minutos en milisegundos
    const CHECK_INTERVAL = 30 * 1000; // Verificar cada 30 segundos
    const ACTIVITY_THRESHOLD = 30 * 60 * 1000; // Considerar actividad si hubo en los últimos 30 minutos
    
    let lastActivityTime = Date.now();
    let refreshTokenInterval = null;
    let inactivityCheckInterval = null;
    let isRenovando = false;
    
    // Eventos que indican actividad del usuario
    const activityEvents = [
        'mousedown', 'mousemove', 'keypress', 'scroll', 
        'touchstart', 'click', 'keydown', 'focus'
    ];
    
    /**
     * Función para renovar el token JWT
     */
    async function renovarToken() {
        if (isRenovando) {
            return; // Evitar múltiples renovaciones simultáneas
        }
        
        const token = localStorage.getItem('jwt_token');
        if (!token) {
            cerrarSesion();
            return;
        }
        
        isRenovando = true;
        
        try {
            const response = await fetch('renovar_token.php', {
                method: 'GET',
                credentials: 'include', // Incluir cookies
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.token) {
                    // Actualizar token en localStorage
                    localStorage.setItem('jwt_token', data.token);
                    // Actualizar cookie también desde el cliente
                    const expirationDate = new Date();
                    expirationDate.setTime(expirationDate.getTime() + (1200 * 1000));
                    document.cookie = `jwt_token=${data.token}; expires=${expirationDate.toUTCString()}; path=/`;
                    console.log('Token renovado exitosamente');
                } else {
                    throw new Error('Error al renovar token');
                }
            } else {
                throw new Error('Error en la respuesta del servidor');
            }
        } catch (error) {
            console.error('Error al renovar token:', error);
            cerrarSesion();
        } finally {
            isRenovando = false;
        }
    }
    
    /**
     * Función para cerrar sesión
     */
    function cerrarSesion() {
        // Limpiar intervalos
        if (refreshTokenInterval) {
            clearInterval(refreshTokenInterval);
        }
        if (inactivityCheckInterval) {
            clearInterval(inactivityCheckInterval);
        }
        
        // Eliminar token y cookie
        localStorage.removeItem('jwt_token');
        document.cookie = 'jwt_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        
        // Redirigir al login
        window.location.replace('/buslinnes/public/login.php');
    }
    
    /**
     * Actualizar el tiempo de última actividad
     */
    function actualizarActividad() {
        lastActivityTime = Date.now();
    }
    
    /**
     * Verificar si el usuario está inactivo
     */
    function verificarInactividad() {
        const tiempoInactivo = Date.now() - lastActivityTime;
        
        if (tiempoInactivo >= INACTIVITY_TIMEOUT) {
            console.log('Usuario inactivo por más de 15 minutos, cerrando sesión...');
            cerrarSesion();
        }
    }
    
    /**
     * Verificar si el token está próximo a expirar y renovarlo si hay actividad
     */
    function verificarYRenovarToken() {
        const token = localStorage.getItem('jwt_token');
        if (!token) {
            cerrarSesion();
            return;
        }
        
        try {
            // Decodificar token para verificar expiración
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
            const payload = JSON.parse(jsonPayload);
            
            const tiempoRestante = (payload.exp * 1000) - Date.now();
            const tiempoMinimoParaRenovar = 60 * 1000; // 60 segundos antes de expirar
            const tiempoDesdeUltimaActividad = Date.now() - lastActivityTime;
            
            // Si el token está próximo a expirar y hay actividad reciente
            if (tiempoRestante < tiempoMinimoParaRenovar && tiempoDesdeUltimaActividad < ACTIVITY_THRESHOLD) {
                renovarToken();
            }
        } catch (error) {
            console.error('Error al verificar token:', error);
            cerrarSesion();
        }
    }
    
    /**
     * Inicializar el gestor de tokens
     */
    function inicializar() {
        // Registrar eventos de actividad
        activityEvents.forEach(event => {
            document.addEventListener(event, actualizarActividad, { passive: true });
        });
        
        // También detectar cuando la ventana recupera el foco
        window.addEventListener('focus', actualizarActividad);
        
        // Renovar token periódicamente si hay actividad
        refreshTokenInterval = setInterval(() => {
            const tiempoDesdeUltimaActividad = Date.now() - lastActivityTime;
            // Solo renovar si hubo actividad reciente
            if (tiempoDesdeUltimaActividad < ACTIVITY_THRESHOLD) {
                renovarToken();
            }
        }, TOKEN_REFRESH_INTERVAL);
        
        // Verificar inactividad periódicamente
        inactivityCheckInterval = setInterval(verificarInactividad, CHECK_INTERVAL);
        
        // También verificar y renovar token periódicamente
        setInterval(verificarYRenovarToken, CHECK_INTERVAL);
        
        // Renovar token inmediatamente al cargar si hay actividad reciente
        setTimeout(() => {
            verificarYRenovarToken();
        }, 5000); // Esperar 5 segundos después de cargar
    }
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar);
    } else {
        inicializar();
    }
    
})();
