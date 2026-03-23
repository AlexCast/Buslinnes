CREATE OR REPLACE FUNCTION fun_insert_incidentes(
    wid_incidente INT,
    wtitulo_incidente VARCHAR,
    wdesc_incidente VARCHAR,
    wid_bus INT,
    wid_conductor INT,
    wtipo_incidente CHAR(1) DEFAULT 'O'
) RETURNS BOOLEAN AS
$$
BEGIN
    IF wid_incidente IS NULL OR wtitulo_incidente IS NULL OR wdesc_incidente IS NULL OR wid_bus IS NULL OR wid_conductor IS NULL THEN
        RAISE EXCEPTION USING ERRCODE = '23502';
    END IF;

    IF wid_incidente <= 0 OR wid_bus <= 0 OR wid_conductor <= 0 THEN
        RAISE NOTICE 'Los IDs deben ser mayores a 0.';
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
        RAISE NOTICE 'Tipo de incidente inválido. Debe ser C, E, D, A u O.';
        RETURN FALSE;
    END IF;

    INSERT INTO tab_incidentes(id_incidente, titulo_incidente, desc_incidente, id_bus, id_conductor, tipo_incidente)
    VALUES (wid_incidente, wtitulo_incidente, wdesc_incidente, wid_bus, wid_conductor, wtipo_incidente);

    RAISE NOTICE 'Incidente insertado correctamente';
    RETURN TRUE;

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'El ID del incidente ya existe.';
        RETURN FALSE;
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'Campos obligatorios no pueden ser nulos.';
        RETURN FALSE;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado: %', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE PLPGSQL;
