-- ==========================================================================
-- Datos de prueba 1/3: 5 usuarios con 2 tutorías cada uno
-- Requiere haber ejecutado studymatch_db.sql (materias y carrera TUP).
-- Se puede ejecutar más de una vez sin duplicar datos.
-- Las contraseñas de prueba están en docs/credenciales_prueba.txt
-- ==========================================================================

USE StudyMatch_DB;
SET NAMES utf8mb4;

INSERT IGNORE INTO usuarios
    (nombre_usuario, correo_usuario, password_usuario, carrera_usuario, anio_cursada_usuario)
VALUES
    ('Lucía Fernández', 'lfernandez@alumnos.frh.utn.edu.ar', '$2y$10$FCPxeP8hSfHbl.lwuQ23bO5LNh7F5e3H2cIRLM2AvY7Zj5tthTv3e', 'TUP', '2do'),
    ('Martín Sosa', 'msosa@alumnos.frh.utn.edu.ar', '$2y$10$obqdjU4Afn.OTSRpb4Bfc.S7.C72kzQKwMzJbRt8aZVVp.W3tS1AK', 'TUP', 'graduado'),
    ('Camila Ortega', 'cortega@alumnos.frh.utn.edu.ar', '$2y$10$qwwAqu571WVm81gBYOmKfe30hYQfXzYGEzortCH.uRWq4xrqj2lt6', 'TUP', '2do'),
    ('Nicolás Benítez', 'nbenitez@alumnos.frh.utn.edu.ar', '$2y$10$6zZYGV3HdfGkuxRCJi106O9tuhsxNjkljZE8fzNPgVrluc21P6QEO', 'TUP', '3ro'),
    ('Sofía Paredes', 'sparedes@alumnos.frh.utn.edu.ar', '$2y$10$CB6EYEOn1JVO0ZMYyvFka.3JyfMVU6/VBLBclOhAkaWVtqyAPchdu', 'TUP', '2do');

INSERT INTO oferta_tutoria (id_tutor, id_materia, disponibilidad, modalidad, descripcion, precio, medios_pago)
SELECT u.id_usuario, m.id_materia, o.disponibilidad, o.modalidad, o.descripcion, o.precio, o.medios_pago
FROM (
    SELECT 'lfernandez@alumnos.frh.utn.edu.ar' AS correo, 'Programación I' AS materia, 'Sábados de 10:00 a 12:00' AS disponibilidad, 'Hibrido' AS modalidad, 'Estructuras de control, funciones y arreglos. Ideal para ingresantes.' AS descripcion, 0.00 AS precio, NULL AS medios_pago
    UNION ALL SELECT 'lfernandez@alumnos.frh.utn.edu.ar', 'Programación II', 'Lunes y miércoles de 18:00 a 20:00', 'Virtual', 'Punteros, memoria dinámica y listas enlazadas. Resolución de parciales anteriores.', 3500.00, 'efectivo,transferencia'
    UNION ALL SELECT 'msosa@alumnos.frh.utn.edu.ar', 'Base de Datos I', 'Martes y jueves de 19:00 a 21:00', 'Virtual', 'Consultas SQL: JOIN, subconsultas y GROUP BY con casos reales.', 4000.00, 'transferencia,mercado_pago'
    UNION ALL SELECT 'msosa@alumnos.frh.utn.edu.ar', 'Base de Datos II', 'Sábados de 15:00 a 17:00', 'Virtual', 'Triggers, procedimientos y transacciones paso a paso.', 4500.00, 'transferencia,mercado_pago'
    UNION ALL SELECT 'cortega@alumnos.frh.utn.edu.ar', 'Matemática', 'Lunes a viernes de 14:00 a 16:00', 'Presencial en UTN', 'Lógica proposicional, conjuntos y funciones con ejercicios guiados.', 0.00, NULL
    UNION ALL SELECT 'cortega@alumnos.frh.utn.edu.ar', 'Probabilidad y Estadística', 'Miércoles de 16:00 a 18:00', 'Hibrido', 'Probabilidad condicional, distribuciones y estadística descriptiva.', 3000.00, 'efectivo'
    UNION ALL SELECT 'nbenitez@alumnos.frh.utn.edu.ar', 'Arquitectura y Sistemas Operativos', 'Viernes de 17:00 a 19:00', 'Presencial en UTN', 'Procesos, planificación y gestión de memoria para el final.', 3500.00, 'efectivo,transferencia'
    UNION ALL SELECT 'nbenitez@alumnos.frh.utn.edu.ar', 'Programación III', 'Domingos de 18:00 a 20:00', 'Virtual', 'Programación orientada a objetos y diseño web.', 0.00, NULL
    UNION ALL SELECT 'sparedes@alumnos.frh.utn.edu.ar', 'Inglés I', 'Miércoles de 20:00 a 21:30', 'Virtual', 'Lectura y traducción de documentación técnica de programación.', 0.00, NULL
    UNION ALL SELECT 'sparedes@alumnos.frh.utn.edu.ar', 'Metodología de Sistemas I', 'Jueves de 18:00 a 20:00', 'Hibrido', 'Análisis y diseño de sistemas: diagramas y casos de uso.', 2500.00, 'mercado_pago'
) AS o
JOIN usuarios u ON u.correo_usuario = o.correo
JOIN carreras c ON c.codigo_carrera = 'TUP'
JOIN materia  m ON m.id_carrera = c.id_carrera AND m.nombre = o.materia
WHERE NOT EXISTS (SELECT 1 FROM oferta_tutoria t WHERE t.id_tutor = u.id_usuario AND t.id_materia = m.id_materia);

SELECT u.nombre_usuario AS tutor, COUNT(t.id_tutoria) AS tutorias
FROM usuarios u LEFT JOIN oferta_tutoria t ON t.id_tutor = u.id_usuario
GROUP BY u.id_usuario ORDER BY u.id_usuario;
