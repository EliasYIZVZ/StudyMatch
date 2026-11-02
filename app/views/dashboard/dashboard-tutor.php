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
                <div class="d-flex align-items-center gap-1 mt-1">
                  <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;" id="userLegajoSlot">Tutor Autorizado • FRBA</span>
                </div>
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

  <!-- ==========================================================================
       MODAL 1: EDITAR INFORMACIÓN DE PERFIL
       ========================================================================== -->
  <div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-success-subtle text-success p-2 rounded-3">
              <i class="bi bi-person-lines-fill fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark" id="modalEditarPerfilLabel">Editar Perfil del Tutor</h5>
              <p class="text-secondary small mb-0">Actualizá tus datos académicos y canales de contacto</p>
            </div>
          </div>
          <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <form id="formPerfil">
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="editNombre" class="form-label small fw-semibold text-dark mb-2">Nombre y Apellido</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-person"></i></span>
                  <input type="text" class="form-control border-start-0 ps-0" id="editNombre" value="" required>
                </div>
              </div>

              <div class="col-md-6">
                <label for="editEmail" class="form-label small fw-semibold text-dark mb-2">Correo Institucional UTN</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-envelope"></i></span>
                  <input type="email" class="form-control border-start-0 ps-0 profile-field-readonly" id="editEmail" value="" readonly>
                </div>
              </div>

              <div class="col-md-6">
                <label for="editRegional" class="form-label small fw-semibold text-dark">Facultad Regional</label>
                <input type="text" class="form-control profile-field-readonly" id="editRegional" value="FRH (Haedo)" readonly>
              </div>

              <div class="col-md-6">
                <label for="editCarrera" class="form-label small fw-semibold text-dark">Carrera</label>
                <select class="form-select profile-field-readonly" id="editCarrera" disabled>
                  <option value="Tecnicatura Universitaria en Programación (TUP)" selected>Tecnicatura Universitaria en Programación (TUP)</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="editNivel" class="form-label small fw-semibold text-dark">Año de Cursada</label>
                <select class="form-select" id="editNivel">
                  <option value="1° Año">1° Año (Ingresante)</option>
                  <option value="2° Año">2° Año</option>
                  <option value="3° Año" selected>3° Año</option>
                  <option value="4° Año">4° Año</option>
                  <option value="5° Año">5° Año (Avanzado)</option>
                  <option value="Graduado">Graduado</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="editDiscord" class="form-label small fw-semibold text-dark">Usuario de Discord</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0" style="color: #2563eb;"><i class="bi bi-discord"></i></span>
                  <input type="text" class="form-control border-start-0 ps-0" id="editDiscord" value="alex_utn#2026" placeholder="usuario#0000">
                </div>
              </div>

              <div class="col-12">
                <label for="editBio" class="form-label small fw-semibold text-dark">Materias que enseñás / Experiencia</label>
                <textarea class="form-control" id="editBio" rows="3">Tutor en Laboratorio de Computación I y Arquitectura de Computadoras. Ayudo a preparar parciales y guías de ejercicios.</textarea>
              </div>
            </div>
          </div>

          <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success px-4 rounded-pill fw-semibold shadow-sm">
              <i class="bi bi-check2 me-1"></i> Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       MODAL 2: SUBIR / CAMBIAR FOTO DE PERFIL
       ========================================================================== -->
  <div class="modal fade" id="modalSubirFoto" tabindex="-1" aria-labelledby="modalSubirFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-success-subtle text-success p-2 rounded-3">
              <i class="bi bi-camera-fill fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark" id="modalSubirFotoLabel">Actualizar Foto de Perfil</h5>
              <p class="text-secondary small mb-0">Seleccioná un avatar o subí tu propia foto</p>
            </div>
          </div>
          <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body p-4 text-center">
          <div class="preview-avatar-box mb-3" id="previewAvatarBox">
            <span id="previewInitials">AL</span>
          </div>

          <div class="mb-4">
            <label for="inputUploadFile" class="btn btn-outline-success rounded-pill px-4 fw-semibold shadow-sm">
              <i class="bi bi-upload me-1"></i> Subir imagen desde la PC
            </label>
            <input type="file" id="inputUploadFile" class="d-none" accept="image/*">
            <div class="text-muted small mt-1" style="font-size: 0.72rem;">Formatos JPG, PNG. Tamaño máximo 2MB</div>
          </div>

          <div class="border-top pt-3">
            <span class="small fw-semibold text-secondary d-block mb-3">O seleccioná un avatar ilustrado:</span>
            <div class="d-flex justify-content-center gap-3">
              <img src="public/img/avatares/bicho1.jpeg" 
                   class="preset-avatar" alt="Avatar 1" onclick="seleccionarPresetAvatar(this.src)">
              <img src="public/img/avatares/bicho2.jfif" 
                   class="preset-avatar" alt="Avatar 2" onclick="seleccionarPresetAvatar(this.src)">
              <img src="public/img/avatares/bicho3.jfif" 
                   class="preset-avatar" alt="Avatar 3" onclick="seleccionarPresetAvatar(this.src)">
            </div>
          </div>
        </div>

        <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
          <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" id="btnGuardarFoto" class="btn btn-success px-4 rounded-pill fw-semibold shadow-sm">
            Guardar Foto
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       MODAL 3: DISCORD Y SALAS DE ESTUDIO
       ========================================================================== -->
  <div class="modal fade" id="modalDiscordConfig" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="p-2 rounded-3 text-white" style="background-color: #2563eb;">
              <i class="bi bi-discord fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark">Comunidad de Discord UTN</h5>
              <p class="text-secondary small mb-0">Canales de voz y salas de estudio compartidas</p>
            </div>
          </div>
          <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body p-4">
          <div class="p-3 rounded-3 mb-3 border d-flex align-items-center justify-content-between" style="background-color: #f5f6fe;">
            <div class="d-flex align-items-center gap-3">
              <i class="bi bi-broadcast fs-3" style="color: #2563eb;"></i>
              <div>
                <span class="d-block fw-bold text-dark small">Servidor Oficial Study Match</span>
                <span class="text-muted" style="font-size: 0.75rem;">+250 estudiantes en línea ahora</span>
              </div>
            </div>
            <span class="badge bg-success rounded-pill px-2.5 py-1 small">Conectado</span>
          </div>
          <p class="text-secondary small">
            Como tutor, tenés permisos para abrir salas privadas de consulta en el servidor oficial de la UTN con pizarra virtual y audio HD.
          </p>
        </div>
        <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
          <button type="button" class="btn btn-success w-100 rounded-pill fw-semibold shadow-sm" data-bs-dismiss="modal">
            Entendido
          </button>
        </div>
      </div>
    </div>
  </div>

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

  <!-- TOAST FLOTANTE DE NOTIFICACIÓN -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="toastSuccess" class="toast align-items-center text-bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill text-success fs-5"></i>
          <span id="toastMessage">¡Perfil actualizado correctamente!</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
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

    // ==========================================
    // 3. Edición de Perfil y Sincronización LocalStorage
    // ==========================================
    const formPerfil = document.getElementById('formPerfil');
    formPerfil.addEventListener('submit', function(e) {
      e.preventDefault();

      const perfilData = {
        nombre: document.getElementById('editNombre').value.trim(),
        email: document.getElementById('editEmail').value.trim(),
        regional: document.getElementById('editRegional').value,
        carrera: document.getElementById('editCarrera').value,
        nivel: document.getElementById('editNivel').value,
        discord: document.getElementById('editDiscord').value.trim()
      };

      localStorage.setItem('study-match-user-profile', JSON.stringify(perfilData));
      aplicarDatosPerfil(perfilData);

      const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalEditarPerfil'));
      if (modalInstance) modalInstance.hide();
      mostrarToast("¡Perfil de tutor actualizado con éxito!");
    });

    function aplicarDatosPerfil(data) {
      if (!data) return;
      document.querySelectorAll('.userNameSlot').forEach(el => el.textContent = data.nombre);
      document.querySelectorAll('.userEmailSlot').forEach(el => el.textContent = data.email);
      
      const carreraCorta = data.carrera.includes('Programación') ? 'TUP' : 'Sistemas';
      document.querySelectorAll('.userCareerSlot').forEach(el => el.textContent = `${carreraCorta} • ${data.nivel}`);
      
      const regionalCorta = data.regional ? data.regional.split(' ')[0] : 'FRH';
      const legajoSlot = document.getElementById('userLegajoSlot');
      if (legajoSlot) legajoSlot.textContent = `Tutor Autorizado • ${regionalCorta}`;

      if (!selectedPhotoUrl) {
        const partes = data.nombre.split(' ');
        const iniciales = partes.length >= 2 
          ? (partes[0][0] + partes[1][0]).toUpperCase() 
          : data.nombre.substring(0, 2).toUpperCase();

        document.querySelectorAll('.userAvatarSlot').forEach(el => {
          el.style.backgroundImage = 'none';
          el.textContent = iniciales;
        });
        const prevInit = document.getElementById('previewInitials');
        if (prevInit) prevInit.textContent = iniciales;
      }
    }

    // ==========================================
    // 4. Subir y Cambiar Foto de Perfil
    // ==========================================
    let selectedPhotoUrl = localStorage.getItem('study-match-user-photo') || null;
    const inputUploadFile = document.getElementById('inputUploadFile');
    const previewAvatarBox = document.getElementById('previewAvatarBox');
    const previewInitials = document.getElementById('previewInitials');
    const btnGuardarFoto = document.getElementById('btnGuardarFoto');

    if (selectedPhotoUrl) {
      document.querySelectorAll('.userAvatarSlot').forEach(slot => {
        slot.style.backgroundImage = `url('${selectedPhotoUrl}')`;
        slot.textContent = '';
      });
      if (previewAvatarBox) previewAvatarBox.style.backgroundImage = `url('${selectedPhotoUrl}')`;
      if (previewInitials) previewInitials.textContent = '';
    }

    if (inputUploadFile) {
      inputUploadFile.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(event) {
            selectedPhotoUrl = event.target.result;
            previewAvatarBox.style.backgroundImage = `url('${selectedPhotoUrl}')`;
            previewInitials.textContent = '';
            document.querySelectorAll('.preset-avatar').forEach(p => p.classList.remove('selected'));
          };
          reader.readAsDataURL(file);
        }
      });
    }

    function seleccionarPresetAvatar(url) {
      selectedPhotoUrl = url;
      previewAvatarBox.style.backgroundImage = `url('${url}')`;
      previewInitials.textContent = '';
      document.querySelectorAll('.preset-avatar').forEach(p => {
        p.classList.toggle('selected', p.src === url);
      });
    }

    if (btnGuardarFoto) {
      btnGuardarFoto.addEventListener('click', function() {
        if (selectedPhotoUrl) {
          localStorage.setItem('study-match-user-photo', selectedPhotoUrl);
          document.querySelectorAll('.userAvatarSlot').forEach(slot => {
            slot.style.backgroundImage = `url('${selectedPhotoUrl}')`;
            slot.textContent = '';
          });
          mostrarToast("¡Foto de perfil actualizada!");
        }
        const modalFotoInstance = bootstrap.Modal.getInstance(document.getElementById('modalSubirFoto'));
        if (modalFotoInstance) modalFotoInstance.hide();
      });
    }

    // ==========================================
    // 5. Toast Helper
    // ==========================================
    function mostrarToast(mensaje) {
      const toastEl = document.getElementById('toastSuccess');
      document.getElementById('toastMessage').textContent = mensaje;
      const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
      toast.show();
    }

    // 6. Solicitudes: se aceptan / rechazan en el servidor (SolicitudController::responder)

    // 7. Publicar tutorías: ver Mis publicaciones (OfertaController)

    // Cargar perfil guardado si existe
    const perfilGuardado = localStorage.getItem('study-match-user-profile');
    if (perfilGuardado) {
      try {
        aplicarDatosPerfil(JSON.parse(perfilGuardado));
      } catch(e) {}
    }

    function aplicarNombreDeSesion(nombre) {
      document.querySelectorAll('.userNameSlot').forEach(el => {
        el.textContent = nombre;
      });

      const editNombre = document.getElementById('editNombre');
      if (editNombre) editNombre.value = nombre;

      if (!selectedPhotoUrl) {
        const partes = nombre.trim().split(/\s+/);
        const iniciales = partes.length >= 2
          ? (partes[0][0] + partes[1][0]).toUpperCase()
          : nombre.substring(0, 2).toUpperCase();
        document.querySelectorAll('.userAvatarSlot').forEach(el => {
          el.style.backgroundImage = 'none';
          el.textContent = iniciales;
        });
        const prevInit = document.getElementById('previewInitials');
        if (prevInit) prevInit.textContent = iniciales;
      }
    }

    fetch('index.php?r=usuario-actual', { credentials: 'same-origin' })
      .then(response => {
        if (!response.ok) throw new Error('No se pudo cargar el usuario de la sesión.');
        return response.json();
      })
      .then(user => {
        if (
          typeof user.nombre !== 'string'
          || user.nombre.trim() === ''
          || typeof user.email !== 'string'
          || user.email.trim() === ''
        ) {
          throw new Error('La respuesta del usuario no contiene un nombre o correo válido.');
        }
        aplicarNombreDeSesion(user.nombre);
        document.getElementById('editEmail').value = user.email;
        // La carrera es fija (TUP): no se toma de la base de datos.
        const nivelPorValorDB = {
          '1ro': '1° Año',
          '2do': '2° Año',
          '3ro': '3° Año',
          '4to': '4° Año',
          '5to': '5° Año',
          'graduado': 'Graduado'
        };
        if (typeof user.nivel === 'string' && nivelPorValorDB[user.nivel]) {
          document.getElementById('editNivel').value = nivelPorValorDB[user.nivel];
        }
        document.querySelectorAll('.userEmailSlot').forEach(el => {
          el.textContent = user.email;
        });
      })
      .catch(error => {
        console.error(error);
        document.querySelectorAll('.userNameSlot').forEach(el => {
          el.textContent = 'No disponible';
        });
        document.querySelectorAll('.userEmailSlot').forEach(el => {
          el.textContent = 'No disponible';
        });
      });
  </script>

  <!-- Bootstrap 5.3 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="public/js/sidebar.js"></script>
  <script src="public/js/main.js"></script>
</body>
</html>
