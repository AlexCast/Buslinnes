CREATE OR REPLACE FUNCTION fun_softdelete_roles(
    wid_rol tab_roles.id_rol%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_roles
    SET
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_rol = wid_rol
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Rol % eliminado lógicamente.', wid_rol;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el rol o ya estaba eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
