CREATE OR REPLACE FUNCTION fun_softdelete_propietarios(
    wid_propietario tab_propietarios.id_propietario%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_propietarios
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_propietario = wid_propietario
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Propietario % eliminado lógicamente.', wid_propietario;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el propietario o ya está eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;


--SELECT * FROM tab_propietarios
--SELECT fun_softdelete_propietarios(1000000001)

