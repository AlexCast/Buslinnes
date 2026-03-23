const toggle = document.getElementById("darkToggle");
const body = document.body;

// Leer preferencia guardada
if (localStorage.getItem("theme") === "dark") {
  body.classList.add("dark");
  if (toggle) toggle.setAttribute("aria-pressed", "true");
} else {
  if (toggle) toggle.setAttribute("aria-pressed", "false");
}

// Solo agregar el listener si el elemento existe
if (toggle) {
  toggle.addEventListener("click", () => {
    const isDark = body.classList.toggle("dark");
    
    // Actualizar estado para lectores de pantalla
    toggle.setAttribute("aria-pressed", isDark ? "true" : "false");

    // Guardar preferencia
    localStorage.setItem("theme", isDark ? "dark" : "light");
  });
}

let csrfToken = null;
let csrfInitPromise = null;

async function initCsrfToken() {
  if (csrfToken) return csrfToken;
  if (csrfInitPromise) return csrfInitPromise;

  csrfInitPromise = fetch('/buslinnes/app/get_csrf_token.php', {
    method: 'GET',
    credentials: 'include',
    headers: { 'Cache-Control': 'no-cache' }
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`CSRF token fetch failed: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      csrfToken = data?.csrf_token || null;
      return csrfToken;
    })
    .catch((error) => {
      console.error('Error inicializando CSRF token:', error);
      return null;
    })
    .finally(() => {
      csrfInitPromise = null;
    });

  return csrfInitPromise;
}

function ensureCsrfInput(form, token) {
  if (!token || !form) return;

  let csrfInput = form.querySelector('input[name="csrf_token"]');
  if (!csrfInput) {
    csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    form.appendChild(csrfInput);
  }
  csrfInput.value = token;
}

function attachCsrfToForms(token) {
  if (!token) return;

  document.querySelectorAll('form[method="POST"], form[method="post"]').forEach((form) => {
    ensureCsrfInput(form, token);
  });
}

document.addEventListener('DOMContentLoaded', async () => {
  const token = await initCsrfToken();
  attachCsrfToForms(token);
});

document.addEventListener('submit', async (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement)) return;

  const method = (form.getAttribute('method') || '').toUpperCase();
  if (method !== 'POST') return;

  if (!csrfToken) {
    csrfToken = await initCsrfToken();
  }

  ensureCsrfInput(form, csrfToken);
}, true);
