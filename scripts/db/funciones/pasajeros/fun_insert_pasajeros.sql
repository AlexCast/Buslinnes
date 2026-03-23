CREATE OR REPLACE FUNCTION fun_insert_pasajeros(
    wid_usuario tab_pasajeros.id_usuario%TYPE,
    wnom_pasajero tab_pasajeros.nom_pasajero%TYPE,
    wemail_pasajero tab_pasajeros.email_pasajero%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_usuario IS NULL OR wid_usuario <= 0 THEN
        RETURN 'el id del pasajero (id_usuario) no puede ser nulo';
    END IF;

    IF LENGTH(wnom_pasajero) < 3 OR wnom_pasajero IS NULL THEN
        RETURN 'El nombre debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    IF wemail_pasajero IS NULL OR POSITION('@' IN wemail_pasajero) = 0 THEN
        RETURN 'El email del pasajero no es valido o viene nulo';
    END IF;

    INSERT INTO tab_pasajeros (id_usuario, nom_pasajero, email_pasajero)
    VALUES (wid_usuario, wnom_pasajero, wemail_pasajero);
    
    RAISE NOTICE 'Ya inserté el pasajero % %', wid_usuario, wnom_pasajero;
    RETURN 'Esta vaina funcionó.. Somos duros en ADSO';


        EXCEPTION
            WHEN SQLSTATE '23502' THEN  
                RAISE NOTICE 'Está mandando un NULO en el ID... Sea serio';
				RETURN FALSE;

			WHEN SQLSTATE '23505' THEN  
                RAISE NOTICE 'El registro ya existe.. Trabaje bien o ábrase llaveee';
				RETURN FALSE;

            WHEN SQLSTATE '22001' THEN  
                RAISE NOTICE 'El nombre es muy corto.. Es de su abuelita?';
				RETURN FALSE;

			WHEN OTHERS THEN
					RAISE NOTICE 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';
					RETURN FALSE;
END;
$$ 
LANGUAGE PLPGSQL;