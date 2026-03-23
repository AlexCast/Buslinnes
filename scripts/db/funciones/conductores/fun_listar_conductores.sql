CREATE OR REPLACE FUNCTION fun_listar_conductores(wid_conductor tab_conductores.id_conductor%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_conductores tab_conductores%ROWTYPE; -- Usamos %ROWTYPE para manejar toda la fila
BEGIN
    IF wid_conductor IS NULL OR wid_conductor <= 0 THEN
        RAISE NOTICE 'El ID del conductor no puede ser nulo o menor/igual a 0.';
        RETURN FALSE;
    END IF;

    -- Obtener el conductor
    SELECT id_conductor, nom_conductor, ape_conductor, email_conductor, licencia_conductor, tipo_licencia, fec_venc_licencia, estado_conductor, edad, tipo_sangre, usr_insert, fec_insert, usr_update, fec_update, usr_delete, fec_delete INTO wreg_conductores FROM tab_conductores WHERE id_conductor = wid_conductor AND fec_delete IS NULL;

    -- Verificar si se encontró un registro
    IF NOT FOUND THEN
        RAISE NOTICE 'El conductor con ID % no existe.', wid_conductor;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Nombre: %', wreg_conductores.id_conductor, wreg_conductores.nom_conductor;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;