-- ================================================
-- RESTORE - BUSLINNES
-- En orden directo al insert para respetar FKs
-- ================================================

-- 1. Usuarios
SELECT fun_restore_usuarios(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario1.buslinnes@example.com' LIMIT 1)
);
SELECT fun_restore_usuarios(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario2.buslinnes@example.com' LIMIT 1)
);
SELECT fun_restore_usuarios(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'conductor1.buslinnes@example.com' LIMIT 1)
);
SELECT fun_restore_usuarios(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'conductor2.buslinnes@example.com' LIMIT 1)
);

-- 2. Rutas
SELECT fun_restore_rutas(600);
SELECT fun_restore_rutas(601);

-- 3. Conductores
SELECT fun_restore_conductores(
    (SELECT id_conductor FROM tab_conductores WHERE email_conductor = 'conductor1.buslinnes@example.com' LIMIT 1)
);
SELECT fun_restore_conductores(
    (SELECT id_conductor FROM tab_conductores WHERE email_conductor = 'conductor2.buslinnes@example.com' LIMIT 1)
);

-- 4. Pasajeros
SELECT fun_restore_pasajeros(
    (SELECT id_usuario FROM tab_pasajeros WHERE email_pasajero = 'usuario1.buslinnes@example.com' LIMIT 1)
);
SELECT fun_restore_pasajeros(
    (SELECT id_usuario FROM tab_pasajeros WHERE email_pasajero = 'usuario2.buslinnes@example.com' LIMIT 1)
);

-- 5. Buses
SELECT fun_restore_buses(500);
SELECT fun_restore_buses(501);

-- 6. Propietarios
SELECT fun_restore_propietarios(1000000210);
SELECT fun_restore_propietarios(1000000211);

-- 7. Parque automotor
SELECT fun_restore_parque_automotor(700);
SELECT fun_restore_parque_automotor(701);

-- 8. Mantenimiento
SELECT fun_restore_mantenimiento(800);
SELECT fun_restore_mantenimiento(801);

-- 9. Incidentes
SELECT fun_restore_incidentes(1200);
SELECT fun_restore_incidentes(1201);

-- 10. Cambio de bus
SELECT fun_restore_cambio_bus(1300);
SELECT fun_restore_cambio_bus(1301);

-- 11. Notificaciones
SELECT fun_restore_notificaciones(1400);
SELECT fun_restore_notificaciones(1401);

-- 12. Ruta-Bus
SELECT fun_restore_ruta_bus(1100);
SELECT fun_restore_ruta_bus(1101);

-- 13. Rutas favoritas
-- No aplica restore: tab_rutas_favoritas no maneja borrado lógico.

-- 14. Usuarios-Roles
SELECT fun_restore_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario1.buslinnes@example.com' LIMIT 1),
    1
);
SELECT fun_restore_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario1.buslinnes@example.com' LIMIT 1),
    3
);
SELECT fun_restore_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario2.buslinnes@example.com' LIMIT 1),
    3
);
SELECT fun_restore_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'conductor1.buslinnes@example.com' LIMIT 1),
    2
);
SELECT fun_restore_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'conductor2.buslinnes@example.com' LIMIT 1),
    2
);
