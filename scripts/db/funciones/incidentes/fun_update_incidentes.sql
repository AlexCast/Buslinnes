CREATE OR REPLACE FUNCTION fun_update_incidentes(
    wid_incidente tab_incidentes.id_incidente%TYPE,
    wtitulo_incidente tab_incidentes.titulo_incidente%TYPE,
    wdesc_incidente tab_incidentes.desc_incidente%TYPE,
    wid_bus tab_incidentes.id_bus%TYPE,
    wid_usuario tab_incidentes.id_usuario%TYPE,
    wtipo_incidente tab_incidentes.tipo_incidente%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_incidente RECORD;
BEGIN
    -- Validaciones iniciales
    IF wid_incidente IS NULL OR wid_incidente <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '23502';
    END IF;

    IF wtitulo_incidente IS NULL OR LENGTH(TRIM(wtitulo_incidente)) < 3 THEN
        RAISE EXCEPTION USING ERRCODE = '22001';
    END IF;

    IF LENGTH(TRIM(wtitulo_incidente)) > 120 THEN
        RAISE EXCEPTION USING ERRCODE = '22002';
    END IF;

    IF wdesc_incidente IS NULL OR LENGTH(TRIM(wdesc_incidente)) < 5 THEN
        RAISE EXCEPTION USING ERRCODE = '22003';
    END IF;

    IF LENGTH(TRIM(wdesc_incidente)) > 2000 THEN
        RAISE EXCEPTION USING ERRCODE = '22004';
    END IF;

    IF wid_bus IS NULL OR wid_bus <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '22005';
    END IF;

    IF wid_usuario IS NULL OR wid_usuario <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '22006';
    END IF;

    IF wtipo_incidente NOT IN ('C', 'E', 'D', 'A', 'O') THEN
        RAISE EXCEPTION USING ERRCODE = '22007';
    END IF;

    -- Verificar si existe el registro
    SELECT id_incidente, titulo_incidente, desc_incidente, id_bus, id_usuario, tipo_incidente
    INTO wreg_incidente
    FROM tab_incidentes
    WHERE id_incidente = wid_incidente AND fec_delete IS NULL;

    IF FOUND THEN
        -- Actualizar el registro existente
        UPDATE tab_incidentes SET
            titulo_incidente = wtitulo_incidente,
            desc_incidente = wdesc_incidente,
            id_bus = wid_bus,
            id_usuario = wid_usuario,
            tipo_incidente = wtipo_incidente
        WHERE id_incidente = wid_incidente AND fec_delete IS NULL;

        RAISE NOTICE 'Incidente con ID % actualizado correctamente', wid_incidente;
        RETURN TRUE;
    ELSE
        RAISE EXCEPTION USING ERRCODE = '23505';
    END IF;

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'El ID del incidente no puede ser nulo o menor/igual a 0';
        RETURN FALSE;

    WHEN SQLSTATE '22001' THEN
        RAISE NOTICE 'El título del incidente debe tener al menos 3 caracteres';
        RETURN FALSE;

    WHEN SQLSTATE '22002' THEN
        RAISE NOTICE 'El título del incidente no puede superar 120 caracteres';
        RETURN FALSE;

    WHEN SQLSTATE '22003' THEN
        RAISE NOTICE 'La descripción del incidente debe tener al menos 5 caracteres';
        RETURN FALSE;

    WHEN SQLSTATE '22004' THEN
        RAISE NOTICE 'La descripción del incidente no puede superar 2000 caracteres';
        RETURN FALSE;

    WHEN SQLSTATE '22005' THEN
        RAISE NOTICE 'El ID del bus debe ser mayor a 0';
        RETURN FALSE;

    WHEN SQLSTATE '22006' THEN
        RAISE NOTICE 'El ID del usuario debe ser mayor a 0';
        RETURN FALSE;

    WHEN SQLSTATE '22007' THEN
        RAISE NOTICE 'Tipo de incidente inválido. Debe ser C, E, D, A u O';
        RETURN FALSE;

    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'El incidente con ID % no existe o ha sido eliminado', wid_incidente;
        RETURN FALSE;

    WHEN SQLSTATE '23503' THEN
        RAISE NOTICE 'Violación de clave foránea. Verifique que el bus y usuario existan';
        RETURN FALSE;

    WHEN OTHERS THEN
        RAISE NOTICE 'Error no esperado: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
