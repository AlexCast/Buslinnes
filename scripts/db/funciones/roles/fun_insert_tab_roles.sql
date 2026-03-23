CREATE OR REPLACE FUNCTION fun_insert_tab_roles(
    wid_rol       INT,
    wnombre_rol   VARCHAR,
    wusr_insert   VARCHAR
)
RETURNS BOOLEAN AS
$$
DECLARE
    v_existe INTEGER;
BEGIN
    IF wid_rol IS NULL OR wid_rol <= 0 THEN
        RAISE NOTICE 'El ID del rol no puede ser nulo o menor o igual a 0.';
        RETURN FALSE;
    END IF;

    -- Validar que el nombre no venga vacío
    IF trim(wnombre_rol) = '' THEN
        RAISE NOTICE 'El nombre del rol no puede estar vacío.';
        RETURN FALSE;
    END IF;

    -- Verificar si ya existe un rol con el mismo nombre (ignora mayúsculas/minúsculas)
    SELECT COUNT(*) INTO v_existe
    FROM tab_roles
    WHERE LOWER(nombre_rol) = LOWER(wnombre_rol)
      AND fec_delete IS NULL;  -- ignora los que están lógicamente borrados

    IF v_existe > 0 THEN
        RAISE NOTICE 'El rol "%" ya existe. Nada que insertar.', wnombre_rol;
        RETURN FALSE;
    END IF;

    -- Insertar el nuevo rol
    INSERT INTO tab_roles (
        id_rol,
        nombre_rol,
        usr_insert,
        fec_insert
    ) VALUES (
        wid_rol,
        wnombre_rol,
        wusr_insert,
        CURRENT_TIMESTAMP
    );

    RAISE NOTICE 'Rol "%" insertado correctamente.', wnombre_rol;
    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

--select fun_insert_tab_roles(1, 'admin', 'system');
--select fun_insert_tab_roles(2, 'conductor', 'system');
--select fun_insert_tab_roles(3, 'pasajero', 'system');