<?php

/**
 * Front controller de Study Match.
 * Todas las peticiones dinámicas entran por aquí: index.php?r=<ruta>
 */

define('APP_PATH', __DIR__ . '/app');

require_once APP_PATH . '/config/Database.php';

spl_autoload_register(function ($class) {
    foreach (['core', 'controllers', 'models'] as $folder) {
        $file = APP_PATH . '/' . $folder . '/' . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

Session::start();

$routes = require APP_PATH . '/config/routes.php';

(new Router($routes))->dispatch(isset($_GET['r']) ? $_GET['r'] : '');
