CREATE OR REPLACE FUNCTION fun_softdelete_usuarios_roles(
    wid_usuario tab_usuarios_roles.id_usuario%TYPE,
    wid_rol     tab_usuarios_roles.id_rol%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_usuarios_roles
    SET
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_usuario = wid_usuario
      AND id_rol     = wid_rol
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Relación Usuario % – Rol % eliminada lógicamente.',
                     wid_usuario, wid_rol;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la relación o ya estaba eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
