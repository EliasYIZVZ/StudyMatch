<?php

/**
 * Modelo de la tabla `materia` (cada materia pertenece a una carrera).
 */
final class Materia
{
    private $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    /**
     * Materias de las carreras activas, ordenadas por año del plan y nombre.
     */
    public function listarActivas()
    {
        return $this->database->query(
            'SELECT m.id_materia, m.nombre, m.anio_carrera, c.codigo_carrera
             FROM materia m
             JOIN carreras c ON c.id_carrera = m.id_carrera
             WHERE c.activa = 1
             ORDER BY c.codigo_carrera, CASE WHEN m.anio_carrera IS NULL THEN 99 ELSE m.anio_carrera END, m.nombre'
        )->fetchAll();
    }

    /**
     * Cantidad de materias de las carreras activas.
     */
    public function contarActivas()
    {
        return (int) $this->database->query(
            'SELECT COUNT(*)
             FROM materia m
             JOIN carreras c ON c.id_carrera = m.id_carrera
             WHERE c.activa = 1'
        )->fetchColumn();
    }

    /**
     * ¿Existe la materia y pertenece a una carrera activa?
     */
    public function existeActiva($idMateria)
    {
        $statement = $this->database->prepare(
            'SELECT 1
             FROM materia m
             JOIN carreras c ON c.id_carrera = m.id_carrera
             WHERE m.id_materia = :id AND c.activa = 1
             LIMIT 1'
        );
        $statement->bindValue(':id', (int) $idMateria, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchColumn() !== false;
    }
}
