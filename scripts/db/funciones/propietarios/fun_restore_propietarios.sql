CREATE OR REPLACE FUNCTION fun_restore_propietarios(wid_propietario tab_propietarios.id_propietario%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_propietarios
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_propietario = wid_propietario AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Propietario % restaurado correctamente.', wid_propietario;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el propietario eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;