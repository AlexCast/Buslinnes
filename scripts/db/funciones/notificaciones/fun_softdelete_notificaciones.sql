CREATE OR REPLACE FUNCTION fun_softdelete_notificaciones(
    wid_notificacion tab_notificaciones.id_notificacion%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_notificaciones
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_notificacion = wid_notificacion
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Notificación con ID % eliminada lógicamente.', wid_notificacion;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la notificación o ya está eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
