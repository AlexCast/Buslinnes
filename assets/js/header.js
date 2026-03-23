document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('.header-container');
    const toggle = document.querySelector('.nav-toggle');
    if (!container || !toggle) return;
    toggle.addEventListener('click', () => {
        container.classList.toggle('nav-open');
    });
});