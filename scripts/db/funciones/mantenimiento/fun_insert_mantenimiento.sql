CREATE OR REPLACE FUNCTION fun_insert_mantenimiento(
    wid_mantenimiento tab_mantenimiento.id_mantenimiento%TYPE,
    wid_bus tab_buses.id_bus%TYPE,
    wdescripcion tab_mantenimiento.descripcion%TYPE,
    wfecha_mantenimiento tab_mantenimiento.fecha_mantenimiento%TYPE,
    wcosto_mantenimiento tab_mantenimiento.costo_mantenimiento%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    IF wid_mantenimiento IS NULL OR wid_mantenimiento <= 0 THEN
        RAISE EXCEPTION USING errcode = '23502';
    END IF;

    IF wid_bus IS NULL OR wid_bus <= 0 THEN
        RAISE EXCEPTION USING errcode = '23502';
    END IF;

    IF wdescripcion IS NULL OR LENGTH(TRIM(wdescripcion)) < 10 THEN
        RAISE EXCEPTION USING errcode = '22001';
    END IF;

    IF wfecha_mantenimiento IS NULL THEN
        RAISE EXCEPTION USING errcode = '23502';
    END IF;

    IF wcosto_mantenimiento IS NULL OR wcosto_mantenimiento < 0 OR wcosto_mantenimiento > 9999999999 THEN
        RAISE EXCEPTION USING errcode = '22003';
    END IF;

    INSERT INTO tab_mantenimiento (
        id_mantenimiento,
        id_bus,
        descripcion,
        fecha_mantenimiento,
        costo_mantenimiento
    ) VALUES (
        wid_mantenimiento,
        wid_bus,
        wdescripcion,
        wfecha_mantenimiento,
        wcosto_mantenimiento
    );

    RETURN TRUE;
EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'Campos obligatorios no pueden ser nulos';
        RETURN FALSE;
    WHEN SQLSTATE '22001' THEN
        RAISE NOTICE 'La descripcion debe tener minimo 10 caracteres';
        RETURN FALSE;
    WHEN SQLSTATE '22003' THEN
        RAISE NOTICE 'El costo de mantenimiento esta fuera de rango';
        RETURN FALSE;
    WHEN SQLSTATE '23503' THEN
        RAISE NOTICE 'El bus no existe';
        RETURN FALSE;
    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'El ID de mantenimiento ya existe';
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;

--SELECT * FROM tab_mantenimiento
--select fun_insert_mantenimiento(7,'uwu','2024-05-01 16:00:00', 180000)