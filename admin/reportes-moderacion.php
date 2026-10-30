<?php
/**
 * ==========================================================================
 * STUDY MATCH • Panel de Moderación y Alertas (admin/reportes-moderacion.php)
 * Plataforma de Apoyo Académico Colaborativo - UTN FRH
 * ==========================================================================
 */
session_start();

// Control de sesión básica de administrador
$adminNombre = $_SESSION['nombre_usuario'] ?? 'Administrador UTN';
$adminEmail  = $_SESSION['correo_usuario'] ?? 'admin@frh.utn.edu.ar';
$adminRol    = 'Superadmin';

/* ==========================================================================
 * GUÍA DE INTEGRACIÓN BACKEND (PHP PDO + MySQL):
 * ==========================================================================
 * Tu compañero de backend podrá conectar esta vista con la base de datos real:
 * 
 * require_once __DIR__ . '/../app/config/Database.php';
 * $db = Database::getInstance()->getConnection();
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
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Study Match - Moderación y Alertas</title>

  <!-- Script preventivo para evitar parpadeo de modo oscuro -->
  <script>
    (function() {
      const savedTheme = localStorage.getItem('study-match-theme') || 'light';
      document.documentElement.setAttribute('data-bs-theme', savedTheme);
    })();
  </script>

  <!-- Google Fonts: Inter & Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3.3 CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap Icons 1.11.3 (CDN) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Estilos base compartidos de Dashboards -->
  <link rel="stylesheet" href="../css/dashboard.css?v=20261029_admin_clean">

  <!-- Estilos globales de Administración (Fondo img_admin.png desenfocado, sidebar claro gris azulado / oscuro grafito) -->
  <link rel="stylesheet" href="../css/admin-global.css?v=20261029_v3">

  <!-- Estilos específicos de Moderación y Alertas -->
  <link rel="stylesheet" href="../css/admin-reportes.css?v=20261029_v3">
</head>
<body class="dashboard-body" data-role="admin">

  <!-- ==========================================================================
       SIDEBAR LATERAL IZQUIERDA (MODO ADMIN - FIJO TONO GRAFITO)
       ========================================================================== -->
  <aside id="mainSidebar" class="sidebar sidebar-admin d-flex flex-column justify-content-between p-3">
    <div>
      <!-- Header con Logo Study Match y Botón de Colapso -->
      <div class="d-flex align-items-center justify-content-between mb-4 px-2 logo-container">
        <a href="dashboard.php" class="d-flex align-items-center gap-2 text-decoration-none overflow-hidden">
          <svg class="logo-svg flex-shrink-0" width="36" height="36" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M28 54 V72 C28 80 72 80 72 72 V54 L50 65 Z" fill="#172554"/>
            <polygon points="50,20 10,42 50,62" fill="#1e3a8a"/>
            <polygon points="50,20 90,42 50,62" fill="#1677c8"/>
            <line x1="78" y1="48" x2="78" y2="70" stroke="#1677c8" stroke-width="3.5" stroke-linecap="round"/>
            <circle cx="78" cy="68" r="3.5" fill="#1677c8"/>
            <polygon points="78,68 73,82 83,82" fill="#1677c8"/>
            <path d="M38 41 L48 51 L76 23 L60 34 L48 44 L42 37 Z" fill="#ffffff" stroke="#ffffff" stroke-linejoin="round" stroke-width="2"/>
          </svg>
          <div class="lh-1">
            <span class="fw-bold fs-5 logo-text">Study <span class="text-primary">Match</span></span>
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill ms-1 sidebar-hide-collapsed" style="font-size: 0.65rem; padding: 2px 7px;">ADMIN</span>
          </div>
        </a>

        <!-- Botón para contraer sidebar -->
        <button id="btnCollapseSidebar" class="btn btn-sm btn-dark border border-secondary border-opacity-25 text-secondary p-1 rounded-2 sidebar-hide-collapsed" title="Contraer menú lateral">
          <i class="bi bi-chevron-left"></i>
        </button>
      </div>

      <!-- Menú de Navegación: GESTIÓN Y ADMINISTRACIÓN -->
      <nav class="d-flex flex-column gap-1">
        <div class="px-3 pb-1 sidebar-section-title sidebar-text">
          Panel de Control
        </div>

        <!-- 1. Inicio / Resumen (Métricas) -->
        <a href="dashboard.php" class="sidebar-link" title="Inicio / Resumen">
          <i class="bi bi-speedometer2 fs-5"></i>
          <span class="sidebar-text">Inicio / Resumen</span>
        </a>

        <!-- 2. Gestión de Usuarios -->
        <a href="gestion-usuarios.php" class="sidebar-link" title="Gestión de Usuarios">
          <i class="bi bi-people fs-5"></i>
          <span class="sidebar-text">Gestión de Usuarios</span>
          <span class="badge bg-primary-subtle text-primary ms-auto sidebar-text" style="font-size: 0.68rem;">1,280</span>
        </a>

        <!-- 3. Gestión de Materias -->
        <a href="gestion-materias.php" class="sidebar-link" title="Gestión de Materias y Carreras">
          <i class="bi bi-journal-bookmark fs-5"></i>
          <span class="sidebar-text">Gestión de Materias</span>
          <span class="badge bg-secondary-subtle text-light ms-auto sidebar-text" style="font-size: 0.68rem;">10</span>
        </a>

        <!-- 4. Salas Públicas -->
        <a href="salas.php" class="sidebar-link" title="Salas Públicas">
          <i class="bi bi-broadcast fs-5 text-danger"></i>
          <span class="sidebar-text">Salas Públicas</span>
          <span class="badge bg-danger text-white rounded-pill ms-auto sidebar-text" style="font-size: 0.65rem;">6 en vivo</span>
        </a>

        <!-- 5. Reportes y Moderación (ACTIVO) -->
        <a href="reportes-moderacion.php" class="sidebar-link active" title="Moderación y Alertas">
          <i class="bi bi-exclamation-triangle fs-5 text-warning"></i>
          <span class="sidebar-text">Reportes</span>
          <span class="badge bg-warning text-dark rounded-pill ms-auto sidebar-text fw-bold" style="font-size: 0.65rem;" id="badgeSidebarReportes"><?= $reportesPendientes; ?></span>
        </a>
      </nav>
    </div>

    <!-- Usuario abajo en el Sidebar: Tarjeta de Administrador -->
    <div class="border-top border-secondary border-opacity-25 pt-3 mt-auto">
      <div class="user-sidebar-card p-2 d-flex align-items-center justify-content-between mb-2">
        <div class="d-flex align-items-center gap-2 overflow-hidden">
          <div class="avatar-user-display avatar-admin">
            AD
          </div>
          <div class="sidebar-text text-truncate lh-sm">
            <span class="d-block fw-bold text-white small text-truncate"><?= htmlspecialchars($adminNombre); ?></span>
            <span class="text-secondary" style="font-size: 0.72rem;">UTN FRH • <?= htmlspecialchars($adminRol); ?></span>
          </div>
        </div>
        <i class="bi bi-shield-check text-primary sidebar-text fs-6 me-1" title="Superadmin verificado"></i>
      </div>

      <!-- Enlace para Cerrar Sesión -->
      <a href="../login.php" class="sidebar-link text-danger p-2" title="Cerrar sesión">
        <i class="bi bi-box-arrow-left"></i>
        <span class="sidebar-text">Cerrar sesión</span>
      </a>
    </div>
  </aside>

  <!-- ==========================================================================
       CONTENIDO PRINCIPAL Y NAVBAR SUPERIOR (CONTAINER-FLUID P-4)
       ========================================================================== -->
  <div class="dashboard-main-wrapper">

    <!-- NAVBAR SUPERIOR DEL ADMINISTRADOR (LIMPIO: SIN BOTÓN DE MODAL) -->
    <header class="topbar-header px-4 py-3 sticky-top">
      <div class="d-flex align-items-center justify-content-between gap-3">
        
        <!-- Toggle de Sidebar + Título / Breadcrumb -->
        <div class="d-flex align-items-center gap-3 flex-grow-1" style="max-width: 650px;">
          <button id="btnToggleSidebar" class="btn btn-light border text-secondary p-2 rounded-3" title="Expandir/Contraer menú lateral">
            <i class="bi bi-layout-sidebar fs-5"></i>
          </button>

          <!-- Título y Breadcrumb -->
          <div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0" style="font-size: 0.72rem;">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Administración</a></li>
                <li class="breadcrumb-item active text-danger fw-semibold" aria-current="page">Moderación y Alertas</li>
              </ol>
            </nav>
            <h1 class="h6 fw-bold mb-0 text-dark">Panel de Moderación y Alertas</h1>
          </div>
        </div>

        <!-- Acciones de Cabecera: Modo Claro/Oscuro + Perfil del Admin -->
        <div class="d-flex align-items-center gap-2">
          
          <!-- Alternador de Tema Claro / Oscuro -->
          <button id="btnToggleTheme" class="btn btn-light border text-secondary p-2 rounded-3" title="Alternar Modo Claro / Oscuro" onclick="toggleTemaModo()">
            <i class="bi bi-moon-stars" id="themeIcon"></i>
          </button>

          <!-- Perfil Desplegable del Administrador -->
          <div class="dropdown">
            <button class="btn p-1 border-0 d-flex align-items-center gap-2 rounded-pill hover-bg-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="avatar-user-display avatar-admin">
                AD
              </div>
              <div class="d-none d-md-block text-start lh-1 pe-1">
                <span class="d-block fw-bold text-dark small"><?= htmlspecialchars($adminNombre); ?></span>
                <span class="text-secondary" style="font-size: 0.72rem;"><?= htmlspecialchars($adminRol); ?></span>
              </div>
              <i class="bi bi-chevron-down text-secondary small d-none d-md-inline"></i>
            </button>

            <!-- Menú Desplegable -->
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" style="min-width: 250px;">
              <li class="px-3 py-2 border-bottom mb-1 bg-light rounded-3">
                <div class="fw-bold text-dark"><?= htmlspecialchars($adminNombre); ?></div>
                <div class="text-secondary small text-truncate"><?= htmlspecialchars($adminEmail); ?></div>
                <div class="mt-1">
                  <span class="badge bg-dark text-white" style="font-size: 0.65rem;">Superadministrador</span>
                </div>
              </li>
              <li>
                <a class="dropdown-item text-danger rounded-3 py-2 d-flex align-items-center gap-2" href="../login.php">
                  <i class="bi bi-box-arrow-left fs-6"></i>
                  <span>Cerrar sesión</span>
                </a>
              </li>
            </ul>
          </div>

        </div>

      </div>
    </header>

    <!-- ÁREA DE CONTENIDO PRINCIPAL: container-fluid p-4 -->
    <main class="container-fluid p-4 view-fade">

      <!-- ====================================================================
           CABECERA CONTEXTUAL DE MODERACIÓN
           ==================================================================== -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white" 
           style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 65%, #991b1b 100%);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div>
            <span class="badge rounded-pill px-3 py-1 small fw-semibold mb-2 d-inline-flex align-items-center gap-1.5"
                  style="background-color: rgba(239, 68, 68, 0.25); color: #fecaca; border: 1px solid rgba(239, 68, 68, 0.4);">
              <i class="bi bi-shield-lock-fill"></i> Seguridad & Convivencia
            </span>
            <h1 class="h3 fw-bold mb-1 text-white">
              Centro de Moderación y Alertas
            </h1>
            <p class="text-white text-opacity-75 small mb-0">
              Gestión de reportes comunitarios y control disciplinario sobre salas de Discord y cuentas de alumnos.
            </p>
          </div>

          <button type="button" 
                  class="btn btn-outline-light rounded-pill btn-sm px-3 fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5"
                  onclick="actualizarListaReportes()">
            <i class="bi bi-arrow-clockwise"></i>
            <span>Actualizar</span>
          </button>
        </div>
      </div>

      <!-- ====================================================================
           TABLA DE AUDITORÍA (KPIs FUSIONADOS EN FILTROS Y REVELACIÓN PROGRESIVA)
           ==================================================================== -->
      <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        
        <!-- Cabecera de la tabla: contadores integrados sin duplicar tarjetas -->
        <div class="p-3.5 px-4 border-bottom">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
              <h5 class="fw-bold text-dark mb-0">Auditoría de Reportes</h5>
              <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                <span class="live-dot pulse-warning"></span>
                <span id="badgeHeaderPendientes"><?= $reportesPendientes; ?> pendientes</span>
              </span>
            </div>

            <!-- Filtros Integrados con Contadores: Todos / Discord / Alumnos + Menú desplegable -->
            <div class="d-flex align-items-center flex-wrap gap-2">
              
              <!-- Filtro tipo píldora con contadores integrados -->
              <div class="btn-group btn-group-sm rounded-pill p-1 bg-light border" role="group" aria-label="Filtro de reportes">
                <button type="button" class="btn btn-sm btn-white active rounded-pill fw-semibold px-3 shadow-sm filter-btn-tipo" onclick="cambiarFiltroTipo('todos', this)">
                  Todos <span class="badge bg-secondary-subtle text-dark ms-1" id="countFilterTodos"><?= $totalReportes; ?></span>
                </button>
                <button type="button" class="btn btn-sm text-secondary rounded-pill fw-semibold px-2.5 filter-btn-tipo" onclick="cambiarFiltroTipo('sala', this)">
                  <i class="bi bi-discord me-1 text-primary"></i>Discord <span class="badge bg-secondary-subtle text-secondary ms-1" id="countFilterSalas"><?= $salasReportadas; ?></span>
                </button>
                <button type="button" class="btn btn-sm text-secondary rounded-pill fw-semibold px-2.5 filter-btn-tipo" onclick="cambiarFiltroTipo('usuario', this)">
                  <i class="bi bi-person me-1"></i>Alumnos <span class="badge bg-secondary-subtle text-secondary ms-1" id="countFilterUsuarios"><?= $usuariosReportados; ?></span>
                </button>
              </div>

              <!-- Menú Desplegable con: Más nuevos, Pendientes, Resueltos -->
              <div class="position-relative">
                <select id="filtroEstadoOrden" 
                        class="form-select form-select-sm rounded-pill bg-light border px-3 py-1.5 fw-semibold text-secondary shadow-none" 
                        style="min-width: 155px; font-size: 0.82rem;" 
                        onchange="cambiarFiltroSelect(this.value)">
                  <option value="nuevos" selected>Más nuevos</option>
                  <option value="pendientes">Pendientes</option>
                  <option value="resueltos">Resueltos</option>
                </select>
              </div>

            </div>
          </div>
        </div>

        <!-- Tabla responsiva y limpia: revelación progresiva sin sobrecargar las filas -->
        <div class="table-responsive">
          <table class="table table-reportes align-middle mb-0" id="tablaReportesAdmin">
            <thead>
              <tr>
                <th scope="col" style="width: 110px;">Tipo</th>
                <th scope="col" style="min-width: 250px;">Elemento Reportado</th>
                <th scope="col" style="min-width: 260px;">Motivo</th>
                <th scope="col" style="width: 140px;">Fecha</th>
                <th scope="col" style="width: 120px;">Estado</th>
                <th scope="col" class="text-end" style="width: 150px;">Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaReportesBody">
              
              <!-- Filas dinámicas de reportes -->
              <?php foreach ($reportes as $rep): ?>
                <?php
                  $esPendiente = ($rep['estado'] === 'pendiente');
                  $esSala = ($rep['tipo'] === 'sala');
                ?>
                <tr class="reporte-row <?= !$esPendiente ? 'fila-resuelta' : ''; ?>" 
                    id="row-reporte-<?= $rep['id_reporte']; ?>"
                    data-id="<?= $rep['id_reporte']; ?>"
                    data-tipo="<?= $rep['tipo']; ?>"
                    data-estado="<?= $rep['estado']; ?>"
                    data-timestamp="<?= $rep['timestamp']; ?>">
                  
                  <!-- 1. Columna: Tipo (Discord / Alumno) -->
                  <td>
                    <?php if ($esSala): ?>
                      <span class="badge badge-tipo-elemento" style="background-color: rgba(88, 101, 242, 0.15); color: #5865F2; border: 1px solid rgba(88, 101, 242, 0.3);">
                        <i class="bi bi-discord"></i> Discord
                      </span>
                    <?php else: ?>
                      <span class="badge badge-tipo-elemento bg-primary-subtle text-primary border border-primary-subtle">
                        <i class="bi bi-person-fill"></i> Alumno
                      </span>
                    <?php endif; ?>
                  </td>

                  <!-- 2. Columna: Elemento Reportado -->
                  <td>
                    <div class="d-flex flex-column">
                      <span class="fw-bold text-dark elemento-nombre-slot text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($rep['elemento']); ?>">
                        <?= htmlspecialchars($rep['elemento']); ?>
                      </span>
                      <span class="text-muted small" style="font-size: 0.74rem;">
                        <?= htmlspecialchars($rep['elemento_sub']); ?>
                      </span>
                    </div>
                  </td>

                  <!-- 3. Columna: Motivo breve en una línea (la descripción completa se lee en el modal) -->
                  <td>
                    <span class="fw-semibold text-danger small d-inline-flex align-items-center gap-1 text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($rep['motivo']); ?>">
                      <i class="bi bi-shield-exclamation text-danger flex-shrink-0"></i>
                      <span class="text-truncate"><?= htmlspecialchars($rep['motivo']); ?></span>
                    </span>
                  </td>

                  <!-- 4. Columna: Fecha y Hora -->
                  <td>
                    <div class="text-secondary small d-flex align-items-center gap-1" style="font-size: 0.78rem;">
                      <i class="bi bi-clock text-muted"></i>
                      <span><?= htmlspecialchars($rep['fecha']); ?></span>
                    </div>
                  </td>

                  <!-- 5. Columna: Estado (Pendiente / Resuelto) -->
                  <td class="estado-badge-slot">
                    <?php if ($esPendiente): ?>
                      <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5">
                        <span class="live-dot pulse-warning"></span>
                        <span>Pendiente</span>
                      </span>
                    <?php else: ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Resuelto</span>
                      </span>
                    <?php endif; ?>
                  </td>

                  <!-- 6. Columna: Acciones Simplificadas [Revisar] + Descarte sutil -->
                  <td class="text-end">
                    <div class="d-inline-flex align-items-center gap-1.5">
                      
                      <!-- Botón Principal: [Revisar] -->
                      <button type="button" 
                              class="btn btn-sm btn-primary rounded-pill btn-action-pill d-inline-flex align-items-center gap-1 shadow-sm px-3"
                              onclick="abrirModalDetalle(<?= htmlspecialchars(json_encode($rep)); ?>)"
                              title="Revisar caso y aplicar sanción">
                        <i class="bi bi-eye"></i>
                        <span>Revisar</span>
                      </button>

                      <!-- Botón Descartar (icono sutil sin texto invasivo) -->
                      <button type="button" 
                              class="btn btn-sm btn-light border text-secondary rounded-circle btn-icon-subtle"
                              onclick="descartarAlerta(<?= $rep['id_reporte']; ?>)"
                              title="Descartar reporte">
                        <i class="bi bi-x-lg"></i>
                      </button>

                    </div>
                  </td>

                </tr>
              <?php endforeach; ?>

            </tbody>
          </table>
        </div>

        <!-- Estado vacío -->
        <div id="emptySearchReportes" class="p-5 text-center bg-white" style="display: none;">
          <div class="bg-light text-muted rounded-circle p-3 d-inline-flex mb-3">
            <i class="bi bi-shield-check fs-1 text-success"></i>
          </div>
          <h5 class="fw-bold text-dark mb-1">Sin alertas que coincidan</h5>
          <p class="text-secondary small mb-3">No hay denuncias pendientes con el filtro seleccionado.</p>
          <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="restablecerFiltrosReportes()">
            Restablecer Filtros
          </button>
        </div>

        <!-- Footer con Paginación Resumida -->
        <div class="card-footer bg-white border-top px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-3" id="paginacionReportesCard">
          <div class="text-secondary small" id="paginacionReportesInfoTexto">
            Mostrando <strong>1 - 5</strong> de <strong><?= $totalReportes; ?></strong> alertas registradas
          </div>
          
          <nav aria-label="Navegación de auditoría">
            <ul class="pagination pagination-sm mb-0" id="paginacionReportesControles">
              <li class="page-item disabled" id="btnPagAntRep">
                <button class="page-link rounded-start-pill px-3" onclick="cambiarPaginaRep('prev')">Anterior</button>
              </li>
              <li class="page-item active" id="btnPagItemRep1">
                <button class="page-link" onclick="irAPaginaRep(1)">1</button>
              </li>
              <li class="page-item" id="btnPagItemRep2" style="display: none;">
                <button class="page-link" onclick="irAPaginaRep(2)">2</button>
              </li>
              <li class="page-item disabled" id="btnPagSigRep">
                <button class="page-link rounded-end-pill px-3" onclick="cambiarPaginaRep('next')">Siguiente</button>
              </li>
            </ul>
          </nav>
        </div>

      </div>

    </main>
  </div>

  <!-- ==========================================================================
       MODAL: DETALLE DEL REPORTE CON ACCIÓN DIRECTA DE DESACTIVAR CUENTA / DISCORD
       ========================================================================== -->
  <div class="modal fade" id="modalDetalleReporte" tabindex="-1" aria-labelledby="modalDetalleReporteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
              <i class="bi bi-card-checklist fs-4"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark" id="modalDetalleReporteLabel">Detalle de la Denuncia</h5>
              <p class="text-secondary small mb-0">Evaluación del reporte y resolución directa</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body p-4">
          
          <div class="row g-3 mb-3">
            <div class="col-md-7">
              <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.68rem;">Elemento Denunciado:</span>
              <h6 class="fw-bold text-dark mb-0" id="detElemento">Cargando...</h6>
              <span class="text-secondary small" id="detElementoSub">...</span>
            </div>
            <div class="col-md-5">
              <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.68rem;">Denunciante:</span>
              <div class="d-flex align-items-center gap-2">
                <div class="avatar-user-badge" id="detDenuncianteAvatar">??</div>
                <div>
                  <span class="fw-bold text-dark small d-block" id="detDenuncianteNombre">...</span>
                  <span class="text-muted" style="font-size: 0.72rem;" id="detDenuncianteRol">...</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Motivo y descripción del incidente -->
          <div class="p-3 bg-light rounded-3 border mb-3">
            <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.68rem;">Motivo e Infracción Declarada:</span>
            <div class="fw-bold text-danger mb-2" id="detMotivo">...</div>
            <p class="text-secondary small mb-0" id="detDescripcion" style="line-height: 1.5;">...</p>
          </div>

          <!-- Evidencia o enlace -->
          <div class="p-3 rounded-3 border mb-2">
            <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.68rem;">Evidencia / Referencia de Ubicación:</span>
            <code class="text-dark d-block text-break" id="detEvidencia">...</code>
          </div>

        </div>

        <!-- Footer con Acciones Directas: Desactivar cuenta de alumno o Discord -->
        <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
          
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
            Cerrar
          </button>

          <div class="d-flex align-items-center gap-2">
            <!-- Botón Descartar desde Modal -->
            <button type="button" 
                    class="btn btn-outline-secondary rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1.5"
                    id="btnDescartarModal"
                    onclick="descartarDesdeModal()">
              <i class="bi bi-trash"></i>
              <span>Descartar Alerta</span>
            </button>

            <!-- Botón Dinámico: Desactivar Sala de Discord O Desactivar Cuenta de Alumno -->
            <button type="button" 
                    class="btn btn-danger rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" 
                    id="btnAccionDesactivarModal" 
                    onclick="ejecutarDesactivacionDesdeDetalle()">
              <i class="bi" id="iconoAccionDesactivar"></i>
              <span id="textoAccionDesactivar">Desactivar</span>
            </button>
          </div>

        </div>

      </div>
    </div>
  </div>

  <!-- TOAST DE NOTIFICACIÓN DE ACCIONES -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="toastAdminReportes" class="toast align-items-center text-bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill text-success fs-5"></i>
          <span id="toastAdminReportesMsg">Acción procesada correctamente.</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       JAVASCRIPT: COMPORTAMIENTOS, FILTRADO Y ACCIONES DESDE EL MODAL
       ========================================================================== -->
  <!-- Bootstrap 5.3.3 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // ==========================================
    // 1. Modo Claro / Oscuro
    // ==========================================
    function aplicarTema(tema) {
      document.documentElement.setAttribute('data-bs-theme', tema);
      localStorage.setItem('study-match-theme', tema);
      const icon = document.getElementById('themeIcon');
      if (icon) {
        if (tema === 'dark') {
          icon.className = 'bi bi-sun-fill text-warning';
        } else {
          icon.className = 'bi bi-moon-stars text-secondary';
        }
      }
    }

    function toggleTemaModo() {
      const temaActual = document.documentElement.getAttribute('data-bs-theme') || 'light';
      const nuevoTema = (temaActual === 'dark') ? 'light' : 'dark';
      aplicarTema(nuevoTema);
      mostrarToast(`Modo ${nuevoTema === 'dark' ? 'oscuro' : 'claro'} activado`);
    }

    // ==========================================
    // 2. Colapso / Expansión del Sidebar
    // ==========================================
    const sidebar = document.getElementById('mainSidebar');
    const btnToggleSidebar = document.getElementById('btnToggleSidebar');
    const btnCollapseSidebar = document.getElementById('btnCollapseSidebar');

    function toggleSidebar() {
      sidebar.classList.toggle('collapsed');
      const isCollapsed = sidebar.classList.contains('collapsed');
      document.body.classList.toggle('sidebar-is-collapsed', isCollapsed);
      localStorage.setItem('study-match-admin-sidebar-collapsed', isCollapsed ? 'true' : 'false');
    }

    if (btnToggleSidebar) btnToggleSidebar.addEventListener('click', toggleSidebar);
    if (btnCollapseSidebar) btnCollapseSidebar.addEventListener('click', toggleSidebar);

    if (localStorage.getItem('study-match-admin-sidebar-collapsed') === 'true') {
      sidebar.classList.add('collapsed');
      document.body.classList.add('sidebar-is-collapsed');
    }

    // ==========================================
    // 3. Filtrado y Ordenamiento (Discord / Alumnos + Menú Desplegable)
    // ==========================================
    const ITEMS_POR_PAGINA = 5;
    let paginaActualRep = 1;
    let filtroTipoActual = 'todos';     // 'todos' | 'sala' (Discord) | 'usuario' (Alumnos)
    let filtroOrdenEstado = 'nuevos';   // 'nuevos' | 'pendientes' | 'resueltos'
    let reportesFiltrados = [];

    function actualizarPaginacionRep() {
      const todasLasFilas = Array.from(document.querySelectorAll('#tablaReportesBody .reporte-row'));

      // Filtrar filas según tipo y desplegable
      reportesFiltrados = todasLasFilas.filter(row => {
        const tipo = row.getAttribute('data-tipo');
        const estado = row.getAttribute('data-estado');

        // Filtro 1: Discord o Alumnos
        let coincideTipo = (filtroTipoActual === 'todos') || (tipo === filtroTipoActual);

        // Filtro 2: Desplegable (Más nuevos, Pendientes, Resueltos)
        let coincideEstado = true;
        if (filtroOrdenEstado === 'pendientes') {
          coincideEstado = (estado === 'pendiente');
        } else if (filtroOrdenEstado === 'resueltos') {
          coincideEstado = (estado === 'resuelto');
        }

        return coincideTipo && coincideEstado;
      });

      // Ordenar por más nuevos si corresponde
      if (filtroOrdenEstado === 'nuevos') {
        reportesFiltrados.sort((a, b) => {
          const tA = parseInt(a.getAttribute('data-timestamp') || '0', 10);
          const tB = parseInt(b.getAttribute('data-timestamp') || '0', 10);
          return tB - tA;
        });
      }

      const totalFiltrados = reportesFiltrados.length;
      const totalPaginas = Math.ceil(totalFiltrados / ITEMS_POR_PAGINA) || 1;

      if (paginaActualRep > totalPaginas) paginaActualRep = totalPaginas;
      if (paginaActualRep < 1) paginaActualRep = 1;

      // Ocultar todas las filas
      todasLasFilas.forEach(row => row.style.display = 'none');

      // Mostrar filas de la página actual
      const inicio = (paginaActualRep - 1) * ITEMS_POR_PAGINA;
      const fin = inicio + ITEMS_POR_PAGINA;
      const visibles = reportesFiltrados.slice(inicio, fin);
      visibles.forEach(row => row.style.display = '');

      // Control de estado vacío
      const emptyState = document.getElementById('emptySearchReportes');
      const pagCard = document.getElementById('paginacionReportesCard');

      if (totalFiltrados === 0) {
        if (emptyState) emptyState.style.display = '';
        if (pagCard) pagCard.style.display = 'none';
      } else {
        if (emptyState) emptyState.style.display = 'none';
        if (pagCard) pagCard.style.display = 'flex';

        const infoTexto = document.getElementById('paginacionReportesInfoTexto');
        if (infoTexto) {
          const primerNum = inicio + 1;
          const ultimoNum = Math.min(fin, totalFiltrados);
          infoTexto.innerHTML = `Mostrando <strong>${primerNum} - ${ultimoNum}</strong> de <strong>${totalFiltrados}</strong> alertas registradas`;
        }

        const btnAnt = document.getElementById('btnPagAntRep');
        const btnSig = document.getElementById('btnPagSigRep');
        const item1 = document.getElementById('btnPagItemRep1');
        const item2 = document.getElementById('btnPagItemRep2');

        if (btnAnt) btnAnt.classList.toggle('disabled', paginaActualRep <= 1);
        if (btnSig) btnSig.classList.toggle('disabled', paginaActualRep >= totalPaginas);

        if (item1) {
          item1.classList.toggle('active', paginaActualRep === 1);
          item1.style.display = totalPaginas >= 1 ? '' : 'none';
        }
        if (item2) {
          item2.classList.toggle('active', paginaActualRep === 2);
          item2.style.display = totalPaginas >= 2 ? '' : 'none';
        }
      }
    }

    function cambiarFiltroTipo(tipo, btn) {
      filtroTipoActual = tipo;
      paginaActualRep = 1;

      document.querySelectorAll('.filter-btn-tipo').forEach(b => {
        b.classList.remove('btn-white', 'active', 'shadow-sm');
        b.classList.add('text-secondary');
      });
      btn.classList.remove('text-secondary');
      btn.classList.add('btn-white', 'active', 'shadow-sm');

      actualizarPaginacionRep();
    }

    function cambiarFiltroSelect(valor) {
      filtroOrdenEstado = valor;
      paginaActualRep = 1;
      actualizarPaginacionRep();
    }

    function irAPaginaRep(num) {
      paginaActualRep = num;
      actualizarPaginacionRep();
    }

    function cambiarPaginaRep(accion) {
      if (accion === 'prev') irAPaginaRep(paginaActualRep - 1);
      if (accion === 'next') irAPaginaRep(paginaActualRep + 1);
    }

    function restablecerFiltrosReportes() {
      filtroTipoActual = 'todos';
      filtroOrdenEstado = 'nuevos';

      const select = document.getElementById('filtroEstadoOrden');
      if (select) select.value = 'nuevos';

      document.querySelectorAll('.filter-btn-tipo').forEach(b => {
        b.classList.remove('btn-white', 'active', 'shadow-sm');
        b.classList.add('text-secondary');
      });
      document.querySelectorAll('.filter-btn-tipo')[0]?.classList.add('btn-white', 'active', 'shadow-sm');

      paginaActualRep = 1;
      actualizarPaginacionRep();
    }

    // ==========================================
    // 4. Modal [Ver Detalle] y Acción de Desactivar
    // ==========================================
    let reporteActivoEnModal = null;

    function abrirModalDetalle(rep) {
      reporteActivoEnModal = rep;
      document.getElementById('detElemento').textContent = rep.elemento;
      document.getElementById('detElementoSub').textContent = rep.elemento_sub;
      document.getElementById('detMotivo').textContent = rep.motivo;
      document.getElementById('detDescripcion').textContent = rep.descripcion;
      document.getElementById('detDenuncianteNombre').textContent = rep.denunciante;
      document.getElementById('detDenuncianteRol').textContent = rep.denunciante_rol;
      document.getElementById('detDenuncianteAvatar').textContent = rep.denunciante_avatar;
      document.getElementById('detEvidencia').textContent = rep.evidencia_url || 'Sin evidencia adjunta';

      // Configurar botón dinámico según si es Discord o Alumno
      const btnDesactivar = document.getElementById('btnAccionDesactivarModal');
      const iconoDesactivar = document.getElementById('iconoAccionDesactivar');
      const textoDesactivar = document.getElementById('textoAccionDesactivar');
      const btnDescartarModal = document.getElementById('btnDescartarModal');

      if (rep.estado === 'resuelto') {
        btnDesactivar.disabled = true;
        btnDesactivar.className = 'btn btn-secondary rounded-pill px-4 disabled';
        iconoDesactivar.className = 'bi bi-check-circle-fill me-1';
        textoDesactivar.textContent = 'Caso ya Resuelto';
        if (btnDescartarModal) btnDescartarModal.style.display = 'none';
      } else {
        btnDesactivar.disabled = false;
        btnDesactivar.className = 'btn btn-danger rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm';
        if (btnDescartarModal) btnDescartarModal.style.display = 'inline-flex';
        
        if (rep.tipo === 'sala') {
          iconoDesactivar.className = 'bi bi-slash-circle me-1';
          textoDesactivar.textContent = 'Desactivar Sala de Discord';
        } else {
          iconoDesactivar.className = 'bi bi-person-slash me-1';
          textoDesactivar.textContent = 'Desactivar Cuenta de Alumno';
        }
      }

      const modalEl = document.getElementById('modalDetalleReporte');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }

    function descartarDesdeModal() {
      if (!reporteActivoEnModal) return;
      const id = reporteActivoEnModal.id_reporte;
      const modalEl = document.getElementById('modalDetalleReporte');
      const modal = bootstrap.Modal.getInstance(modalEl);
      if (modal) modal.hide();
      descartarAlerta(id);
    }

    function ejecutarDesactivacionDesdeDetalle() {
      if (!reporteActivoEnModal) return;

      const id = reporteActivoEnModal.id_reporte;
      const esSala = (reporteActivoEnModal.tipo === 'sala');
      const row = document.getElementById(`row-reporte-${id}`);

      if (row) {
        row.setAttribute('data-estado', 'resuelto');
        row.classList.add('fila-resuelta');

        // Actualizar badge de estado a Resuelto
        const badgeSlot = row.querySelector('.estado-badge-slot');
        if (badgeSlot) {
          badgeSlot.innerHTML = `
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1">
              <i class="bi bi-check-circle-fill"></i>
              <span>Resuelto</span>
            </span>
          `;
        }
      }

      reporteActivoEnModal.estado = 'resuelto';

      // Cerrar modal
      const modalEl = document.getElementById('modalDetalleReporte');
      const modal = bootstrap.Modal.getInstance(modalEl);
      if (modal) modal.hide();

      // Recalcular contador de pendientes
      actualizarContadorPendientes();
      actualizarPaginacionRep();

      const mensaje = esSala ? 
        `Sala de Discord desactivada y reporte #${id} marcado como resuelto.` : 
        `Cuenta de alumno suspendida y reporte #${id} marcado como resuelto.`;

      mostrarToast(mensaje);
    }

    // ==========================================
    // 5. [Descartar Alerta]
    // ==========================================
    function descartarAlerta(id) {
      const row = document.getElementById(`row-reporte-${id}`);
      if (row) {
        row.style.transition = 'all 0.25s ease';
        row.style.opacity = '0';
        row.style.transform = 'scale(0.96)';
        setTimeout(() => {
          row.remove();
          actualizarContadorPendientes();
          actualizarPaginacionRep();
          mostrarToast(`Alerta #${id} descartada por falta de mérito.`);
        }, 250);
      }
    }

    function actualizarContadorPendientes() {
      const rows = document.querySelectorAll('#tablaReportesBody .reporte-row');
      let pendientes = 0;
      let salas = 0;
      let usuarios = 0;
      let total = rows.length;

      rows.forEach(r => {
        const est = r.getAttribute('data-estado');
        const tip = r.getAttribute('data-tipo');
        if (est === 'pendiente') pendientes++;
        if (tip === 'sala') salas++;
        if (tip === 'usuario') usuarios++;
      });

      const badgeHeader = document.getElementById('badgeHeaderPendientes');
      const badgeNav = document.getElementById('badgeSidebarReportes');
      const countTodos = document.getElementById('countFilterTodos');
      const countSalas = document.getElementById('countFilterSalas');
      const countUsuarios = document.getElementById('countFilterUsuarios');

      if (badgeHeader) badgeHeader.textContent = `${pendientes} pendientes`;
      if (badgeNav) badgeNav.textContent = pendientes;
      if (countTodos) countTodos.textContent = total;
      if (countSalas) countSalas.textContent = salas;
      if (countUsuarios) countUsuarios.textContent = usuarios;
    }

    function actualizarListaReportes() {
      mostrarToast(`Auditoría de reportes actualizada.`);
      actualizarPaginacionRep();
    }

    // ==========================================
    // 6. Toast de Notificación
    // ==========================================
    function mostrarToast(mensaje) {
      const toastEl = document.getElementById('toastAdminReportes');
      const toastMsg = document.getElementById('toastAdminReportesMsg');
      if (toastEl && toastMsg) {
        toastMsg.textContent = mensaje;
        const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3200 });
        toast.show();
      }
    }

    // Inicialización al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
      const temaGuardado = localStorage.getItem('study-match-theme') || 'light';
      aplicarTema(temaGuardado);
      actualizarPaginacionRep();
    });
  </script>
</body>
</html>
