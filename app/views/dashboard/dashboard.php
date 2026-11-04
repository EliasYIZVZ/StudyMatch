<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Study Match - Panel de Alumno</title>
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
  <link rel="stylesheet" href="public/css/dashboard.css?v=20261103_page_title">
</head>
<body class="dashboard-body" data-role="alumno">

  <!-- ==========================================================================
       SIDEBAR LATERAL IZQUIERDA (MODO ALUMNO - 100% FIJO)
       ========================================================================== -->
  <?php $sidebarActivo = 'inicio'; require APP_PATH . '/views/partials/sidebar-alumno.php'; ?>

  <!-- ==========================================================================
       CONTENIDO PRINCIPAL: PANEL ALUMNO
       ========================================================================== -->
  <div class="dashboard-main-wrapper">

    <!-- TOPBAR CON BUSCADOR, SWITCH DE ROL Y MENÚ DE PERFIL -->
    <header class="topbar-header px-4 py-3 sticky-top">
      <div class="d-flex align-items-center justify-content-between gap-3">
        
        <!-- Toggle de Sidebar + Buscador Central -->
        <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 600px;">
          <button id="btnToggleSidebar" class="btn btn-light border text-secondary p-2 rounded-3" title="Expandir/Contraer menú lateral">
            <i class="bi bi-layout-sidebar fs-5"></i>
          </button>
        </div>

        <!-- Switch Rol Dual: Alumno (Azul) / Tutor (Verde) + Perfil -->
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-outline-brand btn-sm theme-toggle-btn d-inline-flex align-items-center justify-content-center rounded-pill" type="button" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
            <i class="theme-icon bi bi-sun-fill text-warning"></i>
          </button>
          
          <!-- Switch Alumno / Tutor (Cambia a index.php?r=dashboard-tutor) -->
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

    <main class="p-3 p-md-4 flex-grow-1 view-fade">
      <?php require APP_PATH . '/views/partials/flash.php'; ?>
      <!-- ====================================================================
           2. SECCIÓN DESTACADA: SALAS PÚBLICAS / CLASES GRATUITAS
           ==================================================================== -->
      <section id="salas-publicas" class="mb-5 pb-2">
        <div class="d-flex justify-content-between align-items-end mb-3 flex-wrap gap-3">
          <div>
            <h1 class="page-title fw-bold text-dark mb-1">Encontrá tu próxima clase</h1>
            <p class="text-secondary small mb-0">Tutorías pensadas de estudiantes para estudiantes.</p>
          </div>
        </div>

        <?php if (!$errorOfertas && !empty($ofertas)): ?>
          <?php
          $buscador = [
              'grid' => 'salasPublicasGrid',
              'pagina' => $ofertasVisibles,
              'placeholder' => 'Materia, tutor, tema o modalidad…',
              'materias' => $materias,
              'total' => count($ofertas),
              'unidad' => 'tutorías',
          ];
          require APP_PATH . '/views/partials/buscador-compacto.php';
          ?>
        <?php endif; ?>

        <!-- Grilla de tutorías (DashboardController::alumno -> OfertaTutoria).
             2 por fila en computadora y tablet (md+), 1 en celular. Sin filtros
             se muestran $ofertasVisibles; el resto con "Ver más" (buscador-cards.js). -->
        <?php if ($errorOfertas): ?>
          <div class="public-room-card p-4 p-md-5 text-center">
            <i class="bi bi-exclamation-triangle fs-1 text-warning d-block mb-2"></i>
            <p class="text-secondary mb-0">No se pudieron cargar las tutorías. Intentá nuevamente más tarde.</p>
          </div>
        <?php elseif (empty($ofertas)): ?>
          <div class="public-room-card p-4 p-md-5 text-center">
            <i class="bi bi-journal-x fs-1 text-secondary d-block mb-2"></i>
            <p class="text-secondary mb-0">Aún no existen ofertas de tutoría. Podés realizar publicaciones en tu módulo de tutor.</p>
          </div>
        <?php else: ?>
          <div class="row row-cols-1 row-cols-md-2 g-4" id="salasPublicasGrid">
            <?php foreach ($ofertas as $posicion => $oferta): ?>
              <?php require APP_PATH . '/views/partials/oferta-tutoria-card.php'; ?>
            <?php endforeach; ?>
          </div>

          <!-- Ningún filtro coincide -->
          <div id="buscadorSinResultados" class="text-center py-5 d-none">
            <i class="bi bi-search fs-1 text-secondary d-block mb-2"></i>
            <h2 class="h5 fw-bold text-dark">No se encontraron tutorías con esos criterios</h2>
            <p class="text-secondary small mb-3">Probá con otra palabra o elegí "Todas las materias".</p>
            <button type="button" class="btn btn-outline-primary rounded-pill px-4 btn-sm" id="buscadorRestablecer">
              Restablecer filtros
            </button>
          </div>

          <div class="text-center mt-4">
            <button type="button" id="buscadorVerMas" class="btn btn-outline-primary rounded-pill px-4 fw-semibold<?= count($ofertas) > $ofertasVisibles ? '' : ' d-none' ?>">
              Ver más <i class="bi bi-chevron-down ms-1"></i>
            </button>
          </div>
        <?php endif; ?>
      </section>

      <!-- ====================================================================
           2.1 SECCIÓN: SALAS PÚBLICAS DE ESTUDIO EN DISCORD
           ==================================================================== -->
      <?php if (!empty($salasPublicas)): ?>
      <section id="salas-comunitarias-discord" class="mb-5 pb-2">
        <div class="d-flex justify-content-between align-items-end mb-3 flex-wrap gap-3">
          <div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold mb-2 d-inline-flex align-items-center gap-1">
              <i class="bi bi-discord"></i> Canales Comunitarios
            </span>
            <h2 class="h4 fw-bold text-dark mb-1">Salas Públicas de Estudio en Discord</h2>
            <p class="text-secondary small mb-0">Espacios abiertos de consulta y estudio colaborativo entre alumnos de la UTN.</p>
          </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
          <?php foreach ($salasPublicas as $sala): ?>
            <div class="col" id="sala-card-<?= (int) $sala['id_sala'] ?>">
              <article class="public-room-card h-100 d-flex flex-column p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1">
                    <i class="bi bi-discord"></i> Sala de Discord
                  </span>
                </div>
                <h3 class="h6 fw-bold text-dark mb-2"><?= Vista::e($sala['titulo']) ?></h3>
                <p class="text-secondary small lh-base mb-3 flex-grow-1"><?= Vista::e($sala['descripcion'] ?: 'Espacio de estudio libre para consultas.') ?></p>
                <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto">
                  <div class="d-flex flex-column">
                    <small class="text-muted"><i class="bi bi-person me-1"></i><?= Vista::e($sala['creador_nombre']) ?></small>
                    <button type="button" 
                            class="btn btn-link text-muted p-0 text-decoration-none text-start small mt-0.5" 
                            style="font-size: 0.72rem;"
                            onclick="abrirModalReporte('sala', <?= (int) $sala['id_sala'] ?>, '<?= Vista::e(addslashes($sala['titulo'])) ?>')">
                      <i class="bi bi-flag"></i> Reportar sala
                    </button>
                  </div>
                  <a href="<?= Vista::e($sala['enlace_discord']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-discord btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-sm">
                    <i class="bi bi-discord"></i>
                    <span>Unirse a Discord</span>
                  </a>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <!-- ====================================================================
           3. SECCIÓN: TUTORES RECOMENDADOS O MEJOR CALIFICADOS
           ==================================================================== -->
      <section id="tutores-destacados">
        <div class="d-flex justify-content-between align-items-end section-heading-spacing flex-wrap gap-3">
          <div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold mb-2 d-inline-flex align-items-center gap-1">
              <i class="bi bi-award-fill"></i> Excelencia Académica
            </span>
            <h2 class="h4 fw-bold text-dark mb-0">Tutores Recomendados y Mejor Calificados</h2>
          </div>
          <a href="index.php?r=buscar-tutor" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
            <span>Ver todos los tutores</span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>

        <!-- Grilla Responsiva de Tutores Recomendados -->
        <div class="row g-4" id="tutorCardsGrid">
          
          <!-- Tutor 1 -->
          <div class="col-md-6 col-xl-4">
            <div class="tutor-card p-4 h-100 d-flex flex-column justify-content-between">
              <div>
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar-with-status">
                      <div class="avatar-circle">MF</div>
                      <span class="status-online-dot"></span>
                    </div>
                    <div>
                      <h6 class="fw-bold mb-0 text-dark">Matías Fuentes</h6>
                      <small class="text-secondary">TUP • UTN Haedo (2° Año)</small>
                    </div>
                  </div>
                  <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">Online</span>
                </div>
                <span class="badge bg-primary-subtle text-primary mb-2">Programación II</span>
                <h6 class="fw-bold text-dark">C++ y POO: de punteros a herencia</h6>
                <p class="text-secondary small mb-3">
                  Resolución de guías prácticas de la UTN, preparación para parciales y ayuda con los laboratorios.
                </p>
              </div>
              <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <span class="small fw-semibold text-secondary"><i class="bi bi-star-fill text-warning me-1"></i>5.0 (48 reseñas)</span>
                <a href="index.php?r=buscar-tutor" class="btn btn-sm btn-role-action px-3 rounded-pill fw-semibold shadow-sm">Ver Perfil</a>
              </div>
            </div>
          </div>

          <!-- Tutor 2 -->
          <div class="col-md-6 col-xl-4">
            <div class="tutor-card p-4 h-100 d-flex flex-column justify-content-between">
              <div>
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar-with-status">
                      <img src="public/img/juli.jpeg" alt="Julieta Abbiati" class="rounded-circle border" style="width: 44px; height: 44px; object-fit: cover;">
                      <span class="status-online-dot"></span>
                    </div>
                    <div>
                      <h6 class="fw-bold mb-0 text-dark">Julieta Abbiati</h6>
                      <small class="text-secondary">TUP • Backend Developer</small>
                    </div>
                  </div>
                  <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">Online</span>
                </div>
                <span class="badge bg-primary-subtle text-primary mb-2">Desarrollo Backend</span>
                <h6 class="fw-bold text-dark">APIs REST, Autenticación y PHP</h6>
                <p class="text-secondary small mb-3">
                  Desarrollo de servicios, autenticación con sesiones y estructuración de proyectos web para la facultad.
                </p>
              </div>
              <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <span class="small fw-semibold text-secondary"><i class="bi bi-star-fill text-warning me-1"></i>5.0 (52 reseñas)</span>
                <a href="index.php?r=buscar-tutor" class="btn btn-sm btn-role-action px-3 rounded-pill fw-semibold shadow-sm">Ver Perfil</a>
              </div>
            </div>
          </div>

          <!-- Tutor 3 -->
          <div class="col-md-6 col-xl-4">
            <div class="tutor-card p-4 h-100 d-flex flex-column justify-content-between">
              <div>
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar-with-status">
                      <div class="avatar-circle" style="background: linear-gradient(135deg, #0284c7, #38bdf8);">VR</div>
                      <span class="status-away-dot"></span>
                    </div>
                    <div>
                      <h6 class="fw-bold mb-0 text-dark">Valentina Rojas</h6>
                      <small class="text-secondary">Sistemas • 4° Año</small>
                    </div>
                  </div>
                  <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill small">18:30 hs</span>
                </div>
                <span class="badge bg-success-subtle text-success mb-2">Bases de Datos</span>
                <h6 class="fw-bold text-dark">SQL Server, MySQL y Normalización</h6>
                <p class="text-secondary small mb-3">
                  Consultas complejas, JOINS, triggers y diseño de modelos relacionales sin errores para parciales.
                </p>
              </div>
              <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <span class="small fw-semibold text-secondary"><i class="bi bi-star-fill text-warning me-1"></i>4.9 (35 reseñas)</span>
                <a href="index.php?r=buscar-tutor" class="btn btn-sm btn-role-action px-3 rounded-pill fw-semibold shadow-sm">Ver Perfil</a>
              </div>
            </div>
          </div>

        </div>
      </section>

    </main>
  </div>

  <?php require APP_PATH . '/views/partials/perfil-alumno-modales.php'; ?>
  <?php require APP_PATH . '/views/partials/modal-solicitar-tutoria.php'; ?>
  <?php require APP_PATH . '/views/partials/modal-reportar.php'; ?>

  <!-- OVERLAY DE TRANSICIÓN SUAVE ENTRE ROLES -->
  <div id="roleTransitionOverlay" class="role-transition-overlay">
    <div class="role-transition-card text-center p-4">
      <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle bg-success-subtle text-success mb-3" style="width: 72px; height: 72px;">
        <i class="bi bi-mortarboard fs-1"></i>
      </div>
      <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
        <span class="spinner-grow spinner-grow-sm text-success" role="status" aria-hidden="true"></span>
        <h5 class="fw-bold text-dark mb-0">Cambiando a Panel de Tutor...</h5>
      </div>
      <p class="text-secondary small mb-0">Cargando tus solicitudes, métricas y materias publicadas</p>
    </div>
  </div>

  <!-- JavaScript: Funciones de Panel Alumno -->
  <script>
    // 1. Colapso / Expansión del Sidebar: ver public/js/sidebar.js

    // ==========================================
    // 2. Transición Visual Suave a Modo Tutor
    // ==========================================
    function irAModoTutor() {
      const overlay = document.getElementById('roleTransitionOverlay');
      if (overlay) overlay.classList.add('active');
      document.body.classList.add('page-fading-out');

      setTimeout(() => {
        window.location.href = 'index.php?r=dashboard-tutor';
      }, 340);
    }

    // 3-5. Perfil, foto y toast: ver public/js/perfil.js

    // 6. Buscador de tutorías: ver public/js/buscador-cards.js

    // Carga de datos del usuario de la sesión: ver public/js/perfil.js
  </script>

  <!-- Bootstrap 5.3 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="public/js/perfil.js"></script>
  <script src="public/js/solicitud-tutoria.js?v=20261103_descripcion"></script>
  <script src="public/js/buscador-cards.js?v=20261103"></script>
  <script src="public/js/sidebar.js"></script>
  <script src="public/js/main.js"></script>
</body>
</html>