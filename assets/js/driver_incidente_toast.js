document.addEventListener('DOMContentLoaded', function () {
  var params = new URLSearchParams(window.location.search);
  if (params.get('incidente_reportado') !== '1') {
    return;
  }

  var isDarkMode = document.body.classList.contains('dark');
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
  toast.style.border = isDarkMode ? '1px solid rgba(74, 222, 128, 0.45)' : '1px solid rgba(22, 163, 74, 0.35)';
  toast.style.background = isDarkMode
    ? 'linear-gradient(135deg, rgba(22, 101, 52, 0.95), rgba(21, 128, 61, 0.92))'
    : 'linear-gradient(135deg, rgba(22, 163, 74, 0.96), rgba(21, 128, 61, 0.96))';
  toast.style.color = '#ffffff';
  toast.style.boxShadow = isDarkMode
    ? '0 16px 36px rgba(0, 0, 0, 0.42)'
    : '0 16px 36px rgba(21, 128, 61, 0.28)';
  toast.style.backdropFilter = 'blur(6px)';
  toast.style.opacity = '0';
  toast.style.transition = 'opacity .28s ease, transform .28s ease';

  toast.innerHTML = [
    '<div style="width:30px;height:30px;border-radius:999px;background:rgba(255,255,255,0.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;">',
    '<i class="fas fa-check" style="font-size:14px;color:#fff;"></i>',
    '</div>',
    '<div style="min-width:0;">',
    '<div style="font-weight:700;line-height:1.2;">Incidente reportado</div>',
    '<div style="font-size:12px;opacity:.92;line-height:1.25;">El reporte se guardo correctamente.</div>',
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
  }, 2600);

  params.delete('incidente_reportado');
  var cleanQuery = params.toString();
  var cleanUrl = window.location.pathname + (cleanQuery ? '?' + cleanQuery : '') + window.location.hash;
  window.history.replaceState({}, '', cleanUrl);
});
