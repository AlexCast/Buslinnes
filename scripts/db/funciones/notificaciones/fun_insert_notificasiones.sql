CREATE OR REPLACE FUNCTION fun_insert_notificaciones(wid_notificacion tab_notificaciones.id_notificacion%TYPE,
                                                      wid_usuario tab_usuarios.id_usuario%TYPE,
                                                      wid_rol tab_roles.id_rol%TYPE,
                                                      wtitulo_notificacion tab_notificaciones.titulo_notificacion%TYPE,
                                                      wdescr_notificacion tab_notificaciones.descr_notificacion%TYPE) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_notificacion is null or wid_notificacion <0 THEN
        RETURN FALSE;
    END IF;

    INSERT INTO tab_notificaciones(id_notificacion, id_usuario, id_rol, titulo_notificacion, descr_notificacion, usr_insert, fec_insert)
    VALUES (wid_notificacion, wid_usuario, wid_rol, wtitulo_notificacion, wdescr_notificacion, CURRENT_USER, CURRENT_TIMESTAMP);
    
    RETURN 'Inserción exitosa';

 EXCEPTION
            WHEN SQLSTATE '23502' THEN  
				RETURN FALSE;

			WHEN SQLSTATE '23505' THEN  
				RETURN FALSE;

            WHEN SQLSTATE '22001' THEN  
				RETURN FALSE;

			WHEN OTHERS THEN
					RETURN FALSE;
END;
$$ 
LANGUAGE PLPGSQL;