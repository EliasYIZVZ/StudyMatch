<?php

/**
 * Modelo de la tabla `reportes` (alertas y moderación de salas y usuarios).
 */
final class Reporte
{
    private $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    /**
     * Registra un nuevo reporte en la base de datos.
     *
     * @param int    $idDenunciante ID del alumno/tutor que envía el reporte.
     * @param string $tipoEntidad   'sala' o 'usuario'.
     * @param int    $idReferencia  ID de la sala o ID del usuario denunciado.
     * @param string $motivo        Motivo predefinido seleccionado.
     * @param string $descripcion   Detalle ampliado de la denuncia.
     * @return int                  ID del reporte insertado.
     */
    public function crear($idDenunciante, $tipoEntidad, $idReferencia, $motivo, $descripcion)
    {
        $stmt = $this->database->prepare(
            'INSERT INTO reportes 
                (id_denunciante, tipo_entidad, id_referencia, motivo, descripcion, estado, fecha_creacion)
             VALUES 
                (:denunciante, :tipo, :referencia, :motivo, :descripcion, "pendiente", NOW())'
        );

        $stmt->execute([
            'denunciante' => (int) $idDenunciante,
            'tipo'        => $tipoEntidad,
            'referencia'  => (int) $idReferencia,
            'motivo'      => trim($motivo),
            'descripcion' => trim($descripcion),
        ]);

        return (int) $this->database->lastInsertId();
    }

    /**
     * Lista reportes para el panel de administración con datos del denunciante
     * y del usuario denunciado (si la entidad es de tipo usuario).
     *
     * @param string|null $estado Filtro por estado ('pendiente', 'finalizado', 'rechazado').
     * @param string|null $tipo   Filtro por tipo ('sala', 'usuario').
     * @return array
     */
    public function listarParaAdmin($estado = null, $tipo = null)
    {
        $sql = "SELECT r.id_reporte,
                       r.id_denunciante,
                       r.tipo_entidad AS tipo,
                       r.id_referencia,
                       r.motivo,
                       r.descripcion,
                       r.estado,
                       r.fecha_creacion,
                       r.fecha_resolucion,
                       ud.nombre_usuario AS denunciante_nombre,
                       ud.correo_usuario AS denunciante_correo,
                       ud.rol_usuario    AS denunciante_rol,
                       ur.nombre_usuario AS reportado_nombre,
                       ur.correo_usuario AS reportado_correo,
                       ur.estado_usuario AS reportado_estado
                FROM reportes r
                JOIN usuarios ud ON ud.id_usuario = r.id_denunciante
                LEFT JOIN usuarios ur ON ur.id_usuario = r.id_referencia AND r.tipo_entidad = 'usuario'
                WHERE 1 = 1";

        $params = [];

        if (!empty($estado) && in_array($estado, ['pendiente', 'finalizado', 'rechazado'], true)) {
            $sql .= " AND r.estado = :estado";
            $params['estado'] = $estado;
        }

        if (!empty($tipo) && in_array($tipo, ['sala', 'usuario'], true)) {
            $sql .= " AND r.tipo_entidad = :tipo";
            $params['tipo'] = $tipo;
        }

        $sql .= " ORDER BY r.fecha_creacion DESC";

        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formatear datos para presentación homogénea en la vista
        return array_map(function ($row) {
            $fecha = new DateTime($row['fecha_creacion']);
            $ahora = new DateTime();
            $diferencia = $ahora->diff($fecha);

            if ($diferencia->days === 0) {
                $fechaStr = 'Hoy, ' . $fecha->format('H:i');
            } elseif ($diferencia->days === 1) {
                $fechaStr = 'Ayer, ' . $fecha->format('H:i');
            } else {
                $fechaStr = $fecha->format('d/m/Y');
            }

            // Iniciales del denunciante para avatar
            $partes = explode(' ', trim($row['denunciante_nombre']));
            $avatar = count($partes) >= 2 
                ? strtoupper(substr($partes[0], 0, 1) . substr($partes[1], 0, 1))
                : strtoupper(substr($row['denunciante_nombre'], 0, 2));

            // Elemento denunciado y subtítulo
            if ($row['tipo'] === 'sala') {
                $elemento = 'Sala de Discord #' . $row['id_referencia'];
                $elementoSub = 'Sala Pública Comunitaria';
            } else {
                $elemento = $row['reportado_nombre'] ?: ('Usuario #' . $row['id_referencia']);
                $elementoSub = $row['reportado_correo'] ?: 'Perfil de Alumno';
            }

            return [
                'id_reporte'         => (int) $row['id_reporte'],
                'tipo'               => $row['tipo'],
                'id_referencia'      => (int) $row['id_referencia'],
                'elemento'           => $elemento,
                'elemento_sub'       => $elementoSub,
                'motivo'             => $row['motivo'],
                'descripcion'        => $row['descripcion'],
                'denunciante'        => $row['denunciante_nombre'],
                'denunciante_rol'    => ucfirst($row['denunciante_rol']),
                'denunciante_avatar' => $avatar,
                'fecha'              => $fechaStr,
                'timestamp'          => $fecha->getTimestamp(),
                'estado'             => $row['estado'],
                'fecha_resolucion'   => $row['fecha_resolucion'],
                'reportado_estado'   => $row['reportado_estado'] ?? 'activo',
            ];
        }, $filas);
    }

    /**
     * Resumen de métricas para los badges y tarjetas KPI.
     *
     * @return array
     */
    public function metricas()
    {
        $stmt = $this->database->query(
            "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN estado = 'finalizado' THEN 1 ELSE 0 END) AS finalizados,
                SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) AS rechazados,
                SUM(CASE WHEN tipo_entidad = 'sala' THEN 1 ELSE 0 END) AS salas,
                SUM(CASE WHEN tipo_entidad = 'usuario' THEN 1 ELSE 0 END) AS usuarios
             FROM reportes"
        );

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total'       => (int) ($data['total'] ?? 0),
            'pendientes'  => (int) ($data['pendientes'] ?? 0),
            'finalizados' => (int) ($data['finalizados'] ?? 0),
            'rechazados'  => (int) ($data['rechazados'] ?? 0),
            'salas'       => (int) ($data['salas'] ?? 0),
            'usuarios'    => (int) ($data['usuarios'] ?? 0),
        ];
    }

    /**
     * Actualiza el estado de un reporte ('finalizado' o 'rechazado').
     *
     * @param int    $idReporte
     * @param string $nuevoEstado 'finalizado' o 'rechazado'
     * @return bool
     */
    public function cambiarEstado($idReporte, $nuevoEstado)
    {
        if (!in_array($nuevoEstado, ['pendiente', 'finalizado', 'rechazado'], true)) {
            return false;
        }

        $fechaRes = ($nuevoEstado === 'pendiente') ? null : date('Y-m-d H:i:s');

        $stmt = $this->database->prepare(
            'UPDATE reportes 
             SET estado = :estado, fecha_resolucion = :fecha 
             WHERE id_reporte = :id'
        );

        return $stmt->execute([
            'estado' => $nuevoEstado,
            'fecha'  => $fechaRes,
            'id'     => (int) $idReporte,
        ]);
    }

    /**
     * Obtiene un reporte específico por su ID.
     */
    public function obtenerPorId($idReporte)
    {
        $stmt = $this->database->prepare(
            'SELECT r.*, ud.nombre_usuario AS denunciante_nombre
             FROM reportes r
             JOIN usuarios ud ON ud.id_usuario = r.id_denunciante
             WHERE r.id_reporte = :id'
        );
        $stmt->execute(['id' => (int) $idReporte]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

