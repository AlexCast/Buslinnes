CREATE OR REPLACE FUNCTION fun_listar_pasajeros(wid_usuario tab_pasajeros.id_usuario%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_pasajero tab_pasajeros%ROWTYPE;
BEGIN
    IF wid_usuario IS NULL OR wid_usuario <= 0 THEN
        RAISE NOTICE 'El ID del pasajero no puede ser nulo o menor/igual a 0.';
        RETURN FALSE;
    END IF;

    SELECT id_usuario, nom_pasajero, email_pasajero
    INTO wreg_pasajero
    FROM tab_pasajeros
        WHERE id_usuario = wid_usuario
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'El pasajero con ID % no existe.', wid_usuario;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Nombre: %, Email: %',
        wreg_pasajero.id_usuario,
        wreg_pasajero.nom_pasajero,
        wreg_pasajero.email_pasajero;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;