<?php

/**
 * Modelo de la tabla `consultausuarios` (mensajes del formulario de contacto).
 */
final class Consulta
{
    private $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function create($nombre, $email, $motivo, $asunto, $mensaje)
    {
        $statement = $this->database->prepare(
            'INSERT INTO consultausuarios
                (nombre_usuario, correo_usuario, motivo, asunto, mensaje)
             VALUES
                (:nombre, :email, :motivo, :asunto, :mensaje)'
        );
        $statement->execute([
            'nombre' => $nombre,
            'email' => $email,
            'motivo' => $motivo,
            'asunto' => $asunto,
            'mensaje' => $mensaje,
        ]);
    }
}
