/*
 * Endurece la navegación del cliente mientras la app está abierta.
 * Nota: Los navegadores no permiten bloquear al 100% DevTools ni el cierre de pestaña.
 */
(function () {
  'use strict';
  return; // Desactivar temporalmente para desarrollo.

  const WARNING_MESSAGE = 'Hay cambios o procesos activos en Buslinnes. ¿Seguro que deseas salir?';
  const DEVTOOLS_THRESHOLD = 160;
  let devtoolsOpen = false;
  let lockdownTriggered = false;
  let protectionActive = false;
  let lastDevtoolsIntentAt = 0;
  let suppressBeforeUnloadPrompt = false;

  // Minimiza salida por consola para usuarios estándar.
  const noop = function () {};
  ['log', 'debug', 'info', 'warn', 'error', 'trace', 'table', 'dir'].forEach(function (method) {
    try {
      if (window.console && typeof window.console[method] === 'function') {
        window.console[method] = noop;
      }
    } catch (_) {
      // Ignorar errores de sobrescritura.
    }
  });

  function markDevtoolsIntent() {
    lastDevtoolsIntentAt = Date.now();
  }

  function onPopStateLock() {
    keepHistoryLocked();
  }

  function onBeforeUnloadLock(event) {
    if (suppressBeforeUnloadPrompt) {
      return undefined;
    }
    event.preventDefault();
    event.returnValue = WARNING_MESSAGE;
    return WARNING_MESSAGE;
  }

  function activateProtectionMode() {
    if (protectionActive) {
      return;
    }
    protectionActive = true;
    keepHistoryLocked();
    window.addEventListener('popstate', onPopStateLock);
    window.addEventListener('beforeunload', onBeforeUnloadLock);
  }

  function blockDevToolsShortcuts(event) {
    const key = (event.key || '').toLowerCase();
    const isF12 = key === 'f12';
    const isCtrlShiftCombo = event.ctrlKey && event.shiftKey && (key === 'i' || key === 'j' || key === 'c');
    const isCtrlU = event.ctrlKey && !event.shiftKey && key === 'u';

    if (isF12 || isCtrlShiftCombo || isCtrlU) {
      markDevtoolsIntent();
      activateProtectionMode();
      event.preventDefault();
      event.stopPropagation();
      lockApplication();
      return false;
    }
    return true;
  }

  function keepHistoryLocked() {
    try {
      history.pushState({ appLock: true }, '', window.location.href);
    } catch (_) {
      // Ignorar si el navegador bloquea pushState.
    }
  }

  function lockApplication() {
    if (lockdownTriggered) {
      return;
    }
    lockdownTriggered = true;

    try {
      sessionStorage.clear();
      localStorage.removeItem('jwt_token');
    } catch (_) {
      // Ignorar si el almacenamiento no está disponible.
    }

    // Evita el diálogo "¿Deseas salir del sitio?" durante redirección forzada.
    suppressBeforeUnloadPrompt = true;

    window.setTimeout(function () {
      window.location.replace('/buslinnes/public/logout.php?reason=devtools');
    }, 30);
  }

  function detectDevTools() {
    const widthDiff = window.outerWidth - window.innerWidth;
    const heightDiff = window.outerHeight - window.innerHeight;
    const byWindowDiff = widthDiff > DEVTOOLS_THRESHOLD || heightDiff > DEVTOOLS_THRESHOLD;
    const recentIntent = Date.now() - lastDevtoolsIntentAt < 8000;
    const maybeOpen = byWindowDiff || recentIntent;

    if (maybeOpen && !devtoolsOpen) {
      devtoolsOpen = true;
      activateProtectionMode();
      lockApplication();
    } else if (!maybeOpen && devtoolsOpen) {
      devtoolsOpen = false;
    }
  }

  window.addEventListener('keydown', blockDevToolsShortcuts, true);

  window.setInterval(detectDevTools, 1000);
})();
