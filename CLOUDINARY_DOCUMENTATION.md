# Integración Cloudinary - Documentación Completa

## 📚 Índice
1. [¿Por qué Cloudinary?](#por-qué-cloudinary)
2. [Arquitectura General](#arquitectura-general)
3. [Flujo de Datos](#flujo-de-datos)
4. [Archivos Modificados](#archivos-modificados)
5. [Archivos Creados](#archivos-creados)
6. [Funciones Principales](#funciones-principales)
7. [Proceso de Upload](#proceso-de-upload)
8. [Configuración](#configuración)
9. [Testing](#testing)
10. [Troubleshooting](#troubleshooting)

---

## 🎯 ¿Por qué Cloudinary?

### Problema Original:
- Almacenamiento local en `/buslinnes/assets/img/custom/`
- Consume espacio del servidor
- Sin CDN global
- Difícil de escalar
- Sin backup automático

### Solución con Cloudinary:
```
ANTES: 
  Logo/Favicon → Servidor local → ~1-2MB consumidos
  
AHORA:
  Logo/Favicon → Cloudinary API → Cloud Storage + CDN Global
```

**Beneficios:**
- ✅ Almacenamiento ilimitado en la nube
- ✅ CDN global (carga rápida desde cualquier país)
- ✅ Transformaciones automáticas (resize, crop, etc.)
- ✅ Backup automático
- ✅ Servidor local 100% libre
- ✅ Plan gratuito: 75GB/mes incluido

---

## 🏗️ Arquitectura General

```
┌─────────────────────────────────────────────────────────────┐
│                    ARQUITECTURA CLOUDINARY                  │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  FRONTEND (Browser)                                         │
│  ├─ Buslinnes Interface (Admin Panel)                       │
│  ├─ Subir Logo/Favicon via Form                             │
│  └─ Ver preview en tiempo real                              │
│           ↓                                                  │
│  BACKEND (PHP)                                              │
│  ├─ app/save_branding.php                                   │
│  │  ├─ Validar MIME type                                    │
│  │  ├─ Validar tamaño (2MB logo, 500KB favicon)             │
│  │  └─ Llamar uploadToCloudinary()                          │
│  ├─ src/cloudinary_helper.php                               │
│  │  ├─ Leer .env (credenciales)                             │
│  │  ├─ Construir firma SHA256                               │
│  │  ├─ Enviar archivo via HTTPS stream                      │
│  │  └─ Retornar URL de Cloudinary                           │
│  └─ app/get_branding.php                                    │
│     └─ Retorna JSON con URLs Cloudinary                     │
│           ↓                                                  │
│  BASE DE DATOS                                              │
│  └─ tab_branding_config.favicon_url = "https://res..."      │
│           ↓                                                  │
│  CLOUDINARY API                                             │
│  ├─ Autenticación con firma (API Key + Secret)              │
│  ├─ Almacenamiento en carpeta: buslinnes/                   │
│  └─ Retorna URL pública: https://res.cloudinary.com/...     │
│           ↓                                                  │
│  CDN GLOBAL                                                 │
│  └─ URL disponible desde cualquier país                     │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 Flujo de Datos

### 1️⃣ Upload de Logo/Favicon:

```
Admin Panel (buslinnes_interface.html)
    │
    ├─ Input file: <input type="file" id="brandingLogoFile">
    ├─ Input file: <input type="file" id="brandingFaviconFile">
    │
    └─ Form Submit → POST /app/save_branding.php
       │
       ├─ $ Base64 FormData()
       ├─ $ logo_file → validate MIME (png/jpg/webp)
       ├─ $ favicon_file → validate MIME (ico/png/webp)
       │
       └─ app/save_branding.php
          │
          ├─ Validar JWT token (admin role)
          ├─ Validar tamaño archivos
          │
          └─ Para cada archivo:
             │
             ├─ uploadToCloudinary($tmpPath)
             │  │
             │  ├─ loadEnvConfig() → Lee .env
             │  ├─ Generar timestamp único
             │  ├─ Crear public_id: buslinnes/logo_YYYYMMDDHHmmss_XXXX
             │  ├─ Generar firma: SHA256("public_id=X&timestamp=Y" + apiSecret)
             │  ├─ Construir multipart form data
             │  ├─ POST a https://api.cloudinary.com/v1_1/{cloudName}/image/upload
             │  │
             │  └─ Cloudinary →
             │     ├─ Valida firma
             │     ├─ Almacena en: https://res.cloudinary.com/dvl7uaon7/image/upload/...
             │     └─ Retorna: { secure_url, public_id, format, width, height }
             │
             └─ Retornar { secure_url: "https://res.cloudinary.com/..." }
          │
          └─ Guardar en BD:
             │
             └─ brandingConfigInsert(
                   primaryColor,
                   logoUrlCloudinary,  ← "https://res.cloudinary.com/..."
                   faviconUrlCloudinary ← "https://res.cloudinary.com/..."
                )
                │
                └─ INSERT/UPDATE tab_branding_config
                   SET logo_url = "https://...", favicon_url = "https://..."
```

### 2️⃣ Recuperar y Aplicar Branding:

```
Browser carga página
    │
    └─ branding-runtime.js
       │
       ├─ fetch('/app/get_branding.php')
       │
       └─ GET /app/get_branding.php
          │
          ├─ SELECT logo_url, favicon_url FROM tab_branding_config
          │
          └─ Retorna JSON:
             {
               "primary_color": "#8059d4ff",
               "logo_url": "https://res.cloudinary.com/dvl7uaon7/image/upload/...",
               "favicon_url": "https://res.cloudinary.com/dvl7uaon7/image/upload/...",
               "updated_at": "2026-04-12T10:30:00Z"
             }
       │
       └─ applyBranding(data)
          │
          ├─ Update CSS: --primary-color = #8059d4ff
          ├─ Update <img class="logo-img"> src = favicon_url
          ├─ Update <link rel="icon"> href = favicon_url
          │
          └─ window.__brandingConfig = { ... }
```

---

## 🔧 Archivos Modificados

### 1. `app/save_branding.php`

**Cambios principales:**
```php
// ANTES:
- Guardaba archivos en /assets/img/custom/
- $targetFileFs = '/buslinnes/assets/img/custom/logo_...';
- $logoUrlToSave = '/buslinnes/assets/img/custom/' . $fileName;

// AHORA:
- Llama a uploadToCloudinary()
- $uploadedLogo = uploadToCloudinary($tmpPath, 'image', 'buslinnes/logo_...');
- $logoUrlToSave = $uploadedLogo['secure_url']; // https://res.cloudinary.com/...
```

**Línea agregada al inicio:**
```php
require_once __DIR__ . '/../src/cloudinary_helper.php';
```

**Logo Upload (antes línea ~110):**
```php
try {
    $uploadedLogo = uploadToCloudinary($tmpPath, 'image', 'buslinnes/logo_' . date('YmdHis') . '_' . substr(md5(uniqid()), 0, 8));
    $logoUrlToSave = $uploadedLogo['secure_url'];
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar logo en Cloudinary: ' . $e->getMessage()]);
    exit;
}
```

**Favicon Upload (antes línea ~160):**
```php
try {
    $uploadedFavicon = uploadToCloudinary($tmpPath, 'image', 'buslinnes/favicon_' . date('YmdHis') . '_' . substr(md5(uniqid()), 0, 8));
    $faviconUrlToSave = $uploadedFavicon['secure_url'];
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar favicon en Cloudinary: ' . $e->getMessage()]);
    exit;
}
```

---

## 📄 Archivos Creados

### 1. `.env` → Configuración de Credenciales

**Ubicación:** `/buslinnes/.env`

**Contenido:**
```env
# Cloudinary Configuration
CLOUDINARY_CLOUD_NAME="tu_cloud_name"
CLOUDINARY_API_KEY="tu_api_key"
CLOUDINARY_API_SECRET="tu_api_secret"
```

**Notas:**
- ⚠️ Incluido en `.gitignore` (no se sube a Git)
- ✅ Soporta comillas simples/dobles o sin comillas
- 🔐 Mantén API_SECRET confidencial

---

### 2. `src/cloudinary_helper.php` → Funciones de Upload

**339 líneas de código PHP**

**Funciones principales:**
1. `loadEnvConfig()` - Lee credenciales de .env
2. `uploadToCloudinary()` - Sube archivo a Cloudinary
3. `deleteFromCloudinary()` - Elimina archivo de Cloudinary

---

## 🔌 Funciones Principales

### `loadEnvConfig(): array`

**Propósito:** Leer variables de entorno del archivo `.env`

**Código:**
```php
function loadEnvConfig(): array
{
    $envFile = __DIR__ . '/../.env';
    $config = [
        'CLOUDINARY_CLOUD_NAME' => '',
        'CLOUDINARY_API_KEY' => '',
        'CLOUDINARY_API_SECRET' => ''
    ];

    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remover comillas
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }
                
                if (isset($config[$key])) {
                    $config[$key] = $value;
                }
            }
        }
    }

    return $config;
}
```

**Retorna:**
```php
[
    'CLOUDINARY_CLOUD_NAME' => 'abc123xyz',
    'CLOUDINARY_API_KEY' => '123456789',
    'CLOUDINARY_API_SECRET' => 'abcdef...'
]
```

---

### `uploadToCloudinary($filePath, $resourceType, $publicId): array`

**Propósito:** Subir archivo a Cloudinary API

**Parámetros:**
- `$filePath` (string): Ruta local del archivo
- `$resourceType` (string): Tipo de recurso ('image' o 'raw')
- `$publicId` (string|null): ID único en Cloudinary folder

**Proceso:**
```php
1. Validar que archivo existe
2. Leer credenciales de .env
3. Generar timestamp único
4. Crear public_id: buslinnes/logo_20260412_103000_a1b2c3d4
5. Generar firma SHA256:
   SHA256("public_id=buslinnes/logo_...&timestamp=1712955600000" + SECRET)
6. Leer contenido archivo en binario
7. Construir multipart form data (RFC 2388)
8. Crear stream context con headers HTTPS
9. POST a https://api.cloudinary.com/v1_1/{cloudName}/image/upload
10. Parse JSON response
11. Retornar { secure_url, public_id, format, width, height }
```

**Retorna:**
```php
[
    'secure_url' => 'https://res.cloudinary.com/dvl7uaon7/image/upload/v1712955600/buslinnes/logo_...',
    'public_id' => 'buslinnes/logo_20260412_103000_a1b2c3d4',
    'format' => 'png',
    'width' => 300,
    'height' => 300
]
```

**Excepciones:**
```php
// Si archivo no existe
throw new Exception("Archivo no encontrado: $filePath");

// Si .env no configurado
throw new Exception("Credenciales de Cloudinary no configuradas en .env");

// Si falla la conexión HTTPS
throw new Exception("Error en upload Cloudinary: ...");

// Si Cloudinary rechaza firma
throw new Exception("Error en upload Cloudinary: file_get_contents failed 401 Unauthorized");
```

---

### `deleteFromCloudinary($publicId, $resourceType): bool`

**Propósito:** Eliminar archivo de Cloudinary

**Uso:**
```php
deleteFromCloudinary('buslinnes/logo_20260412_103000_a1b2c3d4', 'image');
```

**Retorna:**
```php
true  // Si se eliminó exitosamente
false // Si falló o no existe
```

---

## 📤 Proceso de Upload Detallado

### Paso 1: Validación de Archivo

```php
// app/save_branding.php - línea ~120
if ((int)$file['size'] <= 0 || (int)$file['size'] > $maxBytesLogo) {
    // Rechazar si > 2MB
}

$mime = detectUploadedMime($tmpPath);
if (!$mime || !isset($allowedMimesLogo[$mime])) {
    // Rechazar si no es png/jpg/webp
}
```

### Paso 2: Generar Firma

```php
// src/cloudinary_helper.php - línea ~60
$timestamp = (int)(microtime(true) * 1000);
$publicId = 'buslinnes/logo_' . date('YmdHis') . '_' . substr(md5(uniqid()), 0, 8);

// La firma es un SHA256 del string: "public_id=X&timestamp=Y" + apiSecret
$toSign = "public_id=$publicId&timestamp=$timestamp" . $apiSecret;
$signature = hash('sha256', $toSign);
```

**Ejemplo de firma:**
```
String a firmar:   "public_id=buslinnes/logo_20260412_103000_a1b2c3d4&timestamp=1712955600000" + "miApiSecret"
Firma SHA256:      e3f4a5b6c7d8e9f0a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3
```

### Paso 3: Construir Multipart Form Data

```php
// RFC 2388 - Multipart Form Data
Content-Type: multipart/form-data; boundary=----Boundary

------Boundary
Content-Disposition: form-data; name="public_id"

buslinnes/logo_20260412_103000_a1b2c3d4
------Boundary
Content-Disposition: form-data; name="timestamp"

1712955600000
------Boundary
Content-Disposition: form-data; name="api_key"

123456789012345
------Boundary
Content-Disposition: form-data; name="signature"

e3f4a5b6c7d8e9f0a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3
------Boundary
Content-Disposition: form-data; name="file"; filename="logo.png"
Content-Type: application/octet-stream

[CONTENIDO BINARIO DEL ARCHIVO]
------Boundary--
```

### Paso 4: HTTPS Stream Request

```php
// src/cloudinary_helper.php - línea ~85
$options = [
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: multipart/form-data; boundary=' . $boundary,
        ],
        'content' => $body,  // Multipart form data
        'timeout' => 60,
    ],
    'ssl' => [
        'verify_peer' => true,
        'verify_peer_name' => true,
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);
```

### Paso 5: Cloudinary Valida y Almacena

```
Cloudinary API (https://api.cloudinary.com/v1_1/dvl7uaon7/image/upload)
├─ Valida firma SHA256 usando su copia del API_SECRET
├─ Verifica que timestamp no esté expirado (< 1 hora)
├─ Valida contenido del archivo
├─ Almacena en: res.cloudinary.com/dvl7uaon7/image/upload/v{version}/buslinnes/logo_...
└─ Retorna JSON: { secure_url, public_id, format, ... }
```

### Paso 6: Guardar en BD

```php
// app/save_branding.php - línea ~220
$saved = brandingConfigSavePayload(
    $primaryColor,
    $logoUrlToSave,      // "https://res.cloudinary.com/..."
    $faviconUrlToSave    // "https://res.cloudinary.com/..."
);

// INSERT INTO tab_branding_config (logo_url, favicon_url) VALUES (...)
```

---

## ⚙️ Configuración

### 1. Obtener Credenciales Cloudinary

**Ir a:** https://cloudinary.com → Dashboard

**Cloud Name (visible arriba):**
```
dvl7uaon7
```

**API Key & Secret (Settings → API Keys):**
```
API Key: 123456789012345
API Secret: abcdef1234567890ghijklmnopqrstu
```

### 2. Actualizar `.env`

**Archivo:** `/buslinnes/.env`

```env
CLOUDINARY_CLOUD_NAME="dvl7uaon7"
CLOUDINARY_API_KEY="123456789012345"
CLOUDINARY_API_SECRET="abcdef1234567890ghijklmnopqrstu"
```

### 3. Verificar Permisos

```bash
# .env debe ser legible por Apache/PHP
ls -la /buslinnes/.env
# Debería mostrar: -rw-r--r--
```

### 4. Git Ignore

```bash
# Verificar que .env está en .gitignore
cat /buslinnes/.gitignore | grep "^.env"
# Debería mostrar: .env
```

---

## 🧪 Testing

### Test 1: Verificar Carga de Config

```php
// Archivo temporal: test_cloudinary_config.php
<?php
require_once __DIR__ . '/src/cloudinary_helper.php';

$config = loadEnvConfig();
var_dump($config);

// Debería mostrar:
// array(3) {
//   ['CLOUDINARY_CLOUD_NAME']=> string(9) "dvl7uaon7"
//   ['CLOUDINARY_API_KEY']=> string(15) "123456789012345"
//   ['CLOUDINARY_API_SECRET']=> string(34) "abcdef..."
// }
```

### Test 2: Test de Upload Manual

```php
<?php
require_once __DIR__ . '/src/cloudinary_helper.php';

try {
    $result = uploadToCloudinary(
        '/buslinnes/assets/img/logomorado.svg',
        'image',
        'buslinnes/test_logo_' . time()
    );
    
    echo "✅ Upload exitoso:\n";
    echo "URL: " . $result['secure_url'] . "\n";
    echo "Public ID: " . $result['public_id'] . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

### Test 3: Verificar en Admin Panel

1. Ve a **Buslinnes Interface** → Personalización de Marca
2. Sube un logo nuevo (PNG/JPG/WEBP)
3. Verifica que aparezca el preview
4. Abre DevTools (F12) → Console:
```javascript
console.log(window.__brandingConfig);
// Debería mostrar URL de Cloudinary
```

### Test 4: Verificar en Media Library

1. Ve a **https://cloudinary.com/console/media_library**
2. Busca carpeta `buslinnes/`
3. Debería ver: `logo_*` y `favicon_*`

---

## 🐛 Troubleshooting

### Error 1: "Credenciales de Cloudinary no configuradas"

**Causa:** `.env` no existe o está vacío

**Solución:**
```powershell
# Verificar .env existe
Test-Path C:\Apache24\htdocs\buslinnes\.env

# Si no existe, crear:
Set-Content -Path C:\Apache24\htdocs\buslinnes\.env -Value @"
CLOUDINARY_CLOUD_NAME="dvl7uaon7"
CLOUDINARY_API_KEY="123456789012345"
CLOUDINARY_API_SECRET="abcdef1234567890"
"@
```

### Error 2: "Failed to open stream: HTTP request failed! 401 Unauthorized"

**Causa:** Firma SHA256 incorrecta o credenciales inválidas

**Solución:**
```php
// Verificar que las credenciales son exactas (sin espacios)
$config = loadEnvConfig();
var_dump($config);
// Comparar con Dashboard de Cloudinary
```

### Error 3: "Error en upload: file_get_contents failed"

**Causa:** Problema de conexión HTTPS o SSL

**Solución:**
```php
// Verificar que OpenSSL está habilitado en php.ini
php -i | grep -i ssl

// Si no está, contactar host
```

### Error 4: "Archivo no encontrado"

**Causa:** La ruta del archivo no es válida

**Solución:**
```php
// Verificar que el archivo existe
if (!file_exists($tmpPath)) {
    error_log("Archivo no existe: $tmpPath");
}
```

### Error 5: Reportes muestran 0 uploads

**Causa:** Delay en reportes de Cloudinary (normal)

**Solución:**
```javascript
// Verificar en Media Library en lugar de reportes
// Los reportes tardan 24-48 horas en actualizarse
// Los archivos están disponibles inmediatamente
```

---

## 📊 Comparativa: Local vs Cloudinary

| Aspecto | Local (`/assets/img/custom/`) | Cloudinary |
|---|---|---|
| **Almacenamiento** | Servidor local | Nube |
| **Espacio consumido** | ~2-4MB por logo/favicon | 0 en servidor |
| **CDN** | No | Sí (global) |
| **Velocidad** | 100ms (desde servidor) | 10-50ms (desde caché CDN) |
| **Escalabilidad** | Limitada (disco lleno) | Ilimitada (plan gratis 75GB) |
| **Backup** | Manual | Automático |
| **Costo** | Incluido hosting | Gratis hasta 75GB/mes |
| **Transformaciones** | Requeridas en PHP | Automáticas Cloudinary |
| **URLs** | `/buslinnes/assets/img/custom/logo.png` | `https://res.cloudinary.com/dvl7uaon7/image/upload/...` |

---

## 🔐 Seguridad

### Firma SHA256 - ¿Cómo Funciona?

**Propósito:** Evitar que alguien modifique parámetros del upload

**Proceso:**
```
1. Frontend sube archivo a Backend
2. Backend genera: public_id + timestamp
3. Backend calcula: SHA256("public_id=X&timestamp=Y" + API_SECRET)
4. Backend envía: public_id, timestamp, FIRMA a Cloudinary
5. Cloudinary valida: SHA256(...) coincida con firma
6. Si coincide → Acepta upload. Si no → Rechaza (401)
```

**Solo nosotros sabemos el API_SECRET:**
```
Si alguien intenta modificar los parámetros:
- Cambia: public_id=HACKER
- Pero no puede recalcular la firma (sin conocer API_SECRET)
- Cloudinary rechaza: 401 Unauthorized
```

### Credenciales en `.env`

**¿Por qué no poner en código?**
```php
// ❌ MAL - expone credenciales
define('CLOUDINARY_API_SECRET', 'abcdef...');

// ✅ BIEN - en .env + .gitignore
$secret = loadEnvConfig()['CLOUDINARY_API_SECRET'];
```

**`.gitignore` protege:**
```bash
# .env no se sube a Git
.env

# Solo se comparte .env.example
.env.example:
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=
```

---

## 📈 Monitoreo y Mantenimiento

### Ver Uso de Cloudinary

**Dashboard:** https://cloudinary.com/console

**Datos visibles:**
- Transformaciones realizadas
- Ancho de banda consumido
- Almacenamiento usado
- Ratio de hits de caché

### Limpiar Media Library

```php
// Eliminar archivos antiguos
deleteFromCloudinary('buslinnes/logo_20260401_100000_a1b2c3d4', 'image');
```

### Cambiar Credenciales

```bash
# 1. En Cloudinary: Settings → Regenerate API Secret
# 2. Actualizar .env:
CLOUDINARY_API_SECRET="nuevo_secret_aqui"
# 3. Reiniciar servicio PHP (o próximo request)
```

---

## 🎓 Resumen Educativo

### Conceptos Clave Aprendidos:

1. **APIs REST** - Comunicación HTTPS con Cloudinary
2. **Multipart Form Data** (RFC 2388) - Envío de archivos binarios
3. **Firmas Criptográficas** - SHA256 para validación
4. **Stream Contexts** - PHP HTTPS sin cURL
5. **Variables de Entorno** - Seguridad en `.env`
6. **CDN** - Distribución global de contenido
7. **Mitigación de Riesgos** - Backup automático en nube

### Referencias:

- 📖 Cloudinary API: https://cloudinary.com/documentation/upload_widget_reference
- 📖 PHP Stream Context: https://www.php.net/manual/context.http.php
- 📖 RFC 2388 Multipart: https://www.ietf.org/rfc/rfc2388.txt
- 📖 SHA256 PHP: https://www.php.net/manual/en/function.hash.php

---

**Documentación creada:** 2026-04-12  
**Versión:** 1.0  
**Autor:** Integración BusLinnes-Cloudinary
