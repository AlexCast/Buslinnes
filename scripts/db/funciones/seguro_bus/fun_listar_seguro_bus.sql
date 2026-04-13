CREATE OR REPLACE FUNCTION fun_listar_seguro_bus(wid_control INT)
RETURNS BOOLEAN AS
$$
DECLARE
    wreg_seguro_bus tab_seguro_bus%ROWTYPE;
BEGIN
    IF wid_control IS NULL OR wid_control <= 0 THEN
        RAISE NOTICE 'El ID de seguro no puede ser nulo o menor o igual a 0.';
        RETURN FALSE;
    END IF;

    SELECT id_control, id_bus, soat_num, soat_venc, soat_aseguradora,
           rcc_num_poliza, rcc_venc, rce_num_poliza, rce_venc,
           exceso_num_poliza, exceso_vencimiento, exceso_valor_cobertura
    INTO wreg_seguro_bus
    FROM tab_seguro_bus
    WHERE id_control = wid_control
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No se encontró el seguro de bus con ID %.', wid_control;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID Control: %, ID Bus: %, SOAT: % (venc %), Aseguradora SOAT: %, RCC: % (venc %), RCE: % (venc %), Exceso: % (venc %, cobertura %)',
        wreg_seguro_bus.id_control,
        wreg_seguro_bus.id_bus,
        wreg_seguro_bus.soat_num,
        wreg_seguro_bus.soat_venc,
        wreg_seguro_bus.soat_aseguradora,
        wreg_seguro_bus.rcc_num_poliza,
        wreg_seguro_bus.rcc_venc,
        wreg_seguro_bus.rce_num_poliza,
        wreg_seguro_bus.rce_venc,
        wreg_seguro_bus.exceso_num_poliza,
        wreg_seguro_bus.exceso_vencimiento,
        wreg_seguro_bus.exceso_valor_cobertura;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error al listar seguro de bus: %', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE PLPGSQL;