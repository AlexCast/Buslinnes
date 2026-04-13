CREATE OR REPLACE FUNCTION fun_insert_usuario(
    wid_usuario tab_usuarios.id_usuario%TYPE,
    wnom_usuario tab_usuarios.nom_usuario%TYPE,
    wemail_usuario tab_usuarios.email_usuario%TYPE,
    wcontrasena tab_usuarios.contrasena%TYPE,
    wtipo_doc tab_usuarios.tipo_doc%TYPE DEFAULT 'CD'
) RETURNS BOOLEAN AS
$$
BEGIN
    -- Validar que el ID del usuario no sea nulo o menor a 1
    IF wid_usuario IS NULL OR wid_usuario <= 0 THEN
        RAISE NOTICE 'El ID del usuario no puede ser nulo o menor a 1';
        RETURN FALSE;
    END IF;
    
    -- Validar que no haya nulos importantes
    IF wnom_usuario IS NULL OR wemail_usuario IS NULL OR wcontrasena IS NULL THEN
        RAISE NOTICE 'El nombre, correo y contraseña son campos obligatorios';
        RETURN FALSE;
    END IF;
    
    -- Validar tipo de documento
    IF wtipo_doc NOT IN ('CD', 'TI', 'CE') THEN
        RAISE NOTICE 'El tipo de documento debe ser CD (Cédula de Ciudadanía), TI (Tarjeta de Identidad) o CE (Cédula Extranjera)';
        RETURN FALSE;
    END IF;
    
    -- Validar formato de correo
    IF wemail_usuario !~ '^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+[.][A-Za-z]+$' THEN
        RAISE NOTICE 'El formato del correo no es válido';
        RETURN FALSE;
    END IF;
    
    -- Validar fortaleza de contraseña (mínimo 8 caracteres)
    IF length(wcontrasena) < 8 THEN
        RAISE NOTICE 'La contraseña debe tener al menos 8 caracteres';
        RETURN FALSE;
    END IF;
    
    -- Validar longitud mínima del nombre
    IF length(wnom_usuario) < 3 THEN
        RAISE NOTICE 'El nombre debe tener al menos 3 caracteres';
        RETURN FALSE;
    END IF;
    
    -- Insertar el nuevo usuario
    INSERT INTO tab_usuarios (
        id_usuario, tipo_doc, nom_usuario, email_usuario, contrasena
    ) VALUES (
        wid_usuario, wtipo_doc, wnom_usuario, wemail_usuario, wcontrasena
    );
    
    RAISE NOTICE 'Usuario insertado correctamente';
    RETURN TRUE;

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'Campos obligatorios no pueden ser NULL';
        RETURN FALSE;
    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'Error: El correo electrónico ya está registrado en el sistema';
        RETURN FALSE;
    WHEN SQLSTATE '23514' THEN
        RAISE NOTICE 'Violación de restricción CHECK. Verifique los valores ingresados';
        RETURN FALSE;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;