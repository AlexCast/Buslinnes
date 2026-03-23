CREATE OR REPLACE FUNCTION fun_restore_mantenimiento(wid_mantenimiento tab_mantenimiento.id_mantenimiento%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_mantenimiento
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_mantenimiento = wid_mantenimiento AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Mantenimiento % restaurado correctamente.', wid_mantenimiento;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el mantenimiento eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;