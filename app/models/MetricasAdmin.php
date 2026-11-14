<?php

/**
 * Métricas del resumen del panel de administración (cards y gráficos).
 *
 * Criterios:
 *  - Alumno: toda cuenta con rol 'usuario' (cualquier alumno puede además tutorar).
 *  - Tutor: cuenta que publicó al menos una oferta de tutoría. "Activo" = cuenta activa.
 *  - Sala activa / suspendida: estado de `salas_publicas`.
 *  - Materias: catálogo completo, agrupado por año del plan (NULL = sin año cargado).
 */
final class MetricasAdmin
{
    private $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    /**
     * Contadores del menú lateral del admin: los mismos en todas las vistas.
     *
     * @return array{usuarios:int, materias:int, salas:int, reportes:int}
     */
    public function contadoresMenu()
    {
        $fila = $this->database->query(
            "SELECT (SELECT COUNT(*) FROM usuarios WHERE rol_usuario = 'usuario') AS usuarios,
                    (SELECT COUNT(*) FROM materia) AS materias,
                    (SELECT COUNT(*) FROM salas_publicas WHERE estado = 'activa') AS salas,
                    (SELECT COUNT(*) FROM reportes WHERE estado = 'pendiente') AS reportes"
        )->fetch();

        return array_map('intval', $fila);
    }

    /**
     * @return array{
     *   alumnos: array{total:int, activos:int, inactivos:int, tutores:int, solo_alumnos:int},
     *   tutores: array{activos:int, inactivos:int, total:int},
     *   salas: array{activas:int, suspendidas:int, total:int},
     *   materias: array{total:int, carreras:int, codigos_carreras:string[], por_carrera:array<string,int>}
     * }
     */
    public function resumen()
    {
        // Usuarios y tutores en una sola consulta.
        $u = $this->database->query(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(u.estado_usuario = 'activo'), 0) AS activos,
                    COALESCE(SUM(u.estado_usuario = 'inactivo'), 0) AS inactivos,
                    COALESCE(SUM(t.id_tutor IS NOT NULL), 0) AS tutores,
                    COALESCE(SUM(t.id_tutor IS NOT NULL AND u.estado_usuario = 'activo'), 0) AS tutores_activos,
                    COALESCE(SUM(t.id_tutor IS NOT NULL AND u.estado_usuario = 'inactivo'), 0) AS tutores_inactivos
             FROM usuarios u
             LEFT JOIN (SELECT DISTINCT id_tutor FROM oferta_tutoria) t ON t.id_tutor = u.id_usuario
             WHERE u.rol_usuario = 'usuario'"
        )->fetch();

        $s = $this->database->query(
            "SELECT COALESCE(SUM(estado = 'activa'), 0) AS activas,
                    COALESCE(SUM(estado = 'suspendida'), 0) AS suspendidas,
                    COUNT(*) AS total
             FROM salas_publicas"
        )->fetch();

        // Materias por carrera (sigla => cantidad); solo las carreras que tienen materias
        $porCarrera = [];
        foreach ($this->database->query(
            'SELECT c.codigo_carrera, COUNT(m.id_materia) AS cantidad
             FROM carreras c
             JOIN materia m ON m.id_carrera = c.id_carrera
             GROUP BY c.id_carrera, c.codigo_carrera
             ORDER BY cantidad DESC, c.codigo_carrera'
        ) as $fila) {
            $porCarrera[$fila['codigo_carrera']] = (int) $fila['cantidad'];
        }

        $codigos = $this->database->query(
            'SELECT codigo_carrera FROM carreras WHERE activa = 1 ORDER BY codigo_carrera'
        )->fetchAll(PDO::FETCH_COLUMN);

        return [
            'alumnos' => [
                'total'        => (int) $u['total'],
                'activos'      => (int) $u['activos'],
                'inactivos'    => (int) $u['inactivos'],
                'tutores'      => (int) $u['tutores'],
                'solo_alumnos' => (int) $u['total'] - (int) $u['tutores'],
            ],
            'tutores' => [
                'activos'   => (int) $u['tutores_activos'],
                'inactivos' => (int) $u['tutores_inactivos'],
                'total'     => (int) $u['tutores'],
            ],
            'salas' => [
                'activas'     => (int) $s['activas'],
                'suspendidas' => (int) $s['suspendidas'],
                'total'       => (int) $s['total'],
            ],
            'materias' => [
                'total'            => array_sum($porCarrera),
                'carreras'         => count($codigos),
                'codigos_carreras' => $codigos,
                'por_carrera'      => $porCarrera,
            ],
        ];
    }
}
