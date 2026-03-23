CREATE OR REPLACE FUNCTION fun_restore_conductores
(
    wid_conductor tab_conductores.id_conductor%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_conductores
    SET 
        fec_delete = NULL,
        usr_delete = NULL
    WHERE id_conductor = wid_conductor
      AND fec_delete IS NOT NULL; -- solo si estaba eliminado lógicamente

    IF FOUND THEN
        RAISE NOTICE 'Conductor % restaurado correctamente.', wid_conductor;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el conductor eliminado.';
        RETURN FALSE;
    END IF;
END;
$$
LANGUAGE PLPGSQL;
