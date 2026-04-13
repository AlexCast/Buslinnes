CREATE OR REPLACE FUNCTION fun_restore_seguro_bus(wid_control INT)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_seguro_bus
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_control = wid_control AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Seguro del bus % restaurado correctamente.', wid_control;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el seguro del bus eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
