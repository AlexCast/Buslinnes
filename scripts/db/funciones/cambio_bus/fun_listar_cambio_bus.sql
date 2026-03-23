CREATE OR REPLACE FUNCTION fun_listar_cambio_bus(wid_cambio_bus tab_cambio_bus.id_cambio_bus%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_cambio_bus tab_cambio_bus%ROWTYPE; -- Usamos %ROWTYPE para manejar toda la fila
BEGIN
    IF wid_cambio_bus <= 0 THEN
        RAISE NOTICE 'El ID del cambio del bus no puede ser menor o igual a 0.';
        RETURN FALSE;
    END IF;
    
    -- Obtener el cambio de bus
    SELECT id_cambio_bus, id_incidente, id_bus, ubicacion_cambio
    INTO wreg_cambio_bus
    FROM tab_cambio_bus
        WHERE id_cambio_bus = wid_cambio_bus
            AND fec_delete IS NULL;
    
    -- Verificar si se encontró un registro
    IF NOT FOUND THEN
        RAISE NOTICE 'El cambio del bus con ID % no existe.', wid_cambio_bus;
        RETURN FALSE;
    END IF;
    
    RAISE NOTICE 'ID cambio Bus: %, incidente: %, bus: %, ubicacion_cambio: %',
                  wreg_cambio_bus.id_cambio_bus, 
                  wreg_cambio_bus.id_incidente,
                  wreg_cambio_bus.id_bus,
                  wreg_cambio_bus.ubicacion_cambio;
    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;

