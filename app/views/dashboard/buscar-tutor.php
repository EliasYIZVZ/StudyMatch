<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Study Match - Buscar Tutor</title>
  <script>
    (function() {
      var theme = localStorage.getItem('study_match_theme');
      document.documentElement.setAttribute('data-bs-theme', theme === 'light' ? 'light' : 'dark');
    })();
  </script>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Estilos del Dashboard y Perfil -->
  <link rel="stylesheet" href="public/css/dashboard.css?v=20261103_page_title">
</head>
<body class="dashboard-body" data-role="alumno">

  <!-- ==========================================================================
       SIDEBAR LATERAL IZQUIERDA (MODO ALUMNO - 100% FIJO)
       ========================================================================== -->
  <?php $sidebarActivo = 'buscar'; require APP_PATH . '/views/partials/sidebar-alumno.php'; ?>

  <!-- ==========================================================================
       CONTENIDO PRINCIPAL: BÚSQUEDA HÍBRIDA DE TUTORES
       ========================================================================== -->
  <div class="dashboard-main-wrapper">

    <!-- TOPBAR -->
    <header class="topbar-header px-4 py-3 sticky-top">
      <div class="d-flex align-items-center justify-content-between gap-3">
        
        <!-- Toggle de Sidebar -->
        <div class="d-flex align-items-center gap-2">
          <button id="btnToggleSidebar" class="btn btn-light border text-secondary p-2 rounded-3" title="Expandir/Contraer menú lateral">
            <i class="bi bi-layout-sidebar fs-5"></i>
          </button>
        </div>

        <!-- Switch Rol Dual + Perfil -->
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-outline-brand btn-sm theme-toggle-btn d-inline-flex align-items-center justify-content-center rounded-pill" type="button" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
            <i class="theme-icon bi bi-sun-fill text-warning"></i>
          </button>

          <div class="role-switch-container d-flex align-items-center gap-1 shadow-sm">
            <span id="label-alumno" class="role-badge-btn" title="Actualmente en Modo Alumno">
              <span>Alumno</span>
            </span>
            <div class="form-check form-switch m-0 p-0 d-flex align-items-center mx-1">
              <input class="form-check-input ms-0" type="checkbox" id="switchRol" onchange="irAModoTutor()" title="Cambiar a Modo Tutor">
            </div>
            <span id="label-tutor" class="role-badge-btn text-secondary" onclick="irAModoTutor()" title="Pasar a Modo Tutor">
              <span>Tutor</span>
            </span>
          </div>

          <?php require APP_PATH . '/views/partials/perfil-alumno-menu.php'; ?>
        </div>
      </div>
    </header>

    <!-- CUERPO DE LA VISTA: BÚSQUEDA HÍBRIDA -->
    <main class="p-3 p-md-4 flex-grow-1 view-fade">
      <?php require APP_PATH . '/views/partials/flash.php'; ?>
      <!-- Encabezado de la página -->
      <div class="d-flex justify-content-between align-items-end mb-3 flex-wrap gap-2">
        <div>
          <h1 class="page-title fw-bold text-dark mb-1">Buscar Tutor</h1>
          <p class="text-secondary small mb-0">Filtrá por materia, modalidad o disponibilidad para coordinar tu próxima tutoría.</p>
        </div>
      </div>
      <?php
      $buscador = [
          'grid' => 'gridResultadosTutores',
          'pagina' => 0,
          'placeholder' => 'Tutor, materia, carrera o modalidad…',
          'materias' => $materias,
          'total' => count($tutores),
          'unidad' => 'tutores',
      ];
      require APP_PATH . '/views/partials/buscador-compacto.php';
      ?>

      <!-- ======================================================================
           RESULTADOS: una card por tutor (usuario con tutorías activas)
           (DashboardController::buscarTutor -> OfertaTutoria::listarParaAlumno)
           ====================================================================== -->
      <?php if ($errorOfertas): ?>
        <div class="tutor-result-card p-4 p-md-5 text-center">
          <i class="bi bi-exclamation-triangle fs-1 text-warning d-block mb-2"></i>
          <p class="text-secondary mb-0">No se pudieron cargar los tutores. Intentá nuevamente más tarde.</p>
        </div>
      <?php elseif (empty($tutores)): ?>
        <div class="tutor-result-card p-4 p-md-5 text-center">
          <i class="bi bi-person-video3 fs-1 text-secondary d-block mb-2"></i>
          <p class="text-secondary mb-0">Todavía no hay tutores con tutorías publicadas.</p>
        </div>
      <?php else: ?>
        <div class="row g-4" id="gridResultadosTutores">
          <?php foreach ($tutores as $posicion => $tutor): ?>
            <?php require APP_PATH . '/views/partials/tutor-perfil-card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Estado Vacío (Empty State cuando ningún filtro coincide) -->
      <div id="buscadorSinResultados" class="text-center py-5 d-none">
        <div class="avatar-circle mx-auto mb-3 bg-light text-secondary fs-3" style="width: 64px; height: 64px;">
          <i class="bi bi-search"></i>
        </div>
        <h5 class="fw-bold text-dark">No se encontraron tutores con esos criterios</h5>
        <p class="text-secondary small mb-3">Probá cambiando el término de búsqueda o seleccionando "Todas las materias".</p>
        <button type="button" class="btn btn-outline-primary rounded-pill px-4 btn-sm" id="buscadorRestablecer">
          Restablecer Filtros
        </button>
      </div>

    </main>
  </div>

  <?php require APP_PATH . '/views/partials/perfil-alumno-modales.php'; ?>

  <!-- Bootstrap 5.3 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="public/js/perfil.js"></script>
  <script src="public/js/sidebar.js"></script>
  <script src="public/js/main.js"></script>
  <script src="public/js/buscador-cards.js?v=20261103"></script>

  <script>
    function irAModoTutor() {
      window.location.href = 'index.php?r=dashboard-tutor';
    }
  </script>
</body>
</html>
