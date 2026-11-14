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
                       TIMESTAMPDIFF(MINUTE, s.fecha_creacion, NOW()) AS minutos_desde,
                       DATEDIFF(CURDATE(), DATE(s.fecha_creacion)) AS dias_desde,
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
            // El tiempo transcurrido lo calcula MySQL (mismo reloj que guardó la fecha):
            // compararlo con la hora de PHP daba horas de más por la diferencia de zona horaria.
            $minutos = max(0, (int) $row['minutos_desde']);
            $dias = (int) $row['dias_desde'];

            if ($dias <= 0) {
                $fechaStr = $minutos < 60
                    ? 'Hoy, hace ' . max(1, $minutos) . ' min'
                    : 'Hoy, hace ' . intdiv($minutos, 60) . ' h';
            } elseif ($dias === 1) {
                $fechaStr = 'Ayer';
            } else {
                $fechaStr = (new DateTime($row['fecha_creacion']))->format('d/m/Y');
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
     * Lo que ve un usuario en Salas públicas: todas las activas más las propias
     * (aunque estén suspendidas, para que vea el motivo). La más nueva primero.
     */
    public function listarVisiblesPara($idUsuario)
    {
        $stmt = $this->database->prepare(
            "SELECT s.id_sala, s.titulo, s.descripcion, s.enlace_discord, s.id_creador, s.estado,
                    s.motivo_suspension, s.fecha_creacion, u.nombre_usuario AS creador_nombre
             FROM salas_publicas s
             JOIN usuarios u ON u.id_usuario = s.id_creador
             WHERE s.estado = 'activa' OR s.id_creador = :id
             ORDER BY s.fecha_creacion DESC, s.id_sala DESC"
        );
        $stmt->execute(['id' => (int) $idUsuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cantidad de salas que tiene publicadas un usuario (cualquier estado).
     */
    public function contarDeUsuario($idUsuario)
    {
        $stmt = $this->database->prepare('SELECT COUNT(*) FROM salas_publicas WHERE id_creador = :id');
        $stmt->execute(['id' => (int) $idUsuario]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * true si ese enlace de Discord ya está publicado (por cualquier usuario).
     */
    public function existeEnlace($enlace)
    {
        $stmt = $this->database->prepare('SELECT 1 FROM salas_publicas WHERE enlace_discord = :enlace LIMIT 1');
        $stmt->execute(['enlace' => $enlace]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Publica una sala nueva (queda activa). Devuelve su id.
     */
    public function crear($idCreador, $titulo, $descripcion, $enlace)
    {
        $stmt = $this->database->prepare(
            'INSERT INTO salas_publicas (titulo, descripcion, enlace_discord, id_creador)
             VALUES (:titulo, :descripcion, :enlace, :creador)'
        );
        $stmt->bindValue(':titulo', $titulo);
        $stmt->bindValue(':descripcion', $descripcion, $descripcion === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':enlace', $enlace);
        $stmt->bindValue(':creador', (int) $idCreador, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->database->lastInsertId();
    }

    /**
     * Elimina una sala propia. Las suspendidas no se pueden eliminar: quedan
     * a la vista del creador con el motivo que indicó la moderación.
     * Devuelve true si se eliminó.
     */
    public function eliminarPropia($idSala, $idCreador)
    {
        $stmt = $this->database->prepare(
            "DELETE FROM salas_publicas WHERE id_sala = :id AND id_creador = :creador AND estado = 'activa'"
        );
        $stmt->execute(['id' => (int) $idSala, 'creador' => (int) $idCreador]);

        return $stmt->rowCount() > 0;
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

