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
     * Catálogo completo para el panel de administración, con la carrera de cada
     * materia y cuántas tutorías se publicaron sobre ella.
     */
    public function listarParaAdmin()
    {
        return $this->database->query(
            'SELECT m.id_materia, m.nombre, m.anio_carrera, m.descripcion,
                    c.id_carrera, c.codigo_carrera, c.nombre_carrera, c.activa,
                    (SELECT COUNT(*) FROM oferta_tutoria o WHERE o.id_materia = m.id_materia) AS tutorias
             FROM materia m
             JOIN carreras c ON c.id_carrera = m.id_carrera
             ORDER BY c.codigo_carrera, CASE WHEN m.anio_carrera IS NULL THEN 99 ELSE m.anio_carrera END, m.nombre'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Carreras con la cantidad de materias de cada una (panel de administración).
     */
    public function listarCarrerasParaAdmin()
    {
        return $this->database->query(
            'SELECT c.id_carrera, c.codigo_carrera, c.nombre_carrera, c.activa,
                    COUNT(m.id_materia) AS materias
             FROM carreras c
             LEFT JOIN materia m ON m.id_carrera = c.id_carrera
             GROUP BY c.id_carrera, c.codigo_carrera, c.nombre_carrera, c.activa
             ORDER BY c.nombre_carrera'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Alta de una materia. Devuelve el id nuevo. Lanza PDOException (1062) si ya existe en esa carrera. */
    public function crear($idCarrera, $nombre, $anio, $descripcion)
    {
        $this->database->prepare(
            'INSERT INTO materia (id_carrera, nombre, anio_carrera, descripcion)
             VALUES (:carrera, :nombre, :anio, :descripcion)'
        )->execute([
            'carrera'     => (int) $idCarrera,
            'nombre'      => $nombre,
            'anio'        => $anio,
            'descripcion' => $descripcion,
        ]);

        return (int) $this->database->lastInsertId();
    }

    /** Edita una materia. Devuelve false si el id no existe. */
    public function actualizar($idMateria, $idCarrera, $nombre, $anio, $descripcion)
    {
        if (!$this->existe($idMateria)) {
            return false;
        }

        $this->database->prepare(
            'UPDATE materia
             SET id_carrera = :carrera, nombre = :nombre, anio_carrera = :anio, descripcion = :descripcion
             WHERE id_materia = :id'
        )->execute([
            'id'          => (int) $idMateria,
            'carrera'     => (int) $idCarrera,
            'nombre'      => $nombre,
            'anio'        => $anio,
            'descripcion' => $descripcion,
        ]);

        return true;
    }

    /**
     * Elimina una materia que no tenga tutorías publicadas.
     * Devuelve 'ok', 'no_existe' o 'con_tutorias'.
     */
    public function eliminar($idMateria)
    {
        $statement = $this->database->prepare(
            'SELECT m.nombre, (SELECT COUNT(*) FROM oferta_tutoria o WHERE o.id_materia = m.id_materia) AS tutorias
             FROM materia m WHERE m.id_materia = :id'
        );
        $statement->execute(['id' => (int) $idMateria]);
        $fila = $statement->fetch(PDO::FETCH_ASSOC);

        if ($fila === false) {
            return ['estado' => 'no_existe'];
        }
        if ((int) $fila['tutorias'] > 0) {
            return ['estado' => 'con_tutorias', 'nombre' => $fila['nombre'], 'tutorias' => (int) $fila['tutorias']];
        }

        $this->database->prepare('DELETE FROM materia WHERE id_materia = :id')->execute(['id' => (int) $idMateria]);

        return ['estado' => 'ok', 'nombre' => $fila['nombre']];
    }

    public function existe($idMateria)
    {
        $statement = $this->database->prepare('SELECT 1 FROM materia WHERE id_materia = :id');
        $statement->execute(['id' => (int) $idMateria]);

        return $statement->fetchColumn() !== false;
    }

    public function existeCarrera($idCarrera)
    {
        $statement = $this->database->prepare('SELECT 1 FROM carreras WHERE id_carrera = :id');
        $statement->execute(['id' => (int) $idCarrera]);

        return $statement->fetchColumn() !== false;
    }

    /** Alta de una carrera (nace activa). Lanza PDOException (1062) si el nombre o el código ya existen. */
    public function crearCarrera($nombre, $codigo)
    {
        $this->database->prepare(
            'INSERT INTO carreras (nombre_carrera, codigo_carrera, activa) VALUES (:nombre, :codigo, 1)'
        )->execute(['nombre' => $nombre, 'codigo' => $codigo]);

        return (int) $this->database->lastInsertId();
    }

    /** Edita nombre, código y estado de una carrera. Devuelve false si el id no existe. */
    public function actualizarCarrera($idCarrera, $nombre, $codigo, $activa)
    {
        if (!$this->existeCarrera($idCarrera)) {
            return false;
        }

        $this->database->prepare(
            'UPDATE carreras SET nombre_carrera = :nombre, codigo_carrera = :codigo, activa = :activa
             WHERE id_carrera = :id'
        )->execute([
            'id'     => (int) $idCarrera,
            'nombre' => $nombre,
            'codigo' => $codigo,
            'activa' => $activa ? 1 : 0,
        ]);

        return true;
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
