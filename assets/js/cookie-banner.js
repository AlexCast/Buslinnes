(function () {
  const banner = document.getElementById("cookie-banner");
  if (!banner) return;

  const btnAccept = document.getElementById("cookie-accept");
  const btnReject = document.getElementById("cookie-reject");
  const btnConfigure = document.getElementById("cookie-configure");

  if (!btnAccept || !btnReject || !btnConfigure) return;

  const FOCUSABLE_SELECTORS =
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';

  function getFocusableElements(container) {
    return Array.from(container.querySelectorAll(FOCUSABLE_SELECTORS)).filter(
      (el) => !el.hasAttribute("disabled") && el.offsetParent !== null
    );
  }

  function trapFocus(event) {
    if (event.key !== "Tab") return;

    const focusable = getFocusableElements(banner);
    if (!focusable.length) return;

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  function hideBanner() {
    banner.classList.add("cookie-banner--hidden");
    banner.setAttribute("aria-hidden", "true");
    document.removeEventListener("keydown", trapFocus);
  }

  function showBanner() {
    banner.classList.remove("cookie-banner--hidden");
    banner.setAttribute("aria-hidden", "false");
    document.addEventListener("keydown", trapFocus);
    btnAccept.focus();
  }

  const CONSENT_KEY = "buslinnes_cookie_consent";

  function saveConsent(value) {
    try {
      localStorage.setItem(CONSENT_KEY, value);
    } catch (e) {
      // Ignorar errores de almacenamiento
    }
  }

  function getConsent() {
    try {
      return localStorage.getItem(CONSENT_KEY);
    } catch (e) {
      return null;
    }
  }

  btnAccept.addEventListener("click", function () {
    saveConsent("accepted");
    hideBanner();
  });

  btnReject.addEventListener("click", function () {
    saveConsent("rejected");
    hideBanner();
  });

  btnConfigure.addEventListener("click", function () {
    // Aquí podrías abrir una página o modal de configuración avanzada
    // Esto es sólo un placeholder accesible
    alert("Aquí se abriría la configuración avanzada de cookies.");
  });

  banner.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      hideBanner();
    }
  });

  if (!getConsent()) {
    showBanner();
  } else {
    hideBanner();
  }
})();
