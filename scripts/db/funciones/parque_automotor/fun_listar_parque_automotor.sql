CREATE OR REPLACE FUNCTION fun_listar_parque_automotor(
    wid_parque_automotor tab_parque_automotor.id_parque_automotor%TYPE
)
RETURNS BOOLEAN AS
$$
DECLARE
    wreg_parque_automotor tab_parque_automotor%ROWTYPE;
BEGIN
    IF wid_parque_automotor IS NULL OR wid_parque_automotor <= 1 THEN
        RAISE EXCEPTION 'Error: El ID del parque automotor no puede ser nulo o menor que 1';
    END IF;

    SELECT id_parque_automotor, id_bus, dir_parque_automotor, usr_insert, fec_insert, usr_update, fec_update, usr_delete, fec_delete INTO wreg_parque_automotor
    FROM tab_parque_automotor
    WHERE id_parque_automotor = wid_parque_automotor
    AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'El parque automotor con ID % no existe o ha sido eliminado.', wid_parque_automotor;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, ID_BUS: %, Dirección: %',
        wreg_parque_automotor.id_parque_automotor,
        wreg_parque_automotor.id_bus,
        wreg_parque_automotor.dir_parque_automotor;
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