CREATE OR REPLACE FUNCTION fn_user_change_password(
    p_user_id INT,
    p_current_password VARCHAR,
    p_new_password VARCHAR,
    p_usr_update VARCHAR
)
RETURNS TABLE(
    success BOOLEAN,
    message VARCHAR
) AS $$
DECLARE
    v_current_hash TEXT;
    v_user_exists INT;
BEGIN
    -- Obtener hash actual
    SELECT contrasena INTO v_current_hash
    FROM tab_usuarios
    WHERE id_usuario = p_user_id AND fec_delete IS NULL;
    
    IF v_current_hash IS NULL THEN
        RETURN QUERY SELECT false, 'Usuario no encontrado o eliminado';
        RETURN;
    END IF;
    
    -- Verificar contraseña actual
    IF NOT crypt(p_current_password, v_current_hash) = v_current_hash THEN
        RETURN QUERY SELECT false, 'Contraseña actual incorrecta';
        RETURN;
    END IF;
    
    -- Actualizar contraseña
    UPDATE tab_usuarios SET
                contrasena = crypt(p_new_password, gen_salt('bf')),
        usr_update = p_usr_update,
        fec_update = NOW()
        WHERE id_usuario = p_user_id
            AND fec_delete IS NULL;
    
    RETURN QUERY SELECT true, 'Contraseña actualizada exitosamente';
END;
$$ LANGUAGE plpgsql;