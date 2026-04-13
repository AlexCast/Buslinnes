CREATE OR REPLACE FUNCTION fun_softdelete_parque_automotor(
    wid_parque_automotor tab_parque_automotor.id_parque_automotor%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    -- Validación
    IF wid_parque_automotor IS NULL THEN
        RETURN FALSE;
    END IF;

    -- Actualización del delete lógico
    UPDATE tab_parque_automotor
    SET usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_delete = CURRENT_TIMESTAMP
    WHERE id_parque_automotor = wid_parque_automotor
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
