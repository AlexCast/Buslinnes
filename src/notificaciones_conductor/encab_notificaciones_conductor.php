<!-- Encabezado de notificaciones para conductor -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BusLinnes | Mis notificaciones</title>
    <!--Favicon-->
    <link rel="apple-touch-icon" sizes="180x180" href="/buslinnes/mkcert/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/buslinnes/mkcert/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/buslinnes/mkcert/favicon.ico">
    <link rel="stylesheet" href="/buslinnes/assets/fontawesome/css/all.min.css">
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
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <div class="brand-text">BusLinnes | Notificaciones</div>
                </div>
            </div>
            <nav class="nav-menu">
                <a href="/buslinnes/src/incidentes_conductor/forma_incidentes.php" class="nav-link" title="Ver mis incidentes">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Reportar incidente</span>
                </a>
                <a href="/buslinnes/templates/driver_interface.html" class="nav-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver</span>
                </a>
            </nav>
        </div>
    </header>






