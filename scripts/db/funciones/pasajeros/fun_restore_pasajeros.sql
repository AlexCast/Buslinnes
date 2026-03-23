CREATE OR REPLACE FUNCTION fun_restore_pasajeros(wid_usuario tab_pasajeros.id_usuario%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_pasajeros
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_usuario = wid_usuario AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Pasajero % restaurado correctamente.', wid_usuario;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el pasajero eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;