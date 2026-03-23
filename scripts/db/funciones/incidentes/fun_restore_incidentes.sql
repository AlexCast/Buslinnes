CREATE OR REPLACE FUNCTION fun_restore_incidentes(wid_incidente INT)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_incidentes
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_incidente = wid_incidente AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Incidente % restaurado correctamente.', wid_incidente;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el incidente eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
