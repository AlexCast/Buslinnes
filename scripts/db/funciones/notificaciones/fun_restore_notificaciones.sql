CREATE OR REPLACE FUNCTION fun_restore_notificaciones(wid_notificacion tab_notificaciones.id_notificacion%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_notificaciones
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_notificacion = wid_notificacion AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Notificación de conductor % restaurada correctamente.', wid_notificacion;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la notificación eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;