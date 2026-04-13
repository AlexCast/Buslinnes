(function () {
  var DEFAULT_PRIMARY_COLOR = '#8059d4ff';
  var DEFAULT_LOGO_URL = '/buslinnes/assets/img/logomorado.svg';
  var DEFAULT_FAVICON_URL = '/buslinnes/mkcert/favicon.ico';

  function getAppBasePath() {
    return window.location.pathname.indexOf('/buslinnes/') === 0 ? '/buslinnes' : '';
  }

  function normalizeColor(value) {
    if (typeof value !== 'string') return DEFAULT_PRIMARY_COLOR;
    var color = value.trim();
    if (/^#(?:[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/.test(color)) {
      return color;
    }
    return DEFAULT_PRIMARY_COLOR;
  }

  function normalizeLogo(value) {
    if (typeof value !== 'string' || !value.trim()) {
      return DEFAULT_LOGO_URL;
    }
    return value.trim();
  }

  function normalizeFavicon(value) {
    if (typeof value !== 'string' || !value.trim()) {
      return DEFAULT_FAVICON_URL;
    }
    return value.trim();
  }

  function applyBranding(data) {
    var primaryColor = normalizeColor(data && data.primary_color);
    var logoUrl = normalizeLogo(data && data.logo_url);
    var faviconUrl = normalizeFavicon(data && data.favicon_url);

    document.documentElement.style.setProperty('--primary-color', primaryColor);

    var logoNodes = document.querySelectorAll('.logo-img, .logo-icon img, .footer-logo-img, .logo-light, .logo-dark');
    logoNodes.forEach(function (node) {
      if (node && node.tagName === 'IMG') {
        node.src = logoUrl;
      }
    });

    // Aplicar favicon - actualizar todos los tipos
    var appleTouchIcon = document.querySelector('link[rel="apple-touch-icon"]');
    if (appleTouchIcon) {
      appleTouchIcon.href = faviconUrl;
    }

    // Actualizar todos los link rel="icon" (múltiples tamaños)
    var iconLinks = document.querySelectorAll('link[rel="icon"]');
    iconLinks.forEach(function(link) {
      link.href = faviconUrl;
    });

    // Si no hay ninguno, crear uno
    if (iconLinks.length === 0) {
      var faviconLink = document.createElement('link');
      faviconLink.rel = 'icon';
      faviconLink.href = faviconUrl;
      faviconLink.type = 'image/x-icon';
      document.head.appendChild(faviconLink);
    }

    // Actualizar shortcut icon también (IE compatibility)
    var shortcutIcons = document.querySelectorAll('link[rel="shortcut icon"]');
    shortcutIcons.forEach(function(link) {
      link.href = faviconUrl;
    });

    window.__brandingConfig = {
      primary_color: primaryColor,
      logo_url: logoUrl,
      favicon_url: faviconUrl,
      updated_at: data && data.updated_at ? data.updated_at : null
    };
  }

  function initBrandingRuntime() {
    var appBasePath = getAppBasePath();
    var url = appBasePath + '/app/get_branding.php';

    fetch(url, { method: 'GET', credentials: 'same-origin' })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }
        return response.json();
      })
      .then(function (payload) {
        if (payload && payload.success && payload.data) {
          applyBranding(payload.data);
          return;
        }
        applyBranding({
          primary_color: DEFAULT_PRIMARY_COLOR,
          logo_url: DEFAULT_LOGO_URL,
          favicon_url: DEFAULT_FAVICON_URL,
          updated_at: null
        });
      })
      .catch(function () {
        applyBranding({
          primary_color: DEFAULT_PRIMARY_COLOR,
          logo_url: DEFAULT_LOGO_URL,
          favicon_url: DEFAULT_FAVICON_URL,
          updated_at: null
        });
      });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBrandingRuntime);
  } else {
    initBrandingRuntime();
  }
})();
