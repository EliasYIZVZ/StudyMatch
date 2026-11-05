<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
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
  <link rel="stylesheet" href="public/css/dashboard.css?v=20261106_iconos">

  <!-- Estilos globales de Administración (sidebar claro gris azulado / oscuro grafito) -->
  <link rel="stylesheet" href="public/css/admin-global.css?v=20261101_panel_bg">

  <!-- Estilos específicos de Moderación y Alertas -->
  <link rel="stylesheet" href="public/css/admin-reportes.css?v=20261104_redise2">
</head>
<body class="dashboard-body" data-role="admin">

  <!-- ==========================================================================
       SIDEBAR LATERAL IZQUIERDA (MODO ADMIN - FIJO TONO GRAFITO)
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

        <!-- 1. Inicio / Resumen (Métricas) -->
        <a href="index.php?r=admin" class="sidebar-link" title="Inicio / Resumen">
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
        <a href="index.php?r=admin-materias" class="sidebar-link" title="Gestión de Materias y Carreras">
          <i class="bi bi-journal-bookmark fs-5"></i>
          <span class="sidebar-text">Gestión de Materias</span>
          <span class="badge bg-primary-subtle text-primary ms-auto sidebar-text" style="font-size: 0.68rem;"><?= (int) $metricas['materias']['total']; ?></span>
        </a>

        <!-- 4. Salas Públicas -->
        <a href="index.php?r=admin-salas" class="sidebar-link" title="Salas Públicas">
          <i class="bi bi-broadcast fs-5 text-danger"></i>
          <span class="sidebar-text">Salas Públicas</span>
          <span class="badge bg-danger text-white rounded-pill ms-auto sidebar-text" style="font-size: 0.65rem;"><?= (int) $metricas['salas']['activas']; ?> en vivo</span>
        </a>

        <!-- 5. Reportes y Moderación (ACTIVO) -->
        <a href="index.php?r=admin-reportes" class="sidebar-link active" title="Moderación y Alertas">
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
                <li class="breadcrumb-item"><a href="index.php?r=admin" class="text-decoration-none text-muted">Administración</a></li>
                <li class="breadcrumb-item active text-danger fw-semibold" aria-current="page">Moderación y Alertas</li>
              </ol>
            </nav>
            <h1 class="h6 fw-bold mb-0 text-dark" style="text-shadow: none;">Panel de Moderación y Alertas</h1>
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

      <!-- ====================================================================
           CABECERA CONTEXTUAL DE MODERACIÓN
           ==================================================================== -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white" 
           style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 65%, #991b1b 100%);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div>
            <span class="badge rounded-pill px-3 py-1 small fw-semibold mb-2 d-inline-flex align-items-center gap-2"
                  style="background-color: rgba(239, 68, 68, 0.25); color: #fecaca; border: 1px solid rgba(239, 68, 68, 0.4);">
              <i class="bi bi-shield-lock-fill"></i> Seguridad & Convivencia
            </span>
            <h1 class="h3 fw-bold mb-1 text-white" style="text-shadow: none;">
              Centro de Moderación y Alertas
            </h1>
            <p class="text-white text-opacity-75 small mb-0">
              Gestión de reportes comunitarios sobre salas, tutorías, mensajes, tutores y alumnos.
            </p>
          </div>

          <button type="button" 
                  class="btn btn-outline-light rounded-pill btn-sm px-3 fw-semibold shadow-sm d-inline-flex align-items-center gap-2"
                  onclick="actualizarListaReportes()">
            <i class="bi bi-arrow-clockwise"></i>
            <span>Actualizar</span>
          </button>
        </div>
      </div>

      <?php
        $e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

        // Presentación de cada tipo de reporte: ícono y clase de color (definidos en admin-reportes.css)
        $estilosTipo = [
            'sala'    => ['bi-discord',          'tipo-sala'],
            'tutoria' => ['bi-journal-text',     'tipo-tutoria'],
            'mensaje' => ['bi-chat-left-text',   'tipo-mensaje'],
            'tutor'   => ['bi-patch-check-fill', 'tipo-tutor'],
            'alumno'  => ['bi-mortarboard-fill', 'tipo-alumno'],
        ];
        $etiquetasTipo = ['sala' => 'Sala', 'tutoria' => 'Tutoría', 'mensaje' => 'Mensaje', 'tutor' => 'Tutor', 'alumno' => 'Alumno'];
      ?>

      <!-- ====================================================================
           RESUMEN (datos reales)
           ==================================================================== -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
            <div class="card-body d-flex align-items-center gap-3">
              <span class="stat-icon stat-icon--amber"><i class="bi bi-hourglass-split"></i></span>
              <div>
                <div class="stat-value" id="statPendientes"><?= (int) $reportesPendientes ?></div>
                <div class="stat-label">Pendientes de revisión</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
            <div class="card-body d-flex align-items-center gap-3">
              <span class="stat-icon stat-icon--green"><i class="bi bi-check-circle-fill"></i></span>
              <div>
                <div class="stat-value" id="statFinalizados"><?= (int) $reportesFinalizados ?></div>
                <div class="stat-label">Finalizados con sanción</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
            <div class="card-body d-flex align-items-center gap-3">
              <span class="stat-icon stat-icon--slate"><i class="bi bi-x-circle-fill"></i></span>
              <div>
                <div class="stat-value" id="statRechazados"><?= (int) $reportesRechazados ?></div>
                <div class="stat-label">Rechazados</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
            <div class="card-body d-flex align-items-center gap-3">
              <span class="stat-icon stat-icon--blue"><i class="bi bi-flag-fill"></i></span>
              <div>
                <div class="stat-value" id="statTotal"><?= (int) $totalReportes ?></div>
                <div class="stat-label">Reportes recibidos</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ====================================================================
           AUDITORÍA DE REPORTES
           ==================================================================== -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">

        <div class="p-4 border-bottom">
          <div class="mb-3">
            <h5 class="fw-bold mb-0">Auditoría de reportes</h5>
            <span class="text-secondary small">Denuncias de la comunidad sobre salas, tutorías, mensajes, tutores y alumnos</span>
          </div>

          <div class="row g-2 align-items-center">
            <div class="col-12 col-lg">
              <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
                <input type="text" id="reporteSearchInput" class="form-control rounded-pill ps-5 py-2 search-input-wide shadow-none"
                       placeholder="Buscar por elemento, motivo o fecha...">
              </div>
            </div>
            <div class="col-6 col-lg-auto">
              <select id="filtroTipo" class="form-select rounded-pill filtro-select shadow-none" aria-label="Filtrar por tipo">
                <option value="">Todos los tipos</option>
                <?php foreach ($etiquetasTipo as $clave => $etiqueta): ?>
                  <option value="<?= $e($clave) ?>"><?= $e($etiqueta) ?> (<?= (int) $conteoTipos[$clave] ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-lg-auto">
              <select id="filtroEstado" class="form-select rounded-pill filtro-select shadow-none" aria-label="Filtrar por estado">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendientes</option>
                <option value="finalizado">Finalizados</option>
                <option value="rechazado">Rechazados</option>
              </select>
            </div>
            <div class="col-12 col-lg-auto">
              <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm px-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-2 w-100" onclick="restablecerFiltrosReportes()">
                <i class="bi bi-funnel"></i><span>Limpiar</span>
              </button>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-reportes align-middle mb-0" id="tablaReportesAdmin">
            <thead>
              <tr>
                <th scope="col" style="width: 120px;">Tipo</th>
                <th scope="col" style="min-width: 200px;">Elemento reportado</th>
                <th scope="col" style="min-width: 190px;">Motivo</th>
                <th scope="col" style="width: 150px;">Fecha</th>
                <th scope="col" style="width: 130px;">Estado</th>
                <th scope="col" class="text-end" style="width: 200px;">Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaReportesBody">
              <?php foreach ($reportes as $rep): ?>
                <?php $estiloTipo = $estilosTipo[$rep['tipo']]; ?>
                <tr class="reporte-row <?= $rep['estado'] !== 'pendiente' ? 'fila-resuelta' : '' ?>"
                    id="row-reporte-<?= (int) $rep['id_reporte'] ?>"
                    data-id="<?= (int) $rep['id_reporte'] ?>"
                    data-tipo="<?= $e($rep['tipo']) ?>"
                    data-estado="<?= $e($rep['estado']) ?>"
                    data-timestamp="<?= (int) $rep['timestamp'] ?>">

                  <td>
                    <span class="badge-tipo-elemento <?= $estiloTipo[1] ?>">
                      <i class="bi <?= $estiloTipo[0] ?>"></i><?= $e($rep['tipo_etiqueta']) ?>
                    </span>
                  </td>

                  <td>
                    <div class="d-flex flex-column">
                      <span class="fw-semibold text-truncate" style="max-width: 230px;" title="<?= $e($rep['elemento']) ?>"><?= $e($rep['elemento']) ?></span>
                      <span class="text-secondary text-truncate" style="font-size: 0.76rem; max-width: 230px;"><?= $e($rep['elemento_sub']) ?></span>
                    </div>
                  </td>

                  <td>
                    <span class="motivo-text d-inline-flex align-items-center gap-2" title="<?= $e($rep['motivo']) ?>">
                      <i class="bi bi-shield-exclamation flex-shrink-0"></i>
                      <span class="text-truncate" style="max-width: 170px;"><?= $e($rep['motivo']) ?></span>
                    </span>
                  </td>

                  <td>
                    <span class="text-secondary small d-inline-flex align-items-center gap-2 text-nowrap">
                      <i class="bi bi-clock"></i><?= $e($rep['fecha']) ?>
                    </span>
                  </td>

                  <td class="estado-badge-slot">
                    <?php if ($rep['estado'] === 'pendiente'): ?>
                      <span class="badge-estado estado-pendiente"><i class="bi bi-hourglass-split"></i>Pendiente</span>
                    <?php elseif ($rep['estado'] === 'finalizado'): ?>
                      <span class="badge-estado estado-finalizado"><i class="bi bi-check-circle-fill"></i>Finalizado</span>
                    <?php else: ?>
                      <span class="badge-estado estado-rechazado"><i class="bi bi-x-circle"></i>Rechazado</span>
                    <?php endif; ?>
                  </td>

                  <td class="text-end acciones-slot">
                    <div class="d-inline-flex align-items-center gap-2">
                      <?php if ($rep['estado'] === 'pendiente'): ?>
                        <button type="button" class="btn btn-sm btn-primary btn-action-pill d-inline-flex align-items-center justify-content-center gap-1"
                                onclick="abrirModalDetalle(<?= (int) $rep['id_reporte'] ?>)" title="Revisar caso y aplicar sanción">
                          <i class="bi bi-eye"></i><span>Revisar</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-action-pill d-inline-flex align-items-center justify-content-center gap-1"
                                onclick="descartarAlerta(<?= (int) $rep['id_reporte'] ?>)" title="Rechazar y desestimar el reporte">
                          <i class="bi bi-x-lg"></i><span>Descartar</span>
                        </button>
                      <?php else: ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-action-pill d-inline-flex align-items-center justify-content-center gap-1"
                                onclick="abrirModalDetalle(<?= (int) $rep['id_reporte'] ?>)" title="Ver el caso">
                          <i class="bi bi-eye"></i><span>Ver caso</span>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>

                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <?php if ($errorReportes): ?>
            <div class="p-5 text-center">
              <i class="bi bi-exclamation-triangle text-warning fs-1 d-block mb-2"></i>
              <p class="text-secondary mb-0">No se pudieron cargar los reportes. Intentá nuevamente más tarde.</p>
            </div>
          <?php endif; ?>

          <div id="emptySearchReportes" class="p-5 text-center text-secondary d-none">
            <i class="bi bi-shield-check text-success fs-2 d-block mb-2"></i>
            <h6 class="fw-bold mb-1"><?= empty($reportes) && !$errorReportes ? 'Todavía no hay reportes' : 'Sin reportes que coincidan' ?></h6>
            <p class="small mb-0">No hay denuncias con los filtros seleccionados.</p>
          </div>
        </div>

        <div class="card-footer border-top px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-3" id="paginacionReportesCard">
          <div class="text-secondary small" id="paginacionReportesInfoTexto"></div>
          <nav aria-label="Navegación de auditoría">
            <ul class="pagination pagination-sm mb-0" id="paginacionReportesControles"></ul>
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
            <div class="bg-primary-subtle text-primary p-2 rounded-3 d-inline-flex">
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
          <div class="p-3 rounded-3 border mb-3 bloque-motivo">
            <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.68rem;">Motivo e Infracción Declarada:</span>
            <div class="fw-bold motivo-text mb-2" id="detMotivo">...</div>
            <p class="text-secondary small mb-0" id="detDescripcion" style="line-height: 1.5;">...</p>
          </div>

          <!-- Información adjunta: copia de lo reportado al momento del reporte -->
          <div class="p-3 rounded-3 border mb-2">
            <span class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.68rem;">Información adjunta (copia al momento del reporte):</span>
            <dl class="row mb-0 small" id="detEvidencia"></dl>
          </div>
          <p class="small text-secondary mb-0 mt-2" id="detSancion"></p>

        </div>

        <!-- Footer con Acciones Directas: Desactivar cuenta de alumno o Discord -->
        <div class="modal-footer border-top px-4 py-3 rounded-bottom-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
          
          <button type="button" class="btn btn-outline-secondary btn-modal-accion" data-bs-dismiss="modal">
            Cerrar
          </button>

          <div class="d-flex align-items-center flex-wrap justify-content-end gap-2">
            <!-- Botón Descartar desde Modal -->
            <button type="button" 
                    class="btn btn-outline-secondary btn-modal-accion"
                    id="btnDescartarModal"
                    onclick="descartarDesdeModal()">
              <i class="bi bi-trash"></i>
              <span>Rechazar Reporte</span>
            </button>

            <!-- Botón Dinámico: Desactivar Sala de Discord O Desactivar Cuenta de Alumno -->
            <button type="button" 
                    class="btn btn-danger btn-modal-accion"
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
    // 3. Búsqueda, filtros y paginación de la auditoría
    // ==========================================
    const ITEMS_POR_PAGINA = 10;
    let paginaActualRep = 1;

    const REPORTES = <?= json_encode(array_column($reportes, null, 'id_reporte'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PARTIAL_OUTPUT_ON_ERROR); ?>;

    const searchRep = document.getElementById('reporteSearchInput');
    const filtroTipo = document.getElementById('filtroTipo');
    const filtroEstado = document.getElementById('filtroEstado');

    function actualizarPaginacionRep() {
      const todasLasFilas = Array.from(document.querySelectorAll('#tablaReportesBody .reporte-row'));
      const query = (searchRep ? searchRep.value : '').toLowerCase().trim();

      const coincidentes = todasLasFilas.filter(row =>
        (!query || row.textContent.toLowerCase().includes(query))
        && (!filtroTipo.value || row.dataset.tipo === filtroTipo.value)
        && (!filtroEstado.value || row.dataset.estado === filtroEstado.value)
      );

      const total = coincidentes.length;
      const totalPaginas = Math.max(1, Math.ceil(total / ITEMS_POR_PAGINA));
      paginaActualRep = Math.min(Math.max(1, paginaActualRep), totalPaginas);

      const inicio = (paginaActualRep - 1) * ITEMS_POR_PAGINA;
      const visibles = new Set(coincidentes.slice(inicio, inicio + ITEMS_POR_PAGINA));
      todasLasFilas.forEach(row => { row.style.display = visibles.has(row) ? '' : 'none'; });

      document.getElementById('emptySearchReportes').classList.toggle('d-none', total > 0);
      document.getElementById('tablaReportesAdmin').classList.toggle('d-none', total === 0);
      document.getElementById('paginacionReportesCard').classList.toggle('d-none', total === 0);

      document.getElementById('paginacionReportesInfoTexto').innerHTML = total === 0
        ? ''
        : `Mostrando <strong>${inicio + 1} - ${Math.min(inicio + ITEMS_POR_PAGINA, total)}</strong> de <strong>${total}</strong> reportes`;

      dibujarPaginacionRep(totalPaginas);
    }

    function dibujarPaginacionRep(totalPaginas) {
      const ul = document.getElementById('paginacionReportesControles');
      ul.innerHTML = '';
      if (totalPaginas <= 1) return;

      const item = (texto, pagina, opciones) => {
        const o = opciones || {};
        const li = document.createElement('li');
        li.className = 'page-item' + (o.activo ? ' active' : '') + (o.deshabilitado ? ' disabled' : '');
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'page-link ' + (o.clase || '');
        b.textContent = texto;
        b.addEventListener('click', () => { paginaActualRep = pagina; actualizarPaginacionRep(); });
        li.appendChild(b);
        ul.appendChild(li);
      };

      item('Anterior', paginaActualRep - 1, { deshabilitado: paginaActualRep <= 1, clase: 'rounded-start-pill px-3' });
      for (let p = 1; p <= totalPaginas; p++) item(String(p), p, { activo: p === paginaActualRep });
      item('Siguiente', paginaActualRep + 1, { deshabilitado: paginaActualRep >= totalPaginas, clase: 'rounded-end-pill px-3' });
    }

    [searchRep, filtroTipo, filtroEstado].forEach(el => {
      if (el) el.addEventListener(el === searchRep ? 'input' : 'change', () => { paginaActualRep = 1; actualizarPaginacionRep(); });
    });

    function restablecerFiltrosReportes() {
      searchRep.value = '';
      filtroTipo.value = '';
      filtroEstado.value = '';
      paginaActualRep = 1;
      actualizarPaginacionRep();
    }

    // Estado y acciones de una fila (también se usan al resolver un reporte sin recargar)
    function htmlEstado(estado) {
      if (estado === 'pendiente') return '<span class="badge-estado estado-pendiente"><i class="bi bi-hourglass-split"></i>Pendiente</span>';
      if (estado === 'finalizado') return '<span class="badge-estado estado-finalizado"><i class="bi bi-check-circle-fill"></i>Finalizado</span>';
      return '<span class="badge-estado estado-rechazado"><i class="bi bi-x-circle"></i>Rechazado</span>';
    }

    function htmlAccionesResuelto(id) {
      return `<div class="d-inline-flex align-items-center gap-2"><button type="button" class="btn btn-sm btn-outline-secondary btn-action-pill d-inline-flex align-items-center justify-content-center gap-1" onclick="abrirModalDetalle(${id})" title="Ver el caso"><i class="bi bi-eye"></i><span>Ver caso</span></button></div>`;
    }

    function marcarFilaResuelta(id, estado) {
      if (REPORTES[id]) REPORTES[id].estado = estado;
      const row = document.getElementById(`row-reporte-${id}`);
      if (!row) return;
      row.setAttribute('data-estado', estado);
      row.classList.add('fila-resuelta');
      row.querySelector('.estado-badge-slot').innerHTML = htmlEstado(estado);
      row.querySelector('.acciones-slot').innerHTML = htmlAccionesResuelto(id);
    }

    // ==========================================
    // 4. Modal [Ver Detalle] y Acción de Desactivar
    // ==========================================
    let reporteActivoEnModal = null;

    function abrirModalDetalle(id) {
      const rep = REPORTES[id];
      if (!rep) return;
      reporteActivoEnModal = rep;
      document.getElementById('detElemento').textContent = rep.elemento;
      document.getElementById('detElementoSub').textContent = rep.elemento_sub;
      document.getElementById('detMotivo').textContent = rep.motivo;
      document.getElementById('detDescripcion').textContent = rep.descripcion;
      document.getElementById('detDenuncianteNombre').textContent = rep.denunciante;
      document.getElementById('detDenuncianteRol').textContent = rep.denunciante_rol;
      document.getElementById('detDenuncianteAvatar').textContent = rep.denunciante_avatar;
      renderEvidencia(rep.evidencia);
      document.getElementById('detSancion').textContent = textoSancion(rep);

      // Configurar botón dinámico según si es Discord o Alumno
      const btnDesactivar = document.getElementById('btnAccionDesactivarModal');
      const iconoDesactivar = document.getElementById('iconoAccionDesactivar');
      const textoDesactivar = document.getElementById('textoAccionDesactivar');
      const btnDescartarModal = document.getElementById('btnDescartarModal');

      if (rep.estado === 'finalizado' || rep.estado === 'rechazado') {
        btnDesactivar.disabled = true;
        btnDesactivar.className = 'btn btn-secondary btn-modal-accion disabled';
        iconoDesactivar.className = 'bi bi-check-circle-fill';
        textoDesactivar.textContent = (rep.estado === 'finalizado') ? 'Caso Finalizado' : 'Caso Rechazado';
        if (btnDescartarModal) btnDescartarModal.style.display = 'none';
      } else {
        btnDesactivar.disabled = false;
        btnDesactivar.className = 'btn btn-danger btn-modal-accion';
        if (btnDescartarModal) btnDescartarModal.style.display = '';
        
        const esSala = rep.tipo === 'sala';
        iconoDesactivar.className = esSala ? 'bi bi-slash-circle' : 'bi bi-person-slash';
        textoDesactivar.textContent = esSala ? 'Suspender Sala y Finalizar' : 'Inactivar Usuario y Finalizar';
      }

      const modalEl = document.getElementById('modalDetalleReporte');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }

    // La evidencia es texto de otros usuarios: se inserta siempre como texto, nunca como HTML
    function renderEvidencia(evidencia) {
      const lista = document.getElementById('detEvidencia');
      lista.textContent = '';
      const claves = Object.keys(evidencia || {});

      if (claves.length === 0) {
        const vacio = document.createElement('dd');
        vacio.className = 'col-12 mb-0 text-muted';
        vacio.textContent = 'Sin información adjunta.';
        lista.appendChild(vacio);
        return;
      }

      claves.forEach(clave => {
        const dt = document.createElement('dt');
        dt.className = 'col-sm-4 text-secondary fw-semibold';
        dt.textContent = clave;
        const dd = document.createElement('dd');
        dd.className = 'col-sm-8 mb-1 text-break';
        dd.style.whiteSpace = 'pre-line';
        dd.textContent = evidencia[clave];
        lista.appendChild(dt);
        lista.appendChild(dd);
      });
    }

    function textoSancion(rep) {
      if (rep.estado !== 'pendiente') return '';
      if (rep.tipo === 'sala') return 'Al finalizar, la sala de Discord quedará suspendida.';
      if (!rep.reportado) return 'El usuario reportado ya no existe: no hay a quién sancionar.';
      if (rep.reportado_estado !== 'activo') return `La cuenta de ${rep.reportado} ya está inactiva.`;
      return `Al finalizar, la cuenta de ${rep.reportado} quedará inactiva (podés reactivarla desde Gestión de Usuarios).`;
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
      const btn = document.getElementById('btnAccionDesactivarModal');
      if (btn) btn.disabled = true;

      const formData = new FormData();
      formData.append('id_reporte', id);
      formData.append('accion', 'finalizar');

      fetch('index.php?r=admin-resolver-reporte', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.ok) {
          marcarFilaResuelta(id, 'finalizado');

          reporteActivoEnModal.estado = 'finalizado';
          const modalEl = document.getElementById('modalDetalleReporte');
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();

          actualizarContadorPendientes();
          actualizarPaginacionRep();
          mostrarToast(data.mensaje || 'Reporte finalizado y sanción aplicada.');
        } else {
          alert(data.error || 'No se pudo resolver el reporte.');
          if (btn) btn.disabled = false;
        }
      })
      .catch(err => {
        console.error(err);
        alert('Error al comunicar con el servidor.');
        if (btn) btn.disabled = false;
      });
    }

    // ==========================================
    // 5. [Descartar Alerta]
    // ==========================================
    function descartarAlerta(id) {
      if (!confirm('¿Deseas rechazar y desestimar este reporte?')) return;

      const formData = new FormData();
      formData.append('id_reporte', id);
      formData.append('accion', 'rechazar');

      fetch('index.php?r=admin-resolver-reporte', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.ok) {
          marcarFilaResuelta(id, 'rechazado');

          actualizarContadorPendientes();
          actualizarPaginacionRep();
          mostrarToast(data.mensaje || `Reporte #${id} rechazado.`);
        } else {
          alert(data.error || 'No se pudo rechazar el reporte.');
        }
      })
      .catch(err => {
        console.error(err);
        alert('Error al procesar el rechazo del reporte.');
      });
    }

    function actualizarContadorPendientes() {
      const cuenta = { pendiente: 0, finalizado: 0, rechazado: 0 };
      const rows = document.querySelectorAll('#tablaReportesBody .reporte-row');
      rows.forEach(r => { cuenta[r.getAttribute('data-estado')]++; });

      const poner = (id, valor) => { const el = document.getElementById(id); if (el) el.textContent = valor; };
      poner('statPendientes', cuenta.pendiente);
      poner('statFinalizados', cuenta.finalizado);
      poner('statRechazados', cuenta.rechazado);
      poner('statTotal', rows.length);
      poner('badgeSidebarReportes', cuenta.pendiente);
    }

    // Vuelve a leer los reportes desde la base de datos
    function actualizarListaReportes() {
      window.location.reload();
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
