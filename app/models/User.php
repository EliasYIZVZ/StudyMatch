<?php

/**
 * Modelo de la tabla `usuarios`.
 */
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
            'SELECT id_usuario, nombre_usuario, correo_usuario, password_usuario,
                    estado_usuario, rol_usuario
             FROM usuarios
             WHERE correo_usuario = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        return $statement->fetch();
    }

    public function findById($id)
    {
        $statement = $this->database->prepare(
            'SELECT id_usuario, nombre_usuario, correo_usuario, estado_usuario, rol_usuario,
                    carrera_usuario, anio_cursada_usuario, foto_usuario
             FROM usuarios
             WHERE id_usuario = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch();
    }

    /**
     * ¿La cuenta existe y está activa? (la usa el Router en cada petición para
     * cerrar la sesión de quien fue inactivado mientras estaba conectado).
     */
    public function estaActivo($id)
    {
        $statement = $this->database->prepare(
            "SELECT 1 FROM usuarios WHERE id_usuario = :id AND estado_usuario = 'activo' LIMIT 1"
        );
        $statement->execute(['id' => (int) $id]);

        return $statement->fetchColumn() !== false;
    }

    /**
     * Alumnos y tutores (no administradores) para la gestión de usuarios del panel admin.
     * "publicadas" son las tutorías que publicó: más de cero lo convierte en tutor.
     */
    public function listarParaAdmin($limite = 500)
    {
        $statement = $this->database->prepare(
            "SELECT u.id_usuario, u.nombre_usuario, u.correo_usuario, u.estado_usuario,
                    u.carrera_usuario, u.anio_cursada_usuario, u.fecha_registro,
                    (SELECT COUNT(*) FROM oferta_tutoria t WHERE t.id_tutor = u.id_usuario) AS publicadas
             FROM usuarios u
             WHERE u.rol_usuario = 'usuario'
             ORDER BY u.fecha_registro DESC, u.id_usuario DESC
             LIMIT :limite"
        );
        $statement->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Inactiva o reactiva una cuenta de alumno/tutor. Los administradores no se tocan.
     * Devuelve los datos básicos del usuario, o false si no existe o es administrador.
     */
    public function cambiarEstado($id, $estado)
    {
        $statement = $this->database->prepare(
            "SELECT id_usuario, nombre_usuario FROM usuarios WHERE id_usuario = :id AND rol_usuario = 'usuario'"
        );
        $statement->execute(['id' => (int) $id]);
        $usuario = $statement->fetch();

        if ($usuario === false) {
            return false;
        }

        $statement = $this->database->prepare('UPDATE usuarios SET estado_usuario = :estado WHERE id_usuario = :id');
        $statement->execute(['estado' => $estado === 'activo' ? 'activo' : 'inactivo', 'id' => (int) $id]);

        return $usuario;
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

    /**
     * Guarda la ruta relativa de la foto de perfil (null = sin foto, se muestran las iniciales).
     */
    public function actualizarFoto($id, $ruta)
    {
        $statement = $this->database->prepare(
            'UPDATE usuarios SET foto_usuario = :foto WHERE id_usuario = :id'
        );
        $statement->bindValue(':foto', $ruta, $ruta === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $statement->execute();
    }
}
