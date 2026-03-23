<!-- OneSignal - Push notifications para usuarios dentro de src -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
window.OneSignalDeferred = window.OneSignalDeferred || [];
OneSignalDeferred.push(async function(OneSignal) {
  try {
    await OneSignal.init({
      appId: "5f5d1290-a2ef-43ce-866d-d2efefec6041",
      safari_web_id: "web.onesignal.auto.0e731bf1-0f8d-4c8c-8593-03e4c907000a",
      notifyButton: { enable: true },
      serviceWorkerParam: { scope: '/' },
      serviceWorkerPath: '/OneSignalSDKWorker.js',
    });
    
    // Solicitar permiso de notificaciones al usuario
    await OneSignal.Notifications.requestPermission();
    
    // Si hay JWT con id_usuario y rol, configurar para recibir notificaciones dirigidas
    var token = typeof localStorage !== 'undefined' && localStorage.getItem('jwt_token');
    if (token) {
      try {
        var base64Url = token.split('.')[1];
        var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        var json = JSON.parse(decodeURIComponent(atob(base64).split('').map(function(c) {
          return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join('')));
        if (json.id_usuario) await OneSignal.login(String(json.id_usuario));
        if (json.id_rol) await OneSignal.User.addTag("rol_id", String(json.id_rol));
      } catch (e) {}
    }
  } catch (e) { console.warn('OneSignal:', e); }
});
</script>


