-- ==========================================================================
-- Datos de prueba 3/3: 3 usuarios sin tutorías
-- Requiere haber ejecutado studymatch_db.sql (materias y carrera TUP).
-- Se puede ejecutar más de una vez sin duplicar datos.
-- Las contraseñas de prueba están en docs/credenciales_prueba.txt
-- ==========================================================================

USE StudyMatch_DB;
SET NAMES utf8mb4;

INSERT IGNORE INTO usuarios
    (nombre_usuario, correo_usuario, password_usuario, carrera_usuario, anio_cursada_usuario)
VALUES
    ('Tomás Aguirre', 'taguirre@alumnos.frh.utn.edu.ar', '$2y$10$yoJYUwHutSIvQVHp11vsruWmU9ES94TM/G3WBWrGyr7T6q6nUzBIy', 'TUP', '1ro'),
    ('Agustina Molina', 'amolina@alumnos.frh.utn.edu.ar', '$2y$10$ycku5LlYOMvRXNcCMzQH8OE.lvbexgjuy8VqPJrZ0Yu6TRRvBkktq', 'TUP', '2do'),
    ('Joaquín Herrera', 'jherrera@alumnos.frh.utn.edu.ar', '$2y$10$ISnfVIP6g1EOB8vDK4aXveh00zVTC6L9Kda9X5I4Fkk/Nq/BjiQ5y', 'TUP', '1ro');

SELECT u.nombre_usuario, COUNT(t.id_tutoria) AS tutorias
FROM usuarios u LEFT JOIN oferta_tutoria t ON t.id_tutor = u.id_usuario
WHERE u.rol_usuario = 'usuario'
GROUP BY u.id_usuario ORDER BY u.id_usuario;
