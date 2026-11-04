<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Study Match - Panel de Administración</title>

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
  <link rel="stylesheet" href="public/css/dashboard.css?v=20261106_iconos">

  <!-- Estilos globales de Administración (sidebar claro gris azulado / oscuro grafito) -->
  <link rel="stylesheet" href="public/css/admin-global.css?v=20261101_panel_bg">

  <!-- Estilos específicos del Dashboard -->
  <link rel="stylesheet" href="public/css/admin-dashboard.css?v=20261106_kpi4">
</head>
<body class="dashboard-body" data-role="admin">

  <!-- ==========================================================================
       SIDEBAR LATERAL IZQUIERDA (MODO CLARO: GRIS AZULADO | MODO OSCURO: GRAFITO)
       ========================================================================== -->
  <aside id="mainSidebar" class="sidebar sidebar-admin d-flex flex-column justify-content-between p-3">
    <div>
      <!-- Header con Logo Study Match -->
      <div class="d-flex align-items-center justify-content-between mb-4 px-2 logo-container">
        <a href="index.php?r=admin" class="d-flex align-items-center gap-2 text-decoration-none overflow-hidden">
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
      </div>

      <!-- Menú de Navegación: GESTIÓN Y ADMINISTRACIÓN -->
      <nav class="d-flex flex-column gap-1">
        <div class="px-3 pb-1 sidebar-section-title sidebar-text">
          Panel de Control
        </div>

        <!-- 1. Inicio / Resumen (Métricas) - ACTIVO -->
        <a href="index.php?r=admin" class="sidebar-link active" title="Inicio / Resumen">
          <i class="bi bi-speedometer2 fs-5"></i>
          <span class="sidebar-text">Inicio / Resumen</span>
        </a>

        <!-- 2. Gestión de Usuarios -->
        <a href="index.php?r=admin-usuarios" class="sidebar-link" title="Gestión de Usuarios">
          <i class="bi bi-people fs-5"></i>
          <span class="sidebar-text">Gestión de Usuarios</span>
          <span class="badge bg-primary-subtle text-primary ms-auto sidebar-text" style="font-size: 0.68rem;"><?= (int) $metricas['alumnos']['total']; ?></span>
        </a>

        <!-- 3. Gestión de Materias -->
        <a href="index.php?r=admin-materias" class="sidebar-link" title="Gestión de Materias">
          <i class="bi bi-journal-bookmark fs-5"></i>
          <span class="sidebar-text">Gestión de Materias</span>
          <span class="badge bg-secondary-subtle text-light ms-auto sidebar-text" style="font-size: 0.68rem;"><?= (int) $metricas['materias']['total']; ?></span>
        </a>

        <!-- 4. Salas Públicas -->
        <a href="index.php?r=admin-salas" class="sidebar-link" title="Salas Públicas">
          <i class="bi bi-broadcast fs-5 text-danger"></i>
          <span class="sidebar-text">Salas Públicas</span>
          <span class="badge bg-danger text-white rounded-pill ms-auto sidebar-text" style="font-size: 0.65rem;"><?= (int) $metricas['salas']['activas']; ?> en vivo</span>
        </a>

        <!-- 5. Reportes -->
        <a href="index.php?r=admin-reportes" class="sidebar-link" title="Reportes del Sistema">
          <i class="bi bi-exclamation-triangle fs-5 text-warning"></i>
          <span class="sidebar-text">Reportes</span>
          <span class="badge bg-warning text-dark rounded-pill ms-auto sidebar-text fw-bold" style="font-size: 0.65rem;"><?= $totalReportes; ?></span>
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
      <a href="index.php?r=logout" class="sidebar-link text-danger p-2" title="Cerrar sesión">
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
        
        <!-- Toggle de Sidebar + Título / Buscador -->
        <div class="d-flex align-items-center gap-3 flex-grow-1" style="max-width: 650px;">
          <button id="btnToggleSidebar" class="btn btn-light border text-secondary p-2 rounded-3" title="Expandir/Contraer menú lateral">
            <i class="bi bi-layout-sidebar fs-5"></i>
          </button>

          <!-- Título y Breadcrumb -->
          <div class="d-none d-sm-block me-2">
            <h1 class="h6 fw-bold mb-0 text-dark" style="text-shadow: none;">Panel de Administración</h1>
            <span class="text-muted small" style="font-size: 0.72rem;">StudyMatch • Resumen General</span>
          </div>

          <!-- Buscador Global Rápido de Administración -->
          <div class="position-relative flex-grow-1">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
            <input type="text" id="adminSearchInput" class="form-control rounded-pill ps-5 bg-light border-0 shadow-none py-2" placeholder="Buscar alumnos, tutores, materias...">
          </div>
        </div>

        <!-- Acciones: Botón Modo Claro/Oscuro + Perfil del Administrador -->
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
                <a class="dropdown-item text-danger rounded-3 py-2 d-flex align-items-center gap-2" href="index.php?r=logout">
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

      <!-- BANNER DE BIENVENIDA LIMPIO -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white" 
           style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 70%, #1e3a8a 100%);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div>
            <span class="badge bg-primary text-white rounded-pill px-3 py-1 small fw-semibold mb-2 d-inline-block">
              <i class="bi bi-shield-check me-1"></i> Control General
            </span>
            <h1 class="h3 fw-bold mb-1 text-white" style="text-shadow: none;">
              Panel de Administración y Métricas
            </h1>
            <p class="text-white text-opacity-75 small mb-0">
              Bienvenido al sistema central de gestión académica de StudyMatch UTN Haedo.
            </p>
          </div>
          <div>
            <button class="btn btn-outline-light rounded-pill px-3.5 py-2 fw-semibold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaMateria">
              <i class="bi bi-plus-circle me-1"></i> Nueva Materia
            </button>
          </div>
        </div>
      </div>

      <!-- ====================================================================
           1. TARJETAS DE RESUMEN (KPIS ENLAZABLES) • datos reales de la BD
           ==================================================================== -->
      <?php
        $mAlumnos  = $metricas['alumnos'];
        $mTutores  = $metricas['tutores'];
        $mSalas    = $metricas['salas'];
        $mMaterias = $metricas['materias'];
      ?>
      <?php if (!empty($errorMetricas)): ?>
        <div class="alert alert-warning small rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
          <i class="bi bi-exclamation-triangle-fill"></i>
          No se pudieron cargar las métricas desde la base de datos. Intentá recargar la página.
        </div>
      <?php endif; ?>

      <section class="mb-4">
        <div class="row g-3 g-xl-4">

          <!-- KPI 1: Alumnos registrados -->
          <div class="col-sm-6 col-xl-3">
            <a href="index.php?r=admin-usuarios&amp;filtro=alumnos" class="metric-card metric-card--alumnos metric-card-link p-3 p-xl-4 h-100 d-block text-decoration-none" title="Ir a Gestión de Usuarios (Alumnos)">
              <span class="d-block text-secondary small fw-semibold mb-2">Alumnos registrados</span>
              <h3 class="fw-bold text-dark mb-1"><?= number_format($mAlumnos['total'], 0, ',', '.'); ?></h3>
              <span class="text-secondary small" style="font-size: 0.72rem;">
                <?= (int) $mAlumnos['activos']; ?> activo<?= (int) $mAlumnos['activos'] === 1 ? '' : 's'; ?> · <?= (int) $mAlumnos['inactivos']; ?> suspendido<?= (int) $mAlumnos['inactivos'] === 1 ? '' : 's'; ?>
              </span>
            </a>
          </div>

          <!-- KPI 2: Tutores activos -->
          <div class="col-sm-6 col-xl-3">
            <a href="index.php?r=admin-usuarios&amp;filtro=tutores" class="metric-card metric-card--tutores metric-card-link p-3 p-xl-4 h-100 d-block text-decoration-none" title="Ir a Gestión de Usuarios (Tutores)">
              <span class="d-block text-secondary small fw-semibold mb-2">Tutores activos</span>
              <h3 class="fw-bold text-dark mb-1"><?= number_format($mTutores['activos'], 0, ',', '.'); ?></h3>
              <span class="text-secondary small" style="font-size: 0.72rem;">
                <?= (int) $mTutores['inactivos']; ?> suspendido<?= (int) $mTutores['inactivos'] === 1 ? '' : 's'; ?> · <?= (int) $mTutores['total']; ?> con tutorías publicadas
              </span>
            </a>
          </div>

          <!-- KPI 3: Salas públicas activas -->
          <div class="col-sm-6 col-xl-3">
            <a href="index.php?r=admin-salas" class="metric-card metric-card--salas metric-card-link p-3 p-xl-4 h-100 d-block text-decoration-none" title="Ir a Salas Públicas">
              <span class="d-block text-secondary small fw-semibold mb-2">Salas públicas activas</span>
              <h3 class="fw-bold text-dark mb-1"><?= number_format($mSalas['activas'], 0, ',', '.'); ?></h3>
              <span class="text-secondary small" style="font-size: 0.72rem;">
                <?= (int) $mSalas['suspendidas']; ?> suspendida<?= (int) $mSalas['suspendidas'] === 1 ? '' : 's'; ?> · <?= (int) $mSalas['total']; ?> en total
              </span>
            </a>
          </div>

          <!-- KPI 4: Materias en catálogo -->
          <div class="col-sm-6 col-xl-3">
            <a href="index.php?r=admin-materias" class="metric-card metric-card--materias metric-card-link p-3 p-xl-4 h-100 d-block text-decoration-none" title="Ir a Gestión de Materias">
              <span class="d-block text-secondary small fw-semibold mb-2">Materias en catálogo</span>
              <h3 class="fw-bold text-dark mb-1"><?= number_format($mMaterias['total'], 0, ',', '.'); ?></h3>
              <span class="text-secondary small" style="font-size: 0.72rem;">
                <?= $mMaterias['codigos_carreras'] ? Vista::e(implode(' · ', $mMaterias['codigos_carreras'])) : 'Sin carreras cargadas'; ?>
              </span>
            </a>
          </div>

        </div>
      </section>

      <!-- ====================================================================
           2. GRÁFICOS DE TORTA: misma información que las tarjetas
           ==================================================================== -->
      <?php
        $graficos = [
            [
                'id'       => 'chartAlumnos',
                'titulo'   => 'Alumnos registrados',
                'subtitulo'=> 'Quiénes también publican tutorías',
                'etiquetas'=> ['Tutores', 'Solo alumnos'],
                'valores'  => [(int) $mAlumnos['tutores'], (int) $mAlumnos['solo_alumnos']],
                'colores'  => ['#bdecb6', '#A7C7E7'],
            ],
            [
                'id'       => 'chartTutores',
                'titulo'   => 'Tutores',
                'subtitulo'=> 'Cuentas habilitadas vs. suspendidas',
                'etiquetas'=> ['Activos', 'Suspendidos'],
                'valores'  => [(int) $mTutores['activos'], (int) $mTutores['inactivos']],
                'colores'  => ['#bdecb6', '#ff746c'],
            ],
            [
                'id'       => 'chartSalas',
                'titulo'   => 'Salas públicas',
                'subtitulo'=> 'Salas activas vs. suspendidas',
                'etiquetas'=> ['Activas', 'Suspendidas'],
                'valores'  => [(int) $mSalas['activas'], (int) $mSalas['suspendidas']],
                'colores'  => ['#bdecb6', '#ff746c'],
            ],
            [
                'id'       => 'chartMaterias',
                'titulo'   => 'Materias en catálogo',
                'subtitulo'=> 'Distribución por año del plan',
                'etiquetas'=> array_keys($mMaterias['por_anio']),
                'valores'  => array_values($mMaterias['por_anio']),
                'colores'  => ['#FFDF00', '#A7C7E7', '#bdecb6', '#8b5cf6', '#ec4899', '#06b6d4', '#94a3b8'],
            ],
        ];
      ?>
      <section class="mb-2" aria-label="Distribución general">
        <div class="d-flex align-items-center gap-2 mb-3">
          <i class="bi bi-pie-chart-fill text-primary fs-5"></i>
          <h2 class="h5 fw-bold text-dark mb-0">Distribución general</h2>
        </div>
        <div class="row g-3 g-xl-4">
          <?php foreach ($graficos as $g): ?>
            <div class="col-sm-6 col-xl-3">
              <div class="card border rounded-4 shadow-sm p-3 p-xl-4 h-100 chart-card">
                <h3 class="h6 fw-bold text-dark mb-0"><?= Vista::e($g['titulo']); ?></h3>
                <span class="text-secondary small mb-3"><?= Vista::e($g['subtitulo']); ?></span>
                <?php if (array_sum($g['valores']) > 0): ?>
                  <div class="chart-wrap flex-grow-1">
                    <canvas id="<?= Vista::e($g['id']); ?>" role="img" aria-label="Gráfico de torta: <?= Vista::e($g['titulo']); ?>"></canvas>
                  </div>
                <?php else: ?>
                  <div class="chart-wrap chart-empty flex-grow-1 text-secondary small">
                    <i class="bi bi-inbox fs-3 mb-1"></i>
                    Todavía no hay datos
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

    </main>
  </div>

  <!-- ==========================================================================
       MODAL: NUEVA MATERIA
       ========================================================================== -->
  <div class="modal fade" id="modalNuevaMateria" tabindex="-1" aria-labelledby="modalNuevaMateriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-warning-subtle text-warning p-2 rounded-3">
              <i class="bi bi-journal-plus fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark" id="modalNuevaMateriaLabel">Agregar Nueva Materia</h5>
              <p class="text-secondary small mb-0">Sumá una materia al catálogo oficial de StudyMatch</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <form id="formNuevaMateria">
          <div class="modal-body p-4">
            <div class="mb-3">
              <label for="nombreMateriaInput" class="form-label small fw-semibold text-secondary">Nombre de la Materia</label>
              <input type="text" class="form-control rounded-3" id="nombreMateriaInput" placeholder="Ej. Paradigmas de Programación" required>
            </div>
            <div class="mb-3">
              <label for="carreraMateriaSelect" class="form-label small fw-semibold text-secondary">Carrera Destino</label>
              <select class="form-select rounded-3" id="carreraMateriaSelect" required>
                <option value="TUP">Tecnicatura Universitaria en Programación (TUP)</option>
                <option value="Sistemas">Ingeniería en Sistemas de Información</option>
                <option value="Electrónica">Ingeniería Electrónica</option>
                <option value="Ferroviaria">Ingeniería Ferroviaria</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="añoMateriaSelect" class="form-label small fw-semibold text-secondary">Nivel / Año</label>
              <select class="form-select rounded-3" id="añoMateriaSelect" required>
                <option value="1">1° Año</option>
                <option value="2">2° Año</option>
                <option value="3">3° Año</option>
                <option value="4">4° Año</option>
                <option value="5">5° Año</option>
              </select>
            </div>
          </div>
          <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Guardar Materia</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- TOAST DE NOTIFICACIÓN -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="toastAdmin" class="toast align-items-center text-bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill text-success fs-5"></i>
          <span id="toastAdminMessage">Acción completada con éxito.</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       JAVASCRIPT: COMPORTAMIENTOS DEL PANEL DE ADMINISTRACIÓN
       ========================================================================== -->
  <!-- Bootstrap 5.3.3 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Chart.js 4.4.3 (gráficos de torta) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

  <script>
    // ==========================================
    // 1. Modo Claro / Modo Oscuro
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
      actualizarColoresGraficos();
    }

    function toggleTemaModo() {
      const temaActual = document.documentElement.getAttribute('data-bs-theme') || 'light';
      const nuevoTema = (temaActual === 'dark') ? 'light' : 'dark';
      aplicarTema(nuevoTema);
      mostrarToast(`Modo ${nuevoTema === 'dark' ? 'oscuro' : 'claro'} activado`);
    }

    // ==========================================
    // 2. Colapso / Expansión del Sidebar (Con memoria en localStorage)
    // ==========================================
    const sidebar = document.getElementById('mainSidebar');
    const btnToggleSidebar = document.getElementById('btnToggleSidebar');

    function toggleSidebar() {
      sidebar.classList.toggle('collapsed');
      const isCollapsed = sidebar.classList.contains('collapsed');
      document.body.classList.toggle('sidebar-is-collapsed', isCollapsed);
      localStorage.setItem('study-match-admin-sidebar-collapsed', isCollapsed ? 'true' : 'false');
    }

    if (btnToggleSidebar) btnToggleSidebar.addEventListener('click', toggleSidebar);

    if (localStorage.getItem('study-match-admin-sidebar-collapsed') === 'true') {
      sidebar.classList.add('collapsed');
      document.body.classList.add('sidebar-is-collapsed');
    }

    // ==========================================
    // 3. Notificaciones Toast Rápidas
    // ==========================================
    function mostrarToast(mensaje) {
      const toastEl = document.getElementById('toastAdmin');
      const toastMsg = document.getElementById('toastAdminMessage');
      if (toastEl && toastMsg) {
        toastMsg.textContent = mensaje;
        const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3500 });
        toast.show();
      }
    }

    // ==========================================
    // 4. Gráficos de torta (datos reales calculados en PHP)
    // ==========================================
    const GRAFICOS = <?= json_encode($graficos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const graficosCreados = [];

    function esModoOscuro() {
      return document.documentElement.getAttribute('data-bs-theme') === 'dark';
    }

    // Leyendas: negro puro en modo claro
    function colorTextoGrafico() {
      return esModoOscuro() ? '#cbd5e1' : '#000000';
    }

    // Oscurece un color #rrggbb (factor 0-1: menor = más oscuro)
    function oscurecer(hex, factor) {
      const n = parseInt(hex.slice(1), 16);
      const c = function (v) { return Math.round(v * factor); };
      return 'rgb(' + c(n >> 16) + ', ' + c((n >> 8) & 255) + ', ' + c(n & 255) + ')';
    }

    // Bordes: en modo claro, el contorno de cada porción en una versión más oscura de su
    // color (se despega del fondo blanco); en oscuro, el color de la tarjeta separa las porciones.
    function bordesGrafico(colores) {
      return esModoOscuro() ? '#1e293b' : colores.map(function (c) { return oscurecer(c, 0.6); });
    }

    function actualizarColoresGraficos() {
      graficosCreados.forEach(function (chart) {
        chart.options.plugins.legend.labels.color = colorTextoGrafico();
        chart.data.datasets[0].borderColor = bordesGrafico(chart.$colores);
        chart.update('none');
      });
    }

    function crearGraficos() {
      if (typeof Chart === 'undefined') return;   // CDN no disponible: quedan las tarjetas
      GRAFICOS.forEach(function (g) {
        const canvas = document.getElementById(g.id);
        if (!canvas) return;                      // sin datos: se muestra el aviso vacío
        const total = g.valores.reduce(function (a, b) { return a + b; }, 0);
        const colores = g.colores.slice(0, g.valores.length);
        const chart = new Chart(canvas, {
          type: 'pie',
          data: {
            labels: g.etiquetas,
            datasets: [{
              data: g.valores,
              backgroundColor: colores,
              borderColor: bordesGrafico(colores),
              borderWidth: 2,
              hoverOffset: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: { color: colorTextoGrafico(), usePointStyle: true, pointStyle: 'circle', boxWidth: 8, padding: 14, font: { family: 'Inter', size: 12 } }
              },
              tooltip: {
                callbacks: {
                  label: function (ctx) {
                    const pct = total ? Math.round(ctx.parsed * 100 / total) : 0;
                    return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                  }
                }
              }
            }
          }
        });
        chart.$colores = colores;   // para recalcular los bordes al cambiar de tema
        graficosCreados.push(chart);
      });
    }

    // ==========================================
    // 5. Formulario de Nueva Materia
    // ==========================================
    const formMateria = document.getElementById('formNuevaMateria');
    if (formMateria) {
      formMateria.addEventListener('submit', function(e) {
        e.preventDefault();
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaMateria'));
        if (modal) modal.hide();
        mostrarToast("Materia agregada exitosamente al catálogo.");
        this.reset();
      });
    }

    // Inicialización al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
      const temaGuardado = localStorage.getItem('study-match-theme') || 'light';
      aplicarTema(temaGuardado);
      crearGraficos();
    });
  </script>
</body>
</html>