
CREATE OR REPLACE FUNCTION fun_listar_ruta_bus(wid_ruta_bus tab_ruta_bus.id_ruta_bus%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_ruta_bus tab_ruta_bus%ROWTYPE;
BEGIN
    IF wid_ruta_bus <= 0 THEN
        RAISE NOTICE 'El ID de la asignación ruta-bus no puede ser menor o igual a 0.';
        RETURN FALSE;
    END IF;

    SELECT id_ruta_bus, id_ruta, id_bus
    INTO wreg_ruta_bus
    FROM tab_ruta_bus
        WHERE id_ruta_bus = wid_ruta_bus
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'La asignación ruta-bus con ID % no existe.', wid_ruta_bus;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID Ruta-Bus: %, ID Ruta: %, ID Bus: %', 
                  wreg_ruta_bus.id_ruta_bus, 
                  wreg_ruta_bus.id_ruta,
                  wreg_ruta_bus.id_bus;
    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
