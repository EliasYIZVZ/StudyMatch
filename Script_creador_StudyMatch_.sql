
CREATE DATABASE IF NOT EXISTS StudyMatch_DB
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE StudyMatch_DB;

SET NAMES utf8mb4;
SET time_zone = '-03:00';
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre_usuario VARCHAR(100) NOT NULL,
    password_usuario VARCHAR(255) NOT NULL,
    correo_usuario VARCHAR(100) NOT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado_usuario ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    tipo_usuario ENUM('alumno', 'tutor', 'admin') NOT NULL,
    area_usuario enum('ciencias', 'humanidades', 'tecnologia', 'arte') NULL, -- completar
    carrera_usuario enum('ingenieria', 'medicina', 'derecho', 'arquitectura', 'negocios', 'ciencias sociales') NULL, -- completar
    anio_cursada_usuario enum('1ro', '2do', '3ro', '4to', '5to', 'graduado') NULL,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uq_usuarios_correo (correo_usuario),
    KEY idx_usuarios_estado_tipo (estado_usuario, tipo_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tutores (
    id_tutor INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    titulo_profesional VARCHAR(255) NULL,
    materias VARCHAR(255) NULL,
    anios_experiencia SMALLINT UNSIGNED NULL,
    PRIMARY KEY (id_tutor),
    UNIQUE KEY uq_tutores_usuario (id_usuario),
    CONSTRAINT fk_tutores_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_tutores_experiencia CHECK (anios_experiencia IS NULL OR anios_experiencia <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
    id_admin INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_admin),
    UNIQUE KEY uq_admins_usuario (id_usuario),
    CONSTRAINT fk_admins_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS auditoria (
    id_auditoria BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NULL,
    id_admin INT UNSIGNED NULL,
    accion ENUM('login', 'crear', 'modificar', 'eliminar') NOT NULL,
    objeto ENUM('login', 'usuario', 'mensaje', 'publicacion', 'solicitud_tutoria') NOT NULL, -- objeto sobre el cual se realiza la acción; se interpreta junto con id_registro
    resultado ENUM('exitoso', 'fallido') NOT NULL DEFAULT 'exitoso',
    id_registro INT UNSIGNED NULL,
    datos_anteriores JSON NULL,
    datos_nuevos JSON NULL,
    ip_origen VARCHAR(45) NULL,
    fecha_accion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_auditoria),
    KEY idx_auditoria_usuario_fecha (id_usuario, fecha_accion),
    KEY idx_auditoria_admin_fecha (id_admin, fecha_accion),
    KEY idx_auditoria_objeto_registro (objeto, id_registro),
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_auditoria_admin FOREIGN KEY (id_admin) REFERENCES admins (id_admin) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agendas (
    id_agenda INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    id_tutoria INT UNSIGNED NULL,
    fecha DATETIME NOT NULL,
    descripcion TEXT NULL,
    PRIMARY KEY (id_agenda),
    KEY idx_agendas_usuario_fecha (id_usuario, fecha),
    CONSTRAINT fk_agendas_tutoria FOREIGN KEY (id_tutoria) REFERENCES tutorias (id_tutoria) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_agendas_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reportes (
    id_reporte INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    tipo_reporte ENUM('usuario', 'mensaje', 'publicacion', 'solicitud_tutoria') NOT NULL,
    id_objeto INT UNSIGNED NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_reporte DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_reporte),
    KEY idx_reportes_usuario (id_usuario),
    CONSTRAINT fk_reportes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conversaciones (
    id_conversacion INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario1 INT UNSIGNED NOT NULL,
    id_usuario2 INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_conversacion),
    UNIQUE KEY uq_conversaciones_usuarios (id_usuario1, id_usuario2),
    KEY idx_conversaciones_usuario2 (id_usuario2),
    CONSTRAINT fk_conversaciones_usuario1 FOREIGN KEY (id_usuario1) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_conversaciones_usuario2 FOREIGN KEY (id_usuario2) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_conversaciones_usuarios CHECK (id_usuario1 <> id_usuario2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mensajes (
    id_mensaje INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_conversacion INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    contenido TEXT NOT NULL,
    fecha_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_mensaje),
    KEY idx_mensajes_conversacion_fecha (id_conversacion, fecha_envio),
    KEY idx_mensajes_usuario (id_usuario),
    CONSTRAINT fk_mensajes_conversacion FOREIGN KEY (id_conversacion) REFERENCES conversaciones (id_conversacion) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_mensajes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS publicaciones (
    id_publicacion INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_publicacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_publicacion),
    KEY idx_publicaciones_usuario_fecha (id_usuario, fecha_publicacion),
    CONSTRAINT fk_publicaciones_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comentarios (
    id_comentario INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    id_publicacion INT UNSIGNED NOT NULL,
    contenido TEXT NOT NULL,
    fecha_comentario DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_comentario),
    KEY idx_comentarios_publicacion_fecha (id_publicacion, fecha_comentario),
    KEY idx_comentarios_usuario (id_usuario),
    CONSTRAINT fk_comentarios_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_comentarios_publicacion FOREIGN KEY (id_publicacion) REFERENCES publicaciones (id_publicacion) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitud_tutorias (
    id_solicitud INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_alumno INT UNSIGNED NOT NULL,
    id_tutor INT UNSIGNED NOT NULL,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'aceptada', 'rechazada') NOT NULL DEFAULT 'pendiente',
    PRIMARY KEY (id_solicitud),
    KEY idx_solicitudes_alumno_estado (id_alumno, estado),
    KEY idx_solicitudes_tutor_estado (id_tutor, estado),
    CONSTRAINT fk_solicitudes_alumno FOREIGN KEY (id_alumno) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_solicitudes_tutor FOREIGN KEY (id_tutor) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_solicitudes_usuarios CHECK (id_alumno <> id_tutor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ofertas_tutorias (
    id_oferta INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_tutor INT UNSIGNED NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_oferta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_oferta),
    KEY idx_ofertas_tutor_fecha (id_tutor, fecha_oferta),
    CONSTRAINT fk_ofertas_tutor FOREIGN KEY (id_tutor) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tutorias (
    id_tutoria INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_alumno INT UNSIGNED NOT NULL,
    id_tutor INT UNSIGNED NOT NULL,
    fecha_tutoria DATETIME NOT NULL,
    duracion SMALLINT UNSIGNED NOT NULL,
    lugar VARCHAR(255) NOT NULL,
    PRIMARY KEY (id_tutoria),
    KEY idx_tutorias_alumno_fecha (id_alumno, fecha_tutoria),
    KEY idx_tutorias_tutor_fecha (id_tutor, fecha_tutoria),
    CONSTRAINT fk_tutorias_alumno FOREIGN KEY (id_alumno) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_tutorias_tutor FOREIGN KEY (id_tutor) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_tutorias_usuarios CHECK (id_alumno <> id_tutor),
    CONSTRAINT chk_tutorias_duracion CHECK (duracion > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS valoraciones_tutores (
    id_valoracion INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_tutor INT UNSIGNED NOT NULL,
    id_alumno INT UNSIGNED NOT NULL,
    id_tutoria INT UNSIGNED NULL,
    puntuacion DECIMAL(2,1) NOT NULL,
    comentario VARCHAR(160) NULL,
    fecha_valoracion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_valoracion),
    UNIQUE KEY uq_valoraciones_tutoria_alumno (id_tutoria, id_alumno),
    KEY idx_valoraciones_tutor_fecha (id_tutor, fecha_valoracion),
    CONSTRAINT fk_valoraciones_tutor FOREIGN KEY (id_tutor) REFERENCES tutores (id_tutor) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_valoraciones_alumno FOREIGN KEY (id_alumno) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_valoraciones_tutoria FOREIGN KEY (id_tutoria) REFERENCES tutorias (id_tutoria) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_valoraciones_puntuacion CHECK (puntuacion >= 1.0 AND puntuacion <= 5.0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS materias (
    id_materia INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    PRIMARY KEY (id_materia),
    UNIQUE KEY uq_materias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quizzes (
    id_quiz INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_materia INT UNSIGNED NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_quiz),
    KEY idx_quizzes_materia_fecha (id_materia, fecha_creacion),
    CONSTRAINT fk_quizzes_materia FOREIGN KEY (id_materia) REFERENCES materias (id_materia) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS repositorio_materia (
    id_repositorio INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_materia INT UNSIGNED NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    tipo_archivo VARCHAR(50) NOT NULL,
    tamanio_archivo INT UNSIGNED NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_repositorio),
    KEY idx_repositorio_materia_fecha (id_materia, fecha_subida),
    CONSTRAINT fk_repositorio_materia FOREIGN KEY (id_materia) REFERENCES materias (id_materia) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_repositorio_tamanio CHECK (tamanio_archivo >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
