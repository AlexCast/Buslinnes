CREATE OR REPLACE FUNCTION fun_restore_cambio_bus(wid_cambio_bus tab_cambio_bus.id_cambio_bus%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_cambio_bus
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_cambio_bus = wid_cambio_bus AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'cambio del Bus % restaurado correctamente.', wid_cambio_bus;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el cambio del bus eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;