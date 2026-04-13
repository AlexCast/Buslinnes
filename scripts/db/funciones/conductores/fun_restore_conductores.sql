CREATE OR REPLACE FUNCTION fun_restore_conductores
(
    wid_usuario tab_conductores.id_usuario%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE tab_conductores
    SET 
        fec_delete = NULL,
        usr_delete = NULL
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NOT NULL; -- solo si estaba eliminado lógicamente

    IF FOUND THEN
        RAISE NOTICE 'Conductor % restaurado correctamente.', wid_usuario;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el conductor eliminado.';
        RETURN FALSE;
    END IF;
END;
$$
LANGUAGE PLPGSQL;
