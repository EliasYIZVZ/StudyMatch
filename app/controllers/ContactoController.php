<?php

final class ContactoController extends Controller
{
    /**
     * Valida los datos del formulario y registra la consulta del usuario.
     */
    public function validarMensaje()
    {
        if (!$this->isPost()) {
            $this->redirect('public/contacto.html', 303);
        }

        $campos = [];
        foreach (['nombre', 'email', 'motivo', 'asunto', 'mensaje'] as $campo) {
            $valor = isset($_POST[$campo]) ? $_POST[$campo] : '';
            if (!is_string($valor)) {
                $this->fail('Error: Todos los campos son obligatorios.', 400);
            }
            $campos[$campo] = trim($valor);
        }

        if (in_array('', $campos, true)) {
            $this->fail('Error: Todos los campos son obligatorios.', 400);
        }

        $longitudes = array_map([$this, 'longitud'], $campos);
        if (in_array(false, $longitudes, true)) {
            $this->fail('Error: Los campos contienen caracteres no válidos.', 400);
        }

        if (
            $longitudes['nombre'] > 100
            || $longitudes['email'] > 100
            || $longitudes['motivo'] > 255
            || $longitudes['asunto'] > 255
        ) {
            $this->fail('Error: Uno o más campos superan la longitud permitida.', 400);
        }

        if ($longitudes['mensaje'] > 600) {
            $this->fail('Error: El mensaje no puede superar los 600 caracteres.', 400);
        }

        if (!filter_var($campos['email'], FILTER_VALIDATE_EMAIL)) {
            $this->fail('Error: El formato del correo no es válido.', 400);
        }

        try {
            (new Consulta(Database::getConnection()))->create(
                $campos['nombre'],
                $campos['email'],
                $campos['motivo'],
                $campos['asunto'],
                $campos['mensaje']
            );
        } catch (PDOException $exception) {
            error_log('Error al registrar la consulta de contacto: ' . $exception->getMessage());
            $this->fail('No se pudo registrar tu consulta. Por favor, intentá nuevamente más tarde.', 500);
        }

        $this->redirect('public/contacto.html?consulta=enviada', 303);
    }

    private function longitud($valor)
    {
        return preg_match_all('/./us', $valor);
    }

    private function fail($message, $status)
    {
        http_response_code($status);
        header('Content-Type: text/plain; charset=utf-8');
        exit($message);
    }
}
