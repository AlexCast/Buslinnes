CREATE OR REPLACE FUNCTION fun_listar_usuarios(wid_usuario tab_usuarios.id_usuario%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_usuario tab_usuarios%ROWTYPE; -- Variable para almacenar la fila del usuario
BEGIN
    -- Validar ID del usuario
    IF wid_usuario <= 0 THEN
        RAISE NOTICE 'El ID del usuario no puede ser menor o igual a 0.';
        RETURN FALSE;
    END IF;
    
    -- Buscar el usuario por ID
    SELECT id_usuario, nombre, correo, contrasena
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
    ID Usuario: %,
    Email: %,
    Nombre: %
    ', 
    wreg_usuario.id_usuario, 
    wreg_usuario.correo,
    wreg_usuario.nombre;
    
    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error al listar usuario: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;