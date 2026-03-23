<?php
http_response_code(404);
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 404 - No encontrado | Buslinnes</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/buslinnes/assets/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/buslinnes/assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/buslinnes/assets/img/favicon-16x16.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="/buslinnes/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/buslinnes/assets/css/404.css">
    <script src="/buslinnes/assets/js/darkmode.js" defer></script>
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">
                <a href="/buslinnes/index.php" style="display:flex;align-items:center;">
                    <img src="/buslinnes/assets/img/logo404.svg" alt="Buslinnes" class="logo-img">
                    <span class="sr-only">Buslinnes - Inicio</span>
                </a>
            </div>



            <nav id="main-nav" class="nav-menu" aria-label="Menú principal">
            </nav>

            <div class="header-auth">
                <button id="darkToggle" class="btn btn-outline" aria-pressed="false" title="Alternar modo oscuro" aria-label="Alternar modo oscuro">
                    <span aria-hidden="true">🌙</span>
                </button>
                <a href="/buslinnes/" class="btn btn-outline">Iniciar sesión</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero" aria-labelledby="error-title">
            <div class="container hero-container">
                <div class="hero-content">
                    <h1 id="error-title" class="sr-only">Error 404, página no encontrada</h1>
                    <div aria-hidden="true">
                        <h1 id="hero-title">Error 404</h1>
                        <h1 id="hero-subtitle">No encontrado</h1>
                    </div>
                    <div class="p-container">
                        <p><strong>La página que intentas abrir no existe o fue movida.</strong></p>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="/buslinnes/assets/img/buslinnes404.svg" alt="Sistema de gestión de transporte" class="hero-img-light">
                    <img src="/buslinnes/assets/img/buslinnes404.svg" alt="Sistema de gestión de transporte" class="hero-img-dark">
                </div>
            </div>
        </section>
    </main>

    <footer id="footer" class="site-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 Buslinnes. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="/buslinnes/assets/js/index.js" defer></script>
</body>
</html>


