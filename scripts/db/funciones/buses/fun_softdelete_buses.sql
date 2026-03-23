CREATE OR REPLACE FUNCTION fun_softdelete_buses(
    wid_bus tab_buses.id_bus%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_buses
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_bus = wid_bus
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Bus % eliminado lógicamente.', wid_bus;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el bus o ya está eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
