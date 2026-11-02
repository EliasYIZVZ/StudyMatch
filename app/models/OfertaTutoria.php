<?php

/**
 * Modelo de la tabla `oferta_tutoria` (tutorías publicadas por los tutores).
 */
final class OfertaTutoria
{
    private $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    /**
     * Ofertas visibles para un alumno: activas, de tutores activos y que no sean propias.
     * Las publicadas más recientemente primero. Incluye la solicitud vigente
     * (pendiente / aceptada) que el alumno ya tenga sobre cada oferta, si existe.
     *
     * @param int $idUsuario Usuario logueado (sus propias ofertas se excluyen).
     * @param int $limite    Cantidad máxima de filas a devolver.
     */
    public function listarParaAlumno($idUsuario, $limite)
    {
        $statement = $this->database->prepare(
            "SELECT t.id_tutoria,
                    t.disponibilidad,
                    t.modalidad,
                    t.descripcion,
                    t.precio,
                    t.medios_pago,
                    m.nombre AS materia,
                    u.nombre_usuario AS tutor,
                    u.foto_usuario AS tutor_foto,
                    s.id_solicitud AS mi_solicitud_id,
                    s.estado AS mi_solicitud_estado
             FROM oferta_tutoria t
             JOIN materia m  ON m.id_materia = t.id_materia
             JOIN usuarios u ON u.id_usuario = t.id_tutor
             LEFT JOIN (
                 SELECT id_oferta, MAX(id_solicitud) AS id_solicitud
                 FROM solicitud_tutoria
                 WHERE id_alumno = :id_alumno
                   AND estado IN ('pendiente', 'aceptada')
                 GROUP BY id_oferta
             ) sv ON sv.id_oferta = t.id_tutoria
             LEFT JOIN solicitud_tutoria s ON s.id_solicitud = sv.id_solicitud
             WHERE t.id_tutor <> :id_usuario
               AND t.estado = 'activa'
               AND u.estado_usuario = 'activo'
             ORDER BY t.fecha_publicacion DESC, t.id_tutoria DESC
             LIMIT :limite"
        );
        $statement->bindValue(':id_usuario', (int) $idUsuario, PDO::PARAM_INT);
        $statement->bindValue(':id_alumno', (int) $idUsuario, PDO::PARAM_INT);
        $statement->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Datos mínimos para validar una nueva solicitud sobre la oferta.
     * Devuelve false si la oferta no existe.
     */
    public function buscarParaSolicitud($idOferta)
    {
        $statement = $this->database->prepare(
            "SELECT t.id_tutoria,
                    t.id_tutor,
                    t.estado,
                    u.estado_usuario AS estado_tutor,
                    u.nombre_usuario AS tutor,
                    m.nombre AS materia
             FROM oferta_tutoria t
             JOIN usuarios u ON u.id_usuario = t.id_tutor
             JOIN materia m  ON m.id_materia = t.id_materia
             WHERE t.id_tutoria = :id
             LIMIT 1"
        );
        $statement->bindValue(':id', (int) $idOferta, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
    }

    // ------------------------------------------------------------------
    // Gestión de las ofertas propias (vista "Mis publicaciones")
    // ------------------------------------------------------------------

    /**
     * Ofertas publicadas por un tutor (activas primero), con la cantidad de
     * solicitudes pendientes y totales de cada una.
     */
    public function listarDeTutor($idTutor)
    {
        $statement = $this->database->prepare(
            "SELECT t.id_tutoria,
                    t.id_materia,
                    t.disponibilidad,
                    t.modalidad,
                    t.descripcion,
                    t.precio,
                    t.medios_pago,
                    t.estado,
                    t.fecha_publicacion,
                    m.nombre AS materia,
                    (SELECT COUNT(*) FROM solicitud_tutoria s
                     WHERE s.id_oferta = t.id_tutoria AND s.estado = 'pendiente') AS pendientes,
                    (SELECT COUNT(*) FROM solicitud_tutoria s
                     WHERE s.id_oferta = t.id_tutoria) AS total_solicitudes
             FROM oferta_tutoria t
             JOIN materia m ON m.id_materia = t.id_materia
             WHERE t.id_tutor = :id_tutor
             ORDER BY CASE t.estado WHEN 'activa' THEN 0 ELSE 1 END,
                      t.fecha_publicacion DESC, t.id_tutoria DESC"
        );
        $statement->bindValue(':id_tutor', (int) $idTutor, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Una oferta solo si pertenece al tutor indicado; false en otro caso.
     */
    public function buscarDeTutor($idOferta, $idTutor)
    {
        $statement = $this->database->prepare(
            'SELECT id_tutoria, estado
             FROM oferta_tutoria
             WHERE id_tutoria = :id AND id_tutor = :id_tutor
             LIMIT 1'
        );
        $statement->bindValue(':id', (int) $idOferta, PDO::PARAM_INT);
        $statement->bindValue(':id_tutor', (int) $idTutor, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
    }

    /**
     * Publica una oferta nueva (queda activa).
     * $datos: id_materia, modalidad, disponibilidad, descripcion, precio, medios_pago (string o null).
     */
    public function crear($idTutor, array $datos)
    {
        $statement = $this->database->prepare(
            'INSERT INTO oferta_tutoria
                (id_tutor, id_materia, disponibilidad, modalidad, descripcion, precio, medios_pago)
             VALUES
                (:id_tutor, :id_materia, :disponibilidad, :modalidad, :descripcion, :precio, :medios_pago)'
        );
        $statement->bindValue(':id_tutor', (int) $idTutor, PDO::PARAM_INT);
        $this->vincularDatos($statement, $datos);
        $statement->execute();
    }

    /**
     * Actualiza una oferta del tutor. La pertenencia se valida antes con buscarDeTutor().
     */
    public function actualizar($idOferta, $idTutor, array $datos)
    {
        $statement = $this->database->prepare(
            'UPDATE oferta_tutoria
             SET id_materia = :id_materia,
                 disponibilidad = :disponibilidad,
                 modalidad = :modalidad,
                 descripcion = :descripcion,
                 precio = :precio,
                 medios_pago = :medios_pago
             WHERE id_tutoria = :id AND id_tutor = :id_tutor'
        );
        $statement->bindValue(':id', (int) $idOferta, PDO::PARAM_INT);
        $statement->bindValue(':id_tutor', (int) $idTutor, PDO::PARAM_INT);
        $this->vincularDatos($statement, $datos);
        $statement->execute();
    }

    /**
     * Cambia el estado ('activa' | 'pausada') de una oferta del tutor.
     */
    public function cambiarEstado($idOferta, $idTutor, $estado)
    {
        $statement = $this->database->prepare(
            'UPDATE oferta_tutoria
             SET estado = :estado
             WHERE id_tutoria = :id AND id_tutor = :id_tutor'
        );
        $statement->bindValue(':estado', $estado === 'activa' ? 'activa' : 'pausada');
        $statement->bindValue(':id', (int) $idOferta, PDO::PARAM_INT);
        $statement->bindValue(':id_tutor', (int) $idTutor, PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Elimina la oferta solo si nunca recibió solicitudes (así se conserva el
     * historial y los chats). Devuelve true si se eliminó.
     */
    public function eliminarSinSolicitudes($idOferta, $idTutor)
    {
        $statement = $this->database->prepare(
            'DELETE FROM oferta_tutoria
             WHERE id_tutoria = :id
               AND id_tutor = :id_tutor
               AND NOT EXISTS (SELECT 1 FROM solicitud_tutoria s WHERE s.id_oferta = :id_oferta)'
        );
        $statement->bindValue(':id', (int) $idOferta, PDO::PARAM_INT);
        $statement->bindValue(':id_tutor', (int) $idTutor, PDO::PARAM_INT);
        $statement->bindValue(':id_oferta', (int) $idOferta, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    private function vincularDatos(PDOStatement $statement, array $datos)
    {
        $statement->bindValue(':id_materia', (int) $datos['id_materia'], PDO::PARAM_INT);
        $statement->bindValue(':disponibilidad', $datos['disponibilidad']);
        $statement->bindValue(':modalidad', $datos['modalidad']);
        $statement->bindValue(':descripcion', $datos['descripcion']);
        $statement->bindValue(':precio', $datos['precio']);
        $statement->bindValue(
            ':medios_pago',
            $datos['medios_pago'],
            $datos['medios_pago'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
    }

    /**
     * Resumen para el Panel Tutor: ofertas activas y totales.
     */
    public function resumenDeTutor($idTutor)
    {
        $statement = $this->database->prepare(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(CASE WHEN estado = 'activa' THEN 1 ELSE 0 END), 0) AS activas
             FROM oferta_tutoria
             WHERE id_tutor = :id_tutor"
        );
        $statement->bindValue(':id_tutor', (int) $idTutor, PDO::PARAM_INT);
        $statement->execute();

        return array_map('intval', $statement->fetch());
    }
}
