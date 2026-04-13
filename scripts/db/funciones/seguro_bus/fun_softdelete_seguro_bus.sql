CREATE OR REPLACE FUNCTION fun_softdelete_seguro_bus(wid_control INT)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_seguro_bus
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_control = wid_control AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Seguro del bus % eliminado lógicamente.', wid_control;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el seguro del bus o ya está eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
