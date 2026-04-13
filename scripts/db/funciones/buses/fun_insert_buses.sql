CREATE OR REPLACE FUNCTION fun_insert_buses(
    wid_bus tab_buses.id_bus%TYPE,
    wid_usuario tab_buses.id_usuario%TYPE,
    wanio_fab tab_buses.anio_fab%TYPE,
    wcapacidad_pasajeros tab_buses.capacidad_pasajeros%TYPE,
    wtipo_bus tab_buses.tipo_bus%TYPE DEFAULT 'U',
    wgps tab_buses.gps%TYPE DEFAULT TRUE,
    wind_estado_buses tab_buses.ind_estado_buses%TYPE DEFAULT 'L'
) RETURNS BOOLEAN AS
$$
    BEGIN
        IF wid_bus IS NULL OR btrim(wid_bus::text) = '' THEN
            RAISE NOTICE 'El ID del bus no puede ser nulo o vacio';
            RETURN FALSE;
        END IF;

        -- Validar que no haya nulos importantes        
        IF wid_usuario IS NULL OR wid_usuario <= 0 THEN
            RAISE NOTICE 'El ID del conductor no puede ser nulo o menor a 1';
            RETURN FALSE;
        END IF;
        
        -- Validar tipo de bus
        IF wtipo_bus NOT IN ('U', 'M', 'A', 'E') THEN
            RAISE NOTICE 'El tipo de bus debe ser U (Urbano), M (Municipal), A (Articulado) o E (Especializado)';
            RETURN FALSE;
        END IF;
        
        -- Validar estado del bus
        IF wind_estado_buses NOT IN ('L', 'F', 'D', 'S', 'T', 'A') THEN
            RAISE NOTICE 'El estado del bus no es válido';
            RETURN FALSE;
        END IF;
        
        -- Insertar el nuevo registro
        INSERT INTO tab_buses (id_bus, id_usuario, anio_fab, capacidad_pasajeros, tipo_bus, gps, ind_estado_buses)
        VALUES (wid_bus, wid_usuario, wanio_fab, wcapacidad_pasajeros, wtipo_bus, wgps, wind_estado_buses);
        
        RAISE NOTICE 'Bus insertado correctamente';
        RETURN TRUE;
    EXCEPTION
        WHEN SQLSTATE '23502' THEN
            RAISE NOTICE 'Campos obligatorios no pueden ser NULL';
            RETURN FALSE;
        WHEN SQLSTATE '23514' THEN
            RAISE NOTICE 'Violación de restricción CHECK. Verifique los valores ingresados';
            RETURN FALSE;
        WHEN SQLSTATE '23505' THEN
            RAISE NOTICE 'El ID del bus ya existe';
            RETURN FALSE;
        WHEN OTHERS THEN
            RAISE NOTICE 'Error: %', SQLERRM;
            RETURN FALSE;
    END;
$$
LANGUAGE PLPGSQL;
