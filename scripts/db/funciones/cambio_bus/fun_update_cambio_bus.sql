CREATE OR REPLACE FUNCTION fun_update_cambio_bus(
    wid_cambio_bus INT,
    wid_incidente INT,
    wid_bus INT,
    wubicacion_cambio VARCHAR
) RETURNS BOOLEAN AS
$$
    DECLARE 
        wreg_cambio_bus RECORD;
    BEGIN
        -- Validaciones iniciales
        IF wid_cambio_bus IS NULL OR wid_cambio_bus <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '23502';
        END IF;

        IF wid_incidente IS NULL OR wid_incidente <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '23502';
        END IF;
        
        IF wid_bus IS NULL OR wid_bus <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '23502';
        END IF;
        
        IF wubicacion_cambio IS NULL OR LENGTH(TRIM(wubicacion_cambio)) = 0 THEN
            RAISE EXCEPTION USING ERRCODE = '23502';
        END IF;

        -- Verificar si existe el registro
        SELECT id_cambio_bus, id_incidente, id_bus, ubicacion_cambio
        INTO wreg_cambio_bus
        FROM tab_cambio_bus 
                WHERE id_cambio_bus = wid_cambio_bus
                    AND fec_delete IS NULL;
        
        IF FOUND THEN
            -- Actualizar el registro existente
            UPDATE tab_cambio_bus SET
                id_incidente = wid_incidente,
                id_bus = wid_bus,
                ubicacion_cambio= wubicacion_cambio
                        WHERE id_cambio_bus = wid_cambio_bus
                            AND fec_delete IS NULL;
            
            RAISE NOTICE 'Bus con ID % actualizado correctamente', wid_cambio_bus;
            RETURN TRUE;
        ELSE
            RAISE EXCEPTION USING ERRCODE = '23505';
        END IF;
        
    EXCEPTION
        WHEN SQLSTATE '23502' THEN
            RAISE NOTICE 'El ID del cambio del bus no puede ser nulo o menor/igual a 0';
            RETURN FALSE;
            
        WHEN SQLSTATE '22002' THEN
            RAISE NOTICE 'El ID del bus no puede ser nulo';
            RETURN FALSE;

        WHEN SQLSTATE '23502' THEN
            RAISE NOTICE 'El ID del incidente no puede ser nulo o menor/igual a 0';
            RETURN FALSE;
            
        WHEN SQLSTATE '23505' THEN
            RAISE NOTICE 'El cambio del bus con ID % no existe para actualizar', wid_cambio_bus;
            RETURN FALSE;
            
        WHEN OTHERS THEN
            RAISE NOTICE 'Error no esperado: %', SQLERRM;
            RETURN FALSE;
    END;
$$
LANGUAGE PLPGSQL;


