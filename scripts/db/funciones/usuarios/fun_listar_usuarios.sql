CREATE OR REPLACE FUNCTION fun_listar_usuarios(wid_usuario tab_usuarios.id_usuario%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_usuario tab_usuarios%ROWTYPE; -- Variable para almacenar la fila del usuario
BEGIN
    -- Si se proporciona ID, validar que sea válido
    IF wid_usuario IS NOT NULL AND wid_usuario <= 0 THEN
        RAISE NOTICE 'El ID del usuario no puede ser menor o igual a 0.';
        RETURN FALSE;
    END IF;
    
    -- Buscar el usuario por ID
    SELECT tipo_doc, id_usuario, nom_usuario, email_usuario, contrasena
    INTO wreg_usuario
    FROM tab_usuarios
        WHERE id_usuario = wid_usuario
            AND fec_delete IS NULL;
    
    -- Verificar si existe
    IF NOT FOUND THEN
        RAISE NOTICE 'El usuario con ID % no existe.', wid_usuario;
        RETURN FALSE;
    END IF;
    
    -- Mostrar los datos del usuario
    RAISE NOTICE '
    Tipo Documento: %,
    ID Usuario: %,
    Email: %,
    Nombre: %
    ', 
    wreg_usuario.tipo_doc,
    wreg_usuario.id_usuario, 
    wreg_usuario.email_usuario,
    wreg_usuario.nom_usuario;
    
    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error al listar usuario: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;