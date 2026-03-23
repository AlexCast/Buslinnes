CREATE OR REPLACE FUNCTION fun_update_usuarios(
    wid_usuario INT,
    wcontrasena VARCHAR,
    wcorreo VARCHAR,
    wnombre VARCHAR,
    wusr_update VARCHAR
) RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_usuario RECORD;
BEGIN
    IF wid_usuario IS NULL OR wid_usuario <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '23502';
    END IF;
    
    IF wcontrasena IS NULL OR LENGTH(wcontrasena) < 8 THEN
        RAISE EXCEPTION USING ERRCODE = '22002';
    END IF;
    
    IF wcorreo IS NULL OR wcorreo !~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        RAISE EXCEPTION USING ERRCODE = '22003';
    END IF;
    
    IF wnombre IS NULL OR LENGTH(wnombre) < 2 THEN
        RAISE EXCEPTION USING ERRCODE = '22004';
    END IF;
    
    IF wusr_update IS NULL THEN
        RAISE EXCEPTION USING ERRCODE = '22005';
    END IF;
    
    
    SELECT id_usuario, nombre, correo, contrasena
    INTO wreg_usuario
    FROM tab_usuarios
    WHERE id_usuario = wid_usuario AND fec_delete IS NULL;
    
    IF FOUND THEN
        UPDATE tab_usuarios SET
            contrasena = wcontrasena,
            correo = wcorreo,
            nombre = wnombre,
            usr_update = wusr_update,
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
        
    WHEN SQLSTATE '22002' THEN
        RAISE NOTICE 'La contraseña no puede ser nula y debe tener al menos 8 caracteres';
        RETURN FALSE;
        
    WHEN SQLSTATE '22003' THEN
        RAISE NOTICE 'El email no puede ser nulo y debe tener un formato válido';
        RETURN FALSE;
        
    WHEN SQLSTATE '22004' THEN
        RAISE NOTICE 'El nombre no puede ser nulo y debe tener al menos 2 caracteres';
        RETURN FALSE;
        
    WHEN SQLSTATE '22005' THEN
        RAISE NOTICE 'El usuario que realiza la actualización no puede ser nulo';
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