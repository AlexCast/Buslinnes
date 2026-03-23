CREATE OR REPLACE FUNCTION iniciar_sesion_usuario(nom_usuario text, cont_usuario text) RETURNS boolean AS
$$
DECLARE
    existe boolean;
BEGIN
    -- Verificar si existe un usuario con ese username y contraseña
    SELECT TRUE INTO existe
    FROM tab_usuarios
        WHERE nombre = nom_usuario
            AND contrasena = cont_usuario
            AND fec_delete IS NULL
    LIMIT 1;

    -- Si se encontró, devolver TRUE, si no, FALSE
    IF existe THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;

