<?php

require_once __DIR__ . '/../config/Database.php';

final class ContactoController
{
    /**
     * Valida los datos del formulario y registra la consulta del usuario.
     */
    public function validarMensaje()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: contacto.html', true, 303);
            exit();
        }

        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : '';
        $asunto = isset($_POST['asunto']) ? $_POST['asunto'] : '';
        $mensaje = isset($_POST['mensaje']) ? $_POST['mensaje'] : '';

        if (
            !is_string($nombre)
            || !is_string($email)
            || !is_string($motivo)
            || !is_string($asunto)
            || !is_string($mensaje)
        ) {
            http_response_code(400);
            exit('Error: Todos los campos son obligatorios.');
        }

        $nombre = trim($nombre);
        $email = trim($email);
        $motivo = trim($motivo);
        $asunto = trim($asunto);
        $mensaje = trim($mensaje);

        if ($nombre === '' || $email === '' || $motivo === '' || $asunto === '' || $mensaje === '') {
            http_response_code(400);
            exit('Error: Todos los campos son obligatorios.');
        }

        $longitudes = [
            $this->longitud($nombre),
            $this->longitud($email),
            $this->longitud($motivo),
            $this->longitud($asunto),
            $this->longitud($mensaje),
        ];
        if (in_array(false, $longitudes, true)) {
            http_response_code(400);
            exit('Error: Los campos contienen caracteres no válidos.');
        }

        if (
            $longitudes[0] > 100
            || $longitudes[1] > 100
            || $longitudes[2] > 255
            || $longitudes[3] > 255
        ) {
            http_response_code(400);
            exit('Error: Uno o más campos superan la longitud permitida.');
        }

        if ($longitudes[4] > 600) {
            http_response_code(400);
            exit('Error: El mensaje no puede superar los 600 caracteres.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            exit('Error: El formato del correo no es válido.');
        }

        try {
            $database = Database::getConnection();
            $statement = $database->prepare(
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

            header('Location: contacto.html?consulta=enviada', true, 303);
            exit();
        } catch (PDOException $exception) {
            error_log('Error al registrar la consulta de contacto: ' . $exception->getMessage());
            http_response_code(500);
            exit('No se pudo registrar tu consulta. Por favor, intentá nuevamente más tarde.');
        }
    }

    private function longitud($valor)
    {
        return preg_match_all('/./us', $valor);
    }
}
