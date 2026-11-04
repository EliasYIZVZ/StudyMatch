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

    // Solicitudes de tutoría (solo POST)
    'solicitar-tutoria'   => ['SolicitudController', 'solicitar', 'page'],
    'responder-solicitud' => ['SolicitudController', 'responder', 'page'],
    'cancelar-solicitud'  => ['SolicitudController', 'cancelar', 'page'],

    // Mis publicaciones (ofertas del tutor)
    'mis-publicaciones' => ['OfertaController', 'lista', 'page'],
    'guardar-oferta'    => ['OfertaController', 'guardar', 'page'],
    'estado-oferta'     => ['OfertaController', 'cambiarEstado', 'page'],

    // Mis tutorías y chat (compartido entre alumno y tutor)
    'mis-tutorias'     => ['TutoriaController', 'lista', 'page'],
    'tutoria'          => ['TutoriaController', 'detalle', 'page'],
    'enviar-mensaje'   => ['TutoriaController', 'enviarMensaje', 'page'],
    'mensajes-tutoria' => ['TutoriaController', 'mensajes', 'api'],

    // Panel de administración (solo rol_usuario = 'admin')
    'admin'           => ['AdminController', 'dashboard', 'admin'],
    'admin-usuarios'  => ['AdminController', 'usuarios', 'admin'],
    'admin-materias'  => ['AdminController', 'materias', 'admin'],
    'admin-materia-guardar'  => ['AdminController', 'guardarMateria', 'admin'],
    'admin-materia-eliminar' => ['AdminController', 'eliminarMateria', 'admin'],
    'admin-carrera-guardar'  => ['AdminController', 'guardarCarrera', 'admin'],
    'admin-salas'     =>['AdminController', 'salas', 'admin'],
    'admin-moderar-sala'     => ['AdminController', 'moderarSala', 'admin'],
    'admin-moderar-usuario'  => ['AdminController', 'moderarUsuario', 'admin'],
    'admin-reportes'  => ['AdminController', 'reportes', 'admin'],
    'admin-resolver-reporte' => ['AdminController', 'resolverReporte', 'admin'],

    // Reportes (usuarios registrados)
    'crear-reporte'   => ['ReporteController', 'crear', 'api'],

    // API
    'usuario-actual'  => ['UsuarioController', 'actual', 'api'],
    'foto-perfil'     => ['UsuarioController', 'foto', 'api'],
];
