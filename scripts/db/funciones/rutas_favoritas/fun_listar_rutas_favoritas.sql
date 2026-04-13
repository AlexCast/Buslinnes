CREATE OR REPLACE FUNCTION fun_listar_rutas_favoritas(wid_ruta_favorita tab_rutas_favoritas.id_ruta_favorita%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_ruta_favorita tab_rutas_favoritas%ROWTYPE; -- Usamos %ROWTYPE para manejar toda la fila
BEGIN
    IF wid_ruta_favorita <= 0 THEN
        RAISE NOTICE 'El ID de la ruta favorita no puede ser menor o igual a 0.';
        RETURN FALSE;
    END IF;
    
    -- Obtener la ruta favorita
    SELECT id_ruta_favorita, id_usuario, id_ruta
    INTO wreg_ruta_favorita
    FROM tab_rutas_favoritas
    WHERE id_ruta_favorita = wid_ruta_favorita;
    
    -- Verificar si se encontró un registro
    IF NOT FOUND THEN
        RAISE NOTICE 'La ruta favorita con ID % no existe.', wid_ruta_favorita;
        RETURN FALSE;
    END IF;
    
    RAISE NOTICE 'ID Ruta Favorita: %, ID Usuario: %, ID Ruta: %', 
                  wreg_ruta_favorita.id_ruta_favorita,
                  wreg_ruta_favorita.id_usuario,
                  wreg_ruta_favorita.id_ruta;
    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;

