<?php

/**
 * Tabla de rutas: 'ruta' => [Controlador, método, requiere sesión]
 *
 * 'auth' => 'page'  requiere sesión de usuario (no admin); sin sesión redirige al inicio.
 * 'auth' => 'admin' requiere sesión de administrador; un usuario común va a su dashboard.
 * 'auth' => 'api'   requiere cualquier sesión; si no hay, responde 401 en JSON.
 */
return [
    ''                => ['HomeController', 'index', null],

    // Autenticación
    'login'           => ['AuthController', 'login', null],
    'registro'        => ['AuthController', 'register', null],
    'logout'          => ['AuthController', 'logout', null],

    // Contacto
    'contacto'        => ['ContactoController', 'validarMensaje', null],

    // Área privada (usuarios registrados)
    'dashboard'       => ['DashboardController', 'alumno', 'page'],
    'dashboard-tutor' => ['DashboardController', 'tutor', 'page'],
    'buscar-tutor'    => ['DashboardController', 'buscarTutor', 'page'],

    // Panel de administración (solo rol_usuario = 'admin')
    'admin'           => ['AdminController', 'dashboard', 'admin'],
    'admin-usuarios'  => ['AdminController', 'usuarios', 'admin'],
    'admin-materias'  => ['AdminController', 'materias', 'admin'],
    'admin-salas'     => ['AdminController', 'salas', 'admin'],
    'admin-reportes'  => ['AdminController', 'reportes', 'admin'],

    // API
    'usuario-actual'  => ['UsuarioController', 'actual', 'api'],
];
