CREATE OR REPLACE FUNCTION fun_softdelete_parque_automotor(
    wid_parque_automotor tab_parque_automotor.id_parque_automotor%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    -- Validación
    IF wid_parque_automotor IS NULL THEN
        RETURN 'Error: el ID del parque automotor no puede ser nulo.';
    END IF;

    -- Actualización del delete lógico
    UPDATE tab_parque_automotor
    SET usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_delete = CURRENT_TIMESTAMP
    WHERE id_parque_automotor = wid_parque_automotor
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RETURN 'Advertencia: el registro no existe o ya fue eliminado.';
    END IF;

    RETURN 'Registro eliminado lógicamente correctamente.';
EXCEPTION
    WHEN OTHERS THEN
        RETURN 'Error inesperado: ' || SQLERRM;
END;
$$ LANGUAGE plpgsql;
