# Documentación técnica de StudyMatch_DB

## 1. Descripción

`StudyMatch_DB` es la base de datos relacional de StudyMatch. Administra usuarios, tutores, tutorías, agenda, conversaciones, publicaciones, reportes, material de estudio, quizzes y valoraciones de tutores.

Motor: **MySQL/InnoDB**  
Codificación: **utf8mb4**  
Zona horaria configurada por el script: **UTC-03:00**

## 2. Instalación

Ejecutar el archivo `Script_creador_StudyMatchDB.sql` con un usuario que tenga permisos para crear bases de datos y tablas:

El script crea `StudyMatch_DB`, selecciona la base, configura `utf8mb4`, establece la zona horaria, desactiva temporalmente las claves foráneas y las reactiva al finalizar.

## 3. Convenciones

- Las claves primarias siguen el formato `id_<entidad>`.
- Las claves foráneas usan restricciones `fk_...`.
- Los campos `fecha_...` registran fechas de creación, envío, solicitud o realización.
- Las contraseñas deben almacenarse como hash.
- `id_registro` en `auditoria` se interpreta junto con `objeto`; no posee una clave foránea porque puede referirse a distintas tablas.

## 4. Tablas

### `usuarios`

Contiene las cuentas de alumnos, tutores y administradores.

| Campo | Tipo | Descripción |
|---|---|---|
| `id_usuario` | `INT UNSIGNED` | Identificador único. |
| `nombre_usuario` | `VARCHAR(100)` | Nombre del usuario. |
| `password_usuario` | `VARCHAR(255)` | Contraseña almacenada como hash. |
| `correo_usuario` | `VARCHAR(100)` | Correo único. |
| `fecha_registro` | `DATETIME` | Fecha de alta. |
| `estado_usuario` | `ENUM` | `activo` o `inactivo`. |
| `tipo_usuario` | `ENUM` | `alumno`, `tutor` o `admin`. |
| `area_usuario` | `ENUM` | Área académica. |
| `carrera_usuario` | `ENUM` | Carrera. |
| `anio_cursada_usuario` | `ENUM` | Año de cursada o `graduado`. |

Restricción: `correo_usuario` es único.

### `tutores`

Extiende la información de un usuario que actúa como tutor. Un usuario solo puede tener un registro en esta tabla. `anios_experiencia` debe estar entre 0 y 100.

Campos principales: `id_tutor`, `id_usuario`, `titulo_profesional`, `materias` y `anios_experiencia`.

### `admins`

Relaciona usuarios con permisos administrativos. Un usuario solo puede tener un registro administrativo.

### `auditoria`

Registra operaciones relevantes realizadas por usuarios o administradores.

| Campo | Descripción |
|---|---|
| `id_auditoria` | Identificador del evento. |
| `id_usuario` | Usuario que realizó la acción, si corresponde. |
| `id_admin` | Administrador que realizó la acción, si corresponde. |
| `accion` | `login`, `crear`, `modificar` o `eliminar`. |
| `objeto` | `login`, `usuario`, `mensaje`, `publicacion` o `solicitud_tutoria`. |
| `resultado` | `exitoso` o `fallido`. |
| `id_registro` | ID del registro afectado dentro del objeto indicado. |
| `datos_anteriores` | Estado anterior en formato JSON. |
| `datos_nuevos` | Estado nuevo en formato JSON. |
| `ip_origen` | Dirección IP de origen. |
| `fecha_accion` | Fecha y hora del evento. |

Ejemplo de JSON:

```json
{
  "nombre_usuario": "Juan Pérez",
  "correo_usuario": "juan@frh.utn.edu.ar",
  "estado_usuario": "activo"
}
```

Para una modificación, `objeto = 'usuario'` e `id_registro = 5` significan que se modificó el usuario con `id_usuario = 5`.

### `agendas`

Guarda eventos personales del usuario. Puede vincular un evento con una tutoría mediante `id_tutoria`.

### `reportes`

Registra reportes realizados por usuarios sobre otros objetos.

| Campo | Descripción |
|---|---|
| `tipo_reporte` | `usuario`, `mensaje`, `publicacion` o `solicitud_tutoria`. |
| `id_objeto` | ID del objeto reportado. |
| `descripcion` | Motivo del reporte. |

`tipo_reporte` e `id_objeto` se interpretan conjuntamente.

### `conversaciones` y `mensajes`

`conversaciones` relaciona dos usuarios y `mensajes` almacena el contenido enviado. Un usuario no puede conversar consigo mismo.

### `publicaciones` y `comentarios`

Permiten publicar contenido y asociar comentarios a una publicación. Ambas entidades quedan vinculadas al usuario que las creó.

### `solicitud_tutorias`

Gestiona solicitudes entre un alumno y un tutor. Los estados permitidos son `pendiente`, `aceptada` y `rechazada`. El alumno y el tutor no pueden ser el mismo usuario.

### `ofertas_tutorias`

Contiene propuestas de tutoría publicadas por tutores.

### `tutorias`

Registra tutorías coordinadas entre alumnos y tutores.

| Campo | Descripción |
|---|---|
| `id_tutoria` | Identificador de la tutoría. |
| `id_alumno` | Usuario que recibe la tutoría. |
| `id_tutor` | Usuario que brinda la tutoría. |
| `fecha_tutoria` | Fecha y hora programada. |
| `duracion` | Duración; debe ser mayor que cero. |
| `lugar` | Lugar o modalidad. |

### `valoraciones_tutores`

Permite que un alumno valore a un tutor después de una tutoría.

| Campo | Descripción |
|---|---|
| `id_valoracion` | Identificador de la valoración. |
| `id_tutor` | Registro correspondiente de `tutores`. |
| `id_alumno` | Usuario que realiza la valoración. |
| `id_tutoria` | Tutoría relacionada, opcional. |
| `puntuacion` | Valor entre `1.0` y `5.0`, con un decimal. |
| `comentario` | Comentario opcional, hasta 160 caracteres. |
| `fecha_valoracion` | Fecha de la valoración. |

La combinación `id_tutoria` e `id_alumno` es única cuando se informa una tutoría. El promedio se calcula así:

```sql
SELECT
    id_tutor,
    ROUND(AVG(puntuacion), 1) AS reputacion_promedio,
    COUNT(*) AS cantidad_valoraciones
FROM valoraciones_tutores
GROUP BY id_tutor;
```

### `materias`, `quizzes` y `repositorio_materia`

- `materias`: catálogo de materias, con nombre único.
- `quizzes`: evaluaciones asociadas a una materia.
- `repositorio_materia`: archivos asociados a una materia, con nombre, tipo, tamaño y ruta.

El tamaño de un archivo no puede ser negativo.

## 5. Relaciones principales

```text
usuarios ──┬── tutores
           ├── admins
           ├── conversaciones ── mensajes
           ├── publicaciones ── comentarios
           ├── solicitud_tutorias
           ├── tutorias
           ├── agendas
           └── auditoria

tutores ─── valoraciones_tutores ─── tutorias
materias ── quizzes
materias ── repositorio_materia
```

## 6. Recomendaciones para el backend

1. Validar permisos antes de crear, modificar o eliminar registros.
2. Usar hashing seguro para las contraseñas.
3. Usar consultas parametrizadas para evitar inyección SQL.
4. Registrar en `auditoria` las acciones administrativas y cambios relevantes.
5. Ejecutar operaciones relacionadas dentro de una transacción.
6. Verificar que el alumno tenga derecho a valorar la tutoría indicada.
7. No confiar únicamente en los valores enviados por el frontend.

## 7. Compatibilidad y mantenimiento

- Se recomienda MySQL 8.0.16 o superior por el uso de restricciones `CHECK`.
- Antes de modificar una tabla existente, crear una migración y realizar un backup.
- `CREATE TABLE IF NOT EXISTS` no actualiza una tabla que ya existe; las modificaciones deben ejecutarse mediante `ALTER TABLE`.
- Mantener sincronizados este documento, el script SQL y los modelos del backend.
