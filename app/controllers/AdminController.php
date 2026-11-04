<?php

/**
 * Panel de administración (solo usuarios con rol_usuario = 'admin').
 * El Router ya verificó el rol antes de llegar aquí.
 *
 * IMPORTANTE: los datos de cada sección son SIMULADOS. Las tablas que necesitan
 * (materias, carreras, salas, reportes, etc.) todavía no existen en la base.
 * Cuando se creen, reemplazar cada método datos*() por llamadas a su modelo.
 */
final class AdminController extends Controller
{
    public function dashboard()
    {
        $this->renderAdmin('admin/dashboard', $this->datosDashboard());
    }

    public function usuarios()
    {
        $this->renderAdmin('admin/gestion-usuarios', $this->datosUsuarios());
    }

    public function materias()
    {
        $this->renderAdmin('admin/gestion-materias', $this->datosMaterias());
    }

    public function salas()
    {
        try {
            $db = Database::getConnection();
            $salasModel = new SalaPublica($db);
            $salas = $salasModel->listarParaAdmin();
        } catch (Exception $e) {
            error_log('Error al cargar salas: ' . $e->getMessage());
            $salas = $this->datosSalas()['salas'];
        }

        $this->renderAdmin('admin/salas', ['salas' => $salas]);
    }

    public function reportes()
    {
        try {
            $db = Database::getConnection();
            $reporteModel = new Reporte($db);
            $reportes = $reporteModel->listarParaAdmin();
            $metricas = $reporteModel->metricas();

            $data = [
                'reportes'            => $reportes,
                'totalReportes'       => $metricas['total'],
                'reportesPendientes'  => $metricas['pendientes'],
                'reportesFinalizados' => $metricas['finalizados'],
                'reportesRechazados'  => $metricas['rechazados'],
                'salasReportadas'     => $metricas['salas'],
                'usuariosReportados'  => $metricas['usuarios'],
            ];
        } catch (Exception $e) {
            error_log('Error al cargar reportes: ' . $e->getMessage());
            $data = $this->datosReportes();
        }

        $this->renderAdmin('admin/reportes-moderacion', $data);
    }

    /**
     * Resuelve o rechaza una alerta de reporte desde el panel de administración.
     */
    /**
     * Suspende o reactiva una sala de Discord desde el panel de administración.
     */
    public function moderarSala()
    {
        if (!$this->isPost()) {
            $this->redirect('index.php?r=admin-salas');
            return;
        }

        $idSala = (int) ($_POST['id_sala'] ?? 0);
        $accion = trim($_POST['accion'] ?? '');
        $motivo = trim($_POST['motivo'] ?? 'Medida de moderación');

        if ($idSala <= 0 || !in_array($accion, ['suspender', 'reactivar'], true)) {
            $this->json(['ok' => false, 'error' => 'Parámetros inválidos.']);
            return;
        }

        try {
            $db = Database::getConnection();
            $salaModel = new SalaPublica($db);

            if ($accion === 'suspender') {
                $salaModel->suspender($idSala, $motivo);
                $desc = "Sala #{$idSala} suspendida por el administrador. Motivo: {$motivo}";
            } else {
                $salaModel->reactivar($idSala);
                $desc = "Sala #{$idSala} reactivada por el administrador.";
            }

            // Registrar en auditoría
            $adminId = Session::userId() ?: 1;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmtAud = $db->prepare(
                "INSERT INTO auditoria (id_usuario, accion, descripcion, ip) VALUES (:admin, 'modificar', :desc, :ip)"
            );
            $stmtAud->execute([
                'admin' => $adminId,
                'desc'  => $desc,
                'ip'    => $ip,
            ]);

            $this->json([
                'ok' => true,
                'mensaje' => ($accion === 'suspender') 
                    ? "Sala #{$idSala} suspendida correctamente." 
                    : "Sala #{$idSala} reactivada con éxito."
            ]);
        } catch (Exception $e) {
            error_log('Error al moderar sala: ' . $e->getMessage());
            $this->json(['ok' => false, 'error' => 'Error en la base de datos.']);
        }
    }

    public function resolverReporte()
    {
        if (!$this->isPost()) {
            $this->redirect('index.php?r=admin-reportes');
            return;
        }

        $idReporte = (int) ($_POST['id_reporte'] ?? 0);
        $accion = trim($_POST['accion'] ?? '');

        if ($idReporte <= 0 || !in_array($accion, ['finalizar', 'rechazar'], true)) {
            $this->json(['ok' => false, 'error' => 'Parámetros inválidos.']);
            return;
        }

        try {
            $db = Database::getConnection();
            $reporteModel = new Reporte($db);
            $reporte = $reporteModel->obtenerPorId($idReporte);

            if (!$reporte) {
                $this->json(['ok' => false, 'error' => 'Reporte no encontrado.']);
                return;
            }

            $nuevoEstado = ($accion === 'finalizar') ? 'finalizado' : 'rechazado';
            $reporteModel->cambiarEstado($idReporte, $nuevoEstado);

            $detalleAuditoria = "Reporte #{$idReporte} marcado como '{$nuevoEstado}'";

            if ($accion === 'finalizar' && $reporte['tipo_entidad'] === 'usuario') {
                $stmt = $db->prepare("UPDATE usuarios SET estado_usuario = 'inactivo' WHERE id_usuario = :id");
                $stmt->execute(['id' => (int) $reporte['id_referencia']]);
                $detalleAuditoria .= " y se suspendió al usuario #{$reporte['id_referencia']}";
            }

            if ($accion === 'finalizar' && $reporte['tipo_entidad'] === 'sala') {
                (new SalaPublica($db))->suspender($reporte['id_referencia'], "Suspendida por resolución de reporte #{$idReporte}");
                $detalleAuditoria .= " y se suspendió la sala de Discord #{$reporte['id_referencia']}";
            }

            $adminId = Session::userId() ?: 1;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmtAud = $db->prepare(
                "INSERT INTO auditoria (id_usuario, accion, descripcion, ip) VALUES (:admin, 'modificar', :desc, :ip)"
            );
            $stmtAud->execute([
                'admin' => $adminId,
                'desc'  => $detalleAuditoria,
                'ip'    => $ip,
            ]);

            $this->json([
                'ok' => true,
                'nuevoEstado' => $nuevoEstado,
                'mensaje' => ($accion === 'finalizar') 
                    ? 'Reporte finalizado y sanción aplicada con éxito.' 
                    : 'Reporte rechazado y desestimado correctamente.'
            ]);
        } catch (Exception $e) {
            error_log('Error al resolver reporte: ' . $e->getMessage());
            $this->json(['ok' => false, 'error' => 'Error en la base de datos.']);
        }
    }

    /**
     * Agrega a la vista los datos del administrador logueado.
     */
    private function renderAdmin($view, array $data)
    {
        $data['adminNombre'] = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : 'Administrador';
        $data['adminEmail'] = isset($_SESSION['correo_usuario']) ? $_SESSION['correo_usuario'] : '';
        $data['adminRol'] = 'Superadmin';

        $this->render($view, $data);
    }

    // ------------------------------------------------------------------
    // Datos simulados: dashboard
    // ------------------------------------------------------------------
    private function datosDashboard()
    {
        /* ==========================================================================
         * GUÍA DE INTEGRACIÓN BACKEND (PHP PDO + MySQL):
         * ==========================================================================
         * Tu compañero de backend podrá conectar esta vista ejecutando:
         * 
         * $db = Database::getConnection();
         * 
         * 1. Métricas del Dashboard:
         *    $totalAlumnos      = (int) $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'Alumno'")->fetchColumn();
         *    $totalTutores      = (int) $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'Tutor' AND estado_verificacion = 'verificado'")->fetchColumn();
         *    $totalSalas        = (int) $db->query("SELECT COUNT(*) FROM salas_publicas WHERE estado = 'activa'")->fetchColumn();
         *    $totalMaterias     = (int) $db->query("SELECT COUNT(*) FROM materias")->fetchColumn();
         *    $tutoresPendientes = (int) $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'Tutor' AND estado_verificacion = 'pendiente'")->fetchColumn();
         *    $totalReportes     = (int) $db->query("SELECT COUNT(*) FROM reportes_moderacion WHERE estado = 'pendiente'")->fetchColumn();
         * 
         * 2. Postulaciones de tutores pendientes:
         *    $stmt = $db->query("SELECT id_usuario, nombre, apellido, legajo, carrera, materia_postula, analitico_url 
         *                        FROM postulaciones_tutores WHERE estado = 'pendiente' ORDER BY fecha DESC LIMIT 5");
         *    $postulaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
         * ========================================================================== */

        // Contadores simulados de métricas principales
        $totalAlumnos      = 1280;
        $totalTutores      = 94;
        $totalSalas        = 6;
        $totalMaterias     = 42;
        $tutoresPendientes = 3;
        $totalReportes     = 3; // Cantidad de reportes pendientes

        return compact(
            'totalAlumnos',
            'totalTutores',
            'totalSalas',
            'totalMaterias',
            'tutoresPendientes',
            'totalReportes'
        );
    }

    // ------------------------------------------------------------------
    // Datos simulados: gestion-usuarios
    // ------------------------------------------------------------------
    private function datosUsuarios()
    {
        /* ==========================================================================
         * GUÍA DE INTEGRACIÓN BACKEND (PHP PDO + MySQL):
         * ==========================================================================
         * Tu compañero de backend podrá conectar esta vista con la base de datos real:
         * 
         * $db = Database::getConnection();
         * 
         * $sql = "SELECT id_usuario, nombre_usuario, correo_usuario, fecha_registro, 
         *                estado_usuario, carrera_usuario, anio_cursada_usuario, 
         *                es_tutor, tutor_verificado, doc_titulo
         *         FROM usuarios 
         *         ORDER BY fecha_registro DESC 
         *         LIMIT 5";
         * $stmt = $db->query($sql);
         * $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
         * ========================================================================== */

        // Datos simulados reducidos a 5 registros de ejemplo
        $usuarios = [
            [
                'id_usuario'       => 110,
                'nombre'           => 'Lucas Benítez',
                'correo'           => 'lbenitez@frh.utn.edu.ar',
                'carrera'          => 'TUP (Programación)',
                'anio'             => '2do año',
                'legajo'           => '51294',
                'es_alumno'        => true,
                'es_tutor'         => false,
                'tutor_verificado' => false,
                'doc_titulo'       => null,
                'estado'           => 'activo',
                'fecha_registro'   => '29/10/2026 10:15',
                'avatar_bg'        => 'bg-primary-subtle text-primary'
            ],
            [
                'id_usuario'       => 109,
                'nombre'           => 'Julieta Abbiati',
                'correo'           => 'jabbiati@frh.utn.edu.ar',
                'carrera'          => 'TUP (Programación)',
                'anio'             => '3er año',
                'legajo'           => '49812',
                'es_alumno'        => true,
                'es_tutor'         => true,
                'tutor_verificado' => true,
                'doc_titulo'       => 'titulo_tecnico_abbiati.jpg',
                'estado'           => 'activo',
                'fecha_registro'   => '28/10/2026 18:40',
                'avatar_bg'        => 'bg-success-subtle text-success'
            ],
            [
                'id_usuario'       => 108,
                'nombre'           => 'Matías Fuentes',
                'correo'           => 'mfuentes@frh.utn.edu.ar',
                'carrera'          => 'Ing. en Sistemas',
                'anio'             => '4to año',
                'legajo'           => '48991',
                'es_alumno'        => true,
                'es_tutor'         => true,
                'tutor_verificado' => true,
                'doc_titulo'       => 'analitico_titulacion_fuentes.pdf',
                'estado'           => 'activo',
                'fecha_registro'   => '28/10/2026 14:22',
                'avatar_bg'        => 'bg-success-subtle text-success'
            ],
            [
                'id_usuario'       => 105,
                'nombre'           => 'Valentina Rojas',
                'correo'           => 'vrojas@frh.utn.edu.ar',
                'carrera'          => 'Ing. en Sistemas',
                'anio'             => '4to año',
                'legajo'           => '47820',
                'es_alumno'        => true,
                'es_tutor'         => true,
                'tutor_verificado' => false, // Tutor sin verificar título aún
                'doc_titulo'       => null,
                'estado'           => 'activo',
                'fecha_registro'   => '26/10/2026 11:15',
                'avatar_bg'        => 'bg-warning-subtle text-warning-emphasis'
            ],
            [
                'id_usuario'       => 103,
                'nombre'           => 'Facundo Morales',
                'correo'           => 'fmorales@frh.utn.edu.ar',
                'carrera'          => 'Ing. Ferroviaria',
                'anio'             => '2do año',
                'legajo'           => '50312',
                'es_alumno'        => true,
                'es_tutor'         => false,
                'tutor_verificado' => false,
                'doc_titulo'       => null,
                'estado'           => 'suspendido',
                'fecha_registro'   => '24/10/2026 22:10',
                'avatar_bg'        => 'bg-danger-subtle text-danger'
            ]
        ];

        // Contadores rápidos para los botones de filtrado
        $cantTotal   = count($usuarios);
        $cantTutores = count(array_filter($usuarios, fn($u) => $u['es_tutor']));
        $cantAlumnos = $cantTotal;

        return compact(
            'usuarios',
            'cantTotal',
            'cantTutores',
            'cantAlumnos'
        );
    }

    // ------------------------------------------------------------------
    // Datos simulados: gestion-materias
    // ------------------------------------------------------------------
    private function datosMaterias()
    {
        /* ==========================================================================
         * GUÍA DE INTEGRACIÓN BACKEND (PHP PDO + MySQL):
         * ==========================================================================
         * Tu compañero de backend podrá conectar esta vista con la base de datos:
         * 
         * $db = Database::getConnection();
         * 
         * 1. Carreras registradas:
         *    $carreras = $db->query("SELECT c.*, COUNT(m.id_materia) as materias_count 
         *                            FROM carreras c 
         *                            LEFT JOIN materias m ON c.codigo = m.carrera 
         *                            GROUP BY c.id_carrera ORDER BY c.nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
         * 
         * 2. Catálogo de materias:
         *    $materias = $db->query("SELECT id_materia, codigo, nombre, carrera, tipo 
         *                            FROM materias ORDER BY codigo ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
         * ========================================================================== */

        // Catálogo de carreras registradas en la facultad
        $carreras = [
            [
                'id_carrera'      => 1,
                'codigo'          => 'TUP',
                'nombre'          => 'Tecnicatura Universitaria en Programación',
                'color'           => 'primary',
                'icono'           => 'bi-code-slash',
                'materias_count'  => 3
            ],
            [
                'id_carrera'      => 2,
                'codigo'          => 'TGE',
                'nombre'          => 'Tecnicatura en Gestión Empresarial',
                'color'           => 'info',
                'icono'           => 'bi-briefcase-fill',
                'materias_count'  => 1
            ],
            [
                'id_carrera'      => 3,
                'codigo'          => 'ISI',
                'nombre'          => 'Ingeniería en Sistemas de Información',
                'color'           => 'success',
                'icono'           => 'bi-cpu-fill',
                'materias_count'  => 0
            ],
            [
                'id_carrera'      => 4,
                'codigo'          => 'LAR',
                'nombre'          => 'Licenciatura en Administración Rural',
                'color'           => 'warning',
                'icono'           => 'bi-tree-fill',
                'materias_count'  => 0
            ]
        ];

        // Datos simulados del catálogo general de materias (5 ejemplos representativos)
        $materias = [
            [
                'id_materia' => 1,
                'codigo'     => 'TUP-01',
                'nombre'     => 'Programación I (C++)',
                'carrera'    => 'TUP',
                'tipo'       => 'carrera'
            ],
            [
                'id_materia' => 2,
                'codigo'     => 'TUP-02',
                'nombre'     => 'Sistemas de Procesamiento de Datos',
                'carrera'    => 'TUP',
                'tipo'       => 'carrera'
            ],
            [
                'id_materia' => 3,
                'codigo'     => 'GEN-01',
                'nombre'     => 'Inglés Técnico I',
                'carrera'    => 'General',
                'tipo'       => 'libre'
            ],
            [
                'id_materia' => 4,
                'codigo'     => 'TGE-01',
                'nombre'     => 'Principios de Administración y Organización',
                'carrera'    => 'TGE',
                'tipo'       => 'carrera'
            ],
            [
                'id_materia' => 5,
                'codigo'     => 'TUP-03',
                'nombre'     => 'Bases de Datos Relacionales (SQL)',
                'carrera'    => 'TUP',
                'tipo'       => 'carrera'
            ]
        ];

        // Contadores rápidos para métricas y filtros
        $cantTotal    = count($materias);
        $cantCarreras = count($carreras);
        $cantLibres   = count(array_filter($materias, fn($m) => $m['carrera'] === 'General' || $m['tipo'] === 'libre'));
        $cantTUP      = count(array_filter($materias, fn($m) => $m['carrera'] === 'TUP'));
        $cantTGE      = count(array_filter($materias, fn($m) => $m['carrera'] === 'TGE'));
        $cantISI      = count(array_filter($materias, fn($m) => $m['carrera'] === 'ISI'));

        return compact(
            'carreras',
            'materias',
            'cantTotal',
            'cantCarreras',
            'cantLibres',
            'cantTUP',
            'cantTGE',
            'cantISI'
        );
    }

    // ------------------------------------------------------------------
    // Datos simulados: salas
    // ------------------------------------------------------------------
    private function datosSalas()
    {
        /* ==========================================================================
         * GUÍA DE INTEGRACIÓN BACKEND (PHP PDO + MySQL):
         * ==========================================================================
         * Tu compañero de backend podrá conectar esta vista con la base de datos real:
         * 
         * $db = Database::getConnection();
         * 
         * $sql = "SELECT s.*, m.codigo_materia, m.nombre_materia, u.nombre, u.apellido, u.rol
         *         FROM salas_publicas s
         *         JOIN materias m ON s.id_materia = m.id_materia
         *         JOIN usuarios u ON s.id_usuario = u.id_usuario
         *         ORDER BY s.estado ASC, s.id_sala DESC
         *         LIMIT 5";
         * $stmt = $db->query($sql);
         * $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
         * ========================================================================== */

        // Datos simulados reducidos a 5 salas de estudio representativas
        $salas = [
            [
                'id_sala'           => 1,
                'titulo'            => 'Programación I • Punteros y Memoria Dinámica (C++)',
                'descripcion'       => 'Resolución grupal de ejercicios prácticos de la Guía 4 y repaso de estructuras de datos para el 2do parcial.',
                'materia'           => 'Programación I (C++)',
                'materia_code'      => 'TUP-01',
                'creador'           => 'Lucas Benítez',
                'creador_rol'       => 'Tutor',
                'creador_avatar'    => 'LB',
                'discord_url'       => 'https://discord.gg/studymatch-tup01',
                'estado'            => 'activa',
                'fecha_creacion'    => 'Hoy, hace 45 min',
                'reportes'          => 0,
                'motivo_suspension' => null
            ],
            [
                'id_sala'           => 2,
                'titulo'            => 'Bases de Datos • Consultas JOIN y Subqueries Complejas',
                'descripcion'       => 'Taller colaborativo en vivo para diseñar diagramas entidad-relación y consultas SQL avanzadas en MySQL Workbench.',
                'materia'           => 'Bases de Datos Relacionales (SQL)',
                'materia_code'      => 'TUP-03',
                'creador'           => 'Sofía Domínguez',
                'creador_rol'       => 'Tutor',
                'creador_avatar'    => 'SD',
                'discord_url'       => 'https://discord.gg/studymatch-tup03',
                'estado'            => 'activa',
                'fecha_creacion'    => 'Hoy, hace 1 h',
                'reportes'          => 0,
                'motivo_suspension' => null
            ],
            [
                'id_sala'           => 3,
                'titulo'            => 'Sistemas de Procesamiento • Arquitectura de Von Neumann',
                'descripcion'       => 'Mesa de debate sobre registros, buses de datos y microoperaciones con simulador de CPU interactivo.',
                'materia'           => 'Sistemas de Procesamiento de Datos',
                'materia_code'      => 'TUP-02',
                'creador'           => 'Ignacio Roldán',
                'creador_rol'       => 'Alumno',
                'creador_avatar'    => 'IR',
                'discord_url'       => 'https://discord.gg/studymatch-tup02',
                'estado'            => 'activa',
                'fecha_creacion'    => 'Hoy, hace 2 h',
                'reportes'          => 1,
                'motivo_suspension' => null
            ],
            [
                'id_sala'           => 4,
                'titulo'            => 'Principios de Administración • Análisis FODA y Estrategia',
                'descripcion'       => 'Armado en grupos del trabajo integrador práctico: diagnósticos organizacionales y planeamiento.',
                'materia'           => 'Principios de Administración y Organización',
                'materia_code'      => 'TGE-01',
                'creador'           => 'Esteban Morales',
                'creador_rol'       => 'Alumno',
                'creador_avatar'    => 'EM',
                'discord_url'       => 'https://discord.gg/studymatch-tge01',
                'estado'            => 'activa',
                'fecha_creacion'    => 'Hoy, hace 3 h',
                'reportes'          => 0,
                'motivo_suspension' => null
            ],
            [
                'id_sala'           => 5,
                'titulo'            => 'Canal General de Charlas y Juegos - Off Topic',
                'descripcion'       => 'Sala suspendida temporalmente por moderación tras reporte de spam y contenidos fuera de las pautas académicas.',
                'materia'           => 'Materia General / Libre',
                'materia_code'      => 'GEN-00',
                'creador'           => 'Martín Pereyra',
                'creador_rol'       => 'Alumno',
                'creador_avatar'    => 'MP',
                'discord_url'       => 'https://discord.gg/studymatch-offtopic',
                'estado'            => 'suspendida',
                'fecha_creacion'    => 'Ayer',
                'reportes'          => 4,
                'motivo_suspension' => 'Spam y contenido no académico reiterado'
            ]
        ];

        // Métricas de salas
        $totalSalas       = count($salas);
        $salasActivas     = count(array_filter($salas, fn($s) => $s['estado'] === 'activa'));
        $salasSuspendidas = count(array_filter($salas, fn($s) => $s['estado'] === 'suspendida'));

        return compact(
            'salas',
            'totalSalas',
            'salasActivas',
            'salasSuspendidas'
        );
    }

    // ------------------------------------------------------------------
    // Datos simulados: reportes-moderacion
    // ------------------------------------------------------------------
    private function datosReportes()
    {
        /* ==========================================================================
         * GUÍA DE INTEGRACIÓN BACKEND (PHP PDO + MySQL):
         * ==========================================================================
         * Tu compañero de backend podrá conectar esta vista con la base de datos real:
         * 
         * $db = Database::getConnection();
         * 
         * $sql = "SELECT r.*, u.nombre as denunciante, u.rol as denunciante_rol 
         *         FROM reportes_moderacion r 
         *         JOIN usuarios u ON r.id_denunciante = u.id_usuario 
         *         ORDER BY r.fecha DESC 
         *         LIMIT 5";
         * $stmt = $db->query($sql);
         * $reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         * ========================================================================== */

        // Datos simulados de reportes simplificados: Discord o Alumnos (5 registros)
        $reportes = [
            [
                'id_reporte'        => 101,
                'tipo'              => 'sala', // 'sala' (Discord) | 'usuario' (Alumno)
                'elemento'          => 'Canal General de Charlas y Juegos - Off Topic',
                'elemento_sub'      => 'Sala Pública de Discord • ID #7',
                'motivo'            => 'Spam y enlaces comerciales no autorizados',
                'descripcion'       => 'Varios usuarios están difundiendo enlaces externos de apuestas y servidores comerciales ajenos a la comunidad académica de la UTN.',
                'denunciante'       => 'Martín Gómez',
                'denunciante_rol'   => 'Tutor',
                'denunciante_avatar'=> 'MG',
                'fecha'             => 'Hoy, 14:32',
                'timestamp'         => 1730223120,
                'estado'            => 'pendiente', // 'pendiente' | 'resuelto'
                'evidencia_url'     => 'https://discord.gg/studymatch-offtopic'
            ],
            [
                'id_reporte'        => 102,
                'tipo'              => 'usuario',
                'elemento'          => 'Carlos Méndez',
                'elemento_sub'      => 'Alumno • Legajo 54.120',
                'motivo'            => 'Conducta inapropiada en chat de voz',
                'descripcion'       => 'Interrumpe con música a alto volumen y expresiones irrespetuosas a los alumnos que intentaban repasar la guía de Programación.',
                'denunciante'       => 'Lucía Varela',
                'denunciante_rol'   => 'Alumno',
                'denunciante_avatar'=> 'LV',
                'fecha'             => 'Hoy, 12:15',
                'timestamp'         => 1730214900,
                'estado'            => 'pendiente',
                'evidencia_url'     => 'Sala de voz #1 (Programación I)'
            ],
            [
                'id_reporte'        => 103,
                'tipo'              => 'sala',
                'elemento'          => 'Sala de Enlaces no Verificados y Publicidad',
                'elemento_sub'      => 'Sala Pública de Discord • ID #8',
                'motivo'            => 'Publicidad y venta de exámenes resueltos',
                'descripcion'       => 'Se publican números de teléfono ofreciendo cobrar dinero por realizar trabajos prácticos y parciales de la carrera.',
                'denunciante'       => 'Prof. Andrea Ferrari',
                'denunciante_rol'   => 'Tutor',
                'denunciante_avatar'=> 'AF',
                'fecha'             => 'Ayer, 18:40',
                'timestamp'         => 1730151600,
                'estado'            => 'pendiente',
                'evidencia_url'     => 'https://discord.gg/studymatch-spam'
            ],
            [
                'id_reporte'        => 104,
                'tipo'              => 'usuario',
                'elemento'          => 'Franco Ibarra',
                'elemento_sub'      => 'Alumno • Legajo 48.910',
                'motivo'            => 'Inasistencia reiterada a tutorías pactadas',
                'descripcion'       => 'El alumno no se conectó en tres ocasiones seguidas a las sesiones confirmadas sin aviso previo al tutor asignado.',
                'denunciante'       => 'Santiago Torres',
                'denunciante_rol'   => 'Tutor',
                'denunciante_avatar'=> 'ST',
                'fecha'             => '27/10/2026',
                'timestamp'         => 1729987200,
                'estado'            => 'resuelto',
                'evidencia_url'     => 'Sesión individual #44'
            ],
            [
                'id_reporte'        => 105,
                'tipo'              => 'usuario',
                'elemento'          => 'Rodrigo Peralta',
                'elemento_sub'      => 'Alumno • Legajo 61.802',
                'motivo'            => 'Uso indebido del chat comunitario',
                'descripcion'       => 'Envío masivo de mensajes repetidos obstaculizando las consultas sobre la materia Sistemas de Procesamiento.',
                'denunciante'       => 'Sofía Domínguez',
                'denunciante_rol'   => 'Tutor',
                'denunciante_avatar'=> 'SD',
                'fecha'             => '25/10/2026',
                'timestamp'         => 1729814400,
                'estado'            => 'resuelto',
                'evidencia_url'     => 'Sala #2 (Sistemas)'
            ]
        ];

        // Métricas para KPIs
        $totalReportes      = count($reportes);
        $reportesPendientes = count(array_filter($reportes, fn($r) => $r['estado'] === 'pendiente'));
        $salasReportadas    = count(array_filter($reportes, fn($r) => $r['tipo'] === 'sala'));
        $usuariosReportados = count(array_filter($reportes, fn($r) => $r['tipo'] === 'usuario'));

        return compact(
            'reportes',
            'totalReportes',
            'reportesPendientes',
            'salasReportadas',
            'usuariosReportados'
        );
    }
}
