CREATE OR REPLACE FUNCTION fun_restore_parque_automotor(
    wid_parque_automotor tab_parque_automotor.id_parque_automotor%TYPE
)RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_parque_automotor
    SET 
        fec_delete = NULL,
        usr_delete = NULL
    WHERE id_parque_automotor = wid_parque_automotor
      AND fec_delete IS NOT NULL; 
    
    IF FOUND THEN
        RAISE NOTICE 'Parque automotor % restaurado correctamente.', wid_parque_automotor;
    RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el parque automotor eliminado.';
        RETURN FALSE;
    END IF;
END;
$$
LANGUAGE PLPGSQL;