# Integración Cloudinary - Setup

## 1️⃣ Obtener Credenciales Cloudinary

Ve a **https://cloudinary.com** → Dashboard y busca:

### En Panel Superior Derecho:
- **Cloud Name** → Mostrado directamente
  ```
  Ej: abc123xyz
  ```

### En Settings → API Keys:
- **API Key**
  ```
  Ej: 123456789012345
  ```
- **API Secret** ⚠️ **CONFIDENCIAL**
  ```
  Ej: AbCdEfGhIjKlMnOpQrStUvWxYz
  ```

---

## 2️⃣ Configurar `.env`

Abre: `/buslinnes/.env`

Reemplaza:
```env
CLOUDINARY_CLOUD_NAME=tu_cloud_name_aqui
CLOUDINARY_API_KEY=tu_api_key_aqui
CLOUDINARY_API_SECRET=tu_api_secret_aqui
```

**Por ejemplo:**
```env
CLOUDINARY_CLOUD_NAME=abc123xyz
CLOUDINARY_API_KEY=123456789012345
CLOUDINARY_API_SECRET=AbCdEfGhIjKlMnOpQrStUvWxYz
```

⚠️ **Importante:**
- Mantén `/buslinnes/.env` en `.gitignore` (no lo subas a Git)
- Las credenciales son secretas

---

## 3️⃣ Verificar Funcionalidad

### En Admin Panel:
1. Ve a **Buslinnes Interface** → Personalización de Marca
2. Sube un **logo** (PNG/JPG/WEBP max 2MB)
3. Sube un **favicon** (ICO/PNG/WEBP max 500KB)
4. Verifica que se guarden exitosamente

### En Browser Console:
```javascript
// Ver URLs de Cloudinary
console.log(window.__brandingConfig);

// Debería salir:
{
  primary_color: "#8059d4ff",
  logo_url: "https://res.cloudinary.com/abc123xyz/image/upload/...",
  favicon_url: "https://res.cloudinary.com/abc123xyz/image/upload/...",
  updated_at: "2026-04-12T..."
}
```

---

## 4️⃣ Cambios Implementados

### Archivos Nuevos:
- ✅ `/buslinnes/.env` → Configuración de credenciales
- ✅ `/buslinnes/src/cloudinary_helper.php` → Funciones de upload/delete

### Archivos Modificados:
- ✅ `/buslinnes/app/save_branding.php` → Usa Cloudinary en lugar de local
  - `uploadToCloudinary()` para logo y favicon
  - Almacenamiento en: `buslinnes/logo_*` y `buslinnes/favicon_*`
  - URLs retornan directamente desde Cloudinary

### Base de Datos:
- ✅ URLs en BD apuntan a Cloudinary (cambian de `/buslinnes/assets/img/custom/...` a `https://res.cloudinary.com/...`)

---

## 5️⃣ Funciones Disponibles

### `uploadToCloudinary(string $filePath, string $resourceType, ?string $publicId): ?array`
Sube archivo a Cloudinary

**Parámetros:**
- `$filePath`: Ruta local del archivo
- `$resourceType`: 'image' o 'raw'
- `$publicId`: Identificador único en Cloudinary (opcional)

**Retorna:**
```php
[
    'secure_url' => 'https://res.cloudinary.com/...',
    'public_id' => 'buslinnes/logo_...',
    'format' => 'png',
    'width' => 300,
    'height' => 300
]
```

### `deleteFromCloudinary(string $publicId, string $resourceType): bool`
Elimina archivo de Cloudinary

**Uso:**
```php
deleteFromCloudinary('buslinnes/logo_abc123', 'image');
```

---

## 6️⃣ Flujo de Datos

```
Usuario sube logo/favicon
  ↓
Browser → FormData → POST /app/save_branding.php
  ↓
PHP valida MIME + tamaño
  ↓
uploadToCloudinary($tmpPath)
  ↓
cURL → API Cloudinary
  ↓
Cloudinary retorna secure_url
  ↓
save_branding.php guarda en BD (URL de Cloudinary)
  ↓
Response JSON con URLs Cloudinary
  ↓
branding-runtime.js aplica favicon/logo
```

---

## 7️⃣ Troubleshooting

### ❌ Error: "Credenciales de Cloudinary no configuradas"
- ✅ Verifica que `.env` existe en `/buslinnes/`
- ✅ Verifica que tiene valores sin estar vacíos
- ✅ Recarga la aplicación

### ❌ Error: "Error en upload Cloudinary: 400"
- ✅ Verifica que Cloud Name, API Key y API Secret son correctos
- ✅ Verifica que el archivo no está dañado
- ✅ Revisa console.log en DevTools del navegador

### ❌ Error: "cURL error 60"
- ✅ Problema de SSL en servidor
- ✅ Contacta con tu proveedor de hosting

---

## 8️⃣ Ventajas de Cloudinary

| Característica | Local | Cloudinary |
|---|---|---|
| **Almacenamiento** | Servidor (caro) | Nube ilimitada |
| **CDN** | No | Global con caché |
| **Transformaciones** | Manual PHP | Automáticas (resize, crop) |
| **Backup** | Manual | Automático |
| **Escalabilidad** | Limitada | Infinita |
| **Costo** | Hosting + espacio | Gratis hasta 75GB/mes |

---

**Configuración completada**: 2026-04-12  
**Próximo paso**: Ingresa las credenciales Cloudinary en `.env`
