CREATE OR REPLACE FUNCTION fun_update_usuarios(
    wid_usuario tab_usuarios.id_usuario%TYPE,
    wtipo_doc tab_usuarios.tipo_doc%TYPE,
    wnom_usuario tab_usuarios.nom_usuario%TYPE,
    wemail_usuario tab_usuarios.email_usuario%TYPE,
    wcontrasena tab_usuarios.contrasena%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_usuario RECORD;
BEGIN
    -- Validaciones iniciales
    IF wid_usuario IS NULL OR wid_usuario <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '23502';
    END IF;
    
    IF wtipo_doc NOT IN ('CD', 'TI', 'CE') THEN
        RAISE EXCEPTION USING ERRCODE = '22001';
    END IF;
    
    IF wnom_usuario IS NULL OR LENGTH(wnom_usuario) < 3 THEN
        RAISE EXCEPTION USING ERRCODE = '22002';
    END IF;
    
    IF wemail_usuario IS NULL OR wemail_usuario !~ '^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+[.][A-Za-z]+$' THEN
        RAISE EXCEPTION USING ERRCODE = '22003';
    END IF;
    
    IF wcontrasena IS NULL OR LENGTH(wcontrasena) < 8 THEN
        RAISE EXCEPTION USING ERRCODE = '22004';
    END IF;
    
    IF wusr_update IS NULL THEN
        RAISE EXCEPTION USING ERRCODE = '22005';
    END IF;
    
    -- Verificar si existe el registro
    SELECT id_usuario, tipo_doc, nom_usuario, email_usuario, contrasena INTO wreg_usuario
    FROM tab_usuarios
    WHERE id_usuario = wid_usuario AND fec_delete IS NULL;
    
    IF FOUND THEN
        -- Actualizar el registro existente
        UPDATE tab_usuarios SET
            tipo_doc = wtipo_doc,
            nom_usuario = wnom_usuario,
            email_usuario = wemail_usuario,
            contrasena = wcontrasena,
            fec_update = NOW()
        WHERE id_usuario = wid_usuario
            AND fec_delete IS NULL;
        
        RAISE NOTICE 'Usuario con ID % actualizado correctamente', wid_usuario;
        RETURN TRUE;
    ELSE
        RAISE EXCEPTION USING ERRCODE = '23505';
    END IF;
    
EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'El ID del usuario no puede ser nulo o menor/igual a 0';
        RETURN FALSE;
        
    WHEN SQLSTATE '22001' THEN
        RAISE NOTICE 'El tipo de documento debe ser CD (Cédula de Ciudadanía), TI (Tarjeta de Identidad) o CE (Cédula Extranjera)';
        RETURN FALSE;
        
    WHEN SQLSTATE '22002' THEN
        RAISE NOTICE 'El nombre no puede ser nulo y debe tener al menos 3 caracteres';
        RETURN FALSE;
        
    WHEN SQLSTATE '22003' THEN
        RAISE NOTICE 'El email no puede ser nulo y debe tener un formato válido';
        RETURN FALSE;
        
    WHEN SQLSTATE '22004' THEN
        RAISE NOTICE 'La contraseña no puede ser nula y debe tener al menos 8 caracteres';
        RETURN FALSE;
         
    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'El usuario con ID % no existe o ha sido eliminado', wid_usuario;
        RETURN FALSE;
        
    WHEN OTHERS THEN
        RAISE NOTICE 'Error no esperado: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;