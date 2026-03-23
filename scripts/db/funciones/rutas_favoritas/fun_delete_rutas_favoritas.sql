CREATE OR REPLACE FUNCTION fun_softdelete_rutas_favoritas(
    wid_ruta_favorita tab_rutas_favoritas.id_ruta_favorita%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    DELETE FROM tab_rutas_favoritas
    WHERE id_ruta_favorita = wid_ruta_favorita;

    IF FOUND THEN
        RAISE NOTICE 'Ruta favorita % eliminada correctamente.', wid_ruta_favorita;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la ruta favorita.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;