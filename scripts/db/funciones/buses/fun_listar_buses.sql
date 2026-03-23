CREATE OR REPLACE FUNCTION fun_listar_buses(wid_bus tab_buses.id_bus%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_bus tab_buses%ROWTYPE; -- Usamos %ROWTYPE para manejar toda la fila
BEGIN
    IF wid_bus <= 0 THEN
        RAISE NOTICE 'El ID del bus no puede ser menor o igual a 0.';
        RETURN FALSE;
    END IF;
    
    -- Obtener el bus
    SELECT id_bus, id_conductor, num_chasis, matricula, anio_fab, capacidad_pasajeros, tipo_bus, gps, ind_estado_buses
    INTO wreg_bus
    FROM tab_buses
        WHERE id_bus = wid_bus
            AND fec_delete IS NULL;
    
    -- Verificar si se encontró un registro
    IF NOT FOUND THEN
        RAISE NOTICE 'El bus con ID % no existe.', wid_bus;
        RETURN FALSE;
    END IF;
    
    RAISE NOTICE 'ID Bus: %, Conductor: %, Chasis: %, Matricula: %, Año: %, Capacidad: %, Tipo: %, GPS: %, Estado: %', 
                  wreg_bus.id_bus, 
                  wreg_bus.id_conductor,
                  wreg_bus.num_chasis,
                  wreg_bus.matricula,
                  wreg_bus.anio_fab,
                  wreg_bus.capacidad_pasajeros,
                  wreg_bus.tipo_bus,
                  wreg_bus.gps,
                  wreg_bus.ind_estado_buses;
    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;

