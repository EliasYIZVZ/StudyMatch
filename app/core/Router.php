<?php

final class Router
{
    private $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch($route)
    {
        $route = is_string($route) ? trim($route, "/ \t\n\r\0\x0B") : '';

        if (!isset($this->routes[$route])) {
            http_response_code(404);
            exit('Página no encontrada.');
        }

        list($controllerName, $method, $auth) = $this->routes[$route];

        if ($auth !== null && !Session::isAuthenticated()) {
            if ($auth === 'api') {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'No hay una sesión activa.']);
                exit();
            }

            header('Location: index.php', true, 303);
            exit();
        }

        if ($auth !== null) {
            // Evita que el navegador muestre páginas privadas cacheadas tras cerrar sesión.
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
        }

        (new $controllerName())->$method();
    }
}
