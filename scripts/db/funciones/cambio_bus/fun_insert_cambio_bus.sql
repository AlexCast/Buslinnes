CREATE OR REPLACE FUNCTION fun_insert_cambio_bus(
    wid_cambio_bus INT,
    wid_incidente INT,
    wid_bus INT,
    wubicacion_cambio VARCHAR
) RETURNS BOOLEAN AS
$$
    BEGIN
        -- Validar que no haya nulos importantes
        IF wid_cambio_bus IS NULL OR wid_incidente IS NULL OR wid_bus IS NULL OR wubicacion_cambio IS NULL THEN
            RAISE EXCEPTION USING errcode = 23502;
        END IF;

        IF wid_cambio_bus <= 0 OR wid_incidente <= 0 OR wid_bus <= 0 THEN
            RAISE EXCEPTION USING errcode = 22003;
        END IF;

        IF LENGTH(TRIM(wubicacion_cambio)) < 3 THEN
            RAISE EXCEPTION USING errcode = 22001;
        END IF;
        
        -- Insertar el nuevo registro
        INSERT INTO tab_cambio_bus (
            id_cambio_bus,
            id_incidente,
            id_bus,
            ubicacion_cambio
        ) VALUES (
            wid_cambio_bus, wid_incidente, wid_bus, wubicacion_cambio
        );
        
        RAISE NOTICE 'Bus insertado correctamente';
        RETURN TRUE;
    EXCEPTION
        WHEN SQLSTATE '23502' THEN
            RAISE NOTICE 'Campos obligatorios no pueden ser NULL';
            RETURN FALSE;
        WHEN SQLSTATE '22003' THEN
            RAISE NOTICE 'Los IDs deben ser mayores a 0';
            RETURN FALSE;
        WHEN SQLSTATE '22001' THEN
            RAISE NOTICE 'La ubicacion del cambio debe tener minimo 3 caracteres';
            RETURN FALSE;
        WHEN SQLSTATE '23514' THEN
            RAISE NOTICE 'Violación de restricción CHECK. Verifique los valores ingresados';
            RETURN FALSE;
        WHEN OTHERS THEN
            RAISE NOTICE 'Error: %', SQLERRM;
            RETURN FALSE;
    END;
$$
LANGUAGE PLPGSQL;
