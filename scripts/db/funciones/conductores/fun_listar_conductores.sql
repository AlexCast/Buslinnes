CREATE OR REPLACE FUNCTION fun_listar_conductores(wid_usuario tab_conductores.id_usuario%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_conductor tab_conductores%ROWTYPE;
BEGIN
    IF wid_usuario IS NULL OR wid_usuario <= 0 THEN
        RAISE NOTICE 'El ID del usuario no puede ser nulo o menor/igual a 0.';
        RETURN FALSE;
    END IF;

    -- Obtener el conductor
    SELECT id_usuario, nom_conductor, ape_conductor, genero_conductor, email_conductor, licencia_conductor, tipo_licencia, fec_venc_licencia, estado_conductor, fec_nacimiento, tipo_sangre
    INTO wreg_conductor
    FROM tab_conductores
        WHERE id_usuario = wid_usuario
            AND fec_delete IS NULL;
    
    -- Verificar si se encontró un registro
    IF NOT FOUND THEN
        RAISE NOTICE 'El conductor con ID % no existe.', wid_usuario;
        RETURN FALSE;
    END IF;
    
    RAISE NOTICE 'ID Usuario: %, Nombre: %, Apellido: %, Género: %, Email: %, Licencia: %, Tipo Licencia: %, Venc. Licencia: %, Estado: %, Nacimiento: %, Tipo Sangre: %', 
                  wreg_conductor.id_usuario,
                  wreg_conductor.nom_conductor,
                  wreg_conductor.ape_conductor,
                  wreg_conductor.genero_conductor,
                  wreg_conductor.email_conductor,
                  wreg_conductor.licencia_conductor,
                  wreg_conductor.tipo_licencia,
                  wreg_conductor.fec_venc_licencia,
                  wreg_conductor.estado_conductor,
                  wreg_conductor.fec_nacimiento,
                  wreg_conductor.tipo_sangre;
    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;