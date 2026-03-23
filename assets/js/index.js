// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const nav = document.getElementById('main-nav');
        const toggle = document.querySelector('.mobile-toggle');
        
        // close mobile menu if open
        if (nav.classList.contains('open')) {
            nav.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus(); // Retornar el foco al botón al cerrar
        }
        
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            // Mover el foco a la sección destino para lectores de pantalla
            target.setAttribute('tabindex', '-1');
            target.focus({ preventScroll: true });
        }
    });
});

// Sticky / fixed header behavior + mobile menu toggle
(function() {
    const header = document.querySelector('header');
    const nav = document.getElementById('main-nav');
    const toggle = document.querySelector('.mobile-toggle');
    if (!nav || !toggle) return;
    const navLinks = nav.querySelectorAll('.nav-link');

    // sticky class on scroll
    window.addEventListener('scroll', function() {
        header.classList.toggle('sticky', window.scrollY > 0);
    });

    function closeMenu() {
        nav.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus(); // Retornar foco
    }

    function openMenu() {
        nav.classList.add('open');
        toggle.setAttribute('aria-expanded', 'true');
        if (navLinks.length > 0) {
            navLinks[0].focus(); // Enfocar el primer enlace al abrir
        }
    }

    // toggle mobile nav
    toggle.addEventListener('click', function() {
        if (nav.classList.contains('open')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    // close nav on resize > 992px
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992 && nav.classList.contains('open')) {
            closeMenu();
        }
    });

    // close nav when clicking outside (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992 && nav.classList.contains('open')) {
            if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                closeMenu();
            }
        }
    });

    // Keyboard navigation (Focus Trap & Escape)
    nav.addEventListener('keydown', function(e) {
        if (window.innerWidth > 992) return; // Solo atrapar foco en móvil

        if (e.key === 'Escape') {
            closeMenu();
            return;
        }

        if (e.key === 'Tab') {
            const firstLink = navLinks[0];
            const lastLink = navLinks[navLinks.length - 1];

            if (e.shiftKey) {
                // Shift + Tab: Si estamos en el primer enlace, volver al toggle
                if (document.activeElement === firstLink) {
                    e.preventDefault();
                    toggle.focus();
                }
            } else {
                // Tab: Si estamos en el último enlace, volver al toggle
                if (document.activeElement === lastLink) {
                    e.preventDefault();
                    toggle.focus();
                }
            }
        }
    });

    // Manejar Tab desde el botón toggle hacia el menú
    toggle.addEventListener('keydown', function(e) {
        if (window.innerWidth > 992) return;

        if (e.key === 'Tab' && !e.shiftKey && nav.classList.contains('open')) {
            e.preventDefault();
            if (navLinks.length > 0) {
                navLinks[0].focus();
            }
        }
    });
})();

// Update copyright year dynamically
document.addEventListener('DOMContentLoaded', function() {
    const copyrightElement = document.querySelector('.footer-bottom p');
    if (copyrightElement) {
        const currentYear = new Date().getFullYear();
        copyrightElement.innerHTML = `&copy; ${currentYear} Buslinnes. Todos los derechos reservados.`;
    }
});
