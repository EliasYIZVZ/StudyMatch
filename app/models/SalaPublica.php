<?php

/**
 * Modelo de la tabla `salas_publicas` (salas de estudio vinculadas a canales de Discord).
 */
final class SalaPublica
{
    private $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    /**
     * Lista todas las salas para el panel de administración, incluyendo cantidad
     * de reportes pendientes y datos del creador y materia.
     *
     * @return array
     */
    public function listarParaAdmin()
    {
        $sql = "SELECT s.id_sala,
                       s.titulo,
                       s.descripcion,
                       s.enlace_discord AS discord_url,
                       s.id_creador,
                       s.estado,
                       s.motivo_suspension,
                       s.fecha_creacion,
                       u.nombre_usuario AS creador_nombre,
                       u.rol_usuario AS creador_rol,
                       (SELECT COUNT(*) 
                        FROM reportes r 
                        WHERE r.tipo_entidad = 'sala' 
                          AND r.id_referencia = s.id_sala 
                          AND r.estado = 'pendiente') AS cant_reportes
                FROM salas_publicas s
                JOIN usuarios u ON u.id_usuario = s.id_creador
                ORDER BY (s.estado = 'activa') DESC, s.fecha_creacion DESC";

        $stmt = $this->database->query($sql);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            $fecha = new DateTime($row['fecha_creacion']);
            $ahora = new DateTime();
            $diferencia = $ahora->diff($fecha);

            if ($diferencia->days === 0) {
                if ($diferencia->h === 0) {
                    $fechaStr = 'Hoy, hace ' . max(1, $diferencia->i) . ' min';
                } else {
                    $fechaStr = 'Hoy, hace ' . $diferencia->h . ' h';
                }
            } elseif ($diferencia->days === 1) {
                $fechaStr = 'Ayer';
            } else {
                $fechaStr = $fecha->format('d/m/Y');
            }

            // Iniciales creador
            $partes = explode(' ', trim($row['creador_nombre']));
            $avatar = count($partes) >= 2 
                ? strtoupper(substr($partes[0], 0, 1) . substr($partes[1], 0, 1))
                : strtoupper(substr($row['creador_nombre'], 0, 2));

            return [
                'id_sala'           => (int) $row['id_sala'],
                'titulo'            => $row['titulo'],
                'descripcion'       => $row['descripcion'] ?: 'Espacio abierto de estudio y consulta en Discord.',
                'creador'           => $row['creador_nombre'],
                'creador_rol'       => ucfirst($row['creador_rol']),
                'creador_avatar'    => $avatar,
                'discord_url'       => $row['discord_url'],
                'estado'            => $row['estado'],
                'fecha_creacion'    => $fechaStr,
                'reportes'          => (int) $row['cant_reportes'],
                'motivo_suspension' => $row['motivo_suspension'],
            ];
        }, $filas);
    }

    /**
     * Lista únicamente salas activas (para la vista de alumnos).
     */
    public function listarActivas()
    {
        $sql = "SELECT s.*, u.nombre_usuario AS creador_nombre
                FROM salas_publicas s
                JOIN usuarios u ON u.id_usuario = s.id_creador
                WHERE s.estado = 'activa'
                ORDER BY s.fecha_creacion DESC";

        return $this->database->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Suspende una sala de Discord y asienta el motivo.
     */
    public function suspender($idSala, $motivo)
    {
        $stmt = $this->database->prepare(
            "UPDATE salas_publicas 
             SET estado = 'suspendida', motivo_suspension = :motivo 
             WHERE id_sala = :id"
        );

        return $stmt->execute([
            'motivo' => trim($motivo),
            'id'     => (int) $idSala,
        ]);
    }

    /**
     * Reactiva una sala previamente suspendida.
     */
    public function reactivar($idSala)
    {
        $stmt = $this->database->prepare(
            "UPDATE salas_publicas 
             SET estado = 'activa', motivo_suspension = NULL 
             WHERE id_sala = :id"
        );

        return $stmt->execute(['id' => (int) $idSala]);
    }

    /**
     * Obtiene una sala por su ID.
     */
    public function obtenerPorId($idSala)
    {
        $stmt = $this->database->prepare("SELECT * FROM salas_publicas WHERE id_sala = :id");
        $stmt->execute(['id' => (int) $idSala]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

