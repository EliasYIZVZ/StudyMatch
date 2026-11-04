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
DROP TABLE IF EXISTS auditoria;
DROP TABLE IF EXISTS reportes;
DROP TABLE IF EXISTS salas_publicas;


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

-- --------------------------------------------------------------------------
-- 9. Auditoria 
--    la idea es que no solo audite acciones de ADMIN 
--    sino de usuario, como login y logout
-- --------------------------------------------------------------------------
CREATE TABLE auditoria (
    id_auditoria INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    accion ENUM('crear', 'modificar', 'eliminar', 'login', 'logout') NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    detalle_previo TEXT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45) NOT NULL,
    PRIMARY KEY (id_auditoria),
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- 10. Reportes.
--    cualquier usuario puede reportar segun opciones en front 
--    mediante back enviar sobre que tipo entidad se genera el reporte
-- --------------------------------------------------------------------------
CREATE TABLE reportes (
    id_reporte INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_denunciante INT UNSIGNED NOT NULL,
    tipo_entidad ENUM('sala', 'usuario') NOT NULL,
    id_referencia INT UNSIGNED NOT NULL,
    motivo VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('pendiente', 'finalizado', 'rechazado') NOT NULL DEFAULT 'pendiente',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_resolucion DATETIME NULL,
    PRIMARY KEY (id_reporte),
    CONSTRAINT fk_reportes_denunciante FOREIGN KEY (id_denunciante) 
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- 11. Salas Públicas de Estudio (Discord)
-- --------------------------------------------------------------------------
CREATE TABLE salas_publicas (
    id_sala INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    enlace_discord VARCHAR(255) NOT NULL,
    id_creador INT UNSIGNED NOT NULL,
    estado ENUM('activa', 'suspendida') NOT NULL DEFAULT 'activa',
    motivo_suspension VARCHAR(255) NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_sala),
    CONSTRAINT fk_salas_creador FOREIGN KEY (id_creador)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
-- --------------------------------------------------------------------------
-- 11. Datos base 
-- --------------------------------------------------------------------------
INSERT INTO carreras (nombre_carrera, codigo_carrera)
VALUES ('Tecnicatura Universitaria en Programación', 'TUP');

INSERT IGNORE INTO materia (id_carrera, nombre, descripcion)
SELECT c.id_carrera, x.nombre, x.descripcion
FROM carreras c
CROSS JOIN (
    SELECT 'Programación I' AS nombre, 'Fundamentos de programación estructurada.' AS descripcion
    UNION ALL SELECT 'Programación II', 'Punteros, memoria dinámica y estructuras de datos.'
    UNION ALL SELECT 'Base de Datos I', 'Modelo relacional, normalización y SQL.'
    UNION ALL SELECT 'Matemática', 'Álgebra, lógica y funciones.'
    UNION ALL SELECT 'Arquitectura y Sistemas Operativos', 'Hardware, procesos y gestión de memoria.'
    UNION ALL SELECT 'Inglés I', 'Lectura comprensiva de textos técnicos.'
    UNION ALL SELECT 'Organización Empresarial', 'Organización de la empresa, conceptos de gestión, administración y estructura organizativa.'
    UNION ALL SELECT 'Probabilidad y Estadística', 'Conceptos de inferencia estadística y modelado de datos'
    UNION ALL SELECT 'Base de Datos II', 'Gestión de triggers, transacciones y lenguajes de consulta'
    UNION ALL SELECT 'Gestión de Desarrollo de Software', 'Metodologías de desarrollo y gestión de proyectos'
    UNION ALL SELECT 'Ingles II', 'Inglés técnico orientado a la programación'
    UNION ALL SELECT 'Introducción al Análisis de Datos', 'Conceptos de visualización y tratamiento de datos'
    UNION ALL SELECT 'Legislación', 'fundamentos del derecho y responsabilidad legal'
    UNION ALL SELECT 'Metodología de Sistemas I', 'Conceptos de análisis y diseño de sistemas'
    UNION ALL SELECT 'Metodología de Sistemas II', 'Conceptos de buen codigo y buenas practicas'
    UNION ALL SELECT 'Programación III', 'Programación orientada a objetos, diseño web y entornos de desarrollo'
    UNION ALL SELECT 'Programación IV', 'Conceptos de full-stack development, UX/UI y deployment'
    UNION ALL SELECT 'Trabajo Final Integrador', 'Aplicación práctica de los conocimientos adquiridos en la carrera'
) AS x
WHERE c.codigo_carrera = 'TUP';




-- Para convertir una cuenta existente en administrador:
-- UPDATE usuarios SET rol_usuario = 'admin' WHERE correo_usuario = 'admin@frh.utn.edu.ar';





