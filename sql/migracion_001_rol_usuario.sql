-- ==========================================================================
-- Migración 001: rol de usuario (acceso al panel de administración)
-- Ejecutar UNA sola vez sobre una base creada con una versión anterior de
-- scriptDB.sql. Las instalaciones nuevas ya incluyen la columna.
-- ==========================================================================

USE StudyMatch_DB;

ALTER TABLE usuarios
    ADD COLUMN rol_usuario ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario'
    AFTER estado_usuario;

-- Para convertir una cuenta existente en administrador:
-- UPDATE usuarios SET rol_usuario = 'admin' WHERE correo_usuario = 'admin@frh.utn.edu.ar';
