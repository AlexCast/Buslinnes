<?php
session_start();
$reason = isset($_GET['reason']) ? $_GET['reason'] : '';
$loginTarget = 'login.php';
if ($reason === 'devtools') {
  $loginTarget = 'login.php?blocked=devtools';
}
// Destruir todas las variables de sesión
$_SESSION = array();
// Si se desea destruir la sesión completamente, también se debe borrar la cookie de sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
// Eliminar cookie del token JWT
setcookie('jwt_token', '', time() - 3600, '/');

// Redirección PHP como respaldo
header("Refresh: 1; url={$loginTarget}");

echo '<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
window.OneSignalDeferred = window.OneSignalDeferred || [];
OneSignalDeferred.push(async function(OneSignal) {
  try {
    // Desconectar de OneSignal para dejar de recibir notificaciones
    await OneSignal.logout();
    console.log("✅ Desconectado de OneSignal");
  } catch (e) { 
    console.warn("⚠️ Error al desconectar de OneSignal:", e); 
  }
});

// Ejecutar logout mientras se carga OneSignal
localStorage.removeItem("jwt_token");

// Redirección inmediata al login
window.location.href = ' . json_encode($loginTarget) . ';
</script>';
exit();


