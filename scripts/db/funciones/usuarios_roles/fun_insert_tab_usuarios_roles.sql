CREATE OR REPLACE FUNCTION fun_insert_tab_usuarios_roles(
    wid_usuario   INT,
    wid_rol       INT,
    wusr_insert   VARCHAR
)
RETURNS BOOLEAN AS
$$
DECLARE
    v_existe INTEGER;
    v_user_ok INTEGER;
    v_rol_ok  INTEGER;
BEGIN
    --Verificar que el usuario exista y no esté borrado
    SELECT COUNT(*) INTO v_user_ok
    FROM tab_usuarios
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL;

    IF v_user_ok = 0 THEN
        RAISE NOTICE 'El usuario con ID % no existe o está eliminado.', wid_usuario;
        RETURN FALSE;
    END IF;

    -- Verificar que el rol exista y no esté borrado
    SELECT COUNT(*) INTO v_rol_ok
    FROM tab_roles
    WHERE id_rol = wid_rol
      AND fec_delete IS NULL;

    IF v_rol_ok = 0 THEN
        RAISE NOTICE 'El rol con ID % no existe o está eliminado.', wid_rol;
        RETURN FALSE;
    END IF;

    -- Verificar si la relación usuario-rol ya existe
    SELECT COUNT(*) INTO v_existe
    FROM tab_usuarios_roles
    WHERE id_usuario = wid_usuario
      AND id_rol = wid_rol
      AND fec_delete IS NULL;

    IF v_existe > 0 THEN
        RAISE NOTICE 'El usuario % ya tiene asignado el rol %.', wid_usuario, wid_rol;
        RETURN FALSE;
    END IF;

    -- Insertar la relación usuario-rol
    INSERT INTO tab_usuarios_roles (
        id_usuario,
        id_rol,
        usr_insert,
        fec_insert
    ) VALUES (
        wid_usuario,
        wid_rol,
        wusr_insert,
        CURRENT_TIMESTAMP
    );

    RAISE NOTICE 'Se asignó el rol % al usuario % correctamente.', wid_rol, wid_usuario;
    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

--select fun_insert_tab_usuarios_roles('3','3','conductor');