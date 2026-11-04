<?php

/**
 * Modelo de la tabla `reportes`: denuncias de alumnos y tutores sobre una sala,
 * una tutoría, un mensaje del chat, un tutor o un alumno.
 *
 * Al crear un reporte se valida que lo reportado exista y que el denunciante
 * pueda reportarlo, se identifica al usuario responsable (a quien se sanciona
 * si el reporte se finaliza) y se guarda una copia del contenido: lo reportado
 * puede editarse o borrarse después y el admin debe ver lo que se denunció.
 */
final class Reporte
{
    const TIPOS = ['sala', 'tutoria', 'mensaje', 'tutor', 'alumno'];

    /** Motivos que se ofrecen (y se aceptan) para cada tipo de reporte. */
    const MOTIVOS = [
        'sala' => [
            'Enlace inválido o caído',
            'Spam o publicidad no autorizada',
            'Venta o fraude académico',
            'Conducta inapropiada en la sala',
            'Otro motivo',
        ],
        'tutoria' => [
            'Información falsa o engañosa',
            'Precio abusivo o cobro indebido',
            'Contenido inapropiado',
            'Publicidad o spam',
            'Otro motivo',
        ],
        'mensaje' => [
            'Lenguaje ofensivo o acoso',
            'Spam o enlaces sospechosos',
            'Pedido de pagos fuera de lo acordado',
            'Contenido inapropiado',
            'Otro motivo',
        ],
        'tutor' => [
            'Inasistencia sin previo aviso',
            'Trato irrespetuoso o conducta inapropiada',
            'Incumplimiento de lo acordado',
            'Perfil falso o información engañosa',
            'Otro motivo',
        ],
        'alumno' => [
            'Inasistencia sin previo aviso',
            'Trato irrespetuoso o conducta inapropiada',
            'Incumplimiento de pago o de lo acordado',
            'Perfil falso o información engañosa',
            'Otro motivo',
        ],
    ];

    /** Nombre visible de cada tipo, para el panel de administración. */
    const ETIQUETAS = [
        'sala'    => 'Sala',
        'tutoria' => 'Tutoría',
        'mensaje' => 'Mensaje',
        'tutor'   => 'Tutor',
        'alumno'  => 'Alumno',
    ];

    const MAX_DESCRIPCION = 1000;

    private $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    /**
     * Valida y registra un reporte.
     *
     * @return array ['ok' => true, 'id' => int] o ['ok' => false, 'error' => string]
     */
    public function crear($idDenunciante, $tipo, $idReferencia, $motivo, $descripcion)
    {
        $idDenunciante = (int) $idDenunciante;
        $idReferencia = (int) $idReferencia;
        $motivo = trim((string) $motivo);
        $descripcion = trim((string) $descripcion);

        if (!in_array($tipo, self::TIPOS, true) || $idReferencia <= 0) {
            return $this->error('El reporte enviado no es válido.');
        }

        if (!in_array($motivo, self::MOTIVOS[$tipo], true)) {
            return $this->error('Elegí un motivo de la lista.');
        }

        if ($descripcion === '') {
            return $this->error('Describí brevemente qué ocurrió.');
        }

        if (mb_strlen($descripcion, 'UTF-8') > self::MAX_DESCRIPCION) {
            return $this->error('La descripción no puede superar los ' . self::MAX_DESCRIPCION . ' caracteres.');
        }

        $entidad = $this->resolverEntidad($tipo, $idReferencia, $idDenunciante);
        if (isset($entidad['error'])) {
            return $entidad;
        }

        if ($entidad['responsable'] !== null && (int) $entidad['responsable'] === $idDenunciante) {
            return $this->error('No podés reportarte a vos mismo.');
        }

        if ($this->existePendiente($idDenunciante, $tipo, $idReferencia)) {
            return $this->error('Ya reportaste esto: tu reporte está pendiente de revisión.');
        }

        $statement = $this->database->prepare(
            'INSERT INTO reportes
                (id_denunciante, tipo_entidad, id_referencia, id_usuario_reportado, motivo, descripcion, evidencia)
             VALUES
                (:denunciante, :tipo, :referencia, :reportado, :motivo, :descripcion, :evidencia)'
        );
        $statement->bindValue(':denunciante', $idDenunciante, PDO::PARAM_INT);
        $statement->bindValue(':tipo', $tipo);
        $statement->bindValue(':referencia', $idReferencia, PDO::PARAM_INT);
        $statement->bindValue(
            ':reportado',
            $entidad['responsable'],
            $entidad['responsable'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );
        $statement->bindValue(':motivo', $motivo);
        $statement->bindValue(':descripcion', $descripcion);
        $statement->bindValue(':evidencia', json_encode($entidad['evidencia'], JSON_UNESCAPED_UNICODE));
        $statement->execute();

        return ['ok' => true, 'id' => (int) $this->database->lastInsertId()];
    }

    /**
     * Comprueba que lo reportado exista y que el denunciante pueda reportarlo.
     *
     * @return array ['responsable' => ?int, 'evidencia' => array] o ['ok' => false, 'error' => string]
     */
    private function resolverEntidad($tipo, $id, $idDenunciante)
    {
        switch ($tipo) {
            case 'sala':
                $statement = $this->database->prepare(
                    "SELECT s.titulo, s.descripcion, s.enlace_discord, s.id_creador, u.nombre_usuario AS creador
                     FROM salas_publicas s
                     JOIN usuarios u ON u.id_usuario = s.id_creador
                     WHERE s.id_sala = :id AND s.estado = 'activa'"
                );
                $statement->execute(['id' => $id]);
                $fila = $statement->fetch(PDO::FETCH_ASSOC);
                if (!$fila) {
                    return $this->error('La sala ya no está disponible.');
                }

                return [
                    'responsable' => (int) $fila['id_creador'],
                    'evidencia' => [
                        'Sala' => $fila['titulo'],
                        'Descripción' => $fila['descripcion'] ?: '(sin descripción)',
                        'Enlace de Discord' => $fila['enlace_discord'],
                        'Creada por' => $fila['creador'],
                    ],
                ];

            case 'tutoria':
                $statement = $this->database->prepare(
                    'SELECT t.id_tutor, t.disponibilidad, t.modalidad, t.descripcion, t.precio, t.estado,
                            m.nombre AS materia, u.nombre_usuario AS tutor
                     FROM oferta_tutoria t
                     JOIN materia m  ON m.id_materia = t.id_materia
                     JOIN usuarios u ON u.id_usuario = t.id_tutor
                     WHERE t.id_tutoria = :id'
                );
                $statement->execute(['id' => $id]);
                $fila = $statement->fetch(PDO::FETCH_ASSOC);
                if (!$fila) {
                    return $this->error('La tutoría ya no está disponible.');
                }

                return [
                    'responsable' => (int) $fila['id_tutor'],
                    'evidencia' => [
                        'Materia' => $fila['materia'],
                        'Publicada por' => $fila['tutor'],
                        'Descripción' => $fila['descripcion'],
                        'Modalidad' => Vista::modalidad($fila['modalidad']),
                        'Disponibilidad' => $fila['disponibilidad'],
                        'Precio' => Vista::precio($fila['precio']),
                        'Estado de la publicación' => $fila['estado'],
                    ],
                ];

            case 'mensaje':
                $statement = $this->database->prepare(
                    'SELECT mt.id_emisor, mt.contenido, mt.fecha,
                            s.estado, s.id_alumno, t.id_tutor,
                            em.nombre_usuario AS emisor
                     FROM mensaje_tutoria mt
                     JOIN solicitud_tutoria s ON s.id_solicitud = mt.id_solicitud
                     JOIN oferta_tutoria t    ON t.id_tutoria = s.id_oferta
                     JOIN usuarios em         ON em.id_usuario = mt.id_emisor
                     WHERE mt.id_mensaje = :id'
                );
                $statement->execute(['id' => $id]);
                $fila = $statement->fetch(PDO::FETCH_ASSOC);

                // Solo participantes del chat, y solo mensajes del otro
                if (!$fila
                    || ((int) $fila['id_alumno'] !== $idDenunciante && (int) $fila['id_tutor'] !== $idDenunciante)) {
                    return $this->error('El mensaje no existe o no tenés acceso a él.');
                }

                if ($fila['estado'] !== 'aceptada') {
                    return $this->error('La tutoría ya está cerrada: no se pueden reportar sus mensajes.');
                }

                return [
                    'responsable' => (int) $fila['id_emisor'],
                    'evidencia' => [
                        'Mensaje' => $fila['contenido'],
                        'Enviado por' => $fila['emisor'],
                        'Fecha' => Vista::fecha($fila['fecha']),
                    ],
                ];

            case 'tutor':
                $usuario = $this->usuarioActivo($id);
                if (!$usuario) {
                    return $this->error('El tutor ya no está disponible.');
                }

                $statement = $this->database->prepare('SELECT COUNT(*) FROM oferta_tutoria WHERE id_tutor = :id');
                $statement->execute(['id' => $id]);
                $publicadas = (int) $statement->fetchColumn();
                if ($publicadas === 0) {
                    return $this->error('Ese usuario no publicó tutorías.');
                }

                return [
                    'responsable' => $id,
                    'evidencia' => $this->evidenciaDeUsuario('Tutor', $usuario) + ['Tutorías publicadas' => (string) $publicadas],
                ];

            case 'alumno':
                $usuario = $this->usuarioActivo($id);
                if (!$usuario) {
                    return $this->error('El alumno ya no está disponible.');
                }

                // Un tutor solo puede reportar a alumnos que le pidieron una tutoría
                $statement = $this->database->prepare(
                    'SELECT COUNT(*)
                     FROM solicitud_tutoria s
                     JOIN oferta_tutoria t ON t.id_tutoria = s.id_oferta
                     WHERE s.id_alumno = :alumno AND t.id_tutor = :tutor'
                );
                $statement->execute(['alumno' => $id, 'tutor' => $idDenunciante]);
                if ((int) $statement->fetchColumn() === 0) {
                    return $this->error('Solo podés reportar a alumnos que te hayan solicitado una tutoría.');
                }

                return [
                    'responsable' => $id,
                    'evidencia' => $this->evidenciaDeUsuario('Alumno', $usuario),
                ];
        }

        return $this->error('El reporte enviado no es válido.');
    }

    private function usuarioActivo($id)
    {
        $statement = $this->database->prepare(
            "SELECT nombre_usuario, correo_usuario, carrera_usuario, anio_cursada_usuario
             FROM usuarios
             WHERE id_usuario = :id AND estado_usuario = 'activo' AND rol_usuario = 'usuario'"
        );
        $statement->execute(['id' => (int) $id]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function evidenciaDeUsuario($rotulo, array $usuario)
    {
        return [
            $rotulo => $usuario['nombre_usuario'],
            'Correo' => $usuario['correo_usuario'],
            'Carrera' => $usuario['carrera_usuario'] ?: '(sin carrera cargada)',
            'Año de cursada' => $usuario['anio_cursada_usuario'] ?: '(sin dato)',
        ];
    }

    private function existePendiente($idDenunciante, $tipo, $idReferencia)
    {
        $statement = $this->database->prepare(
            "SELECT COUNT(*)
             FROM reportes
             WHERE id_denunciante = :denunciante
               AND tipo_entidad = :tipo
               AND id_referencia = :referencia
               AND estado = 'pendiente'"
        );
        $statement->execute([
            'denunciante' => $idDenunciante,
            'tipo' => $tipo,
            'referencia' => $idReferencia,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function error($mensaje)
    {
        return ['ok' => false, 'error' => $mensaje];
    }

    /**
     * Lista los reportes para el panel de administración, con la información
     * ya preparada para la vista (la evidencia guardada al momento del reporte).
     *
     * @return array
     */
    public function listarParaAdmin()
    {
        $statement = $this->database->query(
            "SELECT r.id_reporte, r.tipo_entidad, r.id_referencia, r.id_usuario_reportado,
                    r.motivo, r.descripcion, r.evidencia, r.estado, r.fecha_creacion, r.fecha_resolucion,
                    ud.nombre_usuario AS denunciante_nombre,
                    ud.rol_usuario    AS denunciante_rol,
                    ur.nombre_usuario AS reportado_nombre,
                    ur.correo_usuario AS reportado_correo,
                    ur.estado_usuario AS reportado_estado
             FROM reportes r
             JOIN usuarios ud      ON ud.id_usuario = r.id_denunciante
             LEFT JOIN usuarios ur ON ur.id_usuario = r.id_usuario_reportado
             ORDER BY r.fecha_creacion DESC, r.id_reporte DESC"
        );

        return array_map([$this, 'prepararFila'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    private function prepararFila(array $row)
    {
        $tipo = $row['tipo_entidad'];
        $evidencia = json_decode($row['evidencia'], true);
        $evidencia = is_array($evidencia) ? $evidencia : [];

        $fecha = new DateTime($row['fecha_creacion']);
        $diferencia = (new DateTime())->diff($fecha);
        if ($diferencia->days === 0) {
            $fechaStr = 'Hoy, ' . $fecha->format('H:i');
        } elseif ($diferencia->days === 1) {
            $fechaStr = 'Ayer, ' . $fecha->format('H:i');
        } else {
            $fechaStr = $fecha->format('d/m/Y');
        }

        $reportado = $row['reportado_nombre'] ?: 'Usuario eliminado';
        $elemento = $this->primerValor($evidencia, ['Sala', 'Materia', 'Tutor', 'Alumno', 'Usuario reportado']);
        switch ($tipo) {
            case 'sala':
                $elemento = $elemento ?: 'Sala #' . $row['id_referencia'];
                $sub = 'Sala pública creada por ' . $reportado;
                break;
            case 'tutoria':
                $elemento = $elemento ?: 'Tutoría #' . $row['id_referencia'];
                $sub = 'Tutoría publicada por ' . $reportado;
                break;
            case 'mensaje':
                $elemento = 'Mensaje de ' . $reportado;
                $sub = 'Mensaje de un chat de tutoría';
                break;
            default:
                $elemento = $elemento ?: $reportado;
                $sub = $row['reportado_correo'] ?: self::ETIQUETAS[$tipo];
        }

        $partes = preg_split('/\s+/', trim($row['denunciante_nombre']), -1, PREG_SPLIT_NO_EMPTY);
        $avatar = strtoupper(count($partes) >= 2
            ? mb_substr($partes[0], 0, 1) . mb_substr($partes[1], 0, 1)
            : mb_substr($row['denunciante_nombre'], 0, 2));

        return [
            'id_reporte'         => (int) $row['id_reporte'],
            'tipo'               => $tipo,
            'tipo_etiqueta'      => self::ETIQUETAS[$tipo],
            'id_referencia'      => (int) $row['id_referencia'],
            'elemento'           => $elemento,
            'elemento_sub'       => $sub,
            'motivo'             => $row['motivo'],
            'descripcion'        => $row['descripcion'],
            'evidencia'          => $evidencia,
            'denunciante'        => $row['denunciante_nombre'],
            'denunciante_rol'    => ucfirst($row['denunciante_rol']),
            'denunciante_avatar' => $avatar,
            'reportado'          => $row['reportado_nombre'],
            'reportado_estado'   => $row['reportado_estado'] ?: 'activo',
            'fecha'              => $fechaStr,
            'timestamp'          => $fecha->getTimestamp(),
            'estado'             => $row['estado'],
            'fecha_resolucion'   => $row['fecha_resolucion'],
        ];
    }

    private function primerValor(array $datos, array $claves)
    {
        foreach ($claves as $clave) {
            if (!empty($datos[$clave])) {
                return $datos[$clave];
            }
        }

        return '';
    }

    /**
     * Totales para el encabezado y los filtros del panel.
     *
     * @return array total, pendientes, finalizados, rechazados y un contador por tipo
     */
    public function metricas()
    {
        $data = $this->database->query(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(estado = 'pendiente'), 0)  AS pendientes,
                    COALESCE(SUM(estado = 'finalizado'), 0) AS finalizados,
                    COALESCE(SUM(estado = 'rechazado'), 0)  AS rechazados,
                    COALESCE(SUM(tipo_entidad = 'sala'), 0)    AS sala,
                    COALESCE(SUM(tipo_entidad = 'tutoria'), 0) AS tutoria,
                    COALESCE(SUM(tipo_entidad = 'mensaje'), 0) AS mensaje,
                    COALESCE(SUM(tipo_entidad = 'tutor'), 0)   AS tutor,
                    COALESCE(SUM(tipo_entidad = 'alumno'), 0)  AS alumno
             FROM reportes"
        )->fetch(PDO::FETCH_ASSOC);

        $porTipo = [];
        foreach (self::TIPOS as $tipo) {
            $porTipo[$tipo] = (int) $data[$tipo];
        }

        return [
            'total'       => (int) $data['total'],
            'pendientes'  => (int) $data['pendientes'],
            'finalizados' => (int) $data['finalizados'],
            'rechazados'  => (int) $data['rechazados'],
            'por_tipo'    => $porTipo,
        ];
    }

    /**
     * Cierra un reporte pendiente. Devuelve false si no existe o ya estaba resuelto.
     */
    public function cambiarEstado($idReporte, $nuevoEstado, $idAdmin = null)
    {
        if (!in_array($nuevoEstado, ['finalizado', 'rechazado'], true)) {
            return false;
        }

        $statement = $this->database->prepare(
            "UPDATE reportes
             SET estado = :estado, fecha_resolucion = NOW(), id_admin_resolutor = :admin
             WHERE id_reporte = :id AND estado = 'pendiente'"
        );
        $statement->bindValue(':estado', $nuevoEstado);
        $statement->bindValue(':admin', $idAdmin, $idAdmin === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':id', (int) $idReporte, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    /**
     * Un reporte por su id (columnas crudas), o null si no existe.
     */
    public function obtenerPorId($idReporte)
    {
        $statement = $this->database->prepare('SELECT * FROM reportes WHERE id_reporte = :id');
        $statement->execute(['id' => (int) $idReporte]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
