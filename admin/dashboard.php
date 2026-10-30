<?php
/**
 * ==========================================================================
 * STUDY MATCH • Panel de Administración (admin/dashboard.php)
 * Plataforma de Apoyo Académico Colaborativo - UTN FRH
 * ==========================================================================
 */
session_start();

// Simulación o lectura de sesión de administrador
$adminNombre = $_SESSION['nombre_usuario'] ?? 'Administrador UTN';
$adminEmail  = $_SESSION['correo_usuario'] ?? 'admin@frh.utn.edu.ar';
$adminRol    = 'Superadmin';

/* ==========================================================================
 * GUÍA DE INTEGRACIÓN BACKEND (PHP PDO + MySQL):
 * ==========================================================================
 * Tu compañero de backend podrá conectar esta vista ejecutando:
 * 
 * require_once __DIR__ . '/../app/config/Database.php';
 * $db = Database::getInstance()->getConnection();
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
?>
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
  <link rel="stylesheet" href="../css/dashboard.css?v=20261029_admin_clean">

  <!-- Estilos globales de Administración (Fondo img_admin.png desenfocado, sidebar claro gris azulado / oscuro grafito) -->
  <link rel="stylesheet" href="../css/admin-global.css?v=20261029_v3">

  <!-- Estilos específicos del Dashboard -->
  <link rel="stylesheet" href="../css/admin-dashboard.css?v=20261029_v3">
</head>
<body class="dashboard-body" data-role="admin">

  <!-- ==========================================================================
       SIDEBAR LATERAL IZQUIERDA (MODO CLARO: GRIS AZULADO | MODO OSCURO: GRAFITO)
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
        <button id="btnCollapseSidebar" class="btn btn-sm btn-light border p-1 rounded-2 sidebar-hide-collapsed" title="Contraer menú lateral">
          <i class="bi bi-chevron-left"></i>
        </button>
      </div>

      <!-- Menú de Navegación: GESTIÓN Y ADMINISTRACIÓN -->
      <nav class="d-flex flex-column gap-1">
        <div class="px-3 pb-1 sidebar-section-title sidebar-text">
          Panel de Control
        </div>

        <!-- 1. Inicio / Resumen (Métricas) - ACTIVO -->
        <a href="dashboard.php" class="sidebar-link active" title="Inicio / Resumen">
          <i class="bi bi-speedometer2 fs-5"></i>
          <span class="sidebar-text">Inicio / Resumen</span>
        </a>

        <!-- 2. Gestión de Usuarios -->
        <a href="gestion-usuarios.php" class="sidebar-link" title="Gestión de Usuarios">
          <i class="bi bi-people fs-5"></i>
          <span class="sidebar-text">Gestión de Usuarios</span>
          <span class="badge bg-primary-subtle text-primary ms-auto sidebar-text" style="font-size: 0.68rem;"><?= number_format($totalAlumnos); ?></span>
        </a>

        <!-- 3. Gestión de Materias -->
        <a href="gestion-materias.php" class="sidebar-link" title="Gestión de Materias">
          <i class="bi bi-journal-bookmark fs-5"></i>
          <span class="sidebar-text">Gestión de Materias</span>
          <span class="badge bg-secondary-subtle text-light ms-auto sidebar-text" style="font-size: 0.68rem;"><?= $totalMaterias; ?></span>
        </a>

        <!-- 4. Salas Públicas -->
        <a href="salas.php" class="sidebar-link" title="Salas Públicas">
          <i class="bi bi-broadcast fs-5 text-danger"></i>
          <span class="sidebar-text">Salas Públicas</span>
          <span class="badge bg-danger text-white rounded-pill ms-auto sidebar-text" style="font-size: 0.65rem;"><?= $totalSalas; ?> en vivo</span>
        </a>

        <!-- 5. Reportes -->
        <a href="reportes.php" class="sidebar-link" title="Reportes del Sistema">
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
        
        <!-- Toggle de Sidebar + Título / Buscador -->
        <div class="d-flex align-items-center gap-3 flex-grow-1" style="max-width: 650px;">
          <button id="btnToggleSidebar" class="btn btn-light border text-secondary p-2 rounded-3" title="Expandir/Contraer menú lateral">
            <i class="bi bi-layout-sidebar fs-5"></i>
          </button>

          <!-- Título y Breadcrumb -->
          <div class="d-none d-sm-block me-2">
            <h1 class="h6 fw-bold mb-0 text-dark">Panel de Administración</h1>
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

      <!-- BANNER DE BIENVENIDA LIMPIO -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white" 
           style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 70%, #1e3a8a 100%);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div>
            <span class="badge bg-primary text-white rounded-pill px-3 py-1 small fw-semibold mb-2 d-inline-block">
              <i class="bi bi-shield-check me-1"></i> Control General
            </span>
            <h1 class="h3 fw-bold mb-1 text-white">
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
           1. LAS 4 TARJETAS DE RESUMEN HORIZONTAL (KPIS ENLAZABLES)
           ==================================================================== -->
      <section class="mb-4">
        <div class="row g-3 g-xl-4">
          
          <!-- KPI 1: Cantidad de Alumnos Activos -->
          <div class="col-sm-6 col-xl-3">
            <a href="gestion-usuarios.php?filtro=alumnos" class="card metric-card metric-card-link p-3 p-xl-4 h-100 border rounded-4 shadow-sm" title="Ir a Gestión de Usuarios (Alumnos)">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-primary-subtle text-primary p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                  <i class="bi bi-people-fill fs-5"></i>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <span class="badge bg-primary-subtle text-primary small" style="font-size: 0.72rem;">+12% mes</span>
                  <i class="bi bi-arrow-up-right metric-shortcut-icon text-muted fs-6"></i>
                </div>
              </div>
              <h2 class="h3 fw-bold text-dark mb-1"><?= number_format($totalAlumnos); ?></h2>
              <span class="text-secondary small fw-semibold d-block mb-2">Alumnos Registrados</span>
              <div class="pt-2 border-top d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                <span>Gestión de Usuarios</span>
                <span class="text-primary fw-semibold">Ver todos →</span>
              </div>
            </a>
          </div>

          <!-- KPI 2: Cantidad de Tutores Activos -->
          <div class="col-sm-6 col-xl-3">
            <a href="gestion-usuarios.php?filtro=tutores" class="card metric-card metric-card-link p-3 p-xl-4 h-100 border rounded-4 shadow-sm" title="Ir a Gestión de Usuarios (Tutores)">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-success-subtle text-success p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                  <i class="bi bi-patch-check-fill fs-5"></i>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <span class="badge bg-success-subtle text-success small" style="font-size: 0.72rem;">Habilitados</span>
                  <i class="bi bi-arrow-up-right metric-shortcut-icon text-muted fs-6"></i>
                </div>
              </div>
              <h2 class="h3 fw-bold text-dark mb-1"><?= $totalTutores; ?></h2>
              <span class="text-secondary small fw-semibold d-block mb-2">Tutores Activos</span>
              <div class="pt-2 border-top d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                <span><?= $tutoresPendientes; ?> por verificar</span>
                <span class="text-success fw-semibold">Revisar →</span>
              </div>
            </a>
          </div>

          <!-- KPI 3: Salas Públicas Activas -->
          <div class="col-sm-6 col-xl-3">
            <a href="salas.php" class="card metric-card metric-card-link p-3 p-xl-4 h-100 border rounded-4 shadow-sm" title="Ir a Salas Públicas">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-danger-subtle text-danger p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                  <i class="bi bi-broadcast fs-5"></i>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                  <span class="live-indicator-dot me-1"></span>
                  <span class="badge bg-danger text-white small" style="font-size: 0.72rem;">En Vivo</span>
                  <i class="bi bi-arrow-up-right metric-shortcut-icon text-muted fs-6 ms-1"></i>
                </div>
              </div>
              <h2 class="h3 fw-bold text-dark mb-1"><?= $totalSalas; ?></h2>
              <span class="text-secondary small fw-semibold d-block mb-2">Salas Públicas Activas</span>
              <div class="pt-2 border-top d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                <span>Discord UTN</span>
                <span class="text-danger fw-semibold">Monitorear →</span>
              </div>
            </a>
          </div>

          <!-- KPI 4: Materias Cargadas -->
          <div class="col-sm-6 col-xl-3">
            <a href="gestion-materias.php" class="card metric-card metric-card-link p-3 p-xl-4 h-100 border rounded-4 shadow-sm" title="Ir a Gestión de Materias">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-warning-subtle text-warning p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                  <i class="bi bi-journal-bookmark-fill fs-5"></i>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <span class="badge bg-warning-subtle text-dark small" style="font-size: 0.72rem;">2 Carreras</span>
                  <i class="bi bi-arrow-up-right metric-shortcut-icon text-muted fs-6"></i>
                </div>
              </div>
              <h2 class="h3 fw-bold text-dark mb-1"><?= $totalMaterias; ?></h2>
              <span class="text-secondary small fw-semibold d-block mb-2">Materias en Catálogo</span>
              <div class="pt-2 border-top d-flex align-items-center justify-content-between text-muted" style="font-size: 0.74rem;">
                <span>TUP e Ing. Sistemas</span>
                <span class="text-primary fw-semibold">Administrar →</span>
              </div>
            </a>
          </div>

        </div>
      </section>

      <!-- ====================================================================
           2. SECCIÓN INFERIOR: TUTORES A VERIFICAR + INDICADOR DE REPORTES
           ==================================================================== -->
      <div class="row g-4">
        
        <!-- COLUMNA PRINCIPAL (8 COLS): TARJETA DE TUTORES A VERIFICAR -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-person-check-fill text-success me-1"></i> Tutores a Verificar
                  </h5>
                  <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold" id="badgePostulacionesCount">
                    <?= $tutoresPendientes; ?> pendientes
                  </span>
                </div>
                <p class="text-secondary small mb-0">
                  Estudiantes postulados para dictar tutorías que requieren validación de materias aprobadas.
                </p>
              </div>

              <a href="gestion-usuarios.php?filtro=tutores" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                Ver todos
              </a>
            </div>

            <!-- Lista de postulantes a tutor (3 ejemplos representativos reducidos) -->
            <div class="d-flex flex-column gap-3" id="listaTutoresVerificar">
              
              <!-- Postulante 1 -->
              <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between flex-wrap gap-3 tutor-verify-item">
                <div class="d-flex align-items-center gap-3">
                  <div class="avatar-circle bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                    EM
                  </div>
                  <div>
                    <div class="d-flex align-items-center gap-2">
                      <h6 class="fw-bold text-dark mb-0">Esteban Morales</h6>
                      <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">Legajo: 52140</span>
                    </div>
                    <span class="text-secondary small d-block">
                      Postula para: <strong class="text-dark">Análisis Matemático II</strong> • Sistemas (3° año)
                    </span>
                    <span class="text-muted" style="font-size: 0.72rem;">
                      <i class="bi bi-paperclip me-1"></i>Analítico adjunto: <code>analitico_morales_2026.pdf</code>
                    </span>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                  <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="rechazarTutor(this, 'Esteban Morales')">
                    Rechazar
                  </button>
                  <button class="btn btn-sm btn-success rounded-pill px-3 fw-semibold shadow-sm" onclick="aprobarTutor(this, 'Esteban Morales')">
                    <i class="bi bi-check-lg me-1"></i> Aprobar Tutor
                  </button>
                </div>
              </div>

              <!-- Postulante 2 -->
              <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between flex-wrap gap-3 tutor-verify-item">
                <div class="d-flex align-items-center gap-3">
                  <div class="avatar-circle bg-success-subtle text-success fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                    CL
                  </div>
                  <div>
                    <div class="d-flex align-items-center gap-2">
                      <h6 class="fw-bold text-dark mb-0">Camila López</h6>
                      <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">Legajo: 49301</span>
                    </div>
                    <span class="text-secondary small d-block">
                      Postula para: <strong class="text-dark">Programación I (C++)</strong> • TUP (2° año)
                    </span>
                    <span class="text-muted" style="font-size: 0.72rem;">
                      <i class="bi bi-paperclip me-1"></i>Analítico adjunto: <code>certificado_cursada_tup.pdf</code>
                    </span>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                  <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="rechazarTutor(this, 'Camila López')">
                    Rechazar
                  </button>
                  <button class="btn btn-sm btn-success rounded-pill px-3 fw-semibold shadow-sm" onclick="aprobarTutor(this, 'Camila López')">
                    <i class="bi bi-check-lg me-1"></i> Aprobar Tutor
                  </button>
                </div>
              </div>

              <!-- Postulante 3 -->
              <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between flex-wrap gap-3 tutor-verify-item">
                <div class="d-flex align-items-center gap-3">
                  <div class="avatar-circle bg-warning-subtle text-warning-emphasis fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                    FR
                  </div>
                  <div>
                    <div class="d-flex align-items-center gap-2">
                      <h6 class="fw-bold text-dark mb-0">Franco Rossi</h6>
                      <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">Legajo: 50882</span>
                    </div>
                    <span class="text-secondary small d-block">
                      Postula para: <strong class="text-dark">Bases de Datos</strong> • Sistemas (4° año)
                    </span>
                    <span class="text-muted" style="font-size: 0.72rem;">
                      <i class="bi bi-paperclip me-1"></i>Analítico adjunto: <code>acta_examen_bd.pdf</code>
                    </span>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                  <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="rechazarTutor(this, 'Franco Rossi')">
                    Rechazar
                  </button>
                  <button class="btn btn-sm btn-success rounded-pill px-3 fw-semibold shadow-sm" onclick="aprobarTutor(this, 'Franco Rossi')">
                    <i class="bi bi-check-lg me-1"></i> Aprobar Tutor
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- COLUMNA LATERAL (4 COLS): TARJETA DE REPORTES (SOLO CANTIDAD) -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">
                  Moderación del Sistema
                </span>
                <div class="bg-warning-subtle text-warning p-2 rounded-3">
                  <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                </div>
              </div>

              <!-- Indicador de cantidad de reportes -->
              <h5 class="fw-bold text-dark mb-1">Reportes Pendientes</h5>
              <p class="text-secondary small mb-3">
                Casos reportados por estudiantes o tutores en salas y publicaciones.
              </p>

              <!-- Número destacado de Reportes -->
              <div class="d-flex align-items-baseline gap-2 my-4">
                <span class="display-4 fw-bold text-dark lh-1" id="contadorReportesNumero"><?= $totalReportes; ?></span>
                <span class="text-danger fw-semibold small">
                  <i class="bi bi-arrow-up-circle-fill me-1"></i>Requieren atención
                </span>
              </div>

              <div class="p-3 rounded-3 bg-light border mb-3">
                <div class="d-flex align-items-center justify-content-between text-secondary small">
                  <span>Estado general:</span>
                  <span class="badge bg-warning text-dark rounded-pill fw-bold">Pendientes de revisión</span>
                </div>
              </div>
            </div>

            <!-- Botón de acceso directo hacia Reportes -->
            <div>
              <a href="reportes.php" class="btn btn-outline-danger w-100 rounded-pill py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-shield-exclamation"></i>
                <span>Gestionar <?= $totalReportes; ?> Reportes</span>
              </a>
            </div>
          </div>
        </div>

      </div>

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
    // 4. Verificación de Tutores (Aprobar / Rechazar)
    // ==========================================
    let pendientesCount = <?= $tutoresPendientes; ?>;

    function actualizarContadorPendientes() {
      const badge = document.getElementById('badgePostulacionesCount');
      if (badge) {
        badge.textContent = `${pendientesCount} pendientes`;
      }
      if (pendientesCount <= 0) {
        const lista = document.getElementById('listaTutoresVerificar');
        if (lista) {
          lista.innerHTML = `
            <div class="text-center py-4 text-muted">
              <i class="bi bi-check2-all text-success fs-2 d-block mb-2"></i>
              <span class="small">No hay postulaciones de tutores pendientes por verificar.</span>
            </div>
          `;
        }
      }
    }

    function aprobarTutor(btn, nombre) {
      const item = btn.closest('.tutor-verify-item');
      if (item) {
        item.style.transition = 'all 0.3s ease';
        item.style.opacity = '0';
        item.style.transform = 'scale(0.96)';
        setTimeout(() => {
          item.remove();
          pendientesCount--;
          actualizarContadorPendientes();
          mostrarToast(`¡Tutor ${nombre} aprobado exitosamente!`);
        }, 300);
      }
    }

    function rechazarTutor(btn, nombre) {
      const item = btn.closest('.tutor-verify-item');
      if (item) {
        item.style.transition = 'all 0.3s ease';
        item.style.opacity = '0';
        item.style.transform = 'scale(0.96)';
        setTimeout(() => {
          item.remove();
          pendientesCount--;
          actualizarContadorPendientes();
          mostrarToast(`Postulación de ${nombre} desestimada.`);
        }, 300);
      }
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
    });
  </script>
</body>
</html>