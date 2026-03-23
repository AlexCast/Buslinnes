CREATE OR REPLACE FUNCTION fun_insert_rutas
(
    wid_ruta INT,
    wnom_ruta VARCHAR,
    whora_inicio TIME,
    whora_final TIME,
    winicio_ruta VARCHAR,
    wfin_ruta VARCHAR,
    wlongitud DECIMAL,
    wval_pasaje DECIMAL
) RETURNS BOOLEAN AS
$$
BEGIN
    IF wid_ruta IS NULL OR wid_ruta <= 0 THEN
        RETURN FALSE;
    END IF;

    IF wnom_ruta IS NULL OR LENGTH(wnom_ruta) < 3 THEN
        RETURN FALSE;
    END IF;

    IF whora_inicio IS NULL THEN
        RETURN FALSE;
    END IF;

    IF whora_final IS NULL THEN
        RETURN FALSE;
    END IF;

    IF winicio_ruta IS NULL THEN
        RETURN FALSE;
    END IF;

    IF wfin_ruta IS NULL THEN
        RETURN FALSE;
    END IF;

    IF wlongitud IS NULL OR wlongitud < 0 THEN
        RETURN FALSE;
    END IF;

    IF wval_pasaje IS NULL OR wval_pasaje < 0 THEN
        RETURN FALSE;
    END IF;

    INSERT INTO tab_rutas (
        id_ruta, nom_ruta, hora_inicio, hora_final,
        inicio_ruta, fin_ruta, longitud, val_pasaje
    ) VALUES (
        wid_ruta, wnom_ruta, whora_inicio, whora_final,
        winicio_ruta, wfin_ruta, wlongitud, wval_pasaje
    );

    RETURN TRUE;

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RETURN FALSE;
    WHEN SQLSTATE '23505' THEN
        RETURN FALSE;
    WHEN OTHERS THEN
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;