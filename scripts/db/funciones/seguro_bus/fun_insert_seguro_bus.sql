CREATE OR REPLACE FUNCTION fun_insert_seguro_bus(
    wid_bus tab_seguro_bus.id_bus%TYPE,
    wsoat_num tab_seguro_bus.soat_num%TYPE,
    wsoat_venc tab_seguro_bus.soat_venc%TYPE,
    wsoat_aseguradora tab_seguro_bus.soat_aseguradora%TYPE,
    wrcc_num_poliza tab_seguro_bus.rcc_num_poliza%TYPE,
    wrcc_venc tab_seguro_bus.rcc_venc%TYPE,
    wrce_num_poliza tab_seguro_bus.rce_num_poliza%TYPE,
    wrce_venc tab_seguro_bus.rce_venc%TYPE,
    wexceso_num_poliza tab_seguro_bus.exceso_num_poliza%TYPE,
    wexceso_vencimiento tab_seguro_bus.exceso_vencimiento%TYPE,
    wexceso_valor_cobertura tab_seguro_bus.exceso_valor_cobertura%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    -- Validaciones
    IF wid_bus IS NULL OR wid_bus <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '23502';
    END IF;

    IF wsoat_num IS NULL OR LENGTH(TRIM(wsoat_num)) != 17 THEN
        RAISE EXCEPTION USING ERRCODE = '22001';
    END IF;

    IF wsoat_venc IS NULL OR wsoat_venc <= CURRENT_DATE THEN
        RAISE EXCEPTION USING ERRCODE = '22002';
    END IF;

    IF wsoat_aseguradora IS NULL OR LENGTH(TRIM(wsoat_aseguradora)) < 3 THEN
        RAISE EXCEPTION USING ERRCODE = '22003';
    END IF;

    IF wrcc_num_poliza IS NULL OR LENGTH(TRIM(wrcc_num_poliza)) != 12 THEN
        RAISE EXCEPTION USING ERRCODE = '22004';
    END IF;

    IF wrcc_venc IS NULL OR wrcc_venc <= CURRENT_DATE THEN
        RAISE EXCEPTION USING ERRCODE = '22005';
    END IF;

    IF wrce_num_poliza IS NULL OR LENGTH(TRIM(wrce_num_poliza)) < 5 THEN
        RAISE EXCEPTION USING ERRCODE = '22006';
    END IF;

    IF wrce_venc IS NULL OR wrce_venc <= CURRENT_DATE THEN
        RAISE EXCEPTION USING ERRCODE = '22007';
    END IF;

    IF wexceso_vencimiento IS NULL OR wexceso_vencimiento <= CURRENT_DATE THEN
        RAISE EXCEPTION USING ERRCODE = '22009';
    END IF;

    IF wexceso_valor_cobertura IS NULL OR wexceso_valor_cobertura <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '22010';
    END IF;

    -- Insertar el registro
    INSERT INTO tab_seguro_bus(
        id_bus, soat_num, soat_venc, soat_aseguradora, rcc_num_poliza, rcc_venc,
        rce_num_poliza, rce_venc, exceso_num_poliza, exceso_vencimiento, exceso_valor_cobertura
    ) VALUES (
        wid_bus, wsoat_num, wsoat_venc, wsoat_aseguradora, wrcc_num_poliza, wrcc_venc,
        wrce_num_poliza, wrce_venc, wexceso_num_poliza, wexceso_vencimiento, wexceso_valor_cobertura
    );

    RAISE NOTICE 'Seguro del bus insertado correctamente';
    RETURN TRUE;

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'Campos obligatorios no pueden ser nulos';
        RETURN FALSE;
    WHEN SQLSTATE '22001' THEN
        RAISE NOTICE 'El número SOAT debe tener exactamente 17 caracteres';
        RETURN FALSE;
    WHEN SQLSTATE '22002' THEN
        RAISE NOTICE 'La fecha de vencimiento del SOAT debe ser posterior a hoy';
        RETURN FALSE;
    WHEN SQLSTATE '22003' THEN
        RAISE NOTICE 'La aseguradora del SOAT debe tener al menos 3 caracteres';
        RETURN FALSE;
    WHEN SQLSTATE '22004' THEN
        RAISE NOTICE 'El número de póliza RCC debe tener exactamente 12 caracteres';
        RETURN FALSE;
    WHEN SQLSTATE '22005' THEN
        RAISE NOTICE 'La fecha de vencimiento del RCC debe ser posterior a hoy';
        RETURN FALSE;
    WHEN SQLSTATE '22006' THEN
        RAISE NOTICE 'El número de póliza RCE debe tener al menos 5 caracteres';
        RETURN FALSE;
    WHEN SQLSTATE '22007' THEN
        RAISE NOTICE 'La fecha de vencimiento del RCE debe ser posterior a hoy';
        RETURN FALSE;
    WHEN SQLSTATE '22008' THEN
        RAISE NOTICE 'El número de póliza en exceso debe tener entre 10 y 15 caracteres';
        RETURN FALSE;
    WHEN SQLSTATE '22009' THEN
        RAISE NOTICE 'La fecha de vencimiento del exceso debe ser posterior a hoy';
        RETURN FALSE;
    WHEN SQLSTATE '22010' THEN
        RAISE NOTICE 'El valor de cobertura en exceso debe ser mayor a 0';
        RETURN FALSE;
    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'Ya existe un registro de seguro para este bus';
        RETURN FALSE;
    WHEN SQLSTATE '23503' THEN
        RAISE NOTICE 'El bus especificado no existe';
        RETURN FALSE;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;