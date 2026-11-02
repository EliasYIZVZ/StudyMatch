<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Study Match - Panel del Tutor</title>
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
  <!-- Estilos del Dashboard y Perfil (100% Independiente de Landing) -->
  <link rel="stylesheet" href="public/css/dashboard.css?v=20261101_tutor_bg_ia7">
</head>
<body class="dashboard-body" data-role="tutor">
<?php $cantPendientes = (int) $resumenSolicitudes['pendientes']; ?>

  <!-- ==========================================================================
       SIDEBAR LATERAL IZQUIERDA (MODO TUTOR - 100% FIJO)
       ========================================================================== -->
  <?php $sidebarActivo = 'panel'; require APP_PATH . '/views/partials/sidebar-tutor.php'; ?>

  <!-- ==========================================================================
       CONTENIDO PRINCIPAL: PANEL TUTOR
       ========================================================================== -->
  <div class="dashboard-main-wrapper">

    <!-- TOPBAR CON SWITCH DE ROL Y MENÚ DE PERFIL -->
    <header class="topbar-header px-4 py-3 sticky-top">
      <div class="d-flex align-items-center justify-content-between gap-3">
        
        <!-- Toggle de Sidebar -->
        <div class="d-flex align-items-center gap-2">
          <button id="btnToggleSidebar" class="btn btn-light border text-secondary p-2 rounded-3" title="Expandir/Contraer menú lateral">
            <i class="bi bi-layout-sidebar fs-5"></i>
          </button>
        </div>

        <!-- Switch Rol Dual: Alumno (Azul) / Tutor (Verde) + Perfil -->
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-outline-brand btn-sm theme-toggle-btn d-inline-flex align-items-center justify-content-center rounded-pill" type="button" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
            <i class="theme-icon bi bi-sun-fill text-warning"></i>
          </button>
          
          <!-- Switch Alumno / Tutor (Cambia a index.php?r=dashboard) -->
          <div class="role-switch-container d-flex align-items-center gap-1 shadow-sm">
            <span id="label-alumno" class="role-badge-btn text-secondary" onclick="irAModoAlumno()" title="Pasar a Modo Alumno">
              <span>Alumno</span>
            </span>
            <div class="form-check form-switch m-0 p-0 d-flex align-items-center mx-1">
              <input class="form-check-input ms-0" type="checkbox" id="switchRol" checked onchange="irAModoAlumno()" title="Cambiar a Modo Alumno">
            </div>
            <span id="label-tutor" class="role-badge-btn" title="Actualmente en Modo Tutor">
              <span>Tutor</span>
            </span>
          </div>

          <!-- MENÚ DE PERFIL DESPLEGABLE -->
          <div class="dropdown">
            <button class="btn p-1 border-0 d-flex align-items-center gap-2 rounded-pill hover-bg-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="avatar-user-display userAvatarSlot">
                AL
              </div>
              <div class="d-none d-md-block text-start lh-1 pe-1">
                <span class="d-block fw-bold text-dark small userNameSlot">Cargando...</span>
                <span class="text-secondary userRoleSlot" style="font-size: 0.72rem;">UTN • Tutor</span>
              </div>
              <i class="bi bi-chevron-down text-secondary small d-none d-md-inline"></i>
            </button>

            <!-- Contenido del Menú Desplegable -->
            <ul class="dropdown-menu profile-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" style="min-width: 270px;">
              <li class="px-3 py-2 border-bottom mb-1 bg-light rounded-3">
                <div class="fw-bold text-dark userNameSlot">Cargando...</div>
                <div class="text-secondary small userEmailSlot">Cargando...</div>
              </li>
              
              <!-- Opciones del menú -->
              <li>
                <a class="dropdown-item rounded-3 py-2 d-flex align-items-center gap-2" href="#" onclick="irAModoAlumno()">
                  <i class="bi bi-arrow-left-right text-primary fs-6"></i>
                  <span>Cambiar a Modo Alumno</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item rounded-3 py-2 d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#modalEditarPerfil">
                  <i class="bi bi-pencil-square text-primary fs-6"></i>
                  <span>Editar mi información</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item rounded-3 py-2 d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#modalSubirFoto">
                  <i class="bi bi-camera text-primary fs-6"></i>
                  <span>Cambiar foto de perfil</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item rounded-3 py-2 d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#modalDiscordConfig">
                  <i class="bi bi-discord fs-6" style="color: #2563eb;"></i>
                  <span>Vincular Discord UTN</span>
                </a>
              </li>
              <li><hr class="dropdown-divider my-1"></li>
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

    <!-- CUERPO PRINCIPAL DEL DASHBOARD TUTOR -->
    <main class="p-4 p-md-5 flex-grow-1 view-fade">
      <?php require APP_PATH . '/views/partials/flash.php'; ?>

      <!-- Encabezado Modo Tutor -->
      <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
        <div>
          <span class="mode-indicator-pill mb-2"><i class="bi bi-patch-check-fill"></i> Modo Tutor</span>
          <h1 class="h3 fw-bold text-dark mb-1">Panel del Tutor</h1>
          <p class="text-secondary small mb-0">Un resumen de tus publicaciones, solicitudes y tutorías.</p>
        </div>
        <a href="index.php?r=mis-publicaciones&amp;nueva=1" class="btn btn-role-action rounded-pill px-4 py-2 fw-semibold shadow-sm">
          <i class="bi bi-plus-lg me-1"></i> Publicar nueva tutoría
        </a>
      </div>

      <!-- Métricas del tutor (datos reales) -->
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
          <a href="index.php?r=mis-publicaciones" class="metric-card p-3 p-xl-4 h-100 d-block text-decoration-none">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-secondary small fw-semibold">Publicaciones activas</span>
              <div class="bg-success-subtle text-success p-2 rounded-3"><i class="bi bi-journal-check fs-5"></i></div>
            </div>
            <h3 class="fw-bold text-dark mb-1"><?= (int) $resumenOfertas['activas'] ?></h3>
            <span class="text-secondary small" style="font-size: 0.72rem;">de <?= (int) $resumenOfertas['total'] ?> publicada<?= (int) $resumenOfertas['total'] === 1 ? '' : 's' ?></span>
          </a>
        </div>

        <div class="col-sm-6 col-xl-3">
          <a href="index.php?r=mis-tutorias&amp;rol=tutor&amp;estado=pendientes" class="metric-card p-3 p-xl-4 h-100 d-block text-decoration-none">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-secondary small fw-semibold">Solicitudes pendientes</span>
              <div class="bg-danger-subtle text-danger p-2 rounded-3"><i class="bi bi-bell-fill fs-5"></i></div>
            </div>
            <h3 class="fw-bold text-dark mb-1" id="metricCounterSolicitudes"><?= $cantPendientes ?></h3>
            <span class="<?= $cantPendientes > 0 ? 'text-danger fw-semibold' : 'text-secondary' ?> small" style="font-size: 0.72rem;">
              <?= $cantPendientes > 0 ? 'Requieren tu respuesta' : 'Estás al día' ?>
            </span>
          </a>
        </div>

        <div class="col-sm-6 col-xl-3">
          <a href="index.php?r=mis-tutorias&amp;rol=tutor&amp;estado=en-curso" class="metric-card p-3 p-xl-4 h-100 d-block text-decoration-none">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-secondary small fw-semibold">Tutorías en curso</span>
              <div class="bg-primary-subtle text-primary p-2 rounded-3"><i class="bi bi-chat-dots fs-5"></i></div>
            </div>
            <h3 class="fw-bold text-dark mb-1"><?= (int) $resumenSolicitudes['en_curso'] ?></h3>
            <span class="text-secondary small" style="font-size: 0.72rem;">Coordinalas por chat</span>
          </a>
        </div>

        <div class="col-sm-6 col-xl-3">
          <a href="index.php?r=mis-tutorias&amp;rol=tutor&amp;estado=historial" class="metric-card p-3 p-xl-4 h-100 d-block text-decoration-none">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-secondary small fw-semibold">Tutorías finalizadas</span>
              <div class="bg-warning-subtle text-warning p-2 rounded-3"><i class="bi bi-trophy fs-5"></i></div>
            </div>
            <h3 class="fw-bold text-dark mb-1"><?= (int) $resumenSolicitudes['finalizadas'] ?></h3>
            <span class="text-secondary small" style="font-size: 0.72rem;"><?= (int) $resumenSolicitudes['alumnos'] ?> alumno<?= (int) $resumenSolicitudes['alumnos'] === 1 ? '' : 's' ?> distinto<?= (int) $resumenSolicitudes['alumnos'] === 1 ? '' : 's' ?></span>
          </a>
        </div>
      </div>

      <!-- Solicitudes más antiguas sin responder -->
      <div id="solicitudes" class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
          <div>
            <h5 class="fw-bold text-dark mb-1">
              <i class="bi bi-inbox-fill text-success me-2"></i>Solicitudes por responder
            </h5>
            <p class="text-secondary small mb-0">Las más antiguas primero. Al aceptar se abre el chat con el alumno.</p>
          </div>
          <?php if ($cantPendientes > 0): ?>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill" id="solicitudesHeaderBadge">
              <?= $cantPendientes ?> pendiente<?= $cantPendientes === 1 ? '' : 's' ?>
            </span>
          <?php else: ?>
            <span class="badge bg-secondary-subtle text-secondary border px-3 py-1.5 rounded-pill" id="solicitudesHeaderBadge">
              Sin solicitudes pendientes
            </span>
          <?php endif; ?>
        </div>

        <div class="d-flex flex-column gap-3" id="listaSolicitudesTutor">
          <?php require APP_PATH . '/views/partials/solicitudes-tutor.php'; ?>
        </div>

        <?php if ($cantPendientes > count($solicitudesPendientes)): ?>
          <div class="text-center mt-3">
            <a href="index.php?r=mis-tutorias&amp;rol=tutor&amp;estado=pendientes" class="btn btn-sm btn-outline-success rounded-pill px-4">
              Ver todas las solicitudes (<?= $cantPendientes ?>)
            </a>
          </div>
        <?php endif; ?>
      </div>

      <?php if ((int) $resumenOfertas['activas'] === 0): ?>
        <!-- Invitación a publicar cuando no hay ofertas activas -->
        <div class="text-center py-5 bg-white rounded-4 border border-dashed">
          <i class="bi bi-mortarboard text-success fs-1 d-block mb-2"></i>
          <h5 class="fw-bold">
            <?= (int) $resumenOfertas['total'] === 0 ? '¿Querés empezar a dar tutorías?' : 'No tenés publicaciones activas' ?>
          </h5>
          <p class="text-secondary small mb-3">Publicá las materias que aprobaste y ayudá a tus compañeros de la TUP.</p>
          <a href="index.php?r=mis-publicaciones&amp;nueva=1" class="btn btn-role-action rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Publicar una tutoría
          </a>
        </div>
      <?php endif; ?>

    </main>
  </div>

  <?php $perfilRol = 'tutor'; require APP_PATH . '/views/partials/perfil-alumno-modales.php'; ?>

  <!-- OVERLAY DE TRANSICIÓN SUAVE ENTRE ROLES -->
  <div id="roleTransitionOverlay" class="role-transition-overlay">
    <div class="role-transition-card text-center p-4">
      <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle bg-primary-subtle text-primary mb-3" style="width: 72px; height: 72px;">
        <i class="bi bi-book fs-1"></i>
      </div>
      <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
        <span class="spinner-grow spinner-grow-sm text-primary" role="status" aria-hidden="true"></span>
        <h5 class="fw-bold text-dark mb-0">Cambiando a Panel de Alumno...</h5>
      </div>
      <p class="text-secondary small mb-0">Cargando tutores disponibles y salas de estudio</p>
    </div>
  </div>

  <!-- JavaScript: Funciones de Panel Tutor -->
  <script>
    // 1. Colapso / Expansión del Sidebar: ver public/js/sidebar.js

    // ==========================================
    // 2. Transición Visual Suave a Modo Alumno
    // ==========================================
    function irAModoAlumno() {
      const overlay = document.getElementById('roleTransitionOverlay');
      if (overlay) overlay.classList.add('active');
      document.body.classList.add('page-fading-out');

      setTimeout(() => {
        window.location.href = 'index.php?r=dashboard';
      }, 340);
    }

    // 3-5. Perfil, foto y toast: ver public/js/perfil.js

    // 6. Solicitudes: se aceptan / rechazan en el servidor (SolicitudController::responder)

    // 7. Publicar tutorías: ver Mis publicaciones (OfertaController)
  </script>

  <!-- Bootstrap 5.3 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="public/js/perfil.js"></script>
  <script src="public/js/sidebar.js"></script>
  <script src="public/js/main.js"></script>
</body>
</html>
