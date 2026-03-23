CREATE OR REPLACE FUNCTION fun_softdelete_ruta_waypoints(
    wid_waypoint tab_ruta_waypoints.id_waypoint%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_ruta_waypoints
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
        WHERE id_waypoint = wid_waypoint
      AND fec_delete IS NULL;

    IF FOUND THEN
                RAISE NOTICE 'Waypoint % eliminado lógicamente.', wid_waypoint;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el waypoint o ya está eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
