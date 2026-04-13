# Favicon Branding Implementation - Resumen de Cambios

## Objetivo
Extender el sistema de branding existente para permitir gestión de favicon desde el panel de administración de `buslinnes_interface.html`, reflejando los cambios en toda la arquitectura.

---

## Cambios Implementados

### 1. **Base de Datos** 📊

#### Archivo: `scripts/db/funciones/branding_config/fun_insert_branding_config.sql`
- **Cambios:**
  - Agregado parámetro `wfavicon_url` a la función
  - Extendidas variables locales `v_favicon_url` y `v_current_favicon`
  - Actualizada lógica para preservar favicon existente si no se proporciona uno nuevo
  - Actualizada sentencia INSERT/UPDATE para incluir `favicon_url`
  - Extendido RETURN TABLE con columna `favicon_url`

#### Archivo: `scripts/db/migrations/add_favicon_to_branding.sql` (NUEVO)
- Script de migración para agregar columna `favicon_url` a `tab_branding_config`
- Validaciones para evitar duplicados
- Fallback a valor por defecto: `/buslinnes/mkcert/favicon.ico`

---

### 2. **Backend PHP** 🔧

#### Archivo: `src/branding_config/branding_config_service.php`
- **brandingConfigDefaults()**: Agregado `favicon_url` con valor por defecto
- **brandingConfigGetCurrent()**: 
  - Actualizada query SELECT para incluir `favicon_url`
  - Extendido array de retorno con favicon
- **brandingConfigInsert()**: 
  - Agregado parámetro `$faviconUrl`
  - Actualizado call a función PostgreSQL con 3 parámetros

#### Archivo: `src/branding_config/save_branding.php`
- **brandingConfigSavePayload()**: 
  - Agregado parámetro `$faviconUrl` (opcional)
  - Pasado a `brandingConfigInsert()`

#### Archivo: `app/save_branding.php`
- **Constantes:**
  - Agregado `$defaultFavicon` = `/buslinnes/mkcert/favicon.ico`
  - Agregado `$maxBytesFavicon` = 500KB
  - Agregado `$allowedMimesFavicon` con tipos: ico, png, webp

- **Upload Handler (Nuevo):**
  - Lógica de validación y upload de favicon similar a logo
  - Almacenamiento en: `/assets/img/custom/favicon_YYYYMMDD_HHMMSS_XXXXXXXX.{ext}`
  - Validación MIME tipo y tamaño máximo
  - Normalización de permisos de archivo

- **Respuesta JSON:** Incluye `favicon_url` en data

#### Archivo: `app/get_branding.php`
- Actualizado bloque de defaults para incluir `favicon_url`

---

### 3. **Frontend JavaScript** 🎨

#### Archivo: `assets/js/branding-runtime.js`
- **Constantes:**
  - Agregado `DEFAULT_FAVICON_URL`
  
- **Nuevas Funciones:**
  - `normalizeFavicon()`: Valida y normaliza URL de favicon
  
- **applyBranding():**
  - Recupera favicon_url de config
  - Actualiza o crea `<link rel="icon">`
  - Almacena favicon_url en `window.__brandingConfig`

- **initBrandingRuntime():**
  - Pasa favicon_url en todas las llamadas a applyBranding()

#### Archivo: `templates/buslinnes_interface.html`

**Constantes JS (línea ~730):**
- Agregado `BRANDING_DEFAULT_FAVICON` = `/buslinnes/mkcert/favicon.ico`

**UI Panel (línea ~410-420):**
- Nuevo campo input: `brandingFaviconFile` (type="file")
  - Acepta: .ico, .png, .webp
  - Max: 500KB
- Nueva vista previa: `brandingFaviconPreview` elemento img (32x32px)

**Función: `applyBrandingPreview()`**
- Agregado parámetro `faviconUrl`
- Actualiza preview de favicon en tiempo real
- Maneja referencias a `brandingFaviconPreview`

**Función: `loadBrandingPanelData()`**
- Recupera `favicon_url` de endpoint
- Pasa 3 parámetros a `applyBrandingPreview()`

**Función: `bindBrandingPanelEvents()`**
- Agregar referencias a elementos favicon
- Event listener para `brandingFaviconFile` change
  - Preview en tiempo real usando URL.createObjectURL()
- Append favicon file a FormData en submit
- Limpiar input de favicon tras guardar

**Reset Button:**
- Pasa favicon_url a `applyBrandingPreview()` en response
- Limpia `faviconFile.value`

---

## 📋 Flujo Completo

```
User Interface (buslinnes_interface.html)
    ↓
[1] Select favicon file + Color + Logo
    ↓
[2] Form Submit → Validate + Create FormData
    ↓
[3] POST /app/save_branding.php
    ├─ Logo Upload → /assets/img/custom/logo_*.{ext}
    └─ Favicon Upload → /assets/img/custom/favicon_*.{ext}
    ↓
[4] Backend: app/save_branding.php
    ├─ Migrate & Store in BD
    └─ Return JSON {primary_color, logo_url, favicon_url}
    ↓
[5] GET /app/get_branding.php (runtime)
    ├─ Query tab_branding_config
    └─ Return JSON con favicon_url
    ↓
[6] branding-runtime.js
    ├─ Set CSS --primary-color
    ├─ Update logo images
    └─ Update <link rel="icon">
    ↓
[7] Browser applies favicon + Branding
```

---

## 🔒 Validaciones

### Frontend
- ✅ Color: Regex `#([0-9a-fA-F]{6}|[0-9a-fA-F]{8})`
- ✅ Logo: Max 2MB, types: png, jpg, webp
- ✅ Favicon: Max 500KB, types: ico, png, webp

### Backend
- ✅ MIME type detection (3 métodos fallback)
- ✅ File size validation
- ✅ JWT authentication requerido
- ✅ Admin role required
- ✅ Directory creation + permissions normalization

### Database
- ✅ CHECK constraint: id_config = 1 (single row)
- ✅ Fallback a default si NULL

---

## 📁 Estructura de Directorios Actualizada

```
/buslinnes/
├── assets/
│   └── img/
│       ├── custom/
│       │   ├── logo_20260412_101530_a1b2c3d4.png
│       │   └── favicon_20260412_101535_e5f6g7h8.ico
│       └── logomorado.svg
└── scripts/
    └── db/
        ├── funciones/
        │   └── branding_config/
        │       └── fun_insert_branding_config.sql (✅ ACTUALIZADO)
        └── migrations/
            └── add_favicon_to_branding.sql (🆕 NUEVO)
```

---

## 4. **Integración en Templates y Encabezados** 🔗

#### Templates HTML (8 archivos):
- `buslinnes_interface.html`
- `passenger_interface.html`
- `driver_interface.html`
- `guest_interface.html`
- `index.html`
- `cambio_contraseña.html`
- `driver_professional_profile.html`
- `verificacion_correo.html`

**Cambios aplicados:**
- Favicon links actualizados: `<link rel="apple-touch-icon|icon">` → `/buslinnes/mkcert/favicon.ico`
- Script `branding-runtime.js` agregado en `<head>` antes de `</head>`
- Validación de estructura HTML correcta

#### Archivos Encabezado en `src/` (17 archivos):
- `cambio_bus/encab_cambio_bus.php`
- `buses/encab_buses.php`
- `conductores/encabezado_conductores.php`
- `incidentes/encabezado_incidentes.php`
- `incidentes_conductor/encabezado_incidentes_conductor.php`
- `pasajeros/encabezado_pasajeros.php`
- `notificaciones_pasajero/encabezado_notificaciones_pasajero.php`
- `notificaciones_conductor/encabezado_notificaciones_conductor.php`
- `notificaciones/encabezado_notificaciones.php`
- `propietarios/encabezado_propietarios.php`
- `parque automotor/encab_parque_automotor.php`
- `ruta bus/encabezado_rutas_buses.php`
- `rutas/encab_rutas.php`
- `roles/encabezado_roles.php`
- `mantenimiento/encabezado_mantenimiento.php`
- `usuarios/encabezado_usuarios.php`
- `usuarios_roles/encabezado_usuarios_roles.php`

**Correcciones realizadas:**
- ✅ Favicon links estandarizados en HEAD
- ✅ Script `branding-runtime.js` integrado correctamente (con defer)
- ✅ Posicionado antes de `</head>` para asegurar ejecución temprana
- ✅ Corrección de errores previos:
  - Eliminado `\`n` literal en `parque automotor/encab_parque_automotor.php`
  - Eliminado `\`n` literal en `ruta bus/encabezado_rutas_buses.php`
  - Eliminado doble `</head>` en `rutas/encab_rutas.php`
- ✅ Validación de estructura HTML completa

---

## 🚀 Próximos Pasos (Opcional)

1. **Ejecutar migración SQL:**
   ```sql
   \i scripts/db/migrations/add_favicon_to_branding.sql
   ```

2. **Verificar tabla:**
   ```sql
   SELECT * FROM tab_branding_config;
   ```

3. **Recargar aplicación en navegador** (Ctrl+F5 para cache clear)

4. **Probar en Admin Panel:**
   - Navegar a Buslinnes Interface
   - Desplegar panel "Personalización de Marca"
   - Cargar favicon + color + logo
   - Verificar aplicación inmediata

---

## 🔍 Depuración

### Console Browser DevTools:
```javascript
// Ver configuración actual
console.log(window.__brandingConfig);

// Verificar favicon link
console.log(document.querySelector('link[rel="icon"]'));
```

### Endpoint Testing:
```bash
# GET branding config
curl http://localhost/buslinnes/app/get_branding.php

# POST branding update (requiere token JWT)
curl -X POST http://localhost/buslinnes/app/save_branding.php \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "primary_color=#8059d4ff" \
  -F "logo=@logo.png" \
  -F "favicon=@favicon.ico"
```

---

## ✅ Compatibilidad

- ✅ **PostgreSQL**: Funciones PL/pgSQL
- ✅ **PHP 7.4+**: Type hints, arrow functions
- ✅ **Modern Browsers**: Fetch API, FormData, File API
- ✅ **Fallback**: Si favicon_url NULL → default `/buslinnes/mkcert/favicon.ico`

---

**Implementación completada**: 2026-04-12
**Versión**: 1.0
