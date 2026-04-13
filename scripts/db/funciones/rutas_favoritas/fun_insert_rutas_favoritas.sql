CREATE OR REPLACE FUNCTION fun_insert_rutas_favoritas(
    wid_ruta_favorita tab_rutas_favoritas.id_ruta_favorita%TYPE,
    wid_usuario tab_rutas_favoritas.id_usuario%TYPE,
    wid_ruta tab_rutas_favoritas.id_ruta%TYPE
    ) RETURNS BOOLEAN AS
$$
    BEGIN
    -- Validar nulos
        IF wid_ruta_favorita IS NULL OR wid_usuario IS NULL OR wid_ruta IS NULL THEN
            RAISE NOTICE 'Campos obligatorios no pueden ser NULL';
            RETURN FALSE;
        END IF;

    -- Validar ID de la ruta favorita        
        IF wid_ruta_favorita < 1 THEN
            RAISE NOTICE 'El ID de la ruta favorita debe ser mayor o igual a 1';
            RETURN FALSE;
        END IF;
        
        -- Validar ID del pasajero
        IF wid_pasajero < 1 THEN
            RAISE NOTICE 'El ID del pasajero debe ser mayor o igual a 1';
            RETURN FALSE;
        END IF;
        
        -- Validar ID de la ruta
        IF wid_ruta < 1 THEN
            RAISE NOTICE 'El ID de la ruta debe ser mayor o igual a 1';
            RETURN FALSE;
        END IF;
        
        -- Insertar el nuevo registro
        INSERT INTO tab_rutas_favoritas (
            id_ruta_favorita,
            id_usuario,
            id_ruta
        ) VALUES (
            wid_ruta_favorita, wid_usuario, wid_ruta
        );

        RAISE NOTICE 'Ruta favorita insertada correctamente';
        RETURN TRUE;

        EXCEPTION
        WHEN SQLSTATE '23502' THEN
            RAISE NOTICE 'Campos obligatorios no pueden ser NULL';
            RETURN FALSE;
        WHEN SQLSTATE '23514' THEN
            RAISE NOTICE 'Violación de restricción CHECK. Verifique los valores ingresados';
            RETURN FALSE;
        WHEN SQLSTATE '23505' THEN
            RAISE NOTICE 'El ID de la ruta favorita ya existe';
            RETURN FALSE;
        WHEN OTHERS THEN
            RAISE NOTICE 'Error: %', SQLERRM;
            RETURN FALSE;
    END;
$$
LANGUAGE PLPGSQL;