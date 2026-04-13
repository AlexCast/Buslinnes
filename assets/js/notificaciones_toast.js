document.addEventListener('DOMContentLoaded', function () {
  var params = new URLSearchParams(window.location.search);
  
  if (!params.has('restaurado') && !params.has('insertado') && !params.has('push_error') && !params.has('error_restore') && !params.has('actualizado') && !params.has('error_update')) {
    return;
  }

  var isDarkMode = document.body.classList.contains('dark');
  var toastType = 'success'; // success, warning, error
  var title = '';
  var message = '';
  var icon = 'fa-check';

  // Determine message and type base on URL parameters
  if (params.get('insertado') === '1') {
    title = 'Notificación Enviada';
    message = 'Enviada correctamente.';
    if (params.get('push_enviado') === '1') {
      message += '<br>📱 Notificación push enviada exitosamente.';
    } else if (params.has('sin_push')) {
      message += '<br>ℹ️ No se envió notificación push (desactivado).';
    }
  } else if (params.get('restaurado') === '1') {
    title = 'Notificación Restaurada';
    message = 'La notificación fue restaurada correctamente.';
  } else if (params.has('push_error')) {
    toastType = 'warning';
    title = 'Error en Push';
    message = 'Guardada, pero no se pudo enviar push: ' + decodeURIComponent(params.get('push_error').replace(/\+/g, ' '));
    icon = 'fa-exclamation-triangle';
  } else if (params.get('error_restore') === '1') {
    toastType = 'error';
    title = 'Error de Restauración';
    message = 'No se pudo restaurar la notificación.';
    icon = 'fa-times';
  } else if (params.get('actualizado') === '1') {
    title = 'Notificación Actualizada';
    message = 'La notificación ha sido actualizada correctamente.';
  } else if (params.get('error_update') === '1') {
    toastType = 'error';
    title = 'Error al Actualizar';
    message = 'No se pudo actualizar la notificación. ' + (params.has('msg') ? decodeURIComponent(params.get('msg').replace(/\+/g, ' ')) : '');
    icon = 'fa-times';
  }

  // Define colors based on type and dark mode
  var borderColor, backgroundGradient, boxShadowColor;

  if (toastType === 'success') {
    borderColor = isDarkMode ? 'rgba(74, 222, 128, 0.45)' : 'rgba(22, 163, 74, 0.35)';
    backgroundGradient = isDarkMode
      ? 'linear-gradient(135deg, rgba(22, 101, 52, 0.95), rgba(21, 128, 61, 0.92))'
      : 'linear-gradient(135deg, rgba(22, 163, 74, 0.96), rgba(21, 128, 61, 0.96))';
    boxShadowColor = isDarkMode ? 'rgba(0, 0, 0, 0.42)' : 'rgba(21, 128, 61, 0.28)';
  } else if (toastType === 'warning') {
    borderColor = isDarkMode ? 'rgba(250, 204, 21, 0.45)' : 'rgba(202, 138, 4, 0.35)';
    backgroundGradient = isDarkMode
      ? 'linear-gradient(135deg, rgba(161, 98, 7, 0.95), rgba(133, 77, 14, 0.92))'
      : 'linear-gradient(135deg, rgba(234, 179, 8, 0.96), rgba(202, 138, 4, 0.96))';
    boxShadowColor = isDarkMode ? 'rgba(0, 0, 0, 0.42)' : 'rgba(202, 138, 4, 0.28)';
  } else if (toastType === 'error') {
    borderColor = isDarkMode ? 'rgba(248, 113, 113, 0.45)' : 'rgba(220, 38, 38, 0.35)';
    backgroundGradient = isDarkMode
      ? 'linear-gradient(135deg, rgba(153, 27, 27, 0.95), rgba(127, 29, 29, 0.92))'
      : 'linear-gradient(135deg, rgba(239, 68, 68, 0.96), rgba(220, 38, 38, 0.96))';
    boxShadowColor = isDarkMode ? 'rgba(0, 0, 0, 0.42)' : 'rgba(220, 38, 38, 0.28)';
  }

  var toast = document.createElement('div');
  toast.setAttribute('role', 'status');
  toast.setAttribute('aria-live', 'polite');
  toast.style.position = 'fixed';
  toast.style.top = '18px';
  toast.style.left = '50%';
  toast.style.transform = 'translate(-50%, -16px) scale(0.98)';
  toast.style.zIndex = '9999';
  toast.style.width = 'min(92vw, 460px)';
  toast.style.display = 'flex';
  toast.style.alignItems = 'center';
  toast.style.gap = '12px';
  toast.style.padding = '12px 14px';
  toast.style.borderRadius = '14px';
  toast.style.border = '1px solid ' + borderColor;
  toast.style.background = backgroundGradient;
  toast.style.color = '#ffffff';
  toast.style.boxShadow = '0 16px 36px ' + boxShadowColor;
  toast.style.backdropFilter = 'blur(6px)';
  toast.style.opacity = '0';
  toast.style.transition = 'opacity .28s ease, transform .28s ease';

  toast.innerHTML = [
    '<div style="width:30px;height:30px;border-radius:999px;background:rgba(255,255,255,0.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;">',
    '<i class="fas ' + icon + '" style="font-size:14px;color:#fff;"></i>',
    '</div>',
    '<div style="min-width:0;">',
    '<div style="font-weight:700;line-height:1.2;">' + title + '</div>',
    '<div style="font-size:12px;opacity:.92;line-height:1.25;">' + message + '</div>',
    '</div>'
  ].join('');

  document.body.appendChild(toast);

  requestAnimationFrame(function () {
    toast.style.opacity = '1';
    toast.style.transform = 'translate(-50%, 0) scale(1)';
  });

  setTimeout(function () {
    toast.style.opacity = '0';
    toast.style.transform = 'translate(-50%, -14px) scale(0.98)';
    setTimeout(function () {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 300);
  }, 4000);

  // Clean URL parameters
  ['restaurado', 'insertado', 'push_enviado', 'sin_push', 'push_error', 'error_restore', 'actualizado', 'error_update', 'msg'].forEach(function(param) {
      params.delete(param);
  });
  
  var cleanQuery = params.toString();
  var cleanUrl = window.location.pathname + (cleanQuery ? '?' + cleanQuery : '') + window.location.hash;
  window.history.replaceState({}, '', cleanUrl);
});