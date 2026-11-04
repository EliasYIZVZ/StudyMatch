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
  <link rel="stylesheet" href="public/css/dashboard.css?v=20261106_reportes">

  <!-- Estilos globales de Administración (sidebar claro gris azulado / oscuro grafito) -->
  <link rel="stylesheet" href="public/css/admin-global.css?v=20261101_panel_bg">

  <!-- Estilos específicos de Moderación y Alertas -->
  <link rel="stylesheet" href="public/css/admin-reportes.css?v=20261029_v3">
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
          <span class="badge bg-primary-subtle text-primary ms-auto sidebar-text" style="font-size: 0.68rem;">1,280</span>
        </a>

        <!-- 3. Gestión de Materias -->
        <a href="index.php?r=admin-materias" class="sidebar-link" title="Gestión de Materias y Carreras">
          <i class="bi bi-journal-bookmark fs-5"></i>
          <span class="sidebar-text">Gestión de Materias</span>
          <span class="badge bg-secondary-subtle text-light ms-auto sidebar-text" style="font-size: 0.68rem;">10</span>
        </a>

        <!-- 4. Salas Públicas -->
        <a href="index.php?r=admin-salas" class="sidebar-link" title="Salas Públicas">
          <i class="bi bi-broadcast fs-5 text-danger"></i>
          <span class="sidebar-text">Salas Públicas</span>
          <span class="badge bg-danger text-white rounded-pill ms-auto sidebar-text" style="font-size: 0.65rem;">6 en vivo</span>
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
            <span class="badge rounded-pill px-3 py-1 small fw-semibold mb-2 d-inline-flex align-items-center gap-1.5"
                  style="background-color: rgba(239, 68, 68, 0.25); color: #fecaca; border: 1px solid rgba(239, 68, 68, 0.4);">
              <i class="bi bi-shield-lock-fill"></i> Seguridad & Convivencia
            </span>
            <h1 class="page-title fw-bold mb-1 text-white">
              Centro de Moderación y Alertas
            </h1>
            <p class="text-white text-opacity-75 small mb-0">
              Gestión de reportes comunitarios sobre salas, tutorías, mensajes, tutores y alumnos.
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
              <div class="btn-group btn-group-sm flex-wrap rounded-pill p-1 bg-light border" role="group" aria-label="Filtro de reportes">
                <?php
                  $filtrosTipo = [
                      'sala'    => ['bi-discord text-primary', 'Sala'],
                      'tutoria' => ['bi-journal-text', 'Tutoría'],
                      'mensaje' => ['bi-chat-left-text', 'Mensaje'],
                      'tutor'   => ['bi-patch-check text-primary', 'Tutor'],
                      'alumno'  => ['bi-mortarboard', 'Alumno'],
                  ];
                ?>
                <button type="button" class="btn btn-sm btn-white active rounded-pill fw-semibold px-3 shadow-sm filter-btn-tipo" onclick="cambiarFiltroTipo('todos', this)">
                  Todos <span class="badge bg-secondary-subtle text-dark ms-1" id="countFilterTodos"><?= $totalReportes; ?></span>
                </button>
                <?php foreach ($filtrosTipo as $clave => $filtro): ?>
                  <button type="button" class="btn btn-sm text-secondary rounded-pill fw-semibold px-2.5 filter-btn-tipo" onclick="cambiarFiltroTipo('<?= $clave; ?>', this)">
                    <i class="bi <?= $filtro[0]; ?> me-1"></i><?= $filtro[1]; ?> <span class="badge bg-secondary-subtle text-secondary ms-1" data-count-tipo="<?= $clave; ?>"><?= (int) $conteoTipos[$clave]; ?></span>
                  </button>
                <?php endforeach; ?>
              </div>

              <!-- Menú Desplegable con: Más nuevos, Pendientes, Resueltos -->
              <div class="position-relative">
                <select id="filtroEstadoOrden" 
                        class="form-select form-select-sm rounded-pill bg-light border px-3 py-1.5 fw-semibold text-secondary shadow-none" 
                        style="min-width: 155px; font-size: 0.82rem;" 
                        onchange="cambiarFiltroSelect(this.value)">
                  <option value="nuevos" selected>Más nuevos (Todos)</option>
                  <option value="pendiente">🟡 Pendientes</option>
                  <option value="finalizado">🟢 Finalizados</option>
                  <option value="rechazado">⚪ Rechazados</option>
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
                  
                  <!-- 1. Columna: Tipo (Sala / Tutoría / Mensaje / Tutor / Alumno) -->
                  <td>
                    <?php
                      $estilosTipo = [
                          'sala'    => ['bi-discord', 'badge-tipo-elemento', 'background-color: rgba(88, 101, 242, 0.15); color: #5865F2; border: 1px solid rgba(88, 101, 242, 0.3);'],
                          'tutoria' => ['bi-journal-text', 'badge-tipo-elemento bg-info-subtle text-info-emphasis border border-info-subtle', ''],
                          'mensaje' => ['bi-chat-left-text', 'badge-tipo-elemento bg-warning-subtle text-warning-emphasis border border-warning-subtle', ''],
                          'tutor'   => ['bi-patch-check-fill', 'badge-tipo-elemento bg-success-subtle text-success border border-success-subtle', ''],
                          'alumno'  => ['bi-mortarboard-fill', 'badge-tipo-elemento bg-primary-subtle text-primary border border-primary-subtle', ''],
                      ];
                      $estiloTipo = $estilosTipo[$rep['tipo']];
                    ?>
                    <span class="badge <?= $estiloTipo[1]; ?>"<?= $estiloTipo[2] !== '' ? ' style="' . $estiloTipo[2] . '"' : ''; ?>>
                      <i class="bi <?= $estiloTipo[0]; ?>"></i> <?= htmlspecialchars($rep['tipo_etiqueta']); ?>
                    </span>
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

                  <!-- 5. Columna: Estado (Pendiente / Finalizado / Rechazado) -->
                  <td class="estado-badge-slot">
                    <?php if ($rep['estado'] === 'pendiente'): ?>
                      <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5">
                        <span class="live-dot pulse-warning"></span>
                        <span>Pendiente</span>
                      </span>
                    <?php elseif ($rep['estado'] === 'finalizado'): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Finalizado</span>
                      </span>
                    <?php else: ?>
                      <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-x-circle"></i>
                        <span>Rechazado</span>
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
        <?php if ($errorReportes): ?>
          <div class="p-5 text-center bg-white">
            <i class="bi bi-exclamation-triangle text-warning fs-1 d-block mb-2"></i>
            <p class="text-secondary mb-0">No se pudieron cargar los reportes. Intentá nuevamente más tarde.</p>
          </div>
        <?php endif; ?>

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

          <!-- Información adjunta: copia de lo reportado al momento del reporte -->
          <div class="p-3 rounded-3 border mb-2">
            <span class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.68rem;">Información adjunta (copia al momento del reporte):</span>
            <dl class="row mb-0 small" id="detEvidencia"></dl>
          </div>
          <p class="small text-secondary mb-0 mt-2" id="detSancion"></p>

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
              <span>Rechazar Reporte</span>
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
    // 3. Filtrado y Ordenamiento (Discord / Alumnos + Menú Desplegable)
    // ==========================================
    const ITEMS_POR_PAGINA = 5;
    let paginaActualRep = 1;
    let filtroTipoActual = 'todos';     // 'todos' | 'sala' | 'tutoria' | 'mensaje' | 'tutor' | 'alumno'
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

        // Filtro 2: Desplegable (Más nuevos, Pendientes, Finalizados, Rechazados)
        let coincideEstado = true;
        if (filtroOrdenEstado === 'pendiente') {
          coincideEstado = (estado === 'pendiente');
        } else if (filtroOrdenEstado === 'finalizado') {
          coincideEstado = (estado === 'finalizado');
        } else if (filtroOrdenEstado === 'rechazado') {
          coincideEstado = (estado === 'rechazado');
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
      renderEvidencia(rep.evidencia);
      document.getElementById('detSancion').textContent = textoSancion(rep);

      // Configurar botón dinámico según si es Discord o Alumno
      const btnDesactivar = document.getElementById('btnAccionDesactivarModal');
      const iconoDesactivar = document.getElementById('iconoAccionDesactivar');
      const textoDesactivar = document.getElementById('textoAccionDesactivar');
      const btnDescartarModal = document.getElementById('btnDescartarModal');

      if (rep.estado === 'finalizado' || rep.estado === 'rechazado') {
        btnDesactivar.disabled = true;
        btnDesactivar.className = 'btn btn-secondary rounded-pill px-4 disabled';
        iconoDesactivar.className = 'bi bi-check-circle-fill me-1';
        textoDesactivar.textContent = (rep.estado === 'finalizado') ? 'Caso Finalizado' : 'Caso Rechazado';
        if (btnDescartarModal) btnDescartarModal.style.display = 'none';
      } else {
        btnDesactivar.disabled = false;
        btnDesactivar.className = 'btn btn-danger rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm';
        if (btnDescartarModal) btnDescartarModal.style.display = 'inline-flex';
        
        const esSala = rep.tipo === 'sala';
        iconoDesactivar.className = esSala ? 'bi bi-slash-circle me-1' : 'bi bi-person-slash me-1';
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
        dd.className = 'col-sm-8 mb-1 text-dark text-break';
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
          const row = document.getElementById(`row-reporte-${id}`);
          if (row) {
            row.setAttribute('data-estado', 'finalizado');
            row.classList.add('fila-resuelta');

            const badgeSlot = row.querySelector('.estado-badge-slot');
            if (badgeSlot) {
              badgeSlot.innerHTML = `
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1">
                  <i class="bi bi-check-circle-fill"></i>
                  <span>Finalizado</span>
                </span>
              `;
            }
          }

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
          const row = document.getElementById(`row-reporte-${id}`);
          if (row) {
            row.setAttribute('data-estado', 'rechazado');
            row.classList.add('fila-resuelta');

            const badgeSlot = row.querySelector('.estado-badge-slot');
            if (badgeSlot) {
              badgeSlot.innerHTML = `
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1">
                  <i class="bi bi-x-circle"></i>
                  <span>Rechazado</span>
                </span>
              `;
            }
          }

          if (reporteActivoEnModal && reporteActivoEnModal.id_reporte == id) {
            reporteActivoEnModal.estado = 'rechazado';
          }

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
      const rows = document.querySelectorAll('#tablaReportesBody .reporte-row');
      let pendientes = 0;
      const porTipo = {};
      let total = rows.length;

      rows.forEach(r => {
        const est = r.getAttribute('data-estado');
        const tip = r.getAttribute('data-tipo');
        if (est === 'pendiente') pendientes++;
        porTipo[tip] = (porTipo[tip] || 0) + 1;
      });

      const badgeHeader = document.getElementById('badgeHeaderPendientes');
      const badgeNav = document.getElementById('badgeSidebarReportes');
      const countTodos = document.getElementById('countFilterTodos');

      if (badgeHeader) badgeHeader.textContent = `${pendientes} pendientes`;
      if (badgeNav) badgeNav.textContent = pendientes;
      if (countTodos) countTodos.textContent = total;
      document.querySelectorAll('[data-count-tipo]').forEach(el => {
        el.textContent = porTipo[el.getAttribute('data-count-tipo')] || 0;
      });
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
