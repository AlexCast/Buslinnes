CREATE OR REPLACE FUNCTION fun_softdelete_incidentes(wid_incidente INT)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_incidentes
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_incidente = wid_incidente AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Incidente % eliminado lógicamente.', wid_incidente;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el incidente o ya está eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
