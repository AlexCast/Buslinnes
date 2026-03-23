CREATE OR REPLACE FUNCTION fun_softdelete_mantenimiento(
    wid_mantenimiento tab_mantenimiento.id_mantenimiento%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_mantenimiento
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_mantenimiento = wid_mantenimiento
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Mantenimiento % eliminado lógicamente.', wid_mantenimiento;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el mantenimiento o ya está eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;

--select * from tab_mantenimiento
--SELECT fun_softdelete_mantenimiento(7)