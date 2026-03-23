CREATE OR REPLACE FUNCTION fun_softdelete_ruta_bus(
    wid_ruta_bus tab_ruta_bus.id_ruta_bus%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_ruta_bus
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_ruta_bus = wid_ruta_bus
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Ruta_Bus % eliminada lógicamente.', wid_ruta_bus;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la ruta_bus o ya está eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;

--SELECT * FROM tab_ruta_bus
--SELECT fun_softdelete_ruta_bus(2)


