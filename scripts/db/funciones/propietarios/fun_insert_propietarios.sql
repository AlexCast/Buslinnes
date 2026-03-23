CREATE OR REPLACE FUNCTION fun_insert_propietarios(
    wid_propietario tab_propietarios.id_propietario%TYPE,
    wid_bus tab_buses.id_bus%TYPE,
    wnom_propietario tab_propietarios.nom_propietario%TYPE,
    wape_propietario tab_propietarios.ape_propietario%TYPE,
    wtel_propietario tab_propietarios.tel_propietario%TYPE,
    wemail_propietario tab_propietarios.email_propietario%TYPE
) RETURNS BOOLEAN AS
$$
    BEGIN
        -- Validar que no haya nulos importantes
        IF wid_propietario IS NULL OR wid_bus IS NULL OR wnom_propietario IS NULL OR wape_propietario IS NULL OR wtel_propietario IS NULL OR wemail_propietario IS NULL THEN
            RAISE EXCEPTION USING errcode = 23502;
        END IF;

        IF wid_propietario < 1000000000 THEN
            RAISE EXCEPTION USING errcode = 22003;
        END IF;

        IF wid_bus <= 0 THEN
            RAISE EXCEPTION USING errcode = 22003;
        END IF;

        IF LENGTH(TRIM(wnom_propietario)) < 3 OR LENGTH(TRIM(wape_propietario)) < 3 THEN
            RAISE EXCEPTION USING errcode = 22001;
        END IF;

        IF wtel_propietario < 2999999999 OR wtel_propietario > 9999999999 THEN
            RAISE EXCEPTION USING errcode = 22003;
        END IF;

        IF POSITION('@' IN wemail_propietario) = 0 THEN
            RAISE EXCEPTION USING errcode = 22001;
        END IF;
        
        -- Insertar el nuevo registro
        INSERT INTO tab_propietarios (
            id_propietario,
            id_bus,
            nom_propietario,
            ape_propietario,
            tel_propietario,
            email_propietario
        ) VALUES (
            wid_propietario, wid_bus, wnom_propietario, wape_propietario,
            wtel_propietario, wemail_propietario
        );
        
        RETURN TRUE;
    EXCEPTION
        WHEN SQLSTATE '23502' THEN
            RAISE NOTICE 'ID del propietario no puede ser NULL. No invente';
            RETURN FALSE;
        WHEN SQLSTATE '23514' THEN
            RAISE NOTICE 'Violación de restricción CHECK. Verifique los valores ingresados';
            RETURN FALSE;
        WHEN SQLSTATE '22003' THEN
            RAISE NOTICE 'Alguno de los valores numericos esta fuera de rango';
            RETURN FALSE;
        WHEN SQLSTATE '23503' THEN
            RAISE NOTICE 'El bus indicado no existe';
            RETURN FALSE;
        WHEN SQLSTATE '23505' THEN
            RAISE NOTICE 'El ID de propietario ya existe';
            RETURN FALSE;
        WHEN OTHERS THEN
            RAISE NOTICE 'Error: %', SQLERRM;
            RETURN FALSE;
    END;
$$
LANGUAGE PLPGSQL;

--SELECT fun_insert_propietarios(1000000001, 'Juan', 'Pérez', 3004567890, 'juan.perez@email.com');
--SELECT * FROM tab_propietarios