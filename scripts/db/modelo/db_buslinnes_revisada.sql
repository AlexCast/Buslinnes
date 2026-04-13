
/*****************************************/
/****************buslinnes****************/
/*****************************************/

DROP TABLE IF EXISTS tab_usuarios_roles;
DROP TABLE IF EXISTS tab_notificaciones;
DROP TABLE IF EXISTS tab_roles;
DROP TABLE IF EXISTS tab_mantenimiento;
DROP TABLE IF EXISTS tab_propietarios;
DROP TABLE IF EXISTS tab_parque_automotor;
DROP TABLE IF EXISTS tab_seguro_bus;
DROP TABLE IF EXISTS tab_ruta_bus;
DROP TABLE IF EXISTS tab_cambio_bus;
DROP TABLE IF EXISTS tab_incidentes;
DROP TABLE IF EXISTS tab_buses;
DROP TABLE IF EXISTS tab_conductores;
DROP TABLE IF EXISTS tab_rutas_favoritas;
DROP TABLE IF EXISTS tab_pasajeros;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS tab_usuarios;
DROP TABLE IF EXISTS tab_ruta_waypoints;
DROP TABLE IF EXISTS tab_rutas;
DROP TABLE IF EXISTS tab_branding_config;

CREATE TABLE IF NOT EXISTS tab_usuarios (
    tipo_doc            VARCHAR NOT NULL DEFAULT 'CD'CHECK (tipo_doc = 'CD' OR tipo_doc = 'TI' OR tipo_doc = 'CE'), -- CD = CEDULA DE CIUDADANIA, TI = TARJETA DE IDENTIDAD Y CE = SEDULA EXTRANJERA
    id_usuario          DECIMAL(10,0)  NOT NULL, --Identificador del usuario
    nom_usuario         VARCHAR NOT NULL,  -- Nombre del usuario
    email_usuario       VARCHAR NOT NULL UNIQUE, --Correo del usuario
    contrasena          VARCHAR NOT NULL, --Contraseña 
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_usuario)
);

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id                  SERIAL PRIMARY KEY,--Identificador del token
    email_usuario               VARCHAR NOT NULL,--Correo del usuario
    token               VARCHAR NOT NULL UNIQUE,--Token dado por el sistema
    expires_at          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    created_at          TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email_usuario) REFERENCES tab_usuarios(email_usuario)
);


CREATE TABLE IF NOT EXISTS tab_roles (
    id_rol              INT NOT NULL,--Identificador del Rol
    nombre_rol          VARCHAR NOT NULL UNIQUE,--Nombre del rol
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_rol)
);


CREATE TABLE IF NOT EXISTS tab_usuarios_roles (
    id_usuario          DECIMAL(10,0)  NOT NULL,--Identificador del usuario
    id_rol              INT NOT NULL,--Identificador del Rol
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_usuario, id_rol),
    FOREIGN KEY (id_usuario) REFERENCES tab_usuarios(id_usuario),
    FOREIGN KEY (id_rol) REFERENCES tab_roles(id_rol)
);

CREATE TABLE IF NOT EXISTS tab_rutas(
    id_ruta             INT NOT NULL,--identificador ruta
    nom_ruta            VARCHAR NOT NULL CHECK(LENGTH(nom_ruta)>=3),--nombre de la ruta
    hora_inicio         TIME NOT NULL,--Inicio de actividad ruta
    hora_final          TIME NOT NULL,--Final de actividad ruta
    inicio_ruta         VARCHAR NOT NULL,--donde inicia la ruta
    fin_ruta            VARCHAR NOT NULL,--donde finaliza la ruta
    longitud            DECIMAL(5,0) NOT NULL,--que distancia tiene la ruta
    val_pasaje          DECIMAL(4,0) NOT NULL,--cuanto vale el pasaje
    inicio_lat          DECIMAL(9,6),--latitud punto inicio (para OSRM rutas por calles)
    inicio_lng          DECIMAL(9,6),--longitud punto inicio
    fin_lat             DECIMAL(9,6),--latitud punto fin
    fin_lng             DECIMAL(9,6),--longitud punto fin
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY(id_ruta)
);

CREATE TABLE IF NOT EXISTS tab_ruta_waypoints (
    id_waypoint                  SERIAL not NULL,--Identificador del waypoint
    id_ruta                      INT NOT NULL,--Identificador de la ruta
    orden                        INTEGER NOT NULL,--orden de las paradas
    lat                          NUMERIC(10, 6) NOT NULL,
    lng                          NUMERIC(11, 6) NOT NULL,
    nombre                       VARCHAR(255),--Nombre de las paradas
    usr_insert                   VARCHAR NOT NULL,
    fec_insert                   TIMESTAMP WITHOUT TIME ZONE NOT NULL,  
    usr_update                   VARCHAR,
    fec_update                   TIMESTAMP WITHOUT TIME ZONE,
    UNIQUE(id_ruta, orden),
    PRIMARY KEY (id_waypoint),
    FOREIGN KEY(id_ruta) REFERENCES tab_rutas(id_ruta)
);


CREATE TABLE IF NOT EXISTS tab_conductores(
    id_usuario          DECIMAL(10,0),--id del usuario conductor (FK a tab_usuarios)
    nom_conductor       VARCHAR NOT NULL DEFAULT 'NOMBRE' CHECK(LENGTH(nom_conductor)>=3), --nombre conductor
    ape_conductor       VARCHAR NULL  DEFAULT 'APELLIDO' CHECK(LENGTH(ape_conductor)>=3),--apellido del conductor
    genero_conductor    VARCHAR NOT NULL DEFAULT 'OTRO' CHECK(genero_conductor = 'M' OR genero_conductor = 'F' OR genero_conductor = 'O'),--genero M = masculino, F = femenino y O _ otros 
    email_conductor     VARCHAR NOT NULL UNIQUE,--correo del conductor
    licencia_conductor  VARCHAR NOT NULL CHECK (LENGTH(licencia_conductor) >= 7 AND LENGTH(licencia_conductor) <= 10),
    tipo_licencia       CHAR(2) NOT NULL DEFAULT 'T'CHECK (tipo_licencia = 'C2' OR tipo_licencia = 'C3' OR tipo_licencia = 'T' ) , --C2 = camiones y buses,C3 = vehiculo articulados o pesados, T = que tipo de licencia
    fec_venc_licencia   DATE NULL CHECK (fec_venc_licencia > CURRENT_DATE),
    estado_conductor    CHAR(1) NOT NULL DEFAULT 'P' CHECK (estado_conductor = 'A' OR estado_conductor = 'S' OR estado_conductor = 'R' OR estado_conductor = 'P'),--A = activo, S = suspendido, R = retirado, P = pendiente --El estado del conductor
    fec_nacimiento      DATE,
    tipo_sangre         VARCHAR NOT NULL DEFAULT 'PENDIENTE'CHECK(tipo_sangre = 'A+' OR tipo_sangre = 'A-' OR tipo_sangre = 'B+' OR tipo_sangre = 'B-' OR tipo_sangre = 'AB+' OR tipo_sangre = 'AB-' OR tipo_sangre = 'O+' OR tipo_sangre = 'O-' OR tipo_sangre ='PENDIENTE'),--Tipo de sangre del conductor
    perfil_completdo    BOOLEAN NOT NULL DEFAULT FALSE, --Indica si el conductor ha completado su perfil con toda la información requerida
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_usuario),
    FOREIGN KEY (id_usuario) REFERENCES tab_usuarios(id_usuario)
);

CREATE TABLE IF NOT EXISTS tab_pasajeros(
    id_usuario          DECIMAL(10,0) NOT NULL,                                     --id del usuario pasajero (FK a tab_usuarios)
    nom_pasajero        VARCHAR NOT NULL CHECK(LENGTH(nom_pasajero)>=3), --nombre del pasajero
    email_pasajero       VARCHAR NOT NULL UNIQUE,                                 --correo del pasajero
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_usuario),
    FOREIGN KEY (id_usuario) REFERENCES tab_usuarios(id_usuario)
);

CREATE TABLE  If NOT EXISTS tab_rutas_favoritas (
    id_ruta_favorita    INT NOT NULL,--Identificador de la ruta favorita
    id_usuario          DECIMAL(10,0) NOT NULL,--Identificador del usuario
    id_ruta             INT NOT NULL,--Identificador de las rutas
    PRIMARY KEY(id_ruta_favorita),
    FOREIGN KEY (id_usuario) REFERENCES tab_pasajeros(id_usuario),
    FOREIGN KEY (id_ruta) REFERENCES tab_rutas(id_ruta)
);

CREATE TABLE IF NOT EXISTS tab_buses(
    id_bus              varchar(6) NOT NULL,--Matricula del bus
    id_usuario          DECIMAL(10,0) NOT NULL, --id_usuario asignado al bus
    anio_fab            DECIMAL(4,0)NOT NULL,--año de fabricación del bus
    capacidad_pasajeros DECIMAL(2,0) NOT NULL,--capacidad de pasajeros del bus
    tipo_bus            CHAR(1) NOT NULL DEFAULT 'U' CHECK (tipo_bus = 'U' OR tipo_bus = 'M' OR tipo_bus = 'A' OR tipo_bus = 'E'),--U = urbano,M = Municipal,A = articulado, E = especializado 
    gps                 BOOLEAN NOT NULL DEFAULT TRUE,--false = no esta activado el gps y true = esta activado el gps
    ind_estado_buses    CHAR(1) NOT NULL DEFAULT 'L' CHECK (ind_estado_buses = 'L' OR ind_estado_buses = 'F' OR ind_estado_buses = 'D'OR ind_estado_buses = 'S' OR ind_estado_buses = 'T' OR ind_estado_buses = 'A' ),--L=libre,F=fuera de servicio,D=dañado,S=suspendido,T=taller,A=activo
    usr_insert          VARCHAR         NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY(id_bus),
    FOREIGN KEY(id_usuario) REFERENCES tab_conductores(id_usuario)
);

CREATE TABLE IF NOT EXISTS tab_mantenimiento(
    id_mantenimiento    INT NOT NULL,--identificador del mantenimiento
    id_bus              varchar(6) NOT NULL,--identificador del bus
    descripcion         VARCHAR NOT NULL,--que se hizo en el mantenimiento
    fecha_mantenimiento TIMESTAMP WITHOUT TIME ZONE NOT NULL,--fecha en la que se hizo el mantenimiento
    costo_mantenimiento DECIMAL(10,0) NOT NULL,--costo del mantenimiento
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete		    VARCHAR,
    fec_delete		    TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY(id_mantenimiento),
    FOREIGN KEY(id_bus) REFERENCES tab_buses(id_bus)
);

CREATE TABLE IF NOT EXISTS tab_propietarios(
    id_propietario      DECIMAL(10) NOT NULL CHECK(id_propietario>=1000000000),--el documento de identidad del propietario
    id_bus              varchar(6) NOT NULL,--identificador del bus
    nom_propietario     VARCHAR NOT NULL CHECK(LENGTH(nom_propietario)>=3),--nombre del propietario
    ape_propietario     VARCHAR NOT NULL CHECK(LENGTH(ape_propietario)>=3),--apellido del propietario
    tel_propietario     DECIMAL(10,0) NOT NULL CHECK(tel_propietario>=2999999999),--telefono del propietario
    email_propietario   VARCHAR NOT NULL UNIQUE,--email del propietario
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete		    VARCHAR,
    fec_delete		    TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY(id_propietario),
    FOREIGN KEY(id_bus) REFERENCES tab_buses(id_bus)
);

CREATE TABLE IF NOT EXISTS tab_parque_automotor(
    id_parque_automotor  INT NOT NULL,--identificador del parque automotor
    id_bus               varchar(6) NOT NULL,--identificador del bus
    dir_parque_automotor VARCHAR NOT NULL,--direccion del parque automotor
    usr_insert           VARCHAR NOT NULL,
    fec_insert           TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update           VARCHAR,
    fec_update           TIMESTAMP WITHOUT TIME ZONE,
    usr_delete           VARCHAR,
    fec_delete           TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY(id_parque_automotor),
    FOREIGN KEY(id_bus) REFERENCES tab_buses(id_bus)
);

CREATE TABLE IF NOT EXISTS tab_notificaciones(
    id_notificacion     INT NOT NULL,--identificador notificacion
    id_usuario          INT NULL ,--identificador del usuario
    id_rol              INT NULL,--identificador del rol
    titulo_notificacion VARCHAR NOT NULL,--titulo de la notificacion
    descr_notificacion VARCHAR NOT NULL,--descripcion de la notificacion
    usr_insert          VARCHAR         NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete		    VARCHAR,
    fec_delete		    TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY(id_notificacion),
    FOREIGN KEY(id_usuario)REFERENCES tab_usuarios(id_usuario),
    FOREIGN KEY(id_rol)REFERENCES tab_roles(id_rol)
);

CREATE TABLE IF NOT EXISTS tab_ruta_bus(
    id_ruta_bus         INT NOT NULL,--identificador del bus asignado a la ruta
    id_ruta             INT NOT NULL,--identificador de la ruta
    id_bus              varchar(6) NOT NULL,--identificador del bus
    usr_insert          VARCHAR         NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete		    VARCHAR,
    fec_delete		    TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY(id_ruta_bus),
    FOREIGN KEY(id_ruta)REFERENCES tab_rutas(id_ruta),
    FOREIGN KEY(id_bus)REFERENCES tab_buses(id_bus)
);

CREATE TABLE IF NOT EXISTS tab_incidentes(
    id_incidente        INT NOT NULL,--identificador del incidente
    titulo_incidente    VARCHAR NOT NULL,--titulo del incidente
    desc_incidente      VARCHAR NOT NULL,--descripcion del incidente 
    id_bus              varchar(6) NOT NULL,--identificador del bus
    id_usuario          INT NOT NULL,--identificador del conductor
    tipo_incidente      CHAR(1) NOT NULL DEFAULT 'O' CHECK (tipo_incidente =  'C' OR tipo_incidente =  'E' OR tipo_incidente =  'D' OR tipo_incidente =  'A' OR tipo_incidente =  'O'),--indicador del incidente ej: C=choque, E=embotellamiento, D=desviacion de ruta, A=atropello, O=otros
    usr_insert          VARCHAR         NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete		    VARCHAR,
    fec_delete		    TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY(id_incidente),
    FOREIGN KEY(id_bus)REFERENCES tab_buses(id_bus),
    FOREIGN KEY(id_usuario)REFERENCES tab_conductores(id_usuario)
);

CREATE TABLE IF NOT EXISTS tab_cambio_bus(
    id_cambio_bus       INT NOT NULL,--identificador del cambio de bus
    id_incidente        INT NOT NULL,--identificador del incidente
    id_bus              varchar(6) NOT NULL,--identificador del bus que se va a cambiar
    ubicacion_cambio    VARCHAR NOT NULL,--ubicacion donde se va a hacer el cambio de bus
    usr_insert          VARCHAR         NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete		    VARCHAR,
    fec_delete		    TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY(id_cambio_bus),
    FOREIGN KEY(id_incidente)REFERENCES tab_incidentes(id_incidente),
    FOREIGN KEY(id_bus)REFERENCES tab_buses(id_bus)
);

CREATE TABLE IF NOT EXISTS tab_seguro_bus(
    id_control              INT GENERATED BY DEFAULT AS IDENTITY,
    id_bus                  varchar(6) NOT NULL,--identificador del bus
    soat_num                VARCHAR NOT NULL,--Es un codigo alfanumerico de 17 digitos
    soat_venc               DATE NOT NULL,--Fecha de vencimiento del soat 
    soat_aseguradora        VARCHAR NOT NULL,--Nombre de la aseguradora
    rcc_num_poliza          VARCHAR NOT NULL,--Es un codigo alfanumerico de 12 digitos
    rcc_venc                DATE NOT NULL,--Fecha de vencimiento de rcc 'Responsabilidad Civil Contractual'
    rce_num_poliza          VARCHAR NOT NULL,--Codigo alfanumeroco de la poliza Responsabilidad Civil Extracontractual 
    rce_venc                DATE NOT NULL,--Fecha de vencimiento del Responsabilidad Civil Extracontractual
    exceso_num_poliza       VARCHAR NOT NULL,--codigo alfanumerico de entre 10 a 15 digitos 
    exceso_vencimiento      DATE NOT NULL,--Fecha de vencimiento de Póliza en Exceso
    exceso_valor_cobertura  DECIMAL NOT NULL,--Cuanto es el monto que cubre adicional mente
    usr_insert              VARCHAR         NOT NULL,
    fec_insert              TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update              VARCHAR,
    fec_update              TIMESTAMP WITHOUT TIME ZONE,
    usr_delete		        VARCHAR,
    fec_delete		        TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY(id_control),
    FOREIGN KEY(id_bus) REFERENCES tab_buses(id_bus)
);

CREATE TABLE IF NOT EXISTS tab_branding_config (
    id_config               SMALLINT PRIMARY KEY CHECK (id_config = 1),
    primary_color           VARCHAR(9) NOT NULL DEFAULT '#8059d4ff',
    logo_url                TEXT NOT NULL DEFAULT '/buslinnes/assets/img/logomorado.svg',
    favicon_url             TEXT NOT NULL DEFAULT '/buslinnes/mkcert/favicon.ico',
    updated_at              TIMESTAMP NOT NULL DEFAULT NOW() --Aqui deciden si quieren agregar auditoria a esta tabla o no, por ahora solo se actualiza la fecha de actualizacion cada vez que se hace un cambio
);

INSERT INTO tab_branding_config (id_config, primary_color, logo_url, favicon_url)
VALUES (1, '#8059d4ff', '/buslinnes/assets/img/logomorado.svg', '/buslinnes/mkcert/favicon.ico')
ON CONFLICT (id_config) DO NOTHING;

/*=============================================*/
/*              TRIGGERS                       */
/*=============================================*/

/*---------------------------------------------*/
/* GRUPO 1 - AUDITORÍA                         */
/* Registra automáticamente quién y cuándo     */
/* insertó o modificó cada fila. Rellena       */
/* usr_insert/fec_insert en INSERT y           */
/* usr_update/fec_update en UPDATE.            */
/*---------------------------------------------*/
CREATE OR REPLACE FUNCTION fun_audit_actor() RETURNS VARCHAR AS
$$
DECLARE
    v_actor VARCHAR;
BEGIN
    v_actor := NULLIF(current_setting('app.current_user', true), '');
    IF v_actor IS NULL THEN
        v_actor := CURRENT_USER;
    END IF;
    RETURN v_actor;
END;
$$
LANGUAGE PLPGSQL;

CREATE OR REPLACE FUNCTION fun_audit_tablas() RETURNS TRIGGER AS
$$
    BEGIN
        IF TG_OP = 'INSERT' THEN
            NEW.usr_insert = fun_audit_actor();
            NEW.fec_insert = CURRENT_TIMESTAMP;
            RETURN NEW;
        END IF;
        IF TG_OP = 'UPDATE' THEN
            NEW.usr_update = fun_audit_actor();
            NEW.fec_update = CURRENT_TIMESTAMP;
            RETURN NEW;
        END IF;
    END;
$$
LANGUAGE PLPGSQL;

-- Auditoría: registra insert/update en tab_usuarios
CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON tab_usuarios
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_roles
CREATE OR REPLACE TRIGGER tri_audit_roles BEFORE INSERT OR UPDATE ON tab_roles
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_usuarios_roles
CREATE OR REPLACE TRIGGER tri_audit_usuarios_roles BEFORE INSERT OR UPDATE ON tab_usuarios_roles
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_cambio_bus
CREATE OR REPLACE TRIGGER tri_audit_cambio_bus BEFORE INSERT OR UPDATE ON tab_cambio_bus
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_conductores
CREATE OR REPLACE TRIGGER tri_audit_conductores BEFORE INSERT OR UPDATE ON tab_conductores
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_mantenimiento
CREATE OR REPLACE TRIGGER tri_audit_mantenimiento BEFORE INSERT OR UPDATE ON tab_mantenimiento
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_propietarios
CREATE OR REPLACE TRIGGER tri_audit_propietarios BEFORE INSERT OR UPDATE ON tab_propietarios
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_pasajeros
CREATE OR REPLACE TRIGGER tri_audit_pasajeros BEFORE INSERT OR UPDATE ON tab_pasajeros
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_notificaciones
CREATE OR REPLACE TRIGGER tri_audit_not_pasajeros BEFORE INSERT OR UPDATE ON tab_notificaciones
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_rutas
CREATE OR REPLACE TRIGGER tri_audit_rutas BEFORE INSERT OR UPDATE ON tab_rutas
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_ruta_waypoints
CREATE OR REPLACE TRIGGER tri_audit_ruta_waypoints BEFORE INSERT OR UPDATE ON tab_ruta_waypoints
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_buses
CREATE OR REPLACE TRIGGER tri_audit_buses BEFORE INSERT OR UPDATE ON tab_buses
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_ruta_bus
CREATE OR REPLACE TRIGGER tri_audit_ruta_bus BEFORE INSERT OR UPDATE ON tab_ruta_bus
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_parque_automotor
CREATE OR REPLACE TRIGGER tri_audit_parque_automotor BEFORE INSERT OR UPDATE ON tab_parque_automotor
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_incidentes
CREATE OR REPLACE TRIGGER tri_audit_incidentes BEFORE INSERT OR UPDATE ON tab_incidentes
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- Auditoría: registra insert/update en tab_seguro_bus
CREATE OR REPLACE TRIGGER tri_audit_seguro_bus BEFORE INSERT OR UPDATE ON tab_seguro_bus
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();


/*---------------------------------------------*/
/* GRUPO 2 - SOFT DELETE                       */
/* Intercepta el DELETE antes de ejecutarse y  */
/* en su lugar marca la fila con usr_delete y  */
/* fec_delete (borrado lógico). El registro    */
/* permanece en la tabla pero queda inactivo.  */
/*---------------------------------------------*/
CREATE OR REPLACE FUNCTION fun_soft_delete_tablas() RETURNS TRIGGER AS
$$
BEGIN
    -- Actualizar la misma fila con datos de borrado
    EXECUTE format('UPDATE %I SET usr_delete = $1, fec_delete = CURRENT_TIMESTAMP WHERE ctid = $2', TG_TABLE_NAME)
    USING fun_audit_actor(), OLD.ctid;

    RETURN NULL; -- Evita el DELETE real
END;
$$ LANGUAGE plpgsql;

-- Soft delete: marca como eliminado en tab_usuarios (no borra físicamente)
CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE DELETE ON tab_usuarios
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_roles
CREATE OR REPLACE TRIGGER tri_soft_delete_roles BEFORE DELETE ON tab_roles
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();    

-- Soft delete: marca como eliminado en tab_usuarios_roles
CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios_roles BEFORE DELETE ON tab_usuarios_roles
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_cambio_bus
CREATE OR REPLACE TRIGGER tri_soft_delete_cambio_bus BEFORE DELETE ON tab_cambio_bus
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_conductores
CREATE OR REPLACE TRIGGER tri_soft_delete_conductores BEFORE DELETE ON tab_conductores
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_mantenimiento
CREATE OR REPLACE TRIGGER tri_soft_delete_mantenimiento BEFORE DELETE ON tab_mantenimiento
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_propietarios
CREATE OR REPLACE TRIGGER tri_soft_delete_propietarios BEFORE DELETE ON tab_propietarios
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_pasajeros
CREATE OR REPLACE TRIGGER tri_soft_delete_pasajeros BEFORE DELETE ON tab_pasajeros
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_notificaciones
CREATE OR REPLACE TRIGGER tri_soft_delete_not_pasajeros BEFORE DELETE ON tab_notificaciones
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_rutas
CREATE OR REPLACE TRIGGER tri_soft_delete_rutas BEFORE DELETE ON tab_rutas
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_ruta_waypoints
CREATE OR REPLACE TRIGGER tri_soft_delete_ruta_waypoints BEFORE DELETE ON tab_ruta_waypoints
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_buses
CREATE OR REPLACE TRIGGER tri_soft_delete_buses BEFORE DELETE ON tab_buses
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_ruta_bus
CREATE OR REPLACE TRIGGER tri_soft_delete_ruta_bus BEFORE DELETE ON tab_ruta_bus
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_parque_automotor
CREATE OR REPLACE TRIGGER tri_soft_delete_parque_automotor BEFORE DELETE ON tab_parque_automotor
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_incidentes
CREATE OR REPLACE TRIGGER tri_soft_delete_incidentes BEFORE DELETE ON tab_incidentes
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

-- Soft delete: marca como eliminado en tab_seguro_bus
CREATE OR REPLACE TRIGGER tri_soft_delete_seguro_bus BEFORE DELETE ON tab_seguro_bus
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();
-- =======================
--  Roles (1,2,3)
-- =======================
INSERT INTO tab_roles (id_rol, nombre_rol)
VALUES
  (1, 'admin'),
  (2, 'conductor'),
  (3, 'pasajero');

/*---------------------------------------------*/
/* GRUPO 3 - RECUPERACIÓN DE CONTRASEÑA        */
/*---------------------------------------------*/

-- Índice para búsqueda rápida de tokens por correo
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_email
    ON password_reset_tokens (email_usuario);

-- Índice para búsqueda rápida de tokens por fecha de expiración
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_expires_at
    ON password_reset_tokens (expires_at);

-- Al insertar un token de recuperación, calcula automáticamente
-- created_at (si no viene) y establece expires_at = created_at + 12 horas
CREATE OR REPLACE FUNCTION fun_password_reset_tokens_set_expiration() RETURNS TRIGGER AS
$$
BEGIN
    NEW.created_at := COALESCE(NEW.created_at, CURRENT_TIMESTAMP);
    NEW.expires_at := NEW.created_at + INTERVAL '12 hours';
    RETURN NEW;
END;
$$
LANGUAGE PLPGSQL;

CREATE OR REPLACE TRIGGER tri_password_reset_tokens_set_expiration
BEFORE INSERT ON password_reset_tokens
FOR EACH ROW EXECUTE PROCEDURE fun_password_reset_tokens_set_expiration();

-- Después de cada inserción de token, elimina automáticamente todos
-- los tokens ya expirados para mantener la tabla limpia
CREATE OR REPLACE FUNCTION fun_password_reset_tokens_cleanup_expired() RETURNS TRIGGER AS
$$
BEGIN
    DELETE FROM password_reset_tokens
    WHERE expires_at <= CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$
LANGUAGE PLPGSQL;

CREATE OR REPLACE TRIGGER tri_password_reset_tokens_cleanup_expired
AFTER INSERT ON password_reset_tokens
FOR EACH ROW EXECUTE PROCEDURE fun_password_reset_tokens_cleanup_expired();

/*---------------------------------------------*/
/* GRUPO 4 - CREACIÓN AUTOMÁTICA DE PERFIL     */
/* Al asignar el rol 'pasajero' a un usuario,  */
/* crea automáticamente su perfil en           */
/* tab_pasajeros con nombre y correo tomados   */
/* desde tab_usuarios. Si ya existe, no hace   */
/* nada (ON CONFLICT DO NOTHING).              */
/* El perfil de conductor se crea manualmente  */
/* por administradores desde el CRUD.          */
/*---------------------------------------------*/
CREATE OR REPLACE FUNCTION fun_crear_perfil_por_rol()
RETURNS TRIGGER AS $$
DECLARE
    v_rol    VARCHAR;
    v_nom_usuario VARCHAR;
    v_email_usuario VARCHAR;
BEGIN
    SELECT nombre_rol INTO v_rol
    FROM tab_roles WHERE id_rol = NEW.id_rol;

    SELECT nom_usuario, email_usuario INTO v_nom_usuario, v_email_usuario
    FROM tab_usuarios WHERE id_usuario = NEW.id_usuario;

    IF v_rol = 'pasajero' THEN
        INSERT INTO tab_pasajeros (id_usuario, nom_pasajero, email_pasajero)
        VALUES (NEW.id_usuario, v_nom_usuario, v_email_usuario)
        ON CONFLICT (id_usuario) DO NOTHING;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Crea automáticamente solo el perfil de pasajero al asignar ese rol
CREATE OR REPLACE TRIGGER tri_crear_perfil_por_rol
AFTER INSERT ON tab_usuarios_roles
FOR EACH ROW EXECUTE FUNCTION fun_crear_perfil_por_rol();