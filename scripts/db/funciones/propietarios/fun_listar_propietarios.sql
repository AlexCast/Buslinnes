CREATE OR REPLACE FUNCTION fun_listar_propietarios(wid_propietario tab_propietarios.id_propietario%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_propietario tab_propietarios%ROWTYPE; -- Usamos %ROWTYPE para manejar toda la fila
BEGIN
    IF wid_propietario < 1000000000 THEN
        RAISE NOTICE 'El ID de propietario no puede ser menor a 1000000000.';
        RETURN FALSE;
    END IF;
    -- Obtener el propietario
    SELECT id_propietario, id_bus, nom_propietario, ape_propietario, tel_propietario, email_propietario
    INTO wreg_propietario
    FROM tab_propietarios
        WHERE id_propietario = wid_propietario
            AND fec_delete IS NULL;
    -- Verificar si se encontró un registro
    IF NOT FOUND THEN
        RAISE NOTICE 'El propietario con ID % no existe.', wid_propietario;
        RETURN FALSE;
    END IF;
    RAISE NOTICE 'ID: %, ID_BUS: %, Nombre: %, Apellido: %, Teléfono: %, Email: %', 
                  wreg_propietario.id_propietario,
                  wreg_propietario.id_bus, 
                  wreg_propietario.nom_propietario,
                  wreg_propietario.ape_propietario,
                  wreg_propietario.tel_propietario,
                  wreg_propietario.email_propietario;
    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
--SELECT fun_listar_propietarios(1000000020)
--SELECT * FROM tab_propietarios