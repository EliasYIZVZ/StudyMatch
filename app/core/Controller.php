<?php

abstract class Controller
{
    protected function render($view, array $data = [])
    {
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

    protected function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}
