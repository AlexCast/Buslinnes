CREATE OR REPLACE FUNCTION fun_update_mantenimiento(
    wid_mantenimiento tab_mantenimiento.id_mantenimiento%TYPE,
    wid_bus tab_mantenimiento.id_bus%TYPE,
    wdescripcion tab_mantenimiento.descripcion%TYPE,
    wfecha_mantenimiento tab_mantenimiento.fecha_mantenimiento%TYPE,
    wcosto_mantenimiento tab_mantenimiento.costo_mantenimiento%TYPE
) RETURNS BOOLEAN AS
$$
    DECLARE 
        wreg_mantenimiento RECORD;
    BEGIN
        -- Validaciones iniciales
        IF wid_mantenimiento IS NULL OR wid_mantenimiento <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '23502';
        END IF;
        
        IF wdescripcion IS NULL OR LENGTH(wdescripcion) < 10 THEN
            RAISE EXCEPTION USING ERRCODE = '22001';
        END IF;
        
        IF wcosto_mantenimiento IS NULL OR wcosto_mantenimiento < 10000 THEN
            RAISE EXCEPTION USING ERRCODE = '22002';
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
                id_bus = wid_bus,
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
            
        WHEN SQLSTATE '22002' THEN
            RAISE NOTICE 'El costo del mantenimiento debe ser al menos de 10,000 pesos';
            RETURN FALSE;
            
        WHEN SQLSTATE '23505' THEN
            RAISE NOTICE 'El mantenimiento con ID % no existe para actualizar', wid_mantenimiento;
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