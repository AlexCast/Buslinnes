CREATE OR REPLACE FUNCTION fun_listar_incidentes(wid_incidente INT)
RETURNS BOOLEAN AS
$$
DECLARE
    wreg_incidente tab_incidentes%ROWTYPE;
BEGIN
    IF wid_incidente <= 0 THEN
        RAISE NOTICE 'El ID del incidente no puede ser menor o igual a 0.';
        RETURN FALSE;
    END IF;

    SELECT id_incidente, titulo_incidente, desc_incidente, id_bus, id_conductor, tipo_incidente
    INTO wreg_incidente
    FROM tab_incidentes
        WHERE id_incidente = wid_incidente
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No se encontró el incidente con ID %.', wid_incidente;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Título: %, Descripción: %, ID Bus: %, ID Conductor: %, Tipo: %',
        wreg_incidente.id_incidente,
        wreg_incidente.titulo_incidente,
        wreg_incidente.desc_incidente,
        wreg_incidente.id_bus,
        wreg_incidente.id_conductor,
        wreg_incidente.tipo_incidente;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE PLPGSQL;
