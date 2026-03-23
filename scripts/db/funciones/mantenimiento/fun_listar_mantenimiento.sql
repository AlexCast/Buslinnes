CREATE OR REPLACE FUNCTION fun_listar_mantenimiento(wid_mantenimiento tab_mantenimiento.id_mantenimiento%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_mantenimiento tab_mantenimiento%ROWTYPE; -- Usamos %ROWTYPE para manejar toda la fila
BEGIN
    IF wid_mantenimiento <= 0 THEN
        RAISE NOTICE 'El ID de mantenimiento no puede ser menor o igual a 0.';
        RETURN FALSE;
    END IF;

    -- Obtener el mantenimiento
    SELECT id_mantenimiento, id_bus, descripcion, fecha_mantenimiento, costo_mantenimiento
    INTO wreg_mantenimiento
    FROM tab_mantenimiento
        WHERE id_mantenimiento = wid_mantenimiento
            AND fec_delete IS NULL;

    -- Verificar si se encontró un registro
    IF NOT FOUND THEN
        RAISE NOTICE 'El mantenimiento con ID % no existe.', wid_mantenimiento;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Nombre: %', wreg_mantenimiento.id_mantenimiento, wreg_mantenimiento.descripcion;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;


--SELECT fun_listar_mantenimiento(1)
--SELECT * FROM tab_mantenimiento