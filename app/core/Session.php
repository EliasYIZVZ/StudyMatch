<?php

final class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');

        session_start();
    }

    public static function isAuthenticated()
    {
        return !empty($_SESSION['logueado']) && !empty($_SESSION['id_usuario']);
    }

    public static function login(array $user)
    {
        // Nuevo ID de sesión para evitar fijación de sesión.
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = (int) $user['id_usuario'];
        $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
        $_SESSION['logueado'] = true;
    }

    public static function userId()
    {
        return self::isAuthenticated() ? (int) $_SESSION['id_usuario'] : null;
    }

    public static function destroy()
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => isset($params['samesite']) ? $params['samesite'] : 'Lax',
            ]);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
