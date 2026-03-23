CREATE OR REPLACE FUNCTION fun_restore_ruta_bus(wid_ruta_bus tab_ruta_bus.id_ruta_bus%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_ruta_bus
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_ruta_bus = wid_ruta_bus AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Ruta-bus % restaurada correctamente.', wid_ruta_bus;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la ruta-bus eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;