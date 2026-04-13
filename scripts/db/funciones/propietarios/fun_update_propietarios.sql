CREATE OR REPLACE FUNCTION fun_update_propietarios(
    wid_propietario tab_propietarios.id_propietario%TYPE,
    wid_bus tab_buses.id_bus%TYPE,
    wnom_propietario tab_propietarios.nom_propietario%TYPE,
    wape_propietario tab_propietarios.ape_propietario%TYPE,
    wtel_propietario tab_propietarios.tel_propietario%TYPE,
    wemail_propietario tab_propietarios.email_propietario%TYPE
) RETURNS BOOLEAN AS
$$
    DECLARE 
        v_bus tab_buses.id_bus%TYPE;
        wreg_propietario RECORD;
    BEGIN
        -- Validaciones iniciales
        IF wid_propietario IS NULL OR wid_propietario < 100000 OR wid_propietario > 9999999999 THEN
            RAISE EXCEPTION USING ERRCODE = '23502';
        END IF;

        IF wid_bus IS NULL THEN
            RAISE EXCEPTION USING ERRCODE = '22004';
        END IF;

        v_bus := UPPER(REGEXP_REPLACE(TRIM(COALESCE(wid_bus, '')), '\\s+', '', 'g'));
        IF v_bus !~ '^[A-Z]{3}[0-9]{3}$' THEN
            RAISE EXCEPTION USING ERRCODE = '22023';
        END IF;
        
        IF wnom_propietario IS NULL OR LENGTH(wnom_propietario) < 3 THEN
            RAISE EXCEPTION USING ERRCODE = '22001';
        END IF;
        
        IF wape_propietario IS NULL OR LENGTH(wape_propietario) < 3 THEN
            RAISE EXCEPTION USING ERRCODE = '22002';
        END IF;
        
        IF wtel_propietario IS NULL OR wtel_propietario < 2999999999 THEN
            RAISE EXCEPTION USING ERRCODE = '22003';
        END IF;
        
        IF wemail_propietario IS NULL THEN
            RAISE EXCEPTION USING ERRCODE = '22004';
        END IF;
        
        -- Verificar si existe el registro
        SELECT id_propietario, id_bus, nom_propietario, ape_propietario, tel_propietario, email_propietario
        INTO wreg_propietario
        FROM tab_propietarios
                WHERE id_propietario = wid_propietario
                    AND fec_delete IS NULL;
        
        IF FOUND THEN
            -- Actualizar el registro existente
            UPDATE tab_propietarios SET
                id_bus = v_bus,
                nom_propietario = wnom_propietario,
                ape_propietario = wape_propietario,
                tel_propietario = wtel_propietario,
                email_propietario = wemail_propietario
                        WHERE id_propietario = wid_propietario
                            AND fec_delete IS NULL;
            
            RETURN TRUE;
        ELSE
            RAISE EXCEPTION USING ERRCODE = '23505';
        END IF;
        
    EXCEPTION
        WHEN SQLSTATE '23502' THEN
            RAISE NOTICE 'El ID de propietario no puede ser nulo y debe tener entre 6 y 10 digitos';
            RETURN FALSE;
            
        WHEN SQLSTATE '22001' THEN
            RAISE NOTICE 'El nombre del propietario es muy corto. Mínimo 3 caracteres';
            RETURN FALSE;
            
        WHEN SQLSTATE '22002' THEN
            RAISE NOTICE 'El apellido del propietario es muy corto. Mínimo 3 caracteres';
            RETURN FALSE;
            
        WHEN SQLSTATE '22003' THEN
            RAISE NOTICE 'El teléfono del propietario debe ser al menos 2999999999';
            RETURN FALSE;
            
        WHEN SQLSTATE '22004' THEN
            RAISE NOTICE 'El bus y el email del propietario no pueden ser nulos';
            RETURN FALSE;

        WHEN SQLSTATE '22023' THEN
            RAISE NOTICE 'El ID de bus debe tener formato AAA123';
            RETURN FALSE;
            
        WHEN SQLSTATE '23505' THEN
            RAISE NOTICE 'El propietario con ID % no existe para actualizar', wid_propietario;
            RETURN FALSE;
            
        WHEN OTHERS THEN
            RAISE NOTICE 'Error no esperado: %', SQLERRM;
            RETURN FALSE;
    END;
$$
LANGUAGE PLPGSQL;


-- Ejemplo de uso
--SELECT fun_update_propietarios(1000000001, 'Juan', 'Pérezoso', 3004567890, 'juan.perez@email.com');
--select * from tab_propietarios