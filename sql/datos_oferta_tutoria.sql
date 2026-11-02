-- ==========================================================================
-- Datos de prueba: ofertas de tutoría (tabla oferta_tutoria)
-- Requiere haber ejecutado studymatch_db.sql.
--
-- Los ids no están escritos a mano: los tutores se buscan por correo y las
-- materias por nombre, así el script funciona en cualquier base.
-- Se puede ejecutar más de una vez sin duplicar usuarios ni materias.
-- ==========================================================================

USE StudyMatch_DB;
SET NAMES utf8mb4;

-- --------------------------------------------------------------------------
-- 1. Materias de la TUP usadas en las ofertas (asociadas a la carrera TUP)
-- --------------------------------------------------------------------------
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
) AS x
WHERE c.codigo_carrera = 'TUP';

-- --------------------------------------------------------------------------
-- 2. Tutores de prueba (contraseña de los tres: Tutor2026!)
--    Si ya tenés usuarios propios, podés omitir este bloque y cambiar
--    los correos del paso 3.
-- --------------------------------------------------------------------------
INSERT IGNORE INTO usuarios
    (nombre_usuario, correo_usuario, password_usuario, carrera_usuario, anio_cursada_usuario)
VALUES
    ('Julieta Abbiatti', 'jabbiatti@alumnos.frh.utn.edu.ar', '$2y$10$DCO/mTqCmdRlcuezN8YV5epWmyDy.BwrzABryuBE2/SYDlLu62Jzm', 'TUP', '2do'),
    ('Matías Fuentes',  'mfuentes@alumnos.frh.utn.edu.ar', '$2y$10$DCO/mTqCmdRlcuezN8YV5epWmyDy.BwrzABryuBE2/SYDlLu62Jzm', 'TUP', 'graduado'),
    ('Valentina Rojas', 'vrojas@alumnos.frh.utn.edu.ar',   '$2y$10$DCO/mTqCmdRlcuezN8YV5epWmyDy.BwrzABryuBE2/SYDlLu62Jzm', 'TUP', '2do');

-- --------------------------------------------------------------------------
-- 3. Ofertas de tutoría (6, una por cada card de la grilla del dashboard)
-- --------------------------------------------------------------------------
INSERT INTO oferta_tutoria (id_tutor, id_materia, disponibilidad, modalidad, descripcion)
SELECT u.id_usuario, m.id_materia, o.disponibilidad, o.modalidad, o.descripcion
FROM (
    SELECT 'jabbiatti@alumnos.frh.utn.edu.ar' AS correo, 'Programación II' AS materia,
           'Lunes y miércoles de 18:00 a 20:00' AS disponibilidad, 'Virtual' AS modalidad,
           'Punteros dobles, memoria dinámica y listas enlazadas en C++. Resolución de parciales anteriores.' AS descripcion
    UNION ALL SELECT 'jabbiatti@alumnos.frh.utn.edu.ar', 'Programación I',
           'Sábados de 10:00 a 12:00', 'Hibrido',
           'Estructuras de control, funciones y arreglos. Ideal para ingresantes.'
    UNION ALL SELECT 'mfuentes@alumnos.frh.utn.edu.ar', 'Base de Datos I',
           'Martes y jueves de 19:00 a 21:00', 'Virtual',
           'Consultas SQL complejas: JOIN, subconsultas y GROUP BY con casos reales.'
    UNION ALL SELECT 'mfuentes@alumnos.frh.utn.edu.ar', 'Arquitectura y Sistemas Operativos',
           'Viernes de 17:00 a 19:00', 'Presencial en UTN',
           'Repaso de procesos, planificación y gestión de memoria para el final.'
    UNION ALL SELECT 'vrojas@alumnos.frh.utn.edu.ar', 'Matemática',
           'Lunes a viernes de 14:00 a 16:00', 'Presencial en UTN',
           'Lógica proposicional, conjuntos y funciones. Ejercicios paso a paso.'
    UNION ALL SELECT 'vrojas@alumnos.frh.utn.edu.ar', 'Inglés I',
           'Miércoles de 20:00 a 21:30', 'Virtual',
           'Lectura y traducción de documentación técnica de programación.'
) AS o
JOIN usuarios u ON u.correo_usuario = o.correo
JOIN carreras c ON c.codigo_carrera = 'TUP'
JOIN materia  m ON m.id_carrera = c.id_carrera AND m.nombre = o.materia;

-- Verificación
SELECT t.id_tutoria, u.nombre_usuario AS tutor, m.nombre AS materia, t.modalidad, t.disponibilidad
FROM oferta_tutoria t
JOIN usuarios u ON u.id_usuario = t.id_tutor
JOIN materia  m ON m.id_materia = t.id_materia
ORDER BY t.id_tutoria;
