<?php

final class User
{
    private $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function findByEmail($email)
    {
        $statement = $this->database->prepare(
            'SELECT id_usuario, nombre_usuario, password_usuario
             FROM usuarios
             WHERE correo_usuario = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user;
    }

    public function findById($id)
    {
        $statement = $this->database->prepare(
            'SELECT id_usuario, nombre_usuario, correo_usuario, carrera_usuario, anio_cursada_usuario
             FROM usuarios
             WHERE id_usuario = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch();
    }

    public function emailExists($email)
    {
        $statement = $this->database->prepare(
            'SELECT id_usuario FROM usuarios WHERE correo_usuario = :email LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        return $statement->fetch() !== false;
    }

    public function create($name, $email, $passwordHash)
    {
        $statement = $this->database->prepare(
            'INSERT INTO usuarios (nombre_usuario, correo_usuario, password_usuario)
             VALUES (:name, :email, :password)'
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
        ]);
    }
}
