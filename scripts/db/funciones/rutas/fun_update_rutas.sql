
CREATE OR REPLACE FUNCTION fun_update_rutas
(
    wid_ruta INT,
    wnom_ruta VARCHAR,
    whora_inicio TIME,
    whora_final TIME,
    winicio_ruta VARCHAR,
    wfin_ruta VARCHAR,
    wlongitud DECIMAL,
    wval_pasaje DECIMAL
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_ruta IS NULL OR wid_ruta < 1 THEN
        RETURN 'error: el ID de la ruta no puede ser nulo o menor a cero';
    END IF;

    IF wnom_ruta IS NULL OR LENGTH(wnom_ruta) < 3 THEN
        RETURN 'error: el nombre de la ruta debe tener mínimo 3 caracteres';
    END IF;

    IF whora_inicio IS NULL THEN
        RETURN 'error: la hora de inicio no puede ser nula';
    END IF;

    IF whora_final IS NULL THEN
        RETURN 'error: la hora final no puede ser nula';
    END IF;

    IF winicio_ruta IS NULL THEN
        RETURN 'error: el inicio de la ruta no puede ser nulo';
    END IF;

    IF wfin_ruta IS NULL THEN
        RETURN 'error: el fin de la ruta no puede ser nulo';
    END IF;

    IF wlongitud IS NULL OR wlongitud < 0 THEN
        RETURN 'error: la longitud no puede ser nula o negativa';
    END IF;

    IF wval_pasaje IS NULL OR wval_pasaje < 0 THEN
        RETURN 'error: el valor del pasaje no puede ser nulo o negativo';
    END IF;

    UPDATE tab_rutas
    SET
        nom_ruta = wnom_ruta,
        hora_inicio = whora_inicio,
        hora_final = whora_final,
        inicio_ruta = winicio_ruta,
        fin_ruta = wfin_ruta,
        longitud = wlongitud,
        val_pasaje = wval_pasaje
        WHERE id_ruta = wid_ruta
            AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Ruta % actualizada correctamente.', wid_ruta;
        RETURN 'Ruta actualizada correctamente';
    ELSE
        RAISE NOTICE 'La ruta % no se encontró.', wid_ruta;
        RETURN 'No se pudo actualizar la ruta porque no existe';
    END IF;

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RETURN 'Error: está colocando un valor nulo';
    WHEN SQLSTATE '23505' THEN
        RETURN 'Error: el registro ya existe';
    WHEN SQLSTATE '22001' THEN
        RETURN 'Error: algún campo tiene datos demasiado largos';
    WHEN OTHERS THEN
        RETURN 'Error inesperado: ' || SQLERRM;
END;
$$ 
LANGUAGE PLPGSQL;
