
CREATE OR REPLACE FUNCTION fun_insert_ruta_bus(
    wid_ruta_bus INT,
    wid_ruta INT,
    wid_bus tab_buses.id_bus%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    v_bus tab_buses.id_bus%TYPE;
BEGIN
    IF wid_ruta_bus IS NULL OR wid_ruta IS NULL OR wid_bus IS NULL THEN
        RAISE EXCEPTION USING errcode = 23502;
    END IF;

    IF wid_ruta_bus <= 0 OR wid_ruta <= 0 THEN
        RAISE EXCEPTION USING errcode = 22001;
    END IF;

    v_bus := UPPER(REGEXP_REPLACE(TRIM(COALESCE(wid_bus, '')), '\\s+', '', 'g'));
    IF v_bus !~ '^[A-Z]{3}[0-9]{3}$' THEN
        RAISE EXCEPTION USING errcode = 22023;
    END IF;

    INSERT INTO tab_ruta_bus (id_ruta_bus, id_ruta, id_bus)
    VALUES (wid_ruta_bus, wid_ruta, v_bus);

    RAISE NOTICE 'Asignación ruta-bus insertada correctamente';
    RETURN TRUE;
EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'Campos obligatorios no pueden ser NULL';
        RETURN FALSE;
    WHEN SQLSTATE '22001' THEN
        RAISE NOTICE 'Los IDs deben ser mayores a 0';
        RETURN FALSE;
    WHEN SQLSTATE '22023' THEN
        RAISE NOTICE 'El ID de bus debe tener formato AAA123';
        RETURN FALSE;
    WHEN SQLSTATE '23503' THEN
        RAISE NOTICE 'Error de integridad referencial. Verifique que existan la ruta y el bus';
        RETURN FALSE;
    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'La asignación con ID % ya existe', wid_ruta_bus;
        RETURN FALSE;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
