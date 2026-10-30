<?php
/**
 * ==========================================================================
 * STUDY MATCH • Salas Públicas de Estudio (admin/salas.php)
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
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Study Match - Salas Públicas de Estudio</title>

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

  <!-- Estilos específicos de Salas Públicas (Modo Claro y Oscuro) -->
  <link rel="stylesheet" href="../css/admin-salas.css?v=20261029_v3">
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

        <!-- 4. Salas Públicas (ACTIVO) -->
        <a href="salas.php" class="sidebar-link active" title="Salas Públicas">
          <i class="bi bi-broadcast fs-5 text-danger"></i>
          <span class="sidebar-text">Salas Públicas</span>
          <span class="badge bg-danger text-white rounded-pill ms-auto sidebar-text" style="font-size: 0.65rem;"><?= $salasActivas; ?> en vivo</span>
        </a>

        <!-- 5. Reportes -->
        <a href="reportes.php" class="sidebar-link" title="Reportes del Sistema">
          <i class="bi bi-exclamation-triangle fs-5 text-warning"></i>
          <span class="sidebar-text">Reportes</span>
          <span class="badge bg-warning text-dark rounded-pill ms-auto sidebar-text fw-bold" style="font-size: 0.65rem;">3</span>
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

    <!-- NAVBAR SUPERIOR DEL ADMINISTRADOR -->
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
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Salas Públicas</li>
              </ol>
            </nav>
            <h1 class="h6 fw-bold mb-0 text-dark">Salas Públicas de Estudio</h1>
          </div>
        </div>

        <!-- Acciones de Cabecera: Toggle Modo Claro/Oscuro + Perfil del Admin -->
        <div class="d-flex align-items-center gap-2">
          
          <!-- Botón para Alternar Modo Claro / Oscuro -->
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
           1. ENCABEZADO Y CONTADOR DESTACADO DE SALAS ACTIVAS
           ==================================================================== -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white" 
           style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 65%, #312e81 100%);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div>
            <span class="badge rounded-pill px-3 py-1 small fw-semibold mb-2 d-inline-flex align-items-center gap-1.5"
                  style="background-color: rgba(88, 101, 242, 0.25); color: #c7d2fe; border: 1px solid rgba(88, 101, 242, 0.4);">
              <i class="bi bi-discord"></i> Conectadas a Discord
            </span>
            <h1 class="h3 fw-bold mb-1 text-white">
              Salas Públicas de Estudio
            </h1>
            <p class="text-white text-opacity-75 small mb-0">
              Supervisá y moderá en tiempo real las salas comunitarias creadas por alumnos y tutores de la UTN.
            </p>
          </div>

          <!-- Métricas y Contador Visual Destacado (Sin cantidad de participantes) -->
          <div class="d-flex align-items-center gap-2 flex-wrap">
            
            <!-- Contador visual destacado de "Salas Activas" con pulso verde -->
            <div class="bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
              <span class="live-dot pulse-success"></span>
              <span class="fw-bold small" style="font-size: 0.82rem;">
                <span id="badgeContadorActivas"><?= $salasActivas; ?></span> Salas Activas
              </span>
            </div>

            <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-3 py-2 small border border-white border-opacity-15">
              <i class="bi bi-slash-circle-fill me-1 text-danger"></i> <strong><?= $salasSuspendidas; ?></strong> Suspendidas
            </span>

          </div>
        </div>
      </div>

      <!-- ====================================================================
           2. BARRA DE FILTROS Y BÚSQUEDA SUPERIOR
           ==================================================================== -->
      <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <div class="row g-3 align-items-center justify-content-between">
          
          <!-- Input de búsqueda rápida por tema, materia o creador -->
          <div class="col-12 col-md-6 col-lg-5">
            <div class="position-relative">
              <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
              <input type="text" 
                     id="salaSearchInput" 
                     class="form-control rounded-pill ps-5 py-2 bg-light border shadow-none" 
                     style="font-size: 0.88rem;" 
                     placeholder="Buscar por tema, materia o creador (ej: Programación, Lucas, SQL)...">
            </div>
          </div>

          <!-- Grupo de botones o filtros para alternar el estado de las salas -->
          <div class="col-12 col-md-6 col-lg-7 d-flex justify-content-md-end flex-wrap gap-2">
            <div class="btn-group btn-group-sm rounded-pill p-1 bg-light border" role="group" aria-label="Filtro de estado de salas">
              
              <!-- [Todas] -->
              <button type="button" 
                      class="btn btn-sm btn-white active rounded-pill fw-semibold px-3 shadow-sm filter-btn-estado" 
                      data-filter="todas" 
                      onclick="cambiarFiltroEstado('todas', this)">
                Todas <span class="badge bg-secondary-subtle text-dark ms-1" id="filterCountTodas"><?= $totalSalas; ?></span>
              </button>

              <!-- [🟢 Activas] -->
              <button type="button" 
                      class="btn btn-sm text-secondary rounded-pill fw-semibold px-3 filter-btn-estado" 
                      data-filter="activa" 
                      onclick="cambiarFiltroEstado('activa', this)">
                <span class="live-dot bg-success d-inline-block me-1" style="width: 7px; height: 7px;"></span>
                Activas (<span id="filterCountActivas"><?= $salasActivas; ?></span>)
              </button>

              <!-- [🔴 Inactivas / Suspendidas] -->
              <button type="button" 
                      class="btn btn-sm text-secondary rounded-pill fw-semibold px-3 filter-btn-estado" 
                      data-filter="suspendida" 
                      onclick="cambiarFiltroEstado('suspendida', this)">
                <span class="live-dot bg-danger d-inline-block me-1" style="width: 7px; height: 7px;"></span>
                Inactivas / Suspendidas (<span id="filterCountSuspendidas"><?= $salasSuspendidas; ?></span>)
              </button>

            </div>
          </div>

        </div>
      </div>

      <!-- ====================================================================
           3. LISTADO DE TARJETAS DE SALAS (DISEÑO HORIZONTAL / LIMITADO A 5 POR VISTA)
           ==================================================================== -->
      <div id="contenedorSalas" class="d-flex flex-column gap-3">
        
        <?php foreach ($salas as $sala): ?>
          <?php
            $esActiva = ($sala['estado'] === 'activa');
          ?>
          <div class="sala-card p-3 p-md-4 shadow-sm <?= !$esActiva ? 'sala-suspendida' : ''; ?>" 
               id="card-sala-<?= $sala['id_sala']; ?>"
               data-estado="<?= $sala['estado']; ?>"
               data-materia="<?= htmlspecialchars(strtolower($sala['materia'] . ' ' . $sala['materia_code'])); ?>"
               data-titulo="<?= htmlspecialchars(strtolower($sala['titulo'])); ?>"
               data-creador="<?= htmlspecialchars(strtolower($sala['creador'])); ?>">
            
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
              
              <!-- SECCIÓN IZQUIERDA: ICONO DISCORD + INFORMACIÓN DE LA SALA -->
              <div class="d-flex align-items-start gap-3 flex-grow-1 overflow-hidden">
                
                <!-- Icono distintivo de Discord con su color característico (#5865F2) -->
                <div class="discord-icon-badge" title="Canal de voz / texto en Discord">
                  <i class="bi bi-discord"></i>
                </div>

                <!-- Detalles Principales de la Sala -->
                <div class="flex-grow-1 overflow-hidden">
                  
                  <!-- Fila superior: Badge de Materia + Badge de Estado Visible -->
                  <div class="d-flex align-items-center flex-wrap gap-2 mb-1.5">
                    
                    <!-- Badge de la materia -->
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                      <i class="bi bi-book me-1"></i><?= htmlspecialchars($sala['materia_code']); ?> • <?= htmlspecialchars($sala['materia']); ?>
                    </span>

                    <!-- Badge de estado visible en cada tarjeta (Activa / Suspendida) -->
                    <?php if ($esActiva): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5 badge-estado-slot">
                        <span class="live-dot pulse-success"></span>
                        <span>Activa</span>
                      </span>
                    <?php else: ?>
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5 badge-estado-slot">
                        <i class="bi bi-slash-circle-fill"></i>
                        <span>Suspendida</span>
                      </span>
                    <?php endif; ?>

                    <!-- Alerta de reportes si existen -->
                    <?php if ($sala['reportes'] > 0): ?>
                      <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 small fw-semibold" title="<?= $sala['reportes']; ?> reportes de usuarios">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i><?= $sala['reportes']; ?> <?= $sala['reportes'] === 1 ? 'reporte' : 'reportes'; ?>
                      </span>
                    <?php endif; ?>

                  </div>

                  <!-- Título de la sala -->
                  <h5 class="h6 fw-bold mb-1 text-dark sala-titulo-slot text-truncate">
                    <?= htmlspecialchars($sala['titulo']); ?>
                  </h5>

                  <!-- Descripción breve -->
                  <p class="text-secondary small mb-2 line-clamp-2" style="font-size: 0.82rem; line-height: 1.4;">
                    <?= htmlspecialchars($sala['descripcion']); ?>
                  </p>

                  <!-- Meta-información: Creador y Tiempo (Sin cantidad de participantes) -->
                  <div class="d-flex align-items-center flex-wrap gap-3 text-muted small" style="font-size: 0.78rem;">
                    
                    <!-- Nombre del creador con su avatar y rol -->
                    <div class="d-flex align-items-center gap-1.5">
                      <div class="avatar-creator">
                        <?= htmlspecialchars($sala['creador_avatar']); ?>
                      </div>
                      <span>
                        Por <strong class="text-dark"><?= htmlspecialchars($sala['creador']); ?></strong> 
                        <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.65rem;"><?= htmlspecialchars($sala['creador_rol']); ?></span>
                      </span>
                    </div>

                    <!-- Tiempo transcurrido -->
                    <div class="d-flex align-items-center gap-1">
                      <i class="bi bi-clock text-secondary"></i>
                      <span><?= htmlspecialchars($sala['fecha_creacion']); ?></span>
                    </div>

                    <!-- Motivo de suspensión si aplica -->
                    <?php if (!$esActiva && !empty($sala['motivo_suspension'])): ?>
                      <div class="text-danger fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-shield-exclamation"></i>
                        <span>Motivo: <?= htmlspecialchars($sala['motivo_suspension']); ?></span>
                      </div>
                    <?php endif; ?>

                  </div>

                </div>

              </div>

              <!-- SECCIÓN DERECHA: COLUMNA DE BOTONES DE ACCIÓN POR SALA -->
              <div class="d-flex flex-row flex-lg-column align-items-end justify-content-end gap-2 flex-shrink-0 pt-2 pt-lg-0 border-top border-lg-0">
                
                <!-- Botón [Unirse] (con enlace externo simulado a Discord) -->
                <?php if ($esActiva): ?>
                  <a href="<?= htmlspecialchars($sala['discord_url']); ?>" 
                     target="_blank" 
                     rel="noopener noreferrer" 
                     class="btn btn-discord btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm btn-unirse-slot"
                     title="Abrir canal de voz y texto en Discord">
                    <i class="bi bi-discord"></i>
                    <span>Unirse a Discord</span>
                    <i class="bi bi-box-arrow-up-right small"></i>
                  </a>
                <?php else: ?>
                  <button type="button" 
                          class="btn btn-secondary btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 disabled btn-unirse-slot" 
                          disabled 
                          title="Sala temporalmente suspendida por moderación">
                    <i class="bi bi-lock-fill"></i>
                    <span>Enlace Bloqueado</span>
                  </button>
                <?php endif; ?>

                <!-- Botón de Moderación: [Suspender] o [Reactivar] -->
                <div class="btn-moderacion-container">
                  <?php if ($esActiva): ?>
                    <button type="button" 
                            class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5"
                            onclick="abrirModalSuspension(<?= htmlspecialchars(json_encode($sala)); ?>)"
                            title="Desactivar o suspender sala por reportes de usuarios">
                      <i class="bi bi-slash-circle"></i>
                      <span>Suspender</span>
                    </button>
                  <?php else: ?>
                    <button type="button" 
                            class="btn btn-outline-success btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5"
                            onclick="reactivarSala(<?= $sala['id_sala']; ?>, '<?= addslashes($sala['titulo']); ?>')"
                            title="Reactivar y habilitar sala en el catálogo público">
                      <i class="bi bi-arrow-clockwise"></i>
                      <span>Reactivar Sala</span>
                    </button>
                  <?php endif; ?>
                </div>

              </div>

            </div>

          </div>
        <?php endforeach; ?>

      </div>

      <!-- Estado vacío cuando no hay resultados de búsqueda -->
      <div id="emptySearchState" class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-3" style="display: none;">
        <div class="py-4">
          <div class="bg-light text-muted rounded-circle p-3 d-inline-flex mb-3">
            <i class="bi bi-broadcast-pin fs-1"></i>
          </div>
          <h5 class="fw-bold text-dark mb-1">No se encontraron salas públicas</h5>
          <p class="text-secondary small mb-3">Probá cambiando los términos de búsqueda o alternando los filtros de estado.</p>
          <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="limpiarFiltros()">
            Restablecer Filtros
          </button>
        </div>
      </div>

      <!-- ====================================================================
           4. PAGINACIÓN DE SALAS (LIMITADO A 5 SALAS POR VISTA)
           ==================================================================== -->
      <div class="card border-0 shadow-sm rounded-4 bg-white p-3 d-flex flex-row align-items-center justify-content-between flex-wrap gap-3 mt-3" id="paginacionSalasCard">
        <div class="text-secondary small" id="paginacionInfoTexto">
          Mostrando <strong>1 - 5</strong> de <strong><?= $totalSalas; ?></strong> salas registradas
        </div>
        
        <nav aria-label="Navegación de salas">
          <ul class="pagination pagination-sm mb-0" id="paginacionControles">
            <li class="page-item disabled" id="btnPagAnterior">
              <button class="page-link rounded-start-pill px-3" onclick="cambiarPagina('prev')">Anterior</button>
            </li>
            <li class="page-item active" id="btnPagItem1">
              <button class="page-link" onclick="irAPagina(1)">1</button>
            </li>
            <li class="page-item" id="btnPagItem2">
              <button class="page-link" onclick="irAPagina(2)">2</button>
            </li>
            <li class="page-item" id="btnPagSiguiente">
              <button class="page-link rounded-end-pill px-3" onclick="cambiarPagina('next')">Siguiente</button>
            </li>
          </ul>
        </nav>
      </div>

    </main>
  </div>

  <!-- ==========================================================================
       MODAL DE CONFIRMACIÓN DE SUSPENSIÓN DE SALA
       ========================================================================== -->
  <div class="modal fade" id="modalSuspenderSala" tabindex="-1" aria-labelledby="modalSuspenderSalaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-danger-subtle text-danger p-2 rounded-3">
              <i class="bi bi-shield-x fs-4"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark" id="modalSuspenderSalaLabel">Suspender Sala Pública</h5>
              <p class="text-secondary small mb-0">Moderación y control de convivencia en Discord</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <form id="formSuspensionModal" onsubmit="ejecutarSuspensionSala(event)">
          <div class="modal-body p-4">
            <input type="hidden" id="suspenderSalaId">

            <!-- Ficha resumen de la sala a suspender -->
            <div class="p-3 rounded-3 bg-light border mb-3">
              <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.68rem; letter-spacing: 0.5px;">Sala a Desactivar:</span>
              <h6 class="fw-bold text-dark mb-1" id="suspenderSalaTituloDisplay">Cargando...</h6>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small" id="suspenderSalaMateriaDisplay">
                Materia
              </span>
            </div>

            <!-- Alerta informativa -->
            <div class="alert alert-warning small rounded-3 p-3 mb-3 d-flex gap-2">
              <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0"></i>
              <div>
                Al suspender esta sala, se bloqueará el botón de unión a Discord en StudyMatch y se notificará al creador.
              </div>
            </div>

            <!-- Selector de Motivo de Suspensión -->
            <div class="mb-3">
              <label for="motivoSuspensionSelect" class="form-label small fw-semibold text-secondary mb-1">
                Motivo de la suspensión <span class="text-danger">*</span>
              </label>
              <select class="form-select rounded-3 py-2" id="motivoSuspensionSelect" required>
                <option value="" selected disabled>Seleccioná una causa...</option>
                <option value="Reporte de conducta inapropiada o falta de respeto">Reporte de conducta inapropiada o falta de respeto</option>
                <option value="Contenido no académico o Spam / Publicidad">Contenido no académico o Spam / Publicidad</option>
                <option value="Difusión de enlaces externos no autorizados">Difusión de enlaces externos no autorizados</option>
                <option value="Sala inactiva o creador ausente">Sala inactiva o creador ausente</option>
                <option value="Violación de normas de convivencia de StudyMatch">Violación de normas de convivencia de StudyMatch</option>
              </select>
            </div>

            <!-- Observaciones adicionales del Administrador -->
            <div class="mb-1">
              <label for="observacionesSuspension" class="form-label small fw-semibold text-secondary mb-1">
                Nota adicional para el historial (Opcional)
              </label>
              <textarea class="form-control rounded-3" 
                        id="observacionesSuspension" 
                        rows="2" 
                        placeholder="Detalles sobre el caso o enlace al reporte..."></textarea>
            </div>

          </div>

          <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm">
              <i class="bi bi-shield-x"></i>
              <span>Confirmar Suspensión</span>
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- TOAST DE NOTIFICACIÓN DE ACCIONES -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="toastAdminSalas" class="toast align-items-center text-bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill text-success fs-5" id="toastAdminSalasIcon"></i>
          <span id="toastAdminSalasMsg">Acción procesada con éxito.</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       JAVASCRIPT: COMPORTAMIENTOS, PAGINACIÓN (MÁXIMO 5), MODO OSCURO Y MODALES
       ========================================================================== -->
  <!-- Bootstrap 5.3.3 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // ==========================================
    // 1. Alternador de Modo Claro / Oscuro
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

    // Inicializar icono de tema según el estado actual
    document.addEventListener('DOMContentLoaded', () => {
      const temaGuardado = localStorage.getItem('study-match-theme') || 'light';
      aplicarTema(temaGuardado);
      actualizarPaginacion();
    });

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
    // 3. Paginación de Salas (Máximo 5 por vista)
    // ==========================================
    const ITEMS_POR_PAGINA = 5;
    let paginaActual = 1;
    let filtroEstadoActual = 'todas';
    let salasFiltradas = [];

    function actualizarPaginacion() {
      const todasLasSalas = Array.from(document.querySelectorAll('#contenedorSalas .sala-card'));
      const query = (document.getElementById('salaSearchInput')?.value || '').toLowerCase().trim();

      // Filtrar salas según búsqueda y estado
      salasFiltradas = todasLasSalas.filter(card => {
        const estado = card.getAttribute('data-estado');
        const titulo = card.getAttribute('data-titulo') || '';
        const materia = card.getAttribute('data-materia') || '';
        const creador = card.getAttribute('data-creador') || '';

        const coincideTexto = !query || 
                              titulo.includes(query) || 
                              materia.includes(query) || 
                              creador.includes(query);

        let coincideEstado = false;
        if (filtroEstadoActual === 'todas') {
          coincideEstado = true;
        } else if (filtroEstadoActual === 'activa') {
          coincideEstado = (estado === 'activa');
        } else if (filtroEstadoActual === 'suspendida') {
          coincideEstado = (estado === 'suspendida');
        }

        return coincideTexto && coincideEstado;
      });

      const totalFiltradas = salasFiltradas.length;
      const totalPaginas = Math.ceil(totalFiltradas / ITEMS_POR_PAGINA) || 1;

      // Mantener página en rango válido
      if (paginaActual > totalPaginas) paginaActual = totalPaginas;
      if (paginaActual < 1) paginaActual = 1;

      // Ocultar todas las tarjetas
      todasLasSalas.forEach(card => card.style.display = 'none');

      // Mostrar solo las 5 de la página actual
      const inicio = (paginaActual - 1) * ITEMS_POR_PAGINA;
      const fin = inicio + ITEMS_POR_PAGINA;
      const visibles = salasFiltradas.slice(inicio, fin);
      visibles.forEach(card => card.style.display = '');

      // Estado vacío
      const emptyState = document.getElementById('emptySearchState');
      const paginacionCard = document.getElementById('paginacionSalasCard');

      if (totalFiltradas === 0) {
        if (emptyState) emptyState.style.display = '';
        if (paginacionCard) paginacionCard.style.display = 'none';
      } else {
        if (emptyState) emptyState.style.display = 'none';
        if (paginacionCard) paginacionCard.style.display = 'flex';

        // Actualizar texto de información
        const infoTexto = document.getElementById('paginacionInfoTexto');
        if (infoTexto) {
          const primerNum = inicio + 1;
          const ultimoNum = Math.min(fin, totalFiltradas);
          infoTexto.innerHTML = `Mostrando <strong>${primerNum} - ${ultimoNum}</strong> de <strong>${totalFiltradas}</strong> salas registradas`;
        }

        // Actualizar botones de navegación
        const btnAnt = document.getElementById('btnPagAnterior');
        const btnSig = document.getElementById('btnPagSiguiente');
        const item1 = document.getElementById('btnPagItem1');
        const item2 = document.getElementById('btnPagItem2');

        if (btnAnt) btnAnt.classList.toggle('disabled', paginaActual <= 1);
        if (btnSig) btnSig.classList.toggle('disabled', paginaActual >= totalPaginas);

        if (item1) {
          item1.classList.toggle('active', paginaActual === 1);
          item1.style.display = totalPaginas >= 1 ? '' : 'none';
        }
        if (item2) {
          item2.classList.toggle('active', paginaActual === 2);
          item2.style.display = totalPaginas >= 2 ? '' : 'none';
        }
      }
    }

    function irAPagina(num) {
      paginaActual = num;
      actualizarPaginacion();
      window.scrollTo({ top: 120, behavior: 'smooth' });
    }

    function cambiarPagina(accion) {
      if (accion === 'prev') {
        irAPagina(paginaActual - 1);
      } else if (accion === 'next') {
        irAPagina(paginaActual + 1);
      }
    }

    // ==========================================
    // 4. Filtrado en Tiempo Real (Búsqueda + Estado)
    // ==========================================
    function cambiarFiltroEstado(estado, btn) {
      filtroEstadoActual = estado;
      paginaActual = 1;

      document.querySelectorAll('.filter-btn-estado').forEach(b => {
        b.classList.remove('btn-white', 'active', 'shadow-sm');
        b.classList.add('text-secondary');
      });
      btn.classList.remove('text-secondary');
      btn.classList.add('btn-white', 'active', 'shadow-sm');

      actualizarPaginacion();
    }

    const searchInput = document.getElementById('salaSearchInput');
    if (searchInput) {
      searchInput.addEventListener('input', () => {
        paginaActual = 1;
        actualizarPaginacion();
      });
    }

    function limpiarFiltros() {
      if (searchInput) searchInput.value = '';
      const btnTodas = document.querySelector('[data-filter="todas"]');
      if (btnTodas) cambiarFiltroEstado('todas', btnTodas);
    }

    // ==========================================
    // 5. Modal de Suspensión de Sala
    // ==========================================
    let salaSeleccionadaParaSuspender = null;

    function abrirModalSuspension(sala) {
      salaSeleccionadaParaSuspender = sala;
      document.getElementById('suspenderSalaId').value = sala.id_sala;
      document.getElementById('suspenderSalaTituloDisplay').textContent = sala.titulo;
      document.getElementById('suspenderSalaMateriaDisplay').textContent = `${sala.materia_code} • ${sala.materia}`;
      document.getElementById('motivoSuspensionSelect').value = '';
      document.getElementById('observacionesSuspension').value = '';

      const modalEl = document.getElementById('modalSuspenderSala');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }

    function ejecutarSuspensionSala(e) {
      e.preventDefault();
      if (!salaSeleccionadaParaSuspender) return;

      const id = salaSeleccionadaParaSuspender.id_sala;
      const motivo = document.getElementById('motivoSuspensionSelect').value;
      const card = document.getElementById(`card-sala-${id}`);

      if (card) {
        card.setAttribute('data-estado', 'suspendida');
        card.classList.add('sala-suspendida');

        // Actualizar badge de estado
        const badgeSlot = card.querySelector('.badge-estado-slot');
        if (badgeSlot) {
          badgeSlot.className = 'badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5 badge-estado-slot';
          badgeSlot.innerHTML = '<i class="bi bi-slash-circle-fill"></i><span>Suspendida</span>';
        }

        // Actualizar botón Unirse (bloquearlo)
        const btnUnirse = card.querySelector('.btn-unirse-slot');
        if (btnUnirse) {
          btnUnirse.className = 'btn btn-secondary btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 disabled btn-unirse-slot';
          btnUnirse.innerHTML = '<i class="bi bi-lock-fill"></i><span>Enlace Bloqueado</span>';
          btnUnirse.removeAttribute('href');
          btnUnirse.setAttribute('disabled', 'disabled');
        }

        // Actualizar botón de acción a [Reactivar Sala]
        const btnContainer = card.querySelector('.btn-moderacion-container');
        if (btnContainer) {
          btnContainer.innerHTML = `
            <button type="button" 
                    class="btn btn-outline-success btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5"
                    onclick="reactivarSala(${id}, '${salaSeleccionadaParaSuspender.titulo.replace(/'/g, "\\'")}')"
                    title="Reactivar sala">
              <i class="bi bi-arrow-clockwise"></i>
              <span>Reactivar Sala</span>
            </button>
          `;
        }
      }

      // Cerrar modal
      const modalEl = document.getElementById('modalSuspenderSala');
      const modal = bootstrap.Modal.getInstance(modalEl);
      if (modal) modal.hide();

      // Recalcular contadores y refrescar paginación
      actualizarContadores();
      actualizarPaginacion();

      mostrarToast(`Sala "${salaSeleccionadaParaSuspender.titulo}" suspendida exitosamente.`);
    }

    // ==========================================
    // 6. Reactivar Sala
    // ==========================================
    function reactivarSala(id, titulo) {
      const card = document.getElementById(`card-sala-${id}`);
      if (card) {
        card.setAttribute('data-estado', 'activa');
        card.classList.remove('sala-suspendida');

        // Actualizar badge de estado
        const badgeSlot = card.querySelector('.badge-estado-slot');
        if (badgeSlot) {
          badgeSlot.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5 badge-estado-slot';
          badgeSlot.innerHTML = '<span class="live-dot pulse-success"></span><span>Activa</span>';
        }

        // Actualizar botón Unirse (habilitarlo)
        const btnUnirse = card.querySelector('.btn-unirse-slot');
        if (btnUnirse) {
          btnUnirse.className = 'btn btn-discord btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm btn-unirse-slot';
          btnUnirse.innerHTML = '<i class="bi bi-discord"></i><span>Unirse a Discord</span><i class="bi bi-box-arrow-up-right small"></i>';
          btnUnirse.setAttribute('href', 'https://discord.gg/studymatch');
          btnUnirse.setAttribute('target', '_blank');
          btnUnirse.removeAttribute('disabled');
        }

        // Actualizar botón de acción a [Suspender]
        const btnContainer = card.querySelector('.btn-moderacion-container');
        if (btnContainer) {
          const salaObj = { id_sala: id, titulo: titulo, materia_code: 'TUP', materia: 'Materia' };
          btnContainer.innerHTML = `
            <button type="button" 
                    class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5"
                    onclick='abrirModalSuspension(${JSON.stringify(salaObj)})'
                    title="Suspender sala">
              <i class="bi bi-slash-circle"></i>
              <span>Suspender</span>
            </button>
          `;
        }
      }

      actualizarContadores();
      actualizarPaginacion();
      mostrarToast(`Sala reactivada con éxito.`);
    }

    // ==========================================
    // 7. Recalcular Contadores Dinámicos
    // ==========================================
    function actualizarContadores() {
      const cards = document.querySelectorAll('.sala-card');
      let total = cards.length;
      let activas = 0;
      let suspendidas = 0;

      cards.forEach(card => {
        const est = card.getAttribute('data-estado');
        if (est === 'activa') activas++;
        if (est === 'suspendida') suspendidas++;
      });

      const elActivas = document.getElementById('badgeContadorActivas');
      const elFiltroTodas = document.getElementById('filterCountTodas');
      const elFiltroActivas = document.getElementById('filterCountActivas');
      const elFiltroSuspendidas = document.getElementById('filterCountSuspendidas');

      if (elActivas) elActivas.textContent = activas;
      if (elFiltroTodas) elFiltroTodas.textContent = total;
      if (elFiltroActivas) elFiltroActivas.textContent = activas;
      if (elFiltroSuspendidas) elFiltroSuspendidas.textContent = suspendidas;
    }

    // ==========================================
    // 8. Toast de Notificación
    // ==========================================
    function mostrarToast(mensaje) {
      const toastEl = document.getElementById('toastAdminSalas');
      const toastMsg = document.getElementById('toastAdminSalasMsg');
      if (toastEl && toastMsg) {
        toastMsg.textContent = mensaje;
        const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3000 });
        toast.show();
      }
    }
  </script>
</body>
</html>
