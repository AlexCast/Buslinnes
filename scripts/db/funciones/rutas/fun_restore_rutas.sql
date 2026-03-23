CREATE OR REPLACE FUNCTION fun_restore_rutas(wid_ruta tab_rutas.id_ruta%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_rutas
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_ruta = wid_ruta AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Ruta % restaurada correctamente.', wid_ruta;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la ruta eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
