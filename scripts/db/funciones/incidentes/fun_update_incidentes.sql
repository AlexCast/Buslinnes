CREATE OR REPLACE FUNCTION fun_update_incidentes(
    wid_incidente INT,
    wtitulo_incidente VARCHAR,
    wdesc_incidente VARCHAR,
    wid_bus INT,
    wid_conductor INT,
    wtipo_incidente CHAR(1)
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_incidente RECORD;
BEGIN
    IF wid_incidente IS NULL OR wid_incidente <= 0 OR wtitulo_incidente IS NULL OR wdesc_incidente IS NULL OR wid_bus IS NULL OR wid_conductor IS NULL THEN
        RAISE EXCEPTION USING ERRCODE = '23502';
    END IF;

    IF wid_bus <= 0 OR wid_conductor <= 0 THEN
        RAISE NOTICE 'Los IDs de bus y conductor deben ser mayores a 0.';
        RETURN FALSE;
    END IF;

    IF LENGTH(TRIM(wtitulo_incidente)) < 3 THEN
        RAISE NOTICE 'El título del incidente debe tener al menos 3 caracteres.';
        RETURN FALSE;
    END IF;

    IF LENGTH(TRIM(wtitulo_incidente)) > 120 THEN
        RAISE NOTICE 'El título del incidente no puede superar 120 caracteres.';
        RETURN FALSE;
    END IF;

    IF LENGTH(TRIM(wdesc_incidente)) < 5 THEN
        RAISE NOTICE 'La descripción del incidente debe tener al menos 5 caracteres.';
        RETURN FALSE;
    END IF;

    IF LENGTH(TRIM(wdesc_incidente)) > 2000 THEN
        RAISE NOTICE 'La descripción del incidente no puede superar 2000 caracteres.';
        RETURN FALSE;
    END IF;

    IF wtipo_incidente NOT IN ('C', 'E', 'D', 'A', 'O') THEN
        RAISE NOTICE 'Tipo de incidente inválido.';
        RETURN FALSE;
    END IF;

    SELECT id_incidente, titulo_incidente, desc_incidente, id_bus, id_conductor, tipo_incidente
    INTO wreg_incidente
    FROM tab_incidentes
        WHERE id_incidente = wid_incidente
            AND fec_delete IS NULL;

    IF FOUND THEN
        UPDATE tab_incidentes SET
            titulo_incidente = wtitulo_incidente,
            desc_incidente = wdesc_incidente,
            id_bus = wid_bus,
            id_conductor = wid_conductor,
            tipo_incidente = wtipo_incidente
                WHERE id_incidente = wid_incidente
                    AND fec_delete IS NULL;

        RAISE NOTICE 'Incidente % actualizado correctamente.', wid_incidente;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'El incidente con ID % no existe.', wid_incidente;
        RETURN FALSE;
    END IF;

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'El ID del incidente no puede ser nulo.';
        RETURN FALSE;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error no esperado: %', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE PLPGSQL;
