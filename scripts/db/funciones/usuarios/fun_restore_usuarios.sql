CREATE OR REPLACE FUNCTION fun_restore_usuarios(wid_usuario tab_usuarios.id_usuario%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_usuarios
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_usuario = wid_usuario AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Usuario % restaurado correctamente.', wid_usuario;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el usuario eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;