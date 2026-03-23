-- FUNCTION: public.fun_insert_usuario(character varying, character varying, character varying, character varying)

-- DROP FUNCTION IF EXISTS public.fun_insert_usuario(character varying, character varying, character varying, character varying);

CREATE OR REPLACE FUNCTION public.fun_insert_usuario(
    wcontrasena character varying,
    wcorreo character varying,
    wnombre character varying)
    RETURNS boolean
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
AS $BODY$
BEGIN
    -- Validar campos obligatorios
    IF wcontrasena IS NULL OR wcorreo IS NULL OR wnombre IS NULL THEN
        RAISE EXCEPTION USING errcode = 23502; -- Código para NOT NULL violation
    END IF;

    -- Validar formato de correo (básico)
    IF wcorreo !~ '^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+[.][A-Za-z]+$' THEN
        RAISE NOTICE 'El formato del correo no es válido';
        RETURN FALSE;
    END IF;

    -- Validar fortaleza de contraseña (ejemplo mínimo: 8 caracteres)
    IF length(wcontrasena) < 8 THEN
        RAISE NOTICE 'La contraseña debe tener al menos 8 caracteres';
        RETURN FALSE;
    END IF;

    -- Insertar el nuevo usuario (fec_insert se autocompleta con NOW())
    INSERT INTO tab_usuarios (
        contrasena, correo, nombre
    ) VALUES (
        wcontrasena, wcorreo, wnombre
    );

    RAISE NOTICE 'Usuario insertado correctamente';
    RETURN TRUE;

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'Error: Campos obligatorios no pueden ser NULL';
        RETURN FALSE;
    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'Error: El correo electrónico ya está registrado en el sistema';
        RETURN FALSE;
    WHEN SQLSTATE '23514' THEN
        RAISE NOTICE 'Error: Violación de restricción CHECK';
        RETURN FALSE;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado: %', SQLERRM;
        RETURN FALSE;
END;
$BODY$;