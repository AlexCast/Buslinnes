CREATE OR REPLACE FUNCTION fun_update_pasajeros(
    wid_usuario tab_pasajeros.id_usuario%TYPE,
    wnom_pasajero tab_pasajeros.nom_pasajero%TYPE,
    wemail_pasajero tab_pasajeros.email_pasajero%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_usuario IS NULL OR wid_usuario <= 0 THEN
        RETURN 'el id del pasajero no puede ser nulo';
    END IF;
    IF LENGTH(wnom_pasajero) < 3 OR wnom_pasajero IS NULL THEN
        RETURN 'El nombre debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;
    IF wemail_pasajero IS NULL OR POSITION('@' IN wemail_pasajero) = 0 THEN
        RETURN 'El email del pasajero no es valido o viene nulo';
    END IF;

    UPDATE tab_pasajeros
    SET nom_pasajero = wnom_pasajero,
        email_pasajero = wemail_pasajero
        WHERE id_usuario = wid_usuario
            AND fec_delete IS NULL;
		IF FOUND THEN
			RAISE NOTICE 'Ya actualizé el pasajero %', wnom_pasajero;
            RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
        ELSE
            RAISE NOTICE 'Pequeño demonio, no funcionó esta joda... Y ahora????';
            RETURN 'Eche pa la primaria porque de esto no va a comer... Sorry';
        END IF;
    
EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RAISE EXCEPTION 'El pasajero no existe... Créelo y vuelva, o ni se aparezca más por acá';
		RETURN FALSE;
    WHEN SQLSTATE '23505' THEN
        RAISE EXCEPTION 'El registro ya existe.. Trabaje bien o ábrase llaveee';
		RETURN FALSE;
    WHEN SQLSTATE '22001' THEN
        RAISE EXCEPTION 'El nombre es muy corto.. Es de su abuelita?';
		RETURN FALSE;
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';
		RETURN FALSE;
END;
$$ 
LANGUAGE PLPGSQL;