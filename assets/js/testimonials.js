document.addEventListener('DOMContentLoaded', () => {
    // Elementos principales
    const carousel = document.getElementById('testimonials-carousel');
    const slides = document.querySelectorAll('.testimonial-slide');
    const dotsContainer = document.getElementById('testimonials-dots');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    
    // Variables de control
    let currentIndex = 0;
    let autoplayInterval;
    const autoplayTime = 5000; // 5 segundos
    
    // Inicializar el carrusel
    function initCarousel() {
        // Crear dots de navegación
        slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(index));
            dotsContainer.appendChild(dot);
        });
        
        // Configurar botones de navegación
        prevBtn.addEventListener('click', prevSlide);
        nextBtn.addEventListener('click', nextSlide);
        
        // Iniciar autoplay
        startAutoplay();
        
        // Pausar autoplay en hover
        carousel.addEventListener('mouseenter', stopAutoplay);
        carousel.addEventListener('mouseleave', startAutoplay);
        
        // Soporte para navegación táctil
        setupTouchNavigation();
    }
    
    // Ir a un slide específico
    function goToSlide(index) {
        // Asegurar que el índice esté dentro de los límites
        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;
        
        // Guardar el nuevo índice actual
        currentIndex = index;
        
        // Desplazar el carrusel
        const offset = currentIndex * carousel.clientWidth;
        carousel.scrollTo({
            left: offset,
            behavior: 'smooth'
        });
        
        // Actualizar los dots
        updateDots();
    }
    
    // Actualizar la visualización de los dots
    function updateDots() {
        const dots = dotsContainer.querySelectorAll('.dot');
        dots.forEach((dot, index) => {
            if (index === currentIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }
    
    // Navegar al slide anterior
    function prevSlide() {
        goToSlide(currentIndex - 1);
    }
    
    // Navegar al siguiente slide
    function nextSlide() {
        goToSlide(currentIndex + 1);
    }
    
    // Iniciar reproducción automática
    function startAutoplay() {
        stopAutoplay(); // Evitar múltiples intervalos
        autoplayInterval = setInterval(nextSlide, autoplayTime);
    }
    
    // Detener reproducción automática
    function stopAutoplay() {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
        }
    }
    
    // Configurar navegación táctil para móviles
    function setupTouchNavigation() {
        let touchStartX = 0;
        let touchEndX = 0;
        
        carousel.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            stopAutoplay();
        }, { passive: true });
        
        carousel.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            startAutoplay();
        }, { passive: true });
        
        function handleSwipe() {
            const swipeThreshold = 50;
            if (touchEndX < touchStartX - swipeThreshold) {
                // Deslizamiento a la izquierda
                nextSlide();
            } else if (touchEndX > touchStartX + swipeThreshold) {
                // Deslizamiento a la derecha
                prevSlide();
            }
        }
    }
    
    // Ajustar carrusel en caso de redimensionamiento de ventana
    window.addEventListener('resize', () => {
        // Reposicionar el carrusel al slide actual después de redimensionar
        goToSlide(currentIndex);
    });
    
    // Detectar cuando el scroll del carrusel termina para actualizar el índice
    carousel.addEventListener('scroll', debounce(function() {
        // Obtener el índice basado en la posición de scroll
        const newIndex = Math.round(carousel.scrollLeft / carousel.clientWidth);
        
        // Solo actualizar si el índice ha cambiado
        if (newIndex !== currentIndex) {
            currentIndex = newIndex;
            updateDots();
        }
    }, 100));
    
    // Función debounce para limitar el número de llamadas
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }
    
    // Inicializar el carrusel
    initCarousel();
});