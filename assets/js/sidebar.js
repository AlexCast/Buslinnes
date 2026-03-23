// Script to handle sidebar toggle con ARIA para accesibilidad
const toggleSidebar = document.getElementById('toggleSidebar');
const sidebar = document.getElementById('sidebar');

// Controla si los enlaces del sidebar son focusables según su estado (WCAG 2.4.3)
const navLinksSidebar = document.querySelectorAll('.sidebar .nav-link');
const setSidebarFocusable = (isActive) => {
    if (!navLinksSidebar) return;
    navLinksSidebar.forEach(link => {
        if (isActive) {
            link.removeAttribute('tabindex');
            link.removeAttribute('aria-hidden');
        } else {
            link.setAttribute('tabindex', '-1');
            link.setAttribute('aria-hidden', 'true');
        }
    });
    if (sidebar) {
        sidebar.setAttribute('aria-hidden', isActive ? 'false' : 'true');
    }
};

if (toggleSidebar && sidebar) {
    const setExpanded = (isExpanded) => {
        toggleSidebar.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
    };

    const initialActive = sidebar.classList.contains('active');
    setExpanded(initialActive);
    setSidebarFocusable(initialActive);

    toggleSidebar.addEventListener('click', () => {
        const nowActive = sidebar.classList.toggle('active');
        setExpanded(nowActive);
        setSidebarFocusable(nowActive);
        // Si el menú se abre, mover foco al primer elemento navegable
        if (nowActive) {
            const firstLink = sidebar.querySelector('.nav-link');
            if (firstLink) {
                firstLink.focus();
            }
        }
    });
} else {
    // Si no hay toggle, dejar enlaces accesibles
    setSidebarFocusable(true);
}

// Script to handle section navigation con atributos ARIA
const navLinks = document.querySelectorAll('.nav-link');
const sections = document.querySelectorAll('.content-section');

navLinks.forEach(link => {
    const targetSection = link.getAttribute('data-section');
    if (targetSection) {
        link.setAttribute('aria-controls', `${targetSection}-section`);
    }

    link.addEventListener('click', (e) => {
        // Solo interceptar si el href es '#'
        if (link.getAttribute('href') === '#') {
            e.preventDefault();
            const sectionId = e.currentTarget.getAttribute('data-section');
            if (!sectionId) return;

            // Remove active class and aria-current from all links
            navLinks.forEach(linkEl => {
                linkEl.classList.remove('active');
                linkEl.removeAttribute('aria-current');
            });

            // Add active state to the clicked link
            e.currentTarget.classList.add('active');
            e.currentTarget.setAttribute('aria-current', 'page');

            // Hide all sections
            sections.forEach(section => {
                section.style.display = 'none';
                section.setAttribute('aria-hidden', 'true');
            });

            // Show the target section
            const target = document.getElementById(`${sectionId}-section`);
            if (target) {
                target.style.display = 'block';
                target.setAttribute('aria-hidden', 'false');
                target.setAttribute('tabindex', '-1');
                target.focus();
            }
        }
        // Si no, dejar que el navegador siga el enlace normalmente
    });
});
