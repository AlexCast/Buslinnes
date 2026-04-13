
CREATE OR REPLACE FUNCTION fun_softdelete_conductores(
    wid_usuario tab_conductores.id_usuario%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_conductores
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Conductor % eliminado lógicamente.', wid_usuario;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el conductor o ya está eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
