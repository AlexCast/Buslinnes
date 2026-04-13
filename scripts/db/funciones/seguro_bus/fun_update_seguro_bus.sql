CREATE OR REPLACE FUNCTION fun_update_seguro_bus(
    wid_control tab_seguro_bus.id_control%TYPE,
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
DECLARE
    wreg_seguro_bus RECORD;
BEGIN
    -- Validaciones iniciales
    IF wid_control IS NULL OR wid_control <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '23502';
    END IF;

    IF wid_bus IS NULL OR wid_bus <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '22001';
    END IF;

    IF wsoat_num IS NULL OR LENGTH(TRIM(wsoat_num)) != 17 THEN
        RAISE EXCEPTION USING ERRCODE = '22002';
    END IF;

    IF wsoat_venc IS NULL OR wsoat_venc <= CURRENT_DATE THEN
        RAISE EXCEPTION USING ERRCODE = '22003';
    END IF;

    IF wsoat_aseguradora IS NULL OR LENGTH(TRIM(wsoat_aseguradora)) < 3 THEN
        RAISE EXCEPTION USING ERRCODE = '22004';
    END IF;

    IF wrcc_num_poliza IS NULL OR LENGTH(TRIM(wrcc_num_poliza)) != 12 THEN
        RAISE EXCEPTION USING ERRCODE = '22005';
    END IF;

    IF wrcc_venc IS NULL OR wrcc_venc <= CURRENT_DATE THEN
        RAISE EXCEPTION USING ERRCODE = '22006';
    END IF;

    IF wrce_num_poliza IS NULL OR LENGTH(TRIM(wrce_num_poliza)) < 5 THEN
        RAISE EXCEPTION USING ERRCODE = '22007';
    END IF;

    IF wrce_venc IS NULL OR wrce_venc <= CURRENT_DATE THEN
        RAISE EXCEPTION USING ERRCODE = '22008';
    END IF;

    IF wexceso_vencimiento IS NULL OR wexceso_vencimiento <= CURRENT_DATE THEN
        RAISE EXCEPTION USING ERRCODE = '22010';
    END IF;

    IF wexceso_valor_cobertura IS NULL OR wexceso_valor_cobertura <= 0 THEN
        RAISE EXCEPTION USING ERRCODE = '22011';
    END IF;

    -- Verificar si existe el registro y no está borrado
    SELECT id_control INTO wreg_seguro_bus
    FROM tab_seguro_bus
    WHERE id_control = wid_control AND fec_delete IS NULL;

    IF FOUND THEN
        UPDATE tab_seguro_bus SET
            id_bus = wid_bus,
            soat_num = wsoat_num,
            soat_venc = wsoat_venc,
            soat_aseguradora = wsoat_aseguradora,
            rcc_num_poliza = wrcc_num_poliza,
            rcc_venc = wrcc_venc,
            rce_num_poliza = wrce_num_poliza,
            rce_venc = wrce_venc,
            exceso_num_poliza = wexceso_num_poliza,
            exceso_vencimiento = wexceso_vencimiento,
            exceso_valor_cobertura = wexceso_valor_cobertura
        WHERE id_control = wid_control AND fec_delete IS NULL;

        RAISE NOTICE 'Seguro de bus con ID % actualizado correctamente', wid_control;
        RETURN TRUE;
    ELSE
        RAISE EXCEPTION USING ERRCODE = '23505';
    END IF;

EXCEPTION
    WHEN SQLSTATE '23502' THEN
        RAISE NOTICE 'El ID de seguro no puede ser nulo o menor/igual a 0';
        RETURN FALSE;
    WHEN SQLSTATE '22001' THEN
        RAISE NOTICE 'El ID del bus no puede ser nulo o menor/igual a 0';
        RETURN FALSE;
    WHEN SQLSTATE '22002' THEN
        RAISE NOTICE 'El número de SOAT debe tener exactamente 17 caracteres';
        RETURN FALSE;
    WHEN SQLSTATE '22003' THEN
        RAISE NOTICE 'La fecha de vencimiento del SOAT debe ser futura';
        RETURN FALSE;
    WHEN SQLSTATE '22004' THEN
        RAISE NOTICE 'La aseguradora del SOAT debe tener al menos 3 caracteres';
        RETURN FALSE;
    WHEN SQLSTATE '22005' THEN
        RAISE NOTICE 'El número de póliza RCC debe tener exactamente 12 caracteres';
        RETURN FALSE;
    WHEN SQLSTATE '22006' THEN
        RAISE NOTICE 'La fecha de vencimiento del RCC debe ser futura';
        RETURN FALSE;
    WHEN SQLSTATE '22007' THEN
        RAISE NOTICE 'El número de póliza RCE debe tener al menos 5 caracteres';
        RETURN FALSE;
    WHEN SQLSTATE '22008' THEN
        RAISE NOTICE 'La fecha de vencimiento del RCE debe ser futura';
        RETURN FALSE;
    WHEN SQLSTATE '22010' THEN
        RAISE NOTICE 'La fecha de vencimiento del exceso debe ser futura';
        RETURN FALSE;
    WHEN SQLSTATE '22011' THEN
        RAISE NOTICE 'El valor de cobertura en exceso debe ser mayor a 0';
        RETURN FALSE;
    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'El seguro de bus con ID % no existe o ya está eliminado', wid_control;
        RETURN FALSE;
    WHEN SQLSTATE '23503' THEN
        RAISE NOTICE 'El bus especificado no existe';
        RETURN FALSE;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error no esperado: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;