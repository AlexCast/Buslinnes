CREATE OR REPLACE FUNCTION fun_insert_conductores(
    wid_usuario tab_conductores.id_usuario%TYPE,
    wnom_conductor tab_conductores.nom_conductor%TYPE,
    wape_conductor tab_conductores.ape_conductor%TYPE,
    wgenero_conductor tab_conductores.genero_conductor%TYPE,
    wemail_conductor tab_conductores.email_conductor%TYPE,
    wlicencia_conductor tab_conductores.licencia_conductor%TYPE,
    wtipo_licencia tab_conductores.tipo_licencia%TYPE,
    wfec_venc_licencia tab_conductores.fec_venc_licencia%TYPE,
    westado_conductor tab_conductores.estado_conductor%TYPE,
    wfec_nacimiento tab_conductores.fec_nacimiento%TYPE,
    wtipo_sangre tab_conductores.tipo_sangre%TYPE,
    wperfil_completdo tab_conductores.perfil_completdo%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    -- Validar que el ID del usuario no sea nulo o menor a 1
    IF wid_usuario IS NULL OR wid_usuario <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '23502';
    END IF;

    -- Validar nombre
    IF wnom_conductor IS NULL OR LENGTH(wnom_conductor) < 3 THEN 
        RAISE EXCEPTION USING ERRCODE = '22001';
    END IF;
    
    -- Validar apellido
    IF wape_conductor IS NULL OR LENGTH(wape_conductor) < 3 THEN 
        RAISE EXCEPTION USING ERRCODE = '22002';
    END IF;

    -- Validar género
    IF wgenero_conductor NOT IN ('M', 'F', 'O') THEN
        RAISE EXCEPTION USING ERRCODE = '22003';
    END IF;

    -- Validar email
    IF wemail_conductor IS NULL OR wemail_conductor !~ '^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+[.][A-Za-z]+$' THEN 
        RAISE EXCEPTION USING ERRCODE = '22004';
    END IF;

    -- Validar licencia (7 a 10 dígitos numéricos)
    IF wlicencia_conductor IS NULL OR LENGTH(wlicencia_conductor) < 7 OR LENGTH(wlicencia_conductor) > 10 THEN
        RAISE EXCEPTION USING ERRCODE = '22005';
    END IF;

    -- Validar tipo de licencia
    IF wtipo_licencia NOT IN ('C2', 'C3', 'T') THEN
        RAISE EXCEPTION USING ERRCODE = '22006';
    END IF;
    
    -- Validar fecha de vencimiento (puede ser NULL según schema)
    IF wfec_venc_licencia IS NOT NULL AND wfec_venc_licencia <= CURRENT_DATE THEN
        RAISE EXCEPTION USING ERRCODE = '22007';
    END IF;

    -- Validar estado del conductor
    IF westado_conductor NOT IN ('A', 'S', 'R', 'P') THEN
        RAISE EXCEPTION USING ERRCODE = '22008';
    END IF;

    -- Validar tipo de sangre (puede incluir 'PENDIENTE')
    IF wtipo_sangre NOT IN ('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'PENDIENTE') THEN
        RAISE EXCEPTION USING ERRCODE = '22009';
    END IF;

    -- Insertar el nuevo conductor y marcar perfil como completado
    INSERT INTO tab_conductores (
        id_usuario,
        nom_conductor,
        ape_conductor,
        genero_conductor,
        email_conductor,
        licencia_conductor,
        tipo_licencia,
        fec_venc_licencia,
        estado_conductor,
        fec_nacimiento,
        tipo_sangre,
        perfil_completdo
    )
    VALUES (
        wid_usuario,
        wnom_conductor,
        wape_conductor,
        wgenero_conductor,
        wemail_conductor,
        wlicencia_conductor,
        wtipo_licencia,
        wfec_venc_licencia,
        westado_conductor,
        wfec_nacimiento,
        wtipo_sangre,
        TRUE
    );

    RAISE NOTICE 'Conductor insertado correctamente';
    RETURN TRUE;

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'El ID del usuario no puede ser nulo o menor a 1';
        RETURN FALSE;

    WHEN SQLSTATE '22001' THEN
        RAISE NOTICE 'El nombre del conductor no puede ser nulo o tener menos de 3 caracteres';
        RETURN FALSE;

    WHEN SQLSTATE '22002' THEN
        RAISE NOTICE 'El apellido del conductor no puede ser nulo o tener menos de 3 caracteres';
        RETURN FALSE;

    WHEN SQLSTATE '22003' THEN
        RAISE NOTICE 'El género del conductor debe ser M (masculino), F (femenino) u O (otro)';
        RETURN FALSE;

    WHEN SQLSTATE '22004' THEN
        RAISE NOTICE 'El email del conductor no puede ser nulo o debe tener formato válido';
        RETURN FALSE;

    WHEN SQLSTATE '22005' THEN
        RAISE NOTICE 'La licencia del conductor debe contener entre 7 y 10 digitos numericos';
        RETURN FALSE;

    WHEN SQLSTATE '22006' THEN
        RAISE NOTICE 'El tipo de licencia debe ser C2, C3 o T';
        RETURN FALSE;

    WHEN SQLSTATE '22007' THEN
        RAISE NOTICE 'La fecha de vencimiento de la licencia no debe ser anterior a hoy';
        RETURN FALSE;

    WHEN SQLSTATE '22008' THEN
        RAISE NOTICE 'El estado del conductor debe ser A (activo), S (suspendido), R (retirado) o P (pendiente)';
        RETURN FALSE;

    WHEN SQLSTATE '22009' THEN
        RAISE NOTICE 'El tipo de sangre debe ser A+, A-, B+, B-, AB+, AB-, O+, O- o PENDIENTE';
        RETURN FALSE;

    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'El email del conductor ya está registrado o el usuario ya existe como conductor';
        RETURN FALSE;

    WHEN OTHERS THEN
       RAISE NOTICE 'Error no esperado: %', SQLERRM;
       RETURN FALSE;
END;
$$ 
LANGUAGE PLPGSQL;