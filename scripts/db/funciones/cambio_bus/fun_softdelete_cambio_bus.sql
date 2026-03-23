CREATE OR REPLACE FUNCTION fun_softdelete_cambio_bus(
    wid_cambio_bus tab_cambio_bus.id_cambio_bus%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_cambio_bus
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_cambio_bus = wid_cambio_bus
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'El cambio del Bus % eliminado lógicamente.', wid_cambio_bus;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el cambio del bus o ya está eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;