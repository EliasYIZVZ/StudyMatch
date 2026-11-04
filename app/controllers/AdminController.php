<?php

/**
 * Panel de administración (solo usuarios con rol_usuario = 'admin').
 * El Router ya verificó el rol antes de llegar aquí.
 *
 * Usuarios, salas y reportes leen y escriben en la base de datos. El resumen
 * (dashboard) y las materias siguen con datos SIMULADOS: están en los métodos
 * datos*() de abajo, hasta que se los reemplace por llamadas a su modelo.
 */
final class AdminController extends Controller
{
    public function dashboard()
    {
        $this->renderAdmin('admin/dashboard', $this->datosDashboard());
    }

    /**
     * Gestión de alumnos y tutores. Es tutor quien publicó al menos una tutoría.
     */
    public function usuarios()
    {
        $usuarios = [];
        $errorUsuarios = false;
        $anios = ['1ro' => '1er año', '2do' => '2do año', '3ro' => '3er año', '4to' => '4to año', '5to' => '5to año', 'graduado' => 'Graduado/a'];

        try {
            foreach ((new User(Database::getConnection()))->listarParaAdmin() as $fila) {
                $esTutor = (int) $fila['publicadas'] > 0;
                $estado = $fila['estado_usuario'] === 'activo' ? 'activo' : 'suspendido';

                $usuarios[] = [
                    'id_usuario'     => (int) $fila['id_usuario'],
                    'nombre'         => $fila['nombre_usuario'],
                    'correo'         => $fila['correo_usuario'],
                    'carrera'        => $fila['carrera_usuario'] ?: 'Sin carrera cargada',
                    'anio'           => isset($anios[$fila['anio_cursada_usuario']]) ? $anios[$fila['anio_cursada_usuario']] : 'Sin año cargado',
                    'es_tutor'       => $esTutor,
                    'publicadas'     => (int) $fila['publicadas'],
                    'estado'         => $estado,
                    'fecha_registro' => Vista::fecha($fila['fecha_registro']),
                    'avatar_bg'      => $estado === 'suspendido'
                        ? 'bg-danger-subtle text-danger'
                        : ($esTutor ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary'),
                ];
            }
        } catch (PDOException $e) {
            error_log('Error al cargar los usuarios: ' . $e->getMessage());
            $errorUsuarios = true;
        }

        $this->renderAdmin('admin/gestion-usuarios', [
            'usuarios'      => $usuarios,
            'errorUsuarios' => $errorUsuarios,
            'cantTotal'     => count($usuarios),
            'cantTutores'   => count(array_filter($usuarios, function ($u) { return $u['es_tutor']; })),
            'cantAlumnos'   => count($usuarios),
        ]);
    }

    /**
     * Inactiva ("suspender") o reactiva la cuenta de un alumno o tutor. Responde JSON.
     */
    public function moderarUsuario()
    {
        if (!$this->isPost()) {
            $this->redirect('index.php?r=admin-usuarios');
            return;
        }

        $idUsuario = (int) (isset($_POST['id_usuario']) ? $_POST['id_usuario'] : 0);
        $accion = isset($_POST['accion']) && is_string($_POST['accion']) ? trim($_POST['accion']) : '';
        $motivo = isset($_POST['motivo']) && is_string($_POST['motivo']) ? trim($_POST['motivo']) : '';

        if ($idUsuario <= 0 || !in_array($accion, ['suspender', 'reactivar'], true)) {
            $this->json(['ok' => false, 'error' => 'Parámetros inválidos.']);
            return;
        }

        try {
            $db = Database::getConnection();
            $usuario = (new User($db))->cambiarEstado($idUsuario, $accion === 'suspender' ? 'inactivo' : 'activo');

            if ($usuario === false) {
                $this->json(['ok' => false, 'error' => 'El usuario no existe o no se puede moderar.']);
                return;
            }

            $descripcion = $accion === 'suspender'
                ? "Cuenta de {$usuario['nombre_usuario']} (#{$idUsuario}) suspendida por el administrador."
                    . ($motivo !== '' ? ' Motivo: ' . mb_substr($motivo, 0, 120, 'UTF-8') : '')
                : "Cuenta de {$usuario['nombre_usuario']} (#{$idUsuario}) reactivada por el administrador.";
            $this->registrarAuditoria($db, $descripcion);

            $this->json([
                'ok' => true,
                'estado' => $accion === 'suspender' ? 'suspendido' : 'activo',
                'mensaje' => $accion === 'suspender'
                    ? 'Usuario suspendido correctamente.'
                    : 'Usuario reactivado correctamente.',
            ]);
        } catch (PDOException $e) {
            error_log('Error al moderar el usuario: ' . $e->getMessage());
            $this->json(['ok' => false, 'error' => 'Error en la base de datos.']);
        }
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

        $this->renderAdmin('admin/salas', [
            'salas'            => $salas,
            'totalSalas'       => count($salas),
            'salasActivas'     => count(array_filter($salas, fn($s) => $s['estado'] === 'activa')),
            'salasSuspendidas' => count(array_filter($salas, fn($s) => $s['estado'] === 'suspendida')),
        ]);
    }

    public function reportes()
    {
        $data = [
            'reportes'            => [],
            'totalReportes'       => 0,
            'reportesPendientes'  => 0,
            'reportesFinalizados' => 0,
            'reportesRechazados'  => 0,
            'conteoTipos'         => array_fill_keys(Reporte::TIPOS, 0),
            'errorReportes'       => false,
        ];

        try {
            $reporteModel = new Reporte(Database::getConnection());
            $metricas = $reporteModel->metricas();

            $data['reportes']            = $reporteModel->listarParaAdmin();
            $data['totalReportes']       = $metricas['total'];
            $data['reportesPendientes']  = $metricas['pendientes'];
            $data['reportesFinalizados'] = $metricas['finalizados'];
            $data['reportesRechazados']  = $metricas['rechazados'];
            $data['conteoTipos']         = $metricas['por_tipo'];
        } catch (PDOException $e) {
            error_log('Error al cargar reportes: ' . $e->getMessage());
            $data['errorReportes'] = true;
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

    /**
     * Finaliza o rechaza un reporte pendiente.
     * Finalizar sanciona: inactiva al usuario reportado (alumno, tutor, dueño de la
     * tutoría o autor del mensaje) o, si se reportó una sala, la suspende.
     */
    public function resolverReporte()
    {
        if (!$this->isPost()) {
            $this->redirect('index.php?r=admin-reportes');
            return;
        }

        $idReporte = (int) (isset($_POST['id_reporte']) ? $_POST['id_reporte'] : 0);
        $accion = isset($_POST['accion']) && is_string($_POST['accion']) ? trim($_POST['accion']) : '';

        if ($idReporte <= 0 || !in_array($accion, ['finalizar', 'rechazar'], true)) {
            $this->json(['ok' => false, 'error' => 'Parámetros inválidos.']);
            return;
        }

        $db = Database::getConnection();

        try {
            $reporteModel = new Reporte($db);
            $reporte = $reporteModel->obtenerPorId($idReporte);

            if (!$reporte) {
                $this->json(['ok' => false, 'error' => 'Reporte no encontrado.']);
                return;
            }

            if ($reporte['estado'] !== 'pendiente') {
                $this->json(['ok' => false, 'error' => 'Este reporte ya fue resuelto.']);
                return;
            }

            $nuevoEstado = $accion === 'finalizar' ? 'finalizado' : 'rechazado';
            $adminId = Session::userId();
            $detalle = "Reporte #{$idReporte} marcado como '{$nuevoEstado}'";

            $db->beginTransaction();
            $reporteModel->cambiarEstado($idReporte, $nuevoEstado, $adminId);

            if ($accion === 'finalizar') {
                if ($reporte['tipo_entidad'] === 'sala') {
                    (new SalaPublica($db))->suspender($reporte['id_referencia'], "Suspendida por resolución de reporte #{$idReporte}");
                    $detalle .= " y se suspendió la sala de Discord #{$reporte['id_referencia']}";
                } elseif ($reporte['id_usuario_reportado'] !== null) {
                    $usuario = (new User($db))->cambiarEstado((int) $reporte['id_usuario_reportado'], 'inactivo');
                    $detalle .= $usuario !== false
                        ? " y se inactivó al usuario {$usuario['nombre_usuario']} (#{$reporte['id_usuario_reportado']})"
                        : ' (el usuario reportado no se puede sancionar)';
                }
            }

            $this->registrarAuditoria($db, $detalle);
            $db->commit();

            $this->json([
                'ok' => true,
                'nuevoEstado' => $nuevoEstado,
                'mensaje' => $accion === 'finalizar'
                    ? 'Reporte finalizado y sanción aplicada con éxito.'
                    : 'Reporte rechazado y desestimado correctamente.',
            ]);
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Error al resolver reporte: ' . $e->getMessage());
            $this->json(['ok' => false, 'error' => 'Error en la base de datos.']);
        }
    }

    /**
     * Deja constancia de una acción del administrador en la auditoría.
     */
    private function registrarAuditoria(PDO $db, $descripcion)
    {
        $db->prepare(
            "INSERT INTO auditoria (id_usuario, accion, descripcion, ip) VALUES (:admin, 'modificar', :descripcion, :ip)"
        )->execute([
            'admin' => Session::userId(),
            'descripcion' => $descripcion,
            'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
        ]);
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
}
