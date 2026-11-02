<?php

/**
 * Modelo de la tabla `solicitud_tutoria`.
 *
 * Flujo de estados:
 *   pendiente -> aceptada | rechazada  (tutor)
 *   pendiente | aceptada -> cancelada  (alumno)
 *   aceptada  -> finalizada            (tutor)
 *
 * Cada cambio de estado se hace con un UPDATE condicionado al estado actual
 * y al dueño (tutor de la oferta o alumno que la pidió), así no se puede
 * modificar una solicitud ajena ni saltear pasos del flujo.
 */
final class SolicitudTutoria
{
    /** Transiciones que puede hacer el tutor: acción => [estado actual, estado nuevo]. */
    public const ACCIONES_TUTOR = [
        'aceptar'   => ['pendiente', 'aceptada'],
        'rechazar'  => ['pendiente', 'rechazada'],
        'finalizar' => ['aceptada', 'finalizada'],
    ];

    /** Columnas comunes para listados y detalle de una tutoría. */
    private const SELECT_TUTORIA = "SELECT s.id_solicitud,
                    s.estado,
                    s.mensaje,
                    s.fecha,
                    s.id_alumno,
                    t.id_tutor,
                    t.modalidad,
                    t.disponibilidad,
                    t.precio,
                    t.medios_pago,
                    m.nombre AS materia,
                    a.nombre_usuario AS alumno,
                    a.foto_usuario AS alumno_foto,
                    tu.nombre_usuario AS tutor,
                    tu.foto_usuario AS tutor_foto
             FROM solicitud_tutoria s
             JOIN oferta_tutoria t ON t.id_tutoria = s.id_oferta
             JOIN materia m        ON m.id_materia = t.id_materia
             JOIN usuarios a       ON a.id_usuario = s.id_alumno
             JOIN usuarios tu      ON tu.id_usuario = t.id_tutor";

    /** Orden de los listados: primero las activas, después el historial. */
    private const ORDEN_TUTORIAS = " ORDER BY CASE s.estado WHEN 'aceptada' THEN 0 WHEN 'pendiente' THEN 1 ELSE 2 END,
                      s.fecha DESC, s.id_solicitud DESC";

    private $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function crear($idOferta, $idAlumno, $mensaje)
    {
        $statement = $this->database->prepare(
            'INSERT INTO solicitud_tutoria (id_oferta, id_alumno, mensaje)
             VALUES (:id_oferta, :id_alumno, :mensaje)'
        );
        $statement->bindValue(':id_oferta', (int) $idOferta, PDO::PARAM_INT);
        $statement->bindValue(':id_alumno', (int) $idAlumno, PDO::PARAM_INT);
        $statement->bindValue(':mensaje', $mensaje, $mensaje === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * ¿El alumno ya tiene una solicitud pendiente o aceptada para esta oferta?
     */
    public function existeVigente($idOferta, $idAlumno)
    {
        $statement = $this->database->prepare(
            "SELECT 1
             FROM solicitud_tutoria
             WHERE id_oferta = :id_oferta
               AND id_alumno = :id_alumno
               AND estado IN ('pendiente', 'aceptada')
             LIMIT 1"
        );
        $statement->bindValue(':id_oferta', (int) $idOferta, PDO::PARAM_INT);
        $statement->bindValue(':id_alumno', (int) $idAlumno, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchColumn() !== false;
    }

    /**
     * Solicitudes pendientes recibidas por un tutor en sus ofertas (las más antiguas primero).
     */
    public function listarPendientesDeTutor($idTutor)
    {
        $statement = $this->database->prepare(
            "SELECT s.id_solicitud,
                    s.mensaje,
                    s.fecha,
                    a.nombre_usuario AS alumno,
                    a.foto_usuario AS alumno_foto,
                    m.nombre AS materia
             FROM solicitud_tutoria s
             JOIN oferta_tutoria t ON t.id_tutoria = s.id_oferta
             JOIN materia m        ON m.id_materia = t.id_materia
             JOIN usuarios a       ON a.id_usuario = s.id_alumno
             WHERE t.id_tutor = :id_tutor
               AND s.estado = 'pendiente'
             ORDER BY s.fecha ASC, s.id_solicitud ASC"
        );
        $statement->bindValue(':id_tutor', (int) $idTutor, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Aplica una acción del tutor. Devuelve true si la solicitud cambió de estado.
     */
    public function responderComoTutor($idSolicitud, $idTutor, $accion)
    {
        if (!isset(self::ACCIONES_TUTOR[$accion])) {
            return false;
        }

        list($desde, $hasta) = self::ACCIONES_TUTOR[$accion];

        $statement = $this->database->prepare(
            'UPDATE solicitud_tutoria
             SET estado = :hasta, fecha_actualizacion = CURRENT_TIMESTAMP
             WHERE id_solicitud = :id_solicitud
               AND estado = :desde
               AND id_oferta IN (SELECT id_tutoria FROM oferta_tutoria WHERE id_tutor = :id_tutor)'
        );
        $statement->bindValue(':hasta', $hasta);
        $statement->bindValue(':desde', $desde);
        $statement->bindValue(':id_solicitud', (int) $idSolicitud, PDO::PARAM_INT);
        $statement->bindValue(':id_tutor', (int) $idTutor, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    /**
     * El alumno cancela su solicitud pendiente o aceptada. Devuelve true si se canceló.
     */
    public function cancelarComoAlumno($idSolicitud, $idAlumno)
    {
        $statement = $this->database->prepare(
            "UPDATE solicitud_tutoria
             SET estado = 'cancelada', fecha_actualizacion = CURRENT_TIMESTAMP
             WHERE id_solicitud = :id_solicitud
               AND id_alumno = :id_alumno
               AND estado IN ('pendiente', 'aceptada')"
        );
        $statement->bindValue(':id_solicitud', (int) $idSolicitud, PDO::PARAM_INT);
        $statement->bindValue(':id_alumno', (int) $idAlumno, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    /**
     * Tutorías que el usuario solicitó (rol alumno), con mensajes sin leer.
     */
    public function listarComoAlumno($idUsuario)
    {
        return $this->listarTutorias('s.id_alumno = :id_usuario', $idUsuario);
    }

    /**
     * Tutorías recibidas en las ofertas del usuario (rol tutor), con mensajes sin leer.
     */
    public function listarComoTutor($idUsuario)
    {
        return $this->listarTutorias('t.id_tutor = :id_usuario', $idUsuario);
    }

    private function listarTutorias($condicion, $idUsuario)
    {
        $sql = str_replace('SELECT s.id_solicitud,', 'SELECT s.id_solicitud,
                    (SELECT COUNT(*) FROM mensaje_tutoria mt
                     WHERE mt.id_solicitud = s.id_solicitud
                       AND mt.id_emisor <> :id_lector
                       AND mt.leido = 0) AS no_leidos,', self::SELECT_TUTORIA)
            . ' WHERE ' . $condicion
            . self::ORDEN_TUTORIAS;

        $statement = $this->database->prepare($sql);
        $statement->bindValue(':id_lector', (int) $idUsuario, PDO::PARAM_INT);
        $statement->bindValue(':id_usuario', (int) $idUsuario, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Una tutoría, solo si el usuario es el alumno que la pidió o el tutor de la oferta.
     * Devuelve false en cualquier otro caso.
     */
    public function buscarParaParticipante($idSolicitud, $idUsuario)
    {
        $statement = $this->database->prepare(
            self::SELECT_TUTORIA . '
             WHERE s.id_solicitud = :id_solicitud
               AND (s.id_alumno = :id_alumno OR t.id_tutor = :id_tutor)
             LIMIT 1'
        );
        $statement->bindValue(':id_solicitud', (int) $idSolicitud, PDO::PARAM_INT);
        $statement->bindValue(':id_alumno', (int) $idUsuario, PDO::PARAM_INT);
        $statement->bindValue(':id_tutor', (int) $idUsuario, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
    }

    /**
     * Cantidad de solicitudes pendientes en las ofertas de un tutor.
     */
    public function contarPendientesDeTutor($idTutor)
    {
        $statement = $this->database->prepare(
            "SELECT COUNT(*)
             FROM solicitud_tutoria s
             JOIN oferta_tutoria t ON t.id_tutoria = s.id_oferta
             WHERE t.id_tutor = :id_tutor
               AND s.estado = 'pendiente'"
        );
        $statement->bindValue(':id_tutor', (int) $idTutor, PDO::PARAM_INT);
        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    /**
     * Resumen para el Panel Tutor: solicitudes por estado y alumnos distintos atendidos.
     */
    public function resumenDeTutor($idTutor)
    {
        $statement = $this->database->prepare(
            "SELECT COALESCE(SUM(CASE WHEN s.estado = 'pendiente'  THEN 1 ELSE 0 END), 0) AS pendientes,
                    COALESCE(SUM(CASE WHEN s.estado = 'aceptada'   THEN 1 ELSE 0 END), 0) AS en_curso,
                    COALESCE(SUM(CASE WHEN s.estado = 'finalizada' THEN 1 ELSE 0 END), 0) AS finalizadas,
                    COUNT(DISTINCT CASE WHEN s.estado IN ('aceptada', 'finalizada') THEN s.id_alumno END) AS alumnos
             FROM solicitud_tutoria s
             JOIN oferta_tutoria t ON t.id_tutoria = s.id_oferta
             WHERE t.id_tutor = :id_tutor"
        );
        $statement->bindValue(':id_tutor', (int) $idTutor, PDO::PARAM_INT);
        $statement->execute();

        return array_map('intval', $statement->fetch());
    }
}
