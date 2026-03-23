CREATE OR REPLACE FUNCTION fun_update_conductores
(
    wid_conductor tab_conductores.id_conductor%TYPE,
    wnom_conductor tab_conductores.nom_conductor%TYPE,
    wape_conductor tab_conductores.ape_conductor%TYPE,
    wemail_conductor tab_conductores.email_conductor%TYPE,
    wlicencia_conductor tab_conductores.licencia_conductor%TYPE,
    wtipo_licencia tab_conductores.tipo_licencia%TYPE,
    wfec_ven_licencia tab_conductores.fec_venc_licencia%TYPE,
    westado_conductor tab_conductores.estado_conductor%TYPE,
    wedad_conductor tab_conductores.edad%TYPE,
    wtipo_sangre_conductor tab_conductores.tipo_sangre%TYPE
)
RETURNS VARCHAR AS
$$
BEGIN
    IF wid_conductor IS NULL OR wid_conductor <= 0 THEN
        RETURN 'error: el ID del conductor no puede ser nulo o menor/igual a 0';
    END IF;

    IF wnom_conductor IS NULL OR LENGTH(wnom_conductor) < 3 THEN
        RETURN 'error: el nombre del conductor no puede ser nulo o tener menos de 3 letras';
    END IF;

    IF wape_conductor IS NULL OR LENGTH(wape_conductor) < 3 THEN
        RETURN 'error: el apellido del conductor no puede ser nulo o tener menos de 3 letras';
    END IF;

    IF wemail_conductor IS NULL THEN
        RETURN 'error: el email del conductor no puede ser nulo';
    END IF;

    IF wlicencia_conductor IS NULL THEN
        RETURN 'error: la licencia del conductor no puede ser nula';
    END IF;

    IF wtipo_licencia NOT IN ('C1', 'C2', 'C3') THEN
        RETURN 'error: el tipo de licencia debe ser C1, C2 o C3';
    END IF;

    IF wfec_ven_licencia IS NULL OR wfec_ven_licencia < CURRENT_DATE THEN
        RETURN 'error: la fecha de vencimiento de la licencia no puede ser nula o anterior a hoy';
    END IF;

    IF westado_conductor NOT IN ('A', 'S', 'R') THEN
        RETURN 'error: el estado del conductor debe ser A (activo), S (suspendido) o R (retirado)';
    END IF;

    IF wedad_conductor IS NULL OR wedad_conductor < 18 THEN
        RETURN 'error: la edad del conductor no puede ser nula o menor a 18 años';
    END IF;

    IF wtipo_sangre_conductor NOT IN ('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') THEN
        RETURN 'error: el tipo de sangre debe ser A+, A-, B+, B-, AB+, AB-, O+ u O-';
    END IF;

    UPDATE tab_conductores
    SET
        nom_conductor = wnom_conductor,
        ape_conductor = wape_conductor,
        email_conductor = wemail_conductor,
        licencia_conductor = wlicencia_conductor,
        tipo_licencia = wtipo_licencia,
        fec_venc_licencia = wfec_ven_licencia,
        estado_conductor = westado_conductor,
        edad = wedad_conductor,
        tipo_sangre = wtipo_sangre_conductor
    WHERE id_conductor = wid_conductor AND fec_delete IS NULL;
    
    IF FOUND THEN
        RETURN 'Conductor actualizado correctamente.';
    ELSE
        RETURN 'Error: no existe un conductor con ese ID';
    END IF;

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RETURN 'Error: se intentó insertar un valor nulo';

    WHEN SQLSTATE '23505' THEN
        RETURN 'Error: registro duplicado';

    WHEN SQLSTATE '22001' THEN
        RETURN 'Error: algún campo excede la longitud máxima';

    WHEN others THEN
        RETURN 'Error desconocido: ' || SQLERRM;
END;
$$
LANGUAGE PLPGSQL;