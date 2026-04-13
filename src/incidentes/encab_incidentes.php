<!--- Autor: yerson --->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BusLinnes | Incidentes</title>
    <!--Favicon-->
    <link rel="apple-touch-icon" sizes="180x180" href="/buslinnes/mkcert/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/buslinnes/mkcert/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/buslinnes/mkcert/favicon.ico">
    <link rel="stylesheet" href="/buslinnes/assets/fontawesome/css/all.min.css">
    <!-- Usar rutas absolutas para asegurar carga -->
    <link rel="stylesheet" href="/buslinnes/assets/css/_variables.css">
    <link rel="stylesheet" href="/buslinnes/assets/css/main.css">
    <script src="/buslinnes/assets/js/darkmode.js" defer></script>
    <script src="/buslinnes/assets/js/security.js"></script>
    <script src="/buslinnes/assets/js/token_manager.js"></script>
    <script src="/buslinnes/assets/js/branding-runtime.js" defer></script>
    <?php include_once __DIR__ . '/../onesignal_include.php'; ?>
</head>
<body>
    <header class="header-container">
        <div class="header-content">
            <div class="brand-section">
                <div class="brand-logo">
                    <i class="fas fa-warning"></i>
                </div>
                <div>
                    <div class="brand-text">BusLinnes | Incidentes</div>
                </div>
            </div>
            
            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="listar_incidentes.php" class="nav-link">
                        <i class="fas fa-list"></i>
                        <span>Listar</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/buslinnes/templates/buslinnes_interface.html" class="nav-link">
                        <i class="fas fa-arrow-left"></i>
                        <span>Volver</span>
                    </a>
                </div>
            </nav>
        </div>
    </header>   






