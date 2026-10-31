USE studymatch_DB;

CREATE TABLE IF NOT EXISTS oferta_tutoria (
    id_tutoria INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_tutor INT UNSIGNED NOT NULL,
    id_materia INT UNSIGNED NOT NULL,
    disponibilidad TEXT NOT NULL,
    modalidad ENUM('Virtual', 'Presencial en UTN', 'Hibrido') NOT NULL,
    descripcion TEXT NOT NULL,
    PRIMARY KEY (id_tutoria),
    CONSTRAINT fk_tutorias_tutor FOREIGN KEY (id_tutor) REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS materia (
    id_materia INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    PRIMARY KEY (id_materia),
    UNIQUE KEY uq_materias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE carreras (
    id_carrera INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre_carrera VARCHAR(150) NOT NULL,
    codigo_carrera VARCHAR(20) NOT NULL,
    activa TINYINT(1) NOT NULL DEFAULT 1,

    PRIMARY KEY (id_carrera),
    UNIQUE KEY uq_carreras_nombre (nombre_carrera),
    UNIQUE KEY uq_carreras_codigo (codigo_carrera)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO carreras (nombre_carrera, codigo_carrera)
VALUES ('Tecnicatura Universitaria en Programación', 'TUP');