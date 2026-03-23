CREATE OR REPLACE FUNCTION fun_restore_buses(wid_bus tab_buses.id_bus%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_buses
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_bus = wid_bus AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Bus % restaurado correctamente.', wid_bus;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el bus eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;