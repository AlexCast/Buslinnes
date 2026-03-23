CREATE OR REPLACE FUNCTION fun_insert_parque_automotor(
    wid_parque_automotor tab_parque_automotor.id_parque_automotor%TYPE,
    wid_bus tab_parque_automotor.id_bus%TYPE,
    wdir_parque_automotor tab_parque_automotor.dir_parque_automotor%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    IF wid_parque_automotor IS NULL OR wid_parque_automotor <= 1 THEN
        RAISE EXCEPTION 'Error: El ID del parque automotor no puede ser nulo o menor que 1';
    END IF;

    IF wid_bus IS NULL OR wid_bus <= 1 THEN
        RAISE EXCEPTION 'Error: El ID del bus no puede ser nulo o menor que 1';
    END IF;

    IF wdir_parque_automotor IS NULL OR LENGTH(wdir_parque_automotor) < 5 THEN
        RAISE EXCEPTION 'Error: La dirección del parque automotor no puede ser nula o tener menos de 5 caracteres';
    END IF;

    INSERT INTO tab_parque_automotor (id_parque_automotor,id_bus, dir_parque_automotor)
    VALUES (wid_parque_automotor, wid_bus, wdir_parque_automotor);

    RAISE NOTICE 'Parque automotor insertado correctamente: ID %', wid_parque_automotor;
    RETURN TRUE;
    
    EXCEPTION
        WHEN SQLSTATE '23502' THEN
            RAISE EXCEPTION 'Error: Está colocando un valor nulo en un campo obligatorio';
        WHEN SQLSTATE '23505' THEN
            RAISE EXCEPTION 'Error: El registro ya existe';
        WHEN SQLSTATE '22001' THEN
            RAISE EXCEPTION 'Error: Algún campo es demasiado corto';
        WHEN OTHERS THEN
            RAISE EXCEPTION 'Error desconocido: %', SQLERRM;
END;
$$
LANGUAGE plpgsql;