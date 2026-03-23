CREATE OR REPLACE FUNCTION fun_softdelete_rutas(wid_ruta tab_rutas.id_ruta%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_rutas
    SET 
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_delete = CURRENT_TIMESTAMP
    WHERE 
        id_ruta = wid_ruta
        AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Ruta % eliminada lógicamente por %', wid_ruta, COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER);
        RETURN TRUE;
    ELSE 
        RAISE NOTICE 'No se encontró la ruta % o ya está eliminada.', wid_ruta;
        RETURN FALSE;
    END IF;
END;
$$
LANGUAGE PLPGSQL;
