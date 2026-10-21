
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
    carrera_usuario VARCHAR(50) NULL, -- completar
    anio_cursada_usuario ENUM('1ro', '2do', '3ro', '4to', '5to', 'graduado') NULL,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uq_usuarios_correo (correo_usuario),
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
