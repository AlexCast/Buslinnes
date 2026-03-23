-- Permitir enviar notificación a un usuario O a un rol (el otro queda NULL).
-- Ejecutar este script en la base de datos antes de usar la opción en la app.

-- Permitir NULL en id_usuario e id_rol (envío a usuario O a rol)
ALTER TABLE tab_notificaciones
    ALTER COLUMN id_usuario DROP NOT NULL,
    ALTER COLUMN id_rol DROP NOT NULL;

-- Recrear función de inserción: acepta NULL en uno de los dos
CREATE OR REPLACE FUNCTION fun_insert_notificaciones(
    wid_notificacion tab_notificaciones.id_notificacion%TYPE,
    wid_usuario tab_usuarios.id_usuario%TYPE,
    wid_rol tab_roles.id_rol%TYPE,
    wtitulo_notificacion tab_notificaciones.titulo_notificacion%TYPE,
    wdescr_notificacion tab_notificaciones.descr_notificacion%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_notificacion IS NULL OR wid_notificacion < 1 THEN
        RETURN 'El ID de notificación no es válido';
    END IF;
    -- Debe ser solo usuario O solo rol, no ambos ni ninguno
    IF (wid_usuario IS NOT NULL AND wid_rol IS NOT NULL) THEN
        RETURN 'Elija solo un usuario o solo un rol, no ambos';
    END IF;
    IF (wid_usuario IS NULL AND wid_rol IS NULL) THEN
        RETURN 'Debe elegir un usuario o un rol como destino';
    END IF;
    IF wtitulo_notificacion IS NULL OR TRIM(wtitulo_notificacion) = '' THEN
        RETURN 'El título es obligatorio';
    END IF;
    IF wdescr_notificacion IS NULL OR TRIM(wdescr_notificacion) = '' THEN
        RETURN 'La descripción es obligatoria';
    END IF;

    INSERT INTO tab_notificaciones (id_notificacion, id_usuario, id_rol, titulo_notificacion, descr_notificacion, usr_insert, fec_insert)
    VALUES (wid_notificacion, wid_usuario, wid_rol, wtitulo_notificacion, wdescr_notificacion, CURRENT_USER, CURRENT_TIMESTAMP);

    RETURN 'Inserción exitosa';

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RETURN 'Error: valor obligatorio nulo';
    WHEN SQLSTATE '23505' THEN
        RETURN 'Error: ya existe una notificación con ese ID';
    WHEN SQLSTATE '23503' THEN
        RETURN 'Error: usuario o rol no existe';
    WHEN OTHERS THEN
        RETURN 'Error: ' || SQLERRM;
END;
$$
LANGUAGE plpgsql;

-- Recrear función de actualización: acepta NULL en uno de los dos
CREATE OR REPLACE FUNCTION fun_update_notificaciones(
    wid_notificacion tab_notificaciones.id_notificacion%TYPE,
    wid_usuario tab_usuarios.id_usuario%TYPE,
    wid_rol tab_roles.id_rol%TYPE,
    wtitulo_notificacion tab_notificaciones.titulo_notificacion%TYPE,
    wdescr_notificacion tab_notificaciones.descr_notificacion%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_notificacion IS NULL OR wid_notificacion < 1 THEN
        RETURN 'El ID de notificación no puede ser 0 ni nulo';
    END IF;
    IF (wid_usuario IS NOT NULL AND wid_rol IS NOT NULL) THEN
        RETURN 'Elija solo un usuario o solo un rol, no ambos';
    END IF;
    IF (wid_usuario IS NULL AND wid_rol IS NULL) THEN
        RETURN 'Debe elegir un usuario o un rol como destino';
    END IF;
    IF wtitulo_notificacion IS NULL OR TRIM(wtitulo_notificacion) = '' THEN
        RETURN 'El título de la notificación no puede ser nulo';
    END IF;
    IF wdescr_notificacion IS NULL OR TRIM(wdescr_notificacion) = '' THEN
        RETURN 'La descripción de la notificación no puede ser nula';
    END IF;

    UPDATE tab_notificaciones
    SET id_usuario = wid_usuario,
        id_rol = wid_rol,
        titulo_notificacion = wtitulo_notificacion,
        descr_notificacion = wdescr_notificacion
        WHERE id_notificacion = wid_notificacion
            AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Actualización exitosa';
    ELSE
        RETURN 'Actualización fallida: no existe el registro';
    END IF;

EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RETURN 'Error: usuario o rol no existe';
    WHEN SQLSTATE '23505' THEN
        RETURN 'Error: el registro ya existe';
    WHEN OTHERS THEN
        RETURN 'Error desconocido en la función';
END;
$$
LANGUAGE plpgsql;
