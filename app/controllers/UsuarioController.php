<?php

final class UsuarioController extends Controller
{
    /**
     * Devuelve en JSON los datos del usuario de la sesión actual.
     */
    public function actual()
    {
        try {
            $user = (new User(Database::getConnection()))->findById(Session::userId());
        } catch (PDOException $exception) {
            error_log('Error al cargar el usuario de la sesión: ' . $exception->getMessage());
            $this->json(['error' => 'No se pudo cargar el usuario.'], 500);
        }

        if ($user === false || $user['estado_usuario'] !== 'activo') {
            Session::destroy();
            $this->json(['error' => 'No se encontró el usuario de la sesión.'], 401);
        }

        $this->json([
            'nombre' => $user['nombre_usuario'],
            'email' => $user['correo_usuario'],
            'carrera' => $user['carrera_usuario'],
            'nivel' => $user['anio_cursada_usuario'],
        ]);
    }
}
