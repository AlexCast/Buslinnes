-- ================================================
-- SOFT DELETE - BUSLINNES
-- En orden inverso al insert para respetar FKs
-- ================================================

-- 1. Cambio de bus
SELECT fun_softdelete_cambio_bus(1300);
SELECT fun_softdelete_cambio_bus(1301);

-- 2. Incidentes
SELECT fun_softdelete_incidentes(1200);
SELECT fun_softdelete_incidentes(1201);

-- 3. Notificaciones
SELECT fun_softdelete_notificaciones(1400);
SELECT fun_softdelete_notificaciones(1401);

-- 4. Rutas favoritas
-- En el esquema actual esta función elimina físicamente los registros.
SELECT fun_softdelete_rutas_favoritas(1600);
SELECT fun_softdelete_rutas_favoritas(1601);

-- 5. Usuarios-Roles
SELECT fun_softdelete_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario1.buslinnes@example.com' LIMIT 1),
    1
);
SELECT fun_softdelete_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario1.buslinnes@example.com' LIMIT 1),
    3
);
SELECT fun_softdelete_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario2.buslinnes@example.com' LIMIT 1),
    3
);
SELECT fun_softdelete_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'conductor1.buslinnes@example.com' LIMIT 1),
    2
);
SELECT fun_softdelete_usuarios_roles(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'conductor2.buslinnes@example.com' LIMIT 1),
    2
);

-- 6. Mantenimiento
SELECT fun_softdelete_mantenimiento(800);
SELECT fun_softdelete_mantenimiento(801);

-- 7. Parque automotor
SELECT fun_softdelete_parque_automotor(700);
SELECT fun_softdelete_parque_automotor(701);

-- 8. Ruta-Bus
SELECT fun_softdelete_ruta_bus(1100);
SELECT fun_softdelete_ruta_bus(1101);

-- 9. Propietarios
SELECT fun_softdelete_propietarios(1000000210);
SELECT fun_softdelete_propietarios(1000000211);

-- 10. Buses
SELECT fun_softdelete_buses(500);
SELECT fun_softdelete_buses(501);

-- 11. Pasajeros
SELECT fun_softdelete_pasajeros(
    (SELECT id_usuario FROM tab_pasajeros WHERE email_pasajero = 'usuario1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1)
);
SELECT fun_softdelete_pasajeros(
    (SELECT id_usuario FROM tab_pasajeros WHERE email_pasajero = 'usuario2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1)
);

-- 12. Conductores
SELECT fun_softdelete_conductores(
    (SELECT id_conductor FROM tab_conductores WHERE email_conductor = 'conductor1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1)
);
SELECT fun_softdelete_conductores(
    (SELECT id_conductor FROM tab_conductores WHERE email_conductor = 'conductor2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1)
);

-- 13. Rutas
SELECT fun_softdelete_rutas(600);
SELECT fun_softdelete_rutas(601);

-- 14. Usuarios
SELECT fun_softdelete_usuarios(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1)
);
SELECT fun_softdelete_usuarios(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'usuario2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1)
);
SELECT fun_softdelete_usuarios(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'conductor1.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1)
);
SELECT fun_softdelete_usuarios(
    (SELECT id_usuario FROM tab_usuarios WHERE correo = 'conductor2.buslinnes@example.com' AND fec_delete IS NULL LIMIT 1)
);
