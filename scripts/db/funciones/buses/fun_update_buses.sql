CREATE OR REPLACE FUNCTION fun_update_buses(
    wid_bus tab_buses.id_bus%TYPE,
    wid_conductor tab_buses.id_conductor%TYPE,
    wnum_chasis tab_buses.num_chasis%TYPE,
    wmatricula tab_buses.matricula%TYPE,
    wanio_fab tab_buses.anio_fab%TYPE,
    wcapacidad_pasajeros tab_buses.capacidad_pasajeros%TYPE,
    wtipo_bus tab_buses.tipo_bus%TYPE,
    wgps tab_buses.gps%TYPE,
    wind_estado_buses tab_buses.ind_estado_buses%TYPE 
) RETURNS BOOLEAN AS
$$
    DECLARE 
        wreg_bus RECORD;
    BEGIN
        -- Validaciones iniciales
        IF wid_bus IS NULL OR wid_bus <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '23502';
        END IF;
        
        IF wid_conductor IS NULL OR wid_conductor <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '22002';
        END IF;
        
        IF wnum_chasis IS NULL OR LENGTH(wnum_chasis) != 17 THEN
            RAISE EXCEPTION USING ERRCODE = '22003';
        END IF;
        
        IF wmatricula IS NULL OR LENGTH(wmatricula) != 6 THEN
            RAISE EXCEPTION USING ERRCODE = '22004';
        END IF;
        
        IF wtipo_bus NOT IN ('U', 'M', 'A', 'E') THEN
            RAISE EXCEPTION USING ERRCODE = '22005';
        END IF;
        
        IF wind_estado_buses NOT IN ('L', 'F', 'D', 'S', 'T', 'A') THEN
            RAISE EXCEPTION USING ERRCODE = '22006';
        END IF;
        
        -- Verificar si existe el registro
        SELECT id_bus, id_conductor, num_chasis, matricula, anio_fab, capacidad_pasajeros, tipo_bus, gps, ind_estado_buses INTO wreg_bus FROM tab_buses 
                WHERE id_bus = wid_bus
                    AND fec_delete IS NULL;
        
        IF FOUND THEN
            -- Actualizar el registro existente
            UPDATE tab_buses SET
                id_conductor = wid_conductor,
                num_chasis = wnum_chasis,
                matricula = wmatricula,
                anio_fab = wanio_fab,
                capacidad_pasajeros = wcapacidad_pasajeros,
                tipo_bus = wtipo_bus,
                gps = wgps,
                ind_estado_buses = wind_estado_buses
                        WHERE id_bus = wid_bus
                            AND fec_delete IS NULL;
            
            RAISE NOTICE 'Bus con ID % actualizado correctamente', wid_bus;
            RETURN TRUE;
        ELSE
            RAISE EXCEPTION USING ERRCODE = '23505';
        END IF;
        
    EXCEPTION
        WHEN SQLSTATE '23502' THEN
            RAISE NOTICE 'El ID del bus no puede ser nulo o menor/igual a 0';
            RETURN FALSE;
            
        WHEN SQLSTATE '22002' THEN
            RAISE NOTICE 'El ID del conductor no puede ser nulo o menor a 1000000000';
            RETURN FALSE;
            
        WHEN SQLSTATE '22003' THEN
            RAISE NOTICE 'El número de chasis debe tener exactamente 17 caracteres';
            RETURN FALSE;
            
        WHEN SQLSTATE '22004' THEN
            RAISE NOTICE 'La matrícula debe tener exactamente 6 caracteres';
            RETURN FALSE;
            
        WHEN SQLSTATE '22005' THEN
            RAISE NOTICE 'El tipo de bus debe ser U (Urbano), M (Municipal), A (Articulado) o E (Especializado)';
            RETURN FALSE;
            
        WHEN SQLSTATE '22006' THEN
            RAISE NOTICE 'El estado del bus debe ser L, F, D, S, T o A';
            RETURN FALSE;
            
        WHEN SQLSTATE '23505' THEN
            RAISE NOTICE 'El bus con ID % no existe para actualizar', wid_bus;
            RETURN FALSE;
            
        WHEN OTHERS THEN
            RAISE NOTICE 'Error no esperado: %', SQLERRM;
            RETURN FALSE;
    END;
$$
LANGUAGE PLPGSQL;


--SELECT * FROM tab_buses
--SELECT fun_update_buses(1, 1000000020, '1HGBH41JXMN109186', 'XYZ789', 2022, 45, 'U', TRUE, 'A');
