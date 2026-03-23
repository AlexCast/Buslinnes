-- ================================================
-- UPDATE - BUSLINNES
-- ================================================

-- Usuarios (ID resuelto por correo, firma: id, contrasena, correo, nombre, usr_update)
SELECT fun_update_usuarios(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    'ClaveNueva123', 'usuario1.buslinnes@example.com', 'Usuario Uno Actualizado', 'system'
);
SELECT fun_update_usuarios(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    'ClaveNueva456', 'usuario2.buslinnes@example.com', 'Usuario Dos Actualizado', 'system'
);

-- Roles
SELECT fun_update_roles(1, 'admin');
SELECT fun_update_roles(2, 'conductor');
SELECT fun_update_roles(3, 'pasajero');

-- Rutas
SELECT fun_update_rutas(600, 'Ruta Norte Modificada', '06:30', '22:30', 'Terminal A1', 'Terminal B1', 16, 2600);
SELECT fun_update_rutas(601, 'Ruta Sur Ampliada', '05:15', '21:45', 'Terminal C1', 'Terminal D1', 13, 2300);

-- Conductores (usar id_conductor, no id_usuario)
SELECT fun_update_conductores(
    (SELECT id_conductor FROM tab_conductores WHERE email_conductor = 'conductor1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    'Carlos Actualizado', 'Ramirez Torres', 'conductor1.buslinnes@example.com', 'LIC99999', 'C2', '2029-06-30', 'A', 35, 'O+'
);
SELECT fun_update_conductores(
    (SELECT id_conductor FROM tab_conductores WHERE email_conductor = 'conductor2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    'Andres Actualizado', 'Morales Perez', 'conductor2.buslinnes@example.com', 'LIC88888', 'C3', '2030-11-30', 'A', 40, 'A+'
);

-- Pasajeros
SELECT fun_update_pasajeros(
    (SELECT id_usuario FROM tab_pasajeros WHERE email_pasajero = 'usuario1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    'Usuario Uno Pasajero', 'usuario1.buslinnes@example.com'
);
SELECT fun_update_pasajeros(
    (SELECT id_usuario FROM tab_pasajeros WHERE email_pasajero = 'usuario2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    'Usuario Dos Pasajero', 'usuario2.buslinnes@example.com'
);

-- Buses (id_conductor resuelto por email_conductor)
SELECT fun_update_buses(
    500,
    (SELECT id_conductor FROM tab_conductores WHERE email_conductor = 'conductor1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    'CHS98765432100001', 'ABC999', 2024, 50, 'M', TRUE, 'A'
);
SELECT fun_update_buses(
    501,
    (SELECT id_conductor FROM tab_conductores WHERE email_conductor = 'conductor2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    'CHS98765432100002', 'DEF999', 2023, 42, 'A', FALSE, 'A'
);

-- Propietarios
SELECT fun_update_propietarios(1000000210, 500, 'Luis Fernando', 'Martinez Gomez', 3115556666, 'lf.martinez@example.com');
SELECT fun_update_propietarios(1000000211, 501, 'Diana Patricia', 'Suarez Vega', 3128889901, 'diana.suarez@example.com');

-- Parque automotor
SELECT fun_update_parque_automotor(700, 500, 'Av. Nueva 90 #12-34');
SELECT fun_update_parque_automotor(701, 501, 'Carrera 68 #30-50');

-- Mantenimiento
SELECT fun_update_mantenimiento(800, 500, 'Revision general completa', '2026-02-10 09:00:00'::timestamp, 180000);
SELECT fun_update_mantenimiento(801, 501, 'Cambio de llantas y frenos', '2026-02-12 11:00:00'::timestamp, 220000);

-- Incidentes
SELECT fun_update_incidentes(1200, 'pichazo actualizado', 'Pinchazo atendido en via principal', 500, 100001, 'O');
SELECT fun_update_incidentes(1201, 'falla electrica actualizada', 'Falla electrica reparada', 501, 100002, 'E');

-- Cambio de bus
SELECT fun_update_cambio_bus(1300, 1200, 501, 'Estacion Norte Actualizada');
SELECT fun_update_cambio_bus(1301, 1201, 500, 'Terminal Central Actualizada');

-- Notificaciones
-- Se usa un solo destino por notificación: usuario o rol.
SELECT fun_update_notificaciones(
    1400,
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1),
    NULL,
    'Aviso de ruta actualizado',
    'La ruta 600 ya fue normalizada.'
);
SELECT fun_update_notificaciones(
    1401,
    NULL,
    (SELECT id_rol FROM tab_roles WHERE nombre_rol = 'conductor' AND fec_delete IS NULL LIMIT 1),
    'Recordatorio actualizado',
    'Su perfil fue actualizado exitosamente.'
);

-- Ruta-Bus
SELECT fun_update_ruta_bus(1100, 601, 500);
SELECT fun_update_ruta_bus(1101, 600, 501);

-- Rutas favoritas
SELECT fun_update_rutas_favoritas(
    1600,
    (SELECT id_usuario FROM tab_pasajeros WHERE email_pasajero = 'usuario1.buslinnes@example.com' LIMIT 1),
    601
);
SELECT fun_update_rutas_favoritas(
    1601,
    (SELECT id_usuario FROM tab_pasajeros WHERE email_pasajero = 'usuario2.buslinnes@example.com' LIMIT 1),
    600
);