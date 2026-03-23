CREATE OR REPLACE FUNCTION fun_listar_roles(wid_rol tab_roles.id_rol%TYPE)
RETURNS BOOLEAN AS
$$
DECLARE
    wreg_rol tab_roles%ROWTYPE;  -- Para manejar toda la fila de la tabla
BEGIN
    -- Validación básica
    IF wid_rol <= 0 THEN
        RAISE NOTICE 'El ID del rol no puede ser menor o igual a 0.';
        RETURN FALSE;
    END IF;

    -- Buscar el rol
    SELECT id_rol, nombre_rol INTO wreg_rol
    FROM tab_roles
        WHERE id_rol = wid_rol
            AND fec_delete IS NULL;

    -- Verificar si se encontró
    IF NOT FOUND THEN
        RAISE NOTICE 'El rol con ID % no existe.', wid_rol;
        RETURN FALSE;
    END IF;

    -- Mostrar datos del rol
    RAISE NOTICE 'ID Rol: %, Nombre Rol: %',
                 wreg_rol.id_rol,
                 wreg_rol.nombre_rol;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE plpgsql;
