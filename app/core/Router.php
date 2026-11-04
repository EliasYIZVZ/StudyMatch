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

        // Si el admin inactivó la cuenta mientras estaba conectado, se cierra su sesión
        if ($auth !== null && !$this->cuentaActiva()) {
            Session::destroy();
            Session::start();
            $_SESSION['login_error'] = 'Tu cuenta fue suspendida. Si creés que es un error, contactá a un administrador.';

            if ($auth === 'api') {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Tu cuenta fue suspendida.']);
                exit();
            }

            header('Location: index.php', true, 303);
            exit();
        }

        // Cada rol solo accede a su área: el admin al panel admin y el resto a los dashboards.
        if ($auth === 'admin' && !Session::isAdmin()) {
            header('Location: index.php?r=dashboard', true, 303);
            exit();
        }

        if ($auth === 'page' && Session::isAdmin()) {
            header('Location: index.php?r=admin', true, 303);
            exit();
        }

        if ($auth !== null) {
            // Evita que el navegador muestre páginas privadas cacheadas tras cerrar sesión.
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
        }

        (new $controllerName())->$method();
    }

    /**
     * ¿La cuenta de la sesión sigue activa? Una consulta por clave primaria por petición.
     * Si la base no responde no se expulsa a nadie: la propia página mostrará el error.
     */
    private function cuentaActiva()
    {
        try {
            return (new User(Database::getConnection()))->estaActivo(Session::userId());
        } catch (PDOException $exception) {
            error_log('No se pudo verificar la cuenta de la sesión: ' . $exception->getMessage());
            return true;
        }
    }
}
