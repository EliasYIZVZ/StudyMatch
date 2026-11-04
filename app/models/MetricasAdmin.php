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
     * @return array{
     *   alumnos: array{total:int, activos:int, inactivos:int, tutores:int, solo_alumnos:int},
     *   tutores: array{activos:int, inactivos:int, total:int},
     *   salas: array{activas:int, suspendidas:int, total:int},
     *   materias: array{total:int, carreras:int, codigos_carreras:string[], por_anio:array<string,int>}
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

        $anios = [];
        foreach ($this->database->query(
            'SELECT anio_carrera, COUNT(*) AS cantidad FROM materia GROUP BY anio_carrera ORDER BY (anio_carrera IS NULL), anio_carrera'
        ) as $fila) {
            $etiqueta = $fila['anio_carrera'] === null ? 'Sin año cargado' : $fila['anio_carrera'] . '° año';
            $anios[$etiqueta] = (int) $fila['cantidad'];
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
                'total'            => array_sum($anios),
                'carreras'         => count($codigos),
                'codigos_carreras' => $codigos,
                'por_anio'         => $anios,
            ],
        ];
    }
}
