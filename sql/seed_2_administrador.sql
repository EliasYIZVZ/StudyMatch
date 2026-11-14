-- ==========================================================================
-- Datos de prueba 2/3: administrador (correo que empieza con "admin01")
-- Requiere haber ejecutado studymatch_db.sql (materias y carrera TUP).
-- Se puede ejecutar más de una vez sin duplicar datos.
-- Las contraseñas de prueba están en docs/credenciales_prueba.txt
-- ==========================================================================

USE StudyMatch_DB;
SET NAMES utf8mb4;

INSERT IGNORE INTO usuarios (nombre_usuario, correo_usuario, password_usuario)
VALUES ('Administrador 01', 'admin01@alumnos.frh.utn.edu.ar', '$2y$10$GzpNZmEXJoKMiIPWgTZyzu4nBrFiQjdeVnTOSQTTDDqWcCwfpcQSe');

-- Privilegios de administrador
UPDATE usuarios SET rol_usuario = 'admin' WHERE correo_usuario LIKE 'admin01%';

SELECT id_usuario, nombre_usuario, correo_usuario, rol_usuario FROM usuarios WHERE rol_usuario = 'admin';
