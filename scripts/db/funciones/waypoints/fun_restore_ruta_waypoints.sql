CREATE OR REPLACE FUNCTION fun_restore_ruta_waypoints(wid_waypoint tab_ruta_waypoints.id_waypoint%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_ruta_waypoints
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_waypoint = wid_waypoint AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Waypoint % restaurado correctamente.', wid_waypoint;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el waypoint eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;