CREATE OR REPLACE FUNCTION fun_listar_rutas(wid_ruta tab_rutas.id_ruta%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_rutas tab_rutas%ROWTYPE;
BEGIN
    IF wid_ruta IS NULL OR wid_ruta <= 0 THEN
        RAISE NOTICE 'El ID de la ruta no puede ser nulo ni menor que cero.';
        RETURN FALSE;
    END IF;

        SELECT id_ruta, nom_ruta, hora_inicio, hora_final, inicio_ruta, fin_ruta, longitud, val_pasaje,
            inicio_lat, inicio_lng, fin_lat, fin_lng
        INTO wreg_rutas
        FROM tab_rutas
                WHERE id_ruta = wid_ruta
                    AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'La ruta con ID % no existe.', wid_ruta;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Nombre: %, Hora Inicio: %, Hora Final: %, Inicio: %, Fin: %, Longitud: %, Valor Pasaje: %',
        wreg_rutas.id_ruta,
        wreg_rutas.nom_ruta,
        wreg_rutas.hora_inicio,
        wreg_rutas.hora_final,
        wreg_rutas.inicio_ruta,
        wreg_rutas.fin_ruta,
        wreg_rutas.longitud,
        wreg_rutas.val_pasaje;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
