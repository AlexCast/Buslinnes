CREATE OR REPLACE FUNCTION fun_update_usuarios_roles(
    wid_usuario   tab_usuarios_roles.id_usuario%TYPE,
    wid_rol_old   tab_usuarios_roles.id_rol%TYPE,
    wid_rol_new   tab_usuarios_roles.id_rol%TYPE
)
RETURNS BOOLEAN AS
$$
DECLARE
    wreg tab_usuarios_roles%ROWTYPE;
BEGIN
    IF wid_usuario IS NULL OR wid_usuario <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '23502';
    END IF;

    IF wid_rol_old IS NULL OR wid_rol_old <= 0 OR wid_rol_new IS NULL OR wid_rol_new <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '22002';
    END IF;

        SELECT id_usuario, id_rol INTO wreg
      FROM tab_usuarios_roles
     WHERE id_usuario = wid_usuario
             AND id_rol     = wid_rol_old
             AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE EXCEPTION USING ERRCODE = '23505';
    END IF;

    UPDATE tab_usuarios_roles
       SET id_rol = wid_rol_new
     WHERE id_usuario = wid_usuario
             AND id_rol     = wid_rol_old
             AND fec_delete IS NULL;

    RAISE NOTICE 'Usuario % actualizado de rol % a rol %.', wid_usuario, wid_rol_old, wid_rol_new;
    RETURN TRUE;

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'El ID del usuario no puede ser nulo o <= 0';
        RETURN FALSE;
    WHEN SQLSTATE '22002' THEN
        RAISE NOTICE 'Los IDs de rol no pueden ser nulos o <= 0';
        RETURN FALSE;
    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'La relación usuario-rol original no existe';
        RETURN FALSE;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error no esperado: %', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
