-- ==========================================================================
-- STUDY MATCH • Script unificado de la base de datos
-- Script único: crea la base y todas sus tablas.
--
-- ATENCIÓN: elimina las tablas existentes y las vuelve a crear vacías.
-- Se pierden todos los datos cargados anteriormente.
-- ==========================================================================

CREATE DATABASE IF NOT EXISTS StudyMatch_DB
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE StudyMatch_DB;

SET NAMES utf8mb4;
SET time_zone = '-03:00';
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------------------------
-- 1. Limpieza: eliminación de tablas preexistentes
-- --------------------------------------------------------------------------
DROP TABLE IF EXISTS mensaje_tutoria;
DROP TABLE IF EXISTS solicitud_tutoria;
DROP TABLE IF EXISTS oferta_tutoria;
DROP TABLE IF EXISTS materia;
DROP TABLE IF EXISTS carreras;
DROP TABLE IF EXISTS consultausuarios;
DROP TABLE IF EXISTS usuarios;

-- --------------------------------------------------------------------------
-- 2. Usuarios
-- --------------------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre_usuario VARCHAR(100) NOT NULL,
    password_usuario VARCHAR(255) NOT NULL,
    correo_usuario VARCHAR(100) NOT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado_usuario ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    rol_usuario ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
    carrera_usuario VARCHAR(50) NULL, -- completar
    anio_cursada_usuario ENUM('1ro', '2do', '3ro', '4to', '5to', 'graduado') NULL,
    foto_usuario VARCHAR(255) NULL,                 -- ruta relativa: avatar precargado o foto subida; NULL = iniciales
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uq_usuarios_correo (correo_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- 3. Consultas del formulario de contacto
-- --------------------------------------------------------------------------
CREATE TABLE consultausuarios (
    id_consulta INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre_usuario VARCHAR(100) NOT NULL,
    correo_usuario VARCHAR(100) NOT NULL,
    motivo VARCHAR(255) NULL,
    asunto VARCHAR(255) NULL,
    mensaje TEXT NOT NULL,
    fecha_consulta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_consulta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- 4. Carreras
-- --------------------------------------------------------------------------
CREATE TABLE carreras (
    id_carrera INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre_carrera VARCHAR(150) NOT NULL,
    codigo_carrera VARCHAR(20) NOT NULL,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_carrera),
    UNIQUE KEY uq_carreras_nombre (nombre_carrera),
    UNIQUE KEY uq_carreras_codigo (codigo_carrera)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- 5. Materias (cada materia pertenece a una carrera)
-- --------------------------------------------------------------------------
CREATE TABLE materia (
    id_materia INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_carrera INT UNSIGNED NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    anio_carrera TINYINT UNSIGNED NULL,            -- año del plan de estudios (1, 2, 3...)
    descripcion TEXT NULL,
    PRIMARY KEY (id_materia),
    UNIQUE KEY uq_materia_carrera_nombre (id_carrera, nombre),
    CONSTRAINT chk_materia_anio CHECK (anio_carrera IS NULL OR anio_carrera BETWEEN 1 AND 6),
    CONSTRAINT fk_materia_carrera FOREIGN KEY (id_carrera)
        REFERENCES carreras (id_carrera) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- 6. Ofertas de tutoría
--    Cualquier alumno puede publicar ofertas (actuar como tutor).
--    Las tutorías son individuales (1 a 1).
--    disponibilidad: texto libre por ahora; más adelante será reemplazado
--    por una agenda compartida entre los roles alumno y tutor.
--    precio: en pesos (ARS) por clase; 0 = tutoría gratuita.
-- --------------------------------------------------------------------------
CREATE TABLE oferta_tutoria (
    id_tutoria INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_tutor INT UNSIGNED NOT NULL,
    id_materia INT UNSIGNED NOT NULL,
    disponibilidad TEXT NOT NULL,
    modalidad ENUM('Virtual', 'Presencial en UTN', 'Hibrido') NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    medios_pago SET('efectivo', 'transferencia', 'mercado_pago') NULL,
    estado ENUM('activa', 'pausada') NOT NULL DEFAULT 'activa',
    fecha_publicacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_tutoria),
    KEY idx_oferta_estado_fecha (estado, fecha_publicacion),
    CONSTRAINT chk_oferta_precio CHECK (precio >= 0),
    CONSTRAINT fk_tutorias_tutor FOREIGN KEY (id_tutor)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_tutorias_materia FOREIGN KEY (id_materia)
        REFERENCES materia (id_materia) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- 7. Solicitudes de tutoría (un alumno pide una oferta; el tutor responde)
--    Flujo de estados:
--      pendiente  -> aceptada | rechazada   (responde el tutor)
--      pendiente  -> cancelada              (cancela el alumno)
--      aceptada   -> cancelada              (cancela el alumno)
--      aceptada   -> finalizada             (el tutor da por terminada la tutoría)
--    Un alumno no puede tener dos solicitudes vigentes (pendiente/aceptada)
--    para la misma oferta: lo controla la aplicación.
-- --------------------------------------------------------------------------
CREATE TABLE solicitud_tutoria (
    id_solicitud INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_oferta INT UNSIGNED NOT NULL,
    id_alumno INT UNSIGNED NOT NULL,
    estado ENUM('pendiente', 'aceptada', 'rechazada', 'cancelada', 'finalizada') NOT NULL DEFAULT 'pendiente',
    mensaje VARCHAR(500) NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NULL,              -- último cambio de estado
    PRIMARY KEY (id_solicitud),
    KEY idx_solicitud_oferta_estado (id_oferta, estado),
    KEY idx_solicitud_alumno_estado (id_alumno, estado),
    CONSTRAINT fk_solicitud_oferta FOREIGN KEY (id_oferta)
        REFERENCES oferta_tutoria (id_tutoria) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_solicitud_alumno FOREIGN KEY (id_alumno)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- 8. Mensajes del chat de cada tutoría
--    Cada solicitud tiene su propia conversación entre el alumno y el tutor.
--    Se puede escribir solo mientras la solicitud está 'aceptada'; al
--    finalizar o cancelar, el chat queda en modo solo lectura.
--    Solo texto (los links se muestran como enlaces); sin archivos adjuntos.
-- --------------------------------------------------------------------------
CREATE TABLE mensaje_tutoria (
    id_mensaje INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_solicitud INT UNSIGNED NOT NULL,
    id_emisor INT UNSIGNED NOT NULL,
    contenido VARCHAR(1000) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    leido TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id_mensaje),
    KEY idx_mensaje_solicitud (id_solicitud, id_mensaje),
    KEY idx_mensaje_no_leidos (id_solicitud, leido),
    CONSTRAINT fk_mensaje_solicitud FOREIGN KEY (id_solicitud)
        REFERENCES solicitud_tutoria (id_solicitud) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_mensaje_emisor FOREIGN KEY (id_emisor)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------------------
-- 9. Datos base
-- --------------------------------------------------------------------------
INSERT INTO carreras (nombre_carrera, codigo_carrera)
VALUES ('Tecnicatura Universitaria en Programación', 'TUP');

-- Para convertir una cuenta existente en administrador:
-- UPDATE usuarios SET rol_usuario = 'admin' WHERE correo_usuario = 'admin@frh.utn.edu.ar';
