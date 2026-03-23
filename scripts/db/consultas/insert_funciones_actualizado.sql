-- ================================================
-- DATOS DE PRUEBA - BUSLINNES
-- Los roles admin(1), conductor(2), pasajero(3)
-- ya existen desde el modelo; no se re-insertan.
-- ================================================

-- Usuarios base
SELECT public.fun_insert_usuario('ClaveSegura123', 'usuario1.buslinnes@example.com', 'Usuario Uno');
SELECT public.fun_insert_usuario('ClaveSegura456', 'usuario2.buslinnes@example.com', 'Usuario Dos');
SELECT public.fun_insert_usuario('ClaveSegura789', 'conductor1.buslinnes@example.com', 'Carlos Ramirez');
SELECT public.fun_insert_usuario('ClaveSegura012', 'conductor2.buslinnes@example.com', 'Andres Morales');

-- Usuarios-Roles
-- El rol pasajero crea automáticamente el perfil en tab_pasajeros.
SELECT fun_insert_tab_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    3,
    'system'
);
SELECT fun_insert_tab_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    3,
    'system'
);
SELECT fun_insert_tab_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'conductor1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    2,
    'system'
);
SELECT fun_insert_tab_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'conductor2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    2,
    'system'
);
SELECT fun_insert_tab_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    1,
    'system'
);

-- Rutas
SELECT fun_insert_rutas(600, 'Ruta Norte', '06:00:00', '22:00:00', 'Terminal Norte', 'Terminal Centro', 16, 2800);
SELECT fun_insert_rutas(601, 'Ruta Sur', '05:30:00', '21:30:00', 'Terminal Sur', 'Terminal Centro', 12, 2600);

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM tab_rutas WHERE id_ruta = 600 AND fec_delete IS NULL) THEN
        RAISE EXCEPTION 'No existe la ruta 600; revise fun_insert_rutas antes de continuar.';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM tab_rutas WHERE id_ruta = 601 AND fec_delete IS NULL) THEN
        RAISE EXCEPTION 'No existe la ruta 601; revise fun_insert_rutas antes de continuar.';
    END IF;
END $$;

-- Conductores
-- Nota: tab_conductores suele exigir IDs altos en el modelo (ej. > 100000),
-- por eso se usan IDs fijos de prueba en lugar del id_usuario secuencial.
SELECT fun_insert_conductores(
    100001,
    'Carlos', 'Ramirez', 'conductor1.buslinnes@example.com', 'LIC10001', 'C2', '2028-12-31', 'A', 34, 'O+'
);
SELECT fun_insert_conductores(
    100002,
    'Andres', 'Morales', 'conductor2.buslinnes@example.com', 'LIC10002', 'C3', '2029-11-30', 'A', 39, 'A+'
);

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM tab_conductores WHERE id_conductor = 100001 AND fec_delete IS NULL) THEN
        RAISE EXCEPTION 'No existe el conductor 100001; revise fun_insert_conductores antes de continuar.';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM tab_conductores WHERE id_conductor = 100002 AND fec_delete IS NULL) THEN
        RAISE EXCEPTION 'No existe el conductor 100002; revise fun_insert_conductores antes de continuar.';
    END IF;
END $$;

-- Pasajeros
-- Los perfiles se crean automáticamente al asignar el rol pasajero.
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM tab_pasajeros
        WHERE id_usuario = (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1)
          AND fec_delete IS NULL
    ) THEN
        RAISE EXCEPTION 'No se creó el perfil de pasajero para usuario1.buslinnes@example.com.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM tab_pasajeros
        WHERE id_usuario = (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1)
          AND fec_delete IS NULL
    ) THEN
        RAISE EXCEPTION 'No se creó el perfil de pasajero para usuario2.buslinnes@example.com.';
    END IF;
END $$;

-- Buses (id_conductor resuelto desde tab_conductores por email)
SELECT fun_insert_buses(
    500,
    (SELECT id_conductor FROM tab_conductores WHERE email_conductor = 'conductor1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    'CHS00000000000001', 'ABC123', 2022, 40, 'U', TRUE, 'L'
);
SELECT fun_insert_buses(
    501,
    (SELECT id_conductor FROM tab_conductores WHERE email_conductor = 'conductor2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    'CHS00000000000002', 'DEF456', 2023, 45, 'M', TRUE, 'A'
);

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM tab_buses WHERE id_bus = 500 AND fec_delete IS NULL) THEN
        RAISE EXCEPTION 'No existe el bus 500; revise fun_insert_buses antes de continuar.';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM tab_buses WHERE id_bus = 501 AND fec_delete IS NULL) THEN
        RAISE EXCEPTION 'No existe el bus 501; revise fun_insert_buses antes de continuar.';
    END IF;
END $$;

-- Propietarios
SELECT fun_insert_propietarios(1000000210, 500, 'Luis', 'Martinez', 3115556677, 'luis.martinez@example.com');
SELECT fun_insert_propietarios(1000000211, 501, 'Diana', 'Suarez', 3128889900, 'diana.suarez@example.com');

-- Parque automotor
SELECT fun_insert_parque_automotor(700, 500, 'Zona Industrial Calle 80 #12-34');
SELECT fun_insert_parque_automotor(701, 501, 'Parqueadero Central Carrera 15 #45-60');

-- Mantenimiento
SELECT fun_insert_mantenimiento(800, 500, 'Cambio de aceite y filtros', '2026-01-10 08:00:00', 350000);
SELECT fun_insert_mantenimiento(801, 501, 'Revision de frenos y suspension', '2026-01-12 10:30:00', 420000);

-- Incidentes
SELECT fun_insert_incidentes(1200, 'pichazo', 'Pinchazo en via principal', 500, 100001 , 'O');
SELECT fun_insert_incidentes(1201, 'falla electrica', 'Falla electrica menor', 501, 100002, 'E');
-- Cambio de bus
SELECT fun_insert_cambio_bus(1300, 1200, 501, 'Estacion Norte');
SELECT fun_insert_cambio_bus(1301, 1201, 500, 'Terminal Central');

-- Notificaciones
-- Se usa un destino por notificación: usuario o rol.
SELECT fun_insert_notificaciones(
    1400,
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    NULL,
    'Aviso de ruta',
    'La ruta 600 presenta desvio temporal.'
);
SELECT fun_insert_notificaciones(
    1401,
    NULL,
    (SELECT id_rol FROM tab_roles WHERE nombre_rol = 'conductor' AND fec_delete IS NULL LIMIT 1),
    'Recordatorio',
    'Actualice su informacion de perfil.'
);

-- Ruta-Bus
SELECT fun_insert_ruta_bus(1100, 600, 500);
SELECT fun_insert_ruta_bus(1101, 601, 501);

-- Rutas favoritas (id_pasajero resuelto desde tab_pasajeros por email)
SELECT fun_insert_rutas_favoritas(
    1600,
    (SELECT id_usuario FROM tab_pasajeros WHERE email_pasajero = 'usuario1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    600
);
SELECT fun_insert_rutas_favoritas(
    1601,
    (SELECT id_usuario FROM tab_pasajeros WHERE email_pasajero = 'usuario2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    601
);