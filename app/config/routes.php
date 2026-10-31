<?php

/**
 * Tabla de rutas: 'ruta' => [Controlador, método, requiere sesión]
 *
 * 'auth' => 'page' redirige al inicio si no hay sesión.
 * 'auth' => 'api'  responde 401 en JSON si no hay sesión.
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

    // API
    'usuario-actual'  => ['UsuarioController', 'actual', 'api'],
];
