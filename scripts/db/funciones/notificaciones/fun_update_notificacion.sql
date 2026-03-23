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

    IF wtitulo_notificacion IS NULL THEN
        RETURN 'El título de la notificación no puede ser nulo';
    END IF;

    IF wdescr_notificacion IS NULL THEN
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
        RAISE NOTICE 'Notificación actualizada: %, %', wid_usuario, wtitulo_notificacion;
        RAISE NOTICE 'ID: %, Nombre: %', wid_rol, wdescr_notificacion;
        RAISE NOTICE 'ID: %, Nombre: %', wid_usuario, wtitulo_notificacion;
        RAISE NOTICE 'ID: %, Nombre: %', wid_rol, wdescr_notificacion;
        RETURN 'Actualización exitosa';
    ELSE
        RAISE NOTICE 'No se encontró la notificación para actualizar';
        RETURN 'Actualización fallida: no existe el registro';
    END IF;

EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RETURN 'Error: la notificación no existe o violación de llave foránea';
    WHEN SQLSTATE '23505' THEN
        RETURN 'Error: el registro ya existe';
    WHEN OTHERS THEN
        RETURN 'Error desconocido en la función';
END;
$$ 
LANGUAGE plpgsql;