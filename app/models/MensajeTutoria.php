<?php

/**
 * Modelo de la tabla `mensaje_tutoria` (chat de cada solicitud de tutoría).
 * Los permisos (quién puede leer / escribir) los valida TutoriaController.
 */
final class MensajeTutoria
{
    private $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    /**
     * Mensajes de una solicitud con id mayor a $desdeId, en orden cronológico.
     */
    public function listar($idSolicitud, $desdeId = 0)
    {
        $statement = $this->database->prepare(
            'SELECT id_mensaje, id_emisor, contenido, fecha
             FROM mensaje_tutoria
             WHERE id_solicitud = :id_solicitud
               AND id_mensaje > :desde
             ORDER BY id_mensaje ASC
             LIMIT 500'
        );
        $statement->bindValue(':id_solicitud', (int) $idSolicitud, PDO::PARAM_INT);
        $statement->bindValue(':desde', (int) $desdeId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Guarda un mensaje y lo devuelve tal como quedó en la base.
     */
    public function crear($idSolicitud, $idEmisor, $contenido)
    {
        $statement = $this->database->prepare(
            'INSERT INTO mensaje_tutoria (id_solicitud, id_emisor, contenido)
             VALUES (:id_solicitud, :id_emisor, :contenido)'
        );
        $statement->bindValue(':id_solicitud', (int) $idSolicitud, PDO::PARAM_INT);
        $statement->bindValue(':id_emisor', (int) $idEmisor, PDO::PARAM_INT);
        $statement->bindValue(':contenido', $contenido);
        $statement->execute();

        $consulta = $this->database->prepare(
            'SELECT id_mensaje, id_emisor, contenido, fecha
             FROM mensaje_tutoria
             WHERE id_mensaje = :id'
        );
        $consulta->bindValue(':id', (int) $this->database->lastInsertId(), PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch();
    }

    /**
     * Marca como leídos los mensajes de la otra parte.
     */
    public function marcarLeidos($idSolicitud, $idLector)
    {
        $statement = $this->database->prepare(
            'UPDATE mensaje_tutoria
             SET leido = 1
             WHERE id_solicitud = :id_solicitud
               AND id_emisor <> :id_lector
               AND leido = 0'
        );
        $statement->bindValue(':id_solicitud', (int) $idSolicitud, PDO::PARAM_INT);
        $statement->bindValue(':id_lector', (int) $idLector, PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Mensajes sin leer que recibió un usuario en todas sus tutorías (como alumno o tutor).
     */
    public function contarNoLeidos($idUsuario)
    {
        $statement = $this->database->prepare(
            'SELECT COUNT(*)
             FROM mensaje_tutoria mt
             JOIN solicitud_tutoria s ON s.id_solicitud = mt.id_solicitud
             JOIN oferta_tutoria t    ON t.id_tutoria = s.id_oferta
             WHERE mt.leido = 0
               AND mt.id_emisor <> :id_emisor
               AND (s.id_alumno = :id_alumno OR t.id_tutor = :id_tutor)'
        );
        $statement->bindValue(':id_emisor', (int) $idUsuario, PDO::PARAM_INT);
        $statement->bindValue(':id_alumno', (int) $idUsuario, PDO::PARAM_INT);
        $statement->bindValue(':id_tutor', (int) $idUsuario, PDO::PARAM_INT);
        $statement->execute();

        return (int) $statement->fetchColumn();
    }
}
