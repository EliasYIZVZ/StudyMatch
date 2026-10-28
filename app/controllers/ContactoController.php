<?php

final class ContactoController
{
    /**
     * Valida los datos del formulario y, cuando exista la tabla de consultas,
     * registra el mensaje de contacto.
     */
    public function validarMensaje()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: contacto.html');
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

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            exit('Error: El formato del correo no es válido.');
        }

        $longitudMensaje = preg_match_all('/./us', $mensaje);
        if ($longitudMensaje === false) {
            http_response_code(400);
            exit('Error: El mensaje contiene caracteres no válidos.');
        }

        if ($longitudMensaje > 600) {
            http_response_code(400);
            exit('Error: El mensaje no puede superar los 600 caracteres.');
        }

        http_response_code(501);
        exit('El mensaje fue validado, pero todavía no se puede registrar porque la tabla de consultas no existe.');
    }
}
