CREATE OR REPLACE FUNCTION fun_restore_roles(wid_rol tab_roles.id_rol%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_roles
    SET fec_delete = NULL,usr_delete = NULL
    WHERE id_rol = wid_rol AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Rol % restaurado correctamente.', wid_rol;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el rol eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
