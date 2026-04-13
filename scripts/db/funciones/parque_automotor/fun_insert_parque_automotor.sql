CREATE OR REPLACE FUNCTION fun_insert_parque_automotor(
    wid_parque_automotor tab_parque_automotor.id_parque_automotor%TYPE,
    wid_bus tab_parque_automotor.id_bus%TYPE,
    wdir_parque_automotor tab_parque_automotor.dir_parque_automotor%TYPE
)
RETURNS BOOLEAN AS
$$
DECLARE
    v_bus tab_parque_automotor.id_bus%TYPE;
BEGIN
    IF wid_parque_automotor IS NULL OR wid_parque_automotor <= 0 THEN
        RAISE EXCEPTION USING errcode = '23502';
    END IF;

    v_bus := UPPER(REGEXP_REPLACE(TRIM(COALESCE(wid_bus, '')), '\\s+', '', 'g'));
    IF v_bus = '' THEN
        RAISE EXCEPTION USING errcode = '23502';
    END IF;

    IF v_bus !~ '^[A-Z]{3}[0-9]{3}$' THEN
        RAISE EXCEPTION USING errcode = '22023';
    END IF;

    IF wdir_parque_automotor IS NULL OR LENGTH(TRIM(wdir_parque_automotor)) < 5 THEN
        RAISE EXCEPTION USING errcode = '22001';
    END IF;

    INSERT INTO tab_parque_automotor (id_parque_automotor,id_bus, dir_parque_automotor)
    VALUES (wid_parque_automotor, v_bus, wdir_parque_automotor);

    RAISE NOTICE 'Parque automotor insertado correctamente: ID %', wid_parque_automotor;
    RETURN TRUE;
    
    EXCEPTION
        WHEN SQLSTATE '23502' THEN
            RAISE NOTICE 'Error: campos obligatorios no pueden ser nulos';
            RETURN FALSE;
        WHEN SQLSTATE '23505' THEN
            RAISE NOTICE 'Error: el registro ya existe';
            RETURN FALSE;
        WHEN SQLSTATE '22001' THEN
            RAISE NOTICE 'Error: la direccion debe tener minimo 5 caracteres';
            RETURN FALSE;
        WHEN SQLSTATE '22023' THEN
            RAISE NOTICE 'Error: el ID de bus debe tener formato AAA123';
            RETURN FALSE;
        WHEN SQLSTATE '23503' THEN
            RAISE NOTICE 'Error: el bus no existe';
            RETURN FALSE;
        WHEN OTHERS THEN
            RAISE NOTICE 'Error desconocido: %', SQLERRM;
            RETURN FALSE;
END;
$$
LANGUAGE plpgsql;