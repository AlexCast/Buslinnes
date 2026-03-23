CREATE OR REPLACE FUNCTION fun_update_roles(
    wid_rol      tab_roles.id_rol%TYPE,
    wnombre_rol  VARCHAR
)
RETURNS BOOLEAN AS
$$
DECLARE
    wreg tab_roles%ROWTYPE;
BEGIN
    -- Validaciones
    IF wid_rol IS NULL OR wid_rol <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '23502';
    END IF;

    IF wnombre_rol IS NULL OR LENGTH(TRIM(wnombre_rol)) < 3 THEN
        RAISE EXCEPTION USING ERRCODE = '22001';
    END IF;

    -- Verificar existencia
    SELECT id_rol, nombre_rol INTO wreg FROM tab_roles WHERE id_rol = wid_rol AND fec_delete IS NULL;
    IF NOT FOUND THEN
        RAISE EXCEPTION USING ERRCODE = '23505';
    END IF;

    -- Actualizar
    UPDATE tab_roles
       SET nombre_rol = wnombre_rol
         WHERE id_rol = wid_rol
             AND fec_delete IS NULL;

    RAISE NOTICE 'Rol % actualizado correctamente.', wid_rol;
    RETURN TRUE;

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'El ID del rol no puede ser nulo o menor o igual a 0';
        RETURN FALSE;
    WHEN SQLSTATE '22001' THEN
        RAISE NOTICE 'El nombre del rol debe tener al menos 3 caracteres';
        RETURN FALSE;
    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'No existe un rol con ID %', wid_rol;
        RETURN FALSE;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error no esperado: %', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
