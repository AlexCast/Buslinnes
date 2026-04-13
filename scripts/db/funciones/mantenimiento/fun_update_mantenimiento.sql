CREATE OR REPLACE FUNCTION fun_update_mantenimiento(
    wid_mantenimiento tab_mantenimiento.id_mantenimiento%TYPE,
    wid_bus tab_mantenimiento.id_bus%TYPE,
    wdescripcion tab_mantenimiento.descripcion%TYPE,
    wfecha_mantenimiento tab_mantenimiento.fecha_mantenimiento%TYPE,
    wcosto_mantenimiento tab_mantenimiento.costo_mantenimiento%TYPE
) RETURNS BOOLEAN AS
$$
    DECLARE
        v_bus tab_buses.id_bus%TYPE;
        wreg_mantenimiento RECORD;
    BEGIN
        -- Validaciones iniciales
        IF wid_mantenimiento IS NULL OR wid_mantenimiento <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '23502';
        END IF;

        v_bus := UPPER(REGEXP_REPLACE(TRIM(COALESCE(wid_bus, '')), '\\s+', '', 'g'));
        IF v_bus = '' THEN
            RAISE EXCEPTION USING ERRCODE = '23502';
        END IF;

        IF v_bus !~ '^[A-Z]{3}[0-9]{3}$' THEN
            RAISE EXCEPTION USING ERRCODE = '22023';
        END IF;
        
        IF wdescripcion IS NULL OR LENGTH(TRIM(wdescripcion)) < 10 THEN
            RAISE EXCEPTION USING ERRCODE = '22001';
        END IF;

        IF wfecha_mantenimiento IS NULL THEN
            RAISE EXCEPTION USING ERRCODE = '23502';
        END IF;
        
        IF wcosto_mantenimiento IS NULL OR wcosto_mantenimiento < 0 OR wcosto_mantenimiento > 9999999999 THEN
            RAISE EXCEPTION USING ERRCODE = '22003';
        END IF;
        
        -- Verificar si existe el registro
        SELECT id_mantenimiento, id_bus, descripcion, fecha_mantenimiento, costo_mantenimiento
        INTO wreg_mantenimiento
        FROM tab_mantenimiento 
                WHERE id_mantenimiento = wid_mantenimiento
                    AND fec_delete IS NULL;
        
        IF FOUND THEN
            -- Actualizar el registro existente
            UPDATE tab_mantenimiento SET
                id_bus = v_bus,
                descripcion = wdescripcion,
                fecha_mantenimiento = wfecha_mantenimiento,
                costo_mantenimiento = wcosto_mantenimiento
                        WHERE id_mantenimiento = wid_mantenimiento
                            AND fec_delete IS NULL;
            
            RETURN TRUE;
        ELSE
            RAISE EXCEPTION USING ERRCODE = '23505';
        END IF;
        
    EXCEPTION
        WHEN SQLSTATE '23502' THEN
            RAISE NOTICE 'El ID de mantenimiento no puede ser nulo o negativo';
            RETURN FALSE;
            
        WHEN SQLSTATE '22001' THEN
            RAISE NOTICE 'La descripción es muy corta. Mínimo 10 caracteres';
            RETURN FALSE;

        WHEN SQLSTATE '22023' THEN
            RAISE NOTICE 'El ID de bus debe tener formato AAA123';
            RETURN FALSE;
            
        WHEN SQLSTATE '22003' THEN
            RAISE NOTICE 'El costo del mantenimiento esta fuera de rango';
            RETURN FALSE;
            
        WHEN SQLSTATE '23505' THEN
            RAISE NOTICE 'El mantenimiento con ID % no existe para actualizar', wid_mantenimiento;
            RETURN FALSE;

        WHEN SQLSTATE '23503' THEN
            RAISE NOTICE 'El bus no existe';
            RETURN FALSE;
            
        WHEN OTHERS THEN
            RAISE NOTICE 'Error no esperado: %', SQLERRM;
            RETURN FALSE;
    END;
$$
LANGUAGE PLPGSQL;

-- Ejemplo de uso
--SELECT fun_update_mantenimiento(7, 'Ruedas desgastadas', '2025-01-15 09:30:00', 250000);
--select * from tab_mantenimiento