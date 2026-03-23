CREATE OR REPLACE FUNCTION fun_restore_usuarios_roles(
    wid_usuario tab_usuarios_roles.id_usuario%TYPE,
    wid_rol     tab_usuarios_roles.id_rol%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_usuarios_roles
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_usuario = wid_usuario
      AND id_rol     = wid_rol
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Relación Usuario % – Rol % restaurada correctamente.',
                     wid_usuario, wid_rol;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la relación eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
