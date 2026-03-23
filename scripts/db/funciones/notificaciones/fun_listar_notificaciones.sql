CREATE OR REPLACE FUNCTION fun_listar_notificaciones(wid_notificacion tab_notificaciones.id_notificacion%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_notificacion tab_notificaciones%ROWTYPE;
BEGIN
    IF wid_notificacion < 1 THEN
        RAISE NOTICE 'El ID de la notificacion no puede ser 0.';
        RETURN FALSE;
    END IF;


    SELECT id_notificacion, id_usuario, id_rol, titulo_notificacion, descr_notificacion
    INTO wreg_notificacion
    FROM tab_notificaciones
        WHERE id_notificacion = wid_notificacion
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'la notificacion con ID % no existe.', wid_notificacion;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Nombre: %', wreg_notificacion.id_notificacion, wreg_notificacion.titulo_notificacion;
    RAISE NOTICE 'ID: %, Nombre: %', wreg_notificacion.id_usuario, wreg_notificacion.id_rol;
    RAISE NOTICE 'ID: %, Nombre: %', wreg_notificacion.titulo_notificacion, wreg_notificacion.descr_notificacion;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;