(function () {
  const banner = document.getElementById("cookie-banner");
  if (!banner) return;

  const btnAccept = document.getElementById("cookie-accept");
  const btnReject = document.getElementById("cookie-reject");
  const btnConfigure = document.getElementById("cookie-configure");
  const consentCheckbox = document.getElementById("cookie-consent-checkbox");
  const consentWarning = document.getElementById("cookie-consent-warning");
  const backdrop = document.getElementById("cookie-banner-backdrop");

  if (!btnAccept || !btnReject || !consentCheckbox) return;

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

  function moveFocusOutOfBanner() {
    if (!banner.contains(document.activeElement)) return;

    const main = document.querySelector("main");
    if (main) {
      main.setAttribute("tabindex", "-1");
      main.focus();
      return;
    }

    if (document.activeElement && document.activeElement.blur) {
      document.activeElement.blur();
    }
  }

  function setPageLock(active) {
    const containers = document.querySelectorAll("header, main, footer");

    containers.forEach((el) => {
      if (active) {
        el.setAttribute("inert", "");
        el.setAttribute("aria-hidden", "true");
      } else {
        el.removeAttribute("inert");
        el.removeAttribute("aria-hidden");
      }
    });

    if (backdrop) {
      backdrop.hidden = !active;
    }
  }

  function hideBanner() {
    setPageLock(false);
    moveFocusOutOfBanner();
    banner.classList.add("cookie-banner--hidden");
    banner.setAttribute("aria-hidden", "true");
    document.removeEventListener("keydown", trapFocus);
  }

  function showBanner() {
    banner.classList.remove("cookie-banner--hidden");
    banner.setAttribute("aria-hidden", "false");
    document.addEventListener("keydown", trapFocus);
    consentCheckbox.focus();
    setPageLock(true);
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

  function showConsentWarning(message) {
    if (consentWarning) {
      consentWarning.textContent = message;
    } else {
      alert(message);
    }
  }

  function clearConsentWarning() {
    if (consentWarning) {
      consentWarning.textContent = "";
    }
  }

  btnAccept.addEventListener("click", function () {
    if (!consentCheckbox.checked) {
      showConsentWarning(
        "Debes aceptar la Política de Privacidad, Política de Cookies y los Términos y Condiciones para continuar."
      );
      consentCheckbox.focus();
      return;
    }

    clearConsentWarning();
    saveConsent("accepted");
    hideBanner();
  });

  btnReject.addEventListener("click", function () {
    saveConsent("rejected");
    hideBanner();
  });

  if (btnConfigure) {
    btnConfigure.addEventListener("click", function () {
      alert("Aquí se abriría la configuración avanzada de cookies.");
    });
  }

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
