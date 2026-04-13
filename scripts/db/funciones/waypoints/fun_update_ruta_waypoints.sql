CREATE OR REPLACE FUNCTION fun_update_ruta_waypoints(
    wid_waypoint tab_ruta_waypoints.id_waypoint%TYPE,
    wid_ruta tab_ruta_waypoints.id_ruta%TYPE,
    worden tab_ruta_waypoints.orden%TYPE,
    wlat tab_ruta_waypoints.lat%TYPE,
    wlng tab_ruta_waypoints.lng%TYPE,
    wnombre tab_ruta_waypoints.nombre%TYPE
) RETURNS BOOLEAN AS
$$
    DECLARE 
        wreg_waypoint RECORD;
    BEGIN
        -- Validaciones iniciales
        IF wid_waypoint IS NULL OR wid_waypoint <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '23502';
        END IF;
        
        IF wid_ruta IS NULL OR wid_ruta <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '22002';
        END IF;
        
        IF worden IS NULL OR worden <= 0 THEN
            RAISE EXCEPTION USING ERRCODE = '22003';
        END IF;
        
        IF wlat IS NULL OR wlat < -90 OR wlat > 90 THEN
            RAISE EXCEPTION USING ERRCODE = '22004';
        END IF;
        
        IF wlng IS NULL OR wlng < -180 OR wlng > 180 THEN
            RAISE EXCEPTION USING ERRCODE = '22005';
        END IF;
        
        -- Validar que el nombre no esté vacío si se proporciona
        IF wnombre IS NOT NULL AND LENGTH(TRIM(wnombre)) = 0 THEN
            RAISE EXCEPTION USING ERRCODE = '22006';
        END IF;
        
        -- Verificar si existe la ruta
        IF NOT EXISTS (SELECT 1 FROM tab_rutas WHERE id_ruta = wid_ruta AND fec_delete IS NULL) THEN
            RAISE EXCEPTION USING ERRCODE = '23506';
        END IF;
        
        -- Verificar si existe el waypoint
        SELECT id_waypoint, id_ruta, orden, lat, lng, nombre INTO wreg_waypoint 
        FROM tab_ruta_waypoints 
        WHERE id_waypoint = wid_waypoint AND fec_delete IS NULL;
        
        IF FOUND THEN
            -- Validar que no exista otro waypoint con la misma ruta y orden (excepto el actual)
            IF EXISTS (
                SELECT 1 FROM tab_ruta_waypoints 
                WHERE id_ruta = wid_ruta 
                AND orden = worden 
                AND id_waypoint != wid_waypoint
                AND fec_delete IS NULL
            ) THEN
                RAISE EXCEPTION USING ERRCODE = '23505';
            END IF;
            
            -- Actualizar el registro existente
            UPDATE tab_ruta_waypoints SET
                id_ruta = wid_ruta,
                orden = worden,
                lat = wlat,
                lng = wlng,
                nombre = COALESCE(wnombre, 'Parada sin nombre')
            WHERE id_waypoint = wid_waypoint
                AND fec_delete IS NULL;
            
            RAISE NOTICE 'Waypoint con ID % actualizado correctamente', wid_waypoint;
            RETURN TRUE;
        ELSE
            RAISE EXCEPTION USING ERRCODE = '23507';
        END IF;
        
    EXCEPTION
        WHEN SQLSTATE '23502' THEN
            RAISE NOTICE 'El ID del waypoint no puede ser nulo o menor/igual a 0';
            RETURN FALSE;
            
        WHEN SQLSTATE '22002' THEN
            RAISE NOTICE 'El ID de la ruta no puede ser nulo o menor/igual a 0';
            RETURN FALSE;
            
        WHEN SQLSTATE '22003' THEN
            RAISE NOTICE 'El orden del waypoint no puede ser nulo o menor/igual a 0';
            RETURN FALSE;
            
        WHEN SQLSTATE '22004' THEN
            RAISE NOTICE 'La latitud debe estar entre -90 y 90';
            RETURN FALSE;
            
        WHEN SQLSTATE '22005' THEN
            RAISE NOTICE 'La longitud debe estar entre -180 y 180';
            RETURN FALSE;
            
        WHEN SQLSTATE '22006' THEN
            RAISE NOTICE 'El nombre del waypoint no puede estar vacío';
            RETURN FALSE;
            
        WHEN SQLSTATE '23505' THEN
            RAISE NOTICE 'Ya existe un waypoint con la misma ruta y orden';
            RETURN FALSE;
            
        WHEN SQLSTATE '23506' THEN
            RAISE NOTICE 'La ruta con ID % no existe', wid_ruta;
            RETURN FALSE;
            
        WHEN SQLSTATE '23507' THEN
            RAISE NOTICE 'El waypoint con ID % no existe para actualizar', wid_waypoint;
            RETURN FALSE;
            
        WHEN OTHERS THEN
            RAISE NOTICE 'Error no esperado: %', SQLERRM;
            RETURN FALSE;
    END;
$$
LANGUAGE PLPGSQL;
