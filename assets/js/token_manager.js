/**
 * Gestor de tokens JWT con detección de inactividad
 * Los tokens ya no expiran por tiempo, solo se cierran al cerrar sesión o por inactividad
 * Cierra sesión después de 60 minutos de inactividad
 */

(function() {
    'use strict';

    const INACTIVITY_TIMEOUT = 60 * 60 * 1000; // 60 minutos en milisegundos
    const CHECK_INTERVAL = 30 * 1000; // Verificar cada 30 segundos

    let lastActivityTime = Date.now();
    let inactivityCheckInterval = null;

    // Eventos que indican actividad del usuario
    const activityEvents = [
        'mousedown', 'mousemove', 'keypress', 'scroll',
        'touchstart', 'click', 'keydown', 'focus'
    ];

    /**
     * Función para cerrar sesión
     */
    function cerrarSesion() {
        // Limpiar localStorage
        localStorage.removeItem('jwt_token');

        // Limpiar cookie
        document.cookie = 'jwt_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';

        // Limpiar intervalos
        if (inactivityCheckInterval) {
            clearInterval(inactivityCheckInterval);
        }

        // Redirigir a login
        window.location.href = '/buslinnes/public/login.php';
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
            console.log('Usuario inactivo por más de 60 minutos, cerrando sesión...');
            cerrarSesion();
        }
    }

    /**
     * Inicializar el gestor de tokens
     */
    function inicializar() {
        // Verificar que hay un token válido
        const token = localStorage.getItem('jwt_token');
        if (!token) {
            cerrarSesion();
            return;
        }

        // Registrar eventos de actividad
        activityEvents.forEach(event => {
            document.addEventListener(event, actualizarActividad, { passive: true });
        });

        // También detectar cuando la ventana recupera el foco
        window.addEventListener('focus', actualizarActividad);

        // Verificar inactividad periódicamente
        inactivityCheckInterval = setInterval(verificarInactividad, CHECK_INTERVAL);
    }

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar);
    } else {
        inicializar();
    }

})();
