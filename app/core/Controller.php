<?php

abstract class Controller
{
    protected function render($view, array $data = [])
    {
        // Contadores del menú lateral (mensajes sin leer, solicitudes pendientes)
        if (Session::isAuthenticated() && !array_key_exists('contadores', $data)) {
            $data['contadores'] = $this->contadoresDelMenu();
        }

        extract($data, EXTR_SKIP);
        require APP_PATH . '/views/' . $view . '.php';
    }

    protected function redirect($url, $status = 302)
    {
        header('Location: ' . $url, true, $status);
        exit();
    }

    protected function json(array $data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit();
    }

    /**
     * Mensaje que se muestra una sola vez en la próxima página (ver views/partials/flash.php).
     * $tipo: 'exito' o 'error'.
     */
    protected function flash($tipo, $mensaje)
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }

    protected function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    private function contadoresDelMenu()
    {
        $contadores = ['mensajes' => 0, 'solicitudes' => 0];

        try {
            $database = Database::getConnection();
            $contadores['mensajes'] = (new MensajeTutoria($database))->contarNoLeidos(Session::userId());
            $contadores['solicitudes'] = (new SolicitudTutoria($database))->contarPendientesDeTutor(Session::userId());
        } catch (PDOException $exception) {
            error_log('Error al cargar los contadores del menú: ' . $exception->getMessage());
        }

        return $contadores;
    }
}
