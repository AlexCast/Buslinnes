<!--- Autor: alexndrcastt --->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BusLinnes | Usuarios Roles</title>
    <!--Favicon-->
    <link rel="apple-touch-icon" sizes="180x180" href="/buslinnes/assets/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/buslinnes/assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/buslinnes/assets/img/favicon-16x16.png">
    <link rel="stylesheet" href="/buslinnes/assets/fontawesome/css/all.min.css">
    <!-- Usar rutas absolutas para asegurar carga -->
    <link rel="stylesheet" href="/buslinnes/assets/css/main.css">
    <script src="/buslinnes/assets/js/darkmode.js" defer></script>
    <script src="/buslinnes/assets/js/security.js"></script>
    <script src="/buslinnes/assets/js/token_manager.js"></script>
    <?php include_once __DIR__ . '/../onesignal_include.php'; ?>
</head>
<body>
    <header class="header-container">
        <div class="header-content">
            <div class="brand-section">
                <div class="brand-logo">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <div class="brand-text">BusLinnes | Usuarios Roles</div>
                </div>
            </div>
            
            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="listar_usuarios_roles.php" class="nav-link">
                        <i class="fas fa-list"></i>
                        <span>Listar</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="forma_usuarios_roles.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>Agregar</span>
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






