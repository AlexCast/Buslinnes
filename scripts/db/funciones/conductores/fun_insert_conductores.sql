CREATE OR REPLACE FUNCTION fun_insert_conductores
(
    wid_conductor tab_conductores.id_conductor%TYPE,
    wnom_conductor tab_conductores.nom_conductor%TYPE,
    wape_conductor tab_conductores.ape_conductor%TYPE,
    wemail_conductor tab_conductores.email_conductor%TYPE,
    wlicencia_conductor tab_conductores.licencia_conductor%TYPE,
    wtipo_licencia tab_conductores.tipo_licencia%TYPE,
    wfec_venc_licencia tab_conductores.fec_venc_licencia%TYPE,
    westado_conductor tab_conductores.estado_conductor%TYPE,
    wedad tab_conductores.edad%TYPE,
    wtipo_sangre tab_conductores.tipo_sangre%TYPE
)
RETURNS VARCHAR AS
$$
BEGIN
    IF wid_conductor IS NULL OR wid_conductor <= 0 THEN
        RETURN 'error: el ID del conductor no puede ser nulo o menor a 1';
    END IF;

    IF wnom_conductor IS NULL OR LENGTH(wnom_conductor) < 3 THEN 
        RETURN 'error: el NOMBRE del conductor no puede ser nulo o tener menos de 3 letras ';
    END IF;
    
    IF wape_conductor IS NULL OR LENGTH(wape_conductor) < 3 THEN 
        RETURN 'error: el APELLIDO del conductor no puede ser nulo o tener menos de 3 letras ';
    END IF;

    If wemail_conductor IS NULL THEN 
        RETURN 'error: el EMAIL del conductor no puede ser nulo';
    END IF;

    IF wlicencia_conductor IS NULL THEN
        RETURN 'error: la LICENCIA del conductor no puede ser nulo ';
    END IF;

    IF wtipo_licencia NOT IN ('C1', 'C2', 'C3') THEN
        RETURN 'error: el tipo de licencia debe ser C1 (automoviles de servicio publico), C2 (camiones y buses) o C3 (vehiculo articulados o pesados)';
    END IF;
    
    IF wfec_venc_licencia IS NULL OR wfec_venc_licencia < CURRENT_DATE THEN
        RETURN 'error: la FECHA DE VENCIMIENTO de la licencia no puede ser nula o haber vencido ';
    END IF;

    IF westado_conductor NOT IN ('A', 'S', 'R') THEN
        RETURN 'error: el estado del conductor debe ser A (activo), S (suspendido) o R (retirado)';
    END IF;

    IF wedad IS NULL OR wedad < 18 THEN
        RETURN 'error: la EDAD del conductor no puede ser nula o menor a 18 años ';
    END IF;

    IF wtipo_sangre NOT IN ('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') THEN
        RETURN 'error: el tipo de sangre del conductor debe ser A+, A-, B+, B-, AB+, AB-, O+ u O-';
    END IF;

    INSERT INTO tab_conductores (id_conductor, nom_conductor, ape_conductor, email_conductor, licencia_conductor, tipo_licencia, fec_venc_licencia, estado_conductor, edad, tipo_sangre)
    VALUES (wid_conductor, wnom_conductor, wape_conductor, wemail_conductor, wlicencia_conductor, wtipo_licencia, wfec_venc_licencia, westado_conductor, wedad, wtipo_sangre);

    RAISE NOTICE 'ya se inserto el CONDUCTOR  % % % % % % % % % %',
                wid_conductor, wnom_conductor, wape_conductor, wemail_conductor, wlicencia_conductor, wtipo_licencia,
                wfec_venc_licencia, westado_conductor, wedad, wtipo_sangre;
    RETURN  'Conductor insertado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RETURN 'Error: se intentó insertar un valor nulo';

    WHEN SQLSTATE '23505' THEN
        RETURN 'Error: el registro ya existe';

    WHEN SQLSTATE '22001' THEN
        RETURN 'Error: algún campo excede la longitud máxima';

    WHEN others THEN
       RETURN 'Error desconocido: ' || SQLERRM;
END;
$$ 
LANGUAGE PLPGSQL;