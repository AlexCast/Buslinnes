CREATE OR REPLACE FUNCTION fun_update_rutas_favoritas(
    wid_ruta_favorita tab_rutas_favoritas.id_ruta_favorita%TYPE,
    wid_pasajero tab_rutas_favoritas.id_pasajero%TYPE,
    wid_ruta tab_rutas_favoritas.id_ruta%TYPE
) RETURNS BOOLEAN AS
$$
    DECLARE 
        wreg_ruta_favorita RECORD;
    BEGIN
        -- Validaciones iniciales
        IF wid_ruta_favorita IS NULL OR wid_ruta_favorita <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '23502';
        END IF;

        IF wid_pasajero IS NULL OR wid_pasajero <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '23503';
        END IF;

        IF wid_ruta IS NULL OR wid_ruta <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '23504';
        END IF;

        -- Verificar si existe el registro
        SELECT id_ruta_favorita, id_pasajero, id_ruta INTO wreg_ruta_favorita FROM tab_rutas_favoritas 
        WHERE id_ruta_favorita = wid_ruta_favorita;

        IF FOUND THEN
            -- Actualizar el registro existente
            UPDATE tab_rutas_favoritas SET
                id_pasajero = wid_pasajero,
                id_ruta = wid_ruta
            WHERE id_ruta_favorita = wid_ruta_favorita;

            RAISE NOTICE 'Ruta favorita con ID % actualizada correctamente', wid_ruta_favorita;
            RETURN TRUE;
        ELSE
            RAISE EXCEPTION USING ERRCODE = '23505';
        END IF;

    EXCEPTION
        WHEN SQLSTATE '23502' THEN
            RAISE NOTICE 'El ID de la ruta favorita no puede ser nulo o menor/igual a 0';
            RETURN FALSE;

        WHEN SQLSTATE '23503' THEN
            RAISE NOTICE 'El ID del pasajero no puede ser nulo o menor/igual a 0';
            RETURN FALSE;

        WHEN SQLSTATE '23504' THEN
            RAISE NOTICE 'El ID de la ruta no puede ser nulo o menor/igual a 0';
            RETURN FALSE;

        WHEN SQLSTATE '23505' THEN
            RAISE NOTICE 'La ruta favorita con ID % no existe para actualizar', wid_ruta_favorita;
            RETURN FALSE;

        WHEN OTHERS THEN
            RAISE NOTICE 'Error no esperado: %', SQLERRM;
            RETURN FALSE;
    END;
$$
LANGUAGE PLPGSQL;