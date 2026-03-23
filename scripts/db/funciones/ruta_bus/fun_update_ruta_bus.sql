
CREATE OR REPLACE FUNCTION fun_update_ruta_bus(
    wid_ruta_bus INT,
    wid_ruta INT,
    wid_bus INT
) RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_ruta_bus RECORD;
BEGIN
    IF wid_ruta_bus IS NULL OR wid_ruta_bus <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '23502';
    END IF;
    
    IF wid_ruta IS NULL OR wid_ruta <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '22001';
    END IF;
    
    IF wid_bus IS NULL OR wid_bus <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '22002';
    END IF;

    SELECT id_ruta_bus, id_ruta, id_bus
    INTO wreg_ruta_bus
    FROM tab_ruta_bus 
        WHERE id_ruta_bus = wid_ruta_bus
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE EXCEPTION USING ERRCODE = '23505';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM tab_ruta_bus
        WHERE id_ruta_bus = wid_ruta_bus
          AND fec_delete IS NOT NULL
    ) THEN
        RAISE EXCEPTION USING ERRCODE = '22005';
    END IF;

    UPDATE tab_ruta_bus SET
        id_ruta = wid_ruta,
        id_bus = wid_bus
        WHERE id_ruta_bus = wid_ruta_bus
            AND fec_delete IS NULL;

    RAISE NOTICE 'Asignación ruta-bus con ID % actualizada correctamente', wid_ruta_bus;
    RETURN TRUE;
EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'El ID de la asignación ruta-bus no puede ser nulo o menor/igual a 0';
        RETURN FALSE;
    WHEN SQLSTATE '22001' THEN
        RAISE NOTICE 'El ID de la ruta no puede ser nulo o menor/igual a 0';
        RETURN FALSE;
    WHEN SQLSTATE '22002' THEN
        RAISE NOTICE 'El ID del bus no puede ser nulo o menor/igual a 0';
        RETURN FALSE;
    WHEN SQLSTATE '22005' THEN
        RAISE NOTICE 'No se puede actualizar una asignación ruta-bus que ha sido eliminada lógicamente';
        RETURN FALSE;
    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'La asignación ruta-bus con ID % no existe', wid_ruta_bus;
        RETURN FALSE;
    WHEN SQLSTATE '23503' THEN
        RAISE NOTICE 'Error de integridad referencial. Verifique que existan la ruta y el bus';
        RETURN FALSE;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error no esperado: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
