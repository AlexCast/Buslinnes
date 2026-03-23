CREATE OR REPLACE FUNCTION fun_listar_usuarios_roles(wid_usuario tab_usuarios_roles.id_usuario%TYPE)
RETURNS BOOLEAN AS
$$
DECLARE
    wreg_rel tab_usuarios_roles%ROWTYPE; -- Manejar la fila completa
    wcursor CURSOR FOR
        SELECT id_usuario, id_rol
        FROM tab_usuarios_roles
                WHERE id_usuario = wid_usuario
                    AND fec_delete IS NULL;
    wcount INT := 0;
BEGIN
    -- Validación básica
    IF wid_usuario <= 0 THEN
        RAISE NOTICE 'El ID del usuario no puede ser menor o igual a 0.';
        RETURN FALSE;
    END IF;

    -- Abrir cursor para recorrer todos los roles del usuario
    OPEN wcursor;
    LOOP
        FETCH wcursor INTO wreg_rel;
        EXIT WHEN NOT FOUND;
        wcount := wcount + 1;

        RAISE NOTICE 'Usuario ID: %, Rol ID: %',
                     wreg_rel.id_usuario,
                     wreg_rel.id_rol;
    END LOOP;
    CLOSE wcursor;

    -- Si no se encontró ningún registro
    IF wcount = 0 THEN
        RAISE NOTICE 'El usuario con ID % no tiene roles asignados o no existe.', wid_usuario;
        RETURN FALSE;
    END IF;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE plpgsql;
