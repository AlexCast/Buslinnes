-- Migración: Agregar soporte para favicon en branding_config
-- Fecha: 2026-04-12
-- Descripción: Extiende la tabla tab_branding_config para soportar gestión de favicon

-- Verificar si la columna ya existe
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'tab_branding_config' 
        AND column_name = 'favicon_url'
    ) THEN
        -- Agregar columna favicon_url si no existe
        ALTER TABLE tab_branding_config 
        ADD COLUMN favicon_url TEXT DEFAULT '/buslinnes/mkcert/favicon.ico';
        
        RAISE NOTICE 'Columna favicon_url agregada exitosamente a tab_branding_config';
    ELSE
        RAISE NOTICE 'La columna favicon_url ya existe en tab_branding_config';
    END IF;
END
$$;

-- Actualizar la función de inserción de branding_config
-- (Ya debe estar reemplazada por la versión actualizada en fun_insert_branding_config.sql)

-- Verificar integridad: todas las filas deben tener favicon_url
UPDATE tab_branding_config 
SET favicon_url = '/buslinnes/mkcert/favicon.ico' 
WHERE favicon_url IS NULL;

-- Log de versión
COMMENT ON COLUMN tab_branding_config.favicon_url IS 'URL del favicon. Soporta: ico, png, webp. Máx 500KB';
