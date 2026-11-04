<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Study Match - Gestión de Materias y Carreras</title>

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

  <!-- Estilos específicos y optimizados de Gestión de Materias -->
  <link rel="stylesheet" href="public/css/admin-materias.css?v=20261104_crud">
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

        <!-- 3. Gestión de Materias (ACTIVO) -->
        <a href="index.php?r=admin-materias" class="sidebar-link active" title="Gestión de Materias y Carreras">
          <i class="bi bi-journal-bookmark fs-5"></i>
          <span class="sidebar-text">Gestión de Materias</span>
          <span class="badge bg-primary-subtle text-primary ms-auto sidebar-text" style="font-size: 0.68rem;"><?= (int) $cantTotal; ?></span>
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
          <span class="badge bg-warning text-dark rounded-pill ms-auto sidebar-text fw-bold" style="font-size: 0.65rem;"><?= (int) $totalReportes; ?></span>
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
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Materias y Carreras</li>
              </ol>
            </nav>
            <h1 class="h6 fw-bold mb-0 text-dark" style="text-shadow: none;">Gestión de Materias y Carreras</h1>
          </div>
        </div>

        <!-- Acciones: Modo Claro/Oscuro + Perfil Desplegable del Administrador -->
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

    <!-- ÁREA DE CONTENIDO PRINCIPAL -->
    <main class="container-fluid p-4 view-fade">

      <?php
        $e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

        // Años del plan que existen en el catálogo (las materias sin año se agrupan aparte)
        $aniosDisponibles = [];
        foreach ($materias as $m) {
            $aniosDisponibles[$m['anio'] === null ? 'sin' : (string) $m['anio']] = true;
        }
        uksort($aniosDisponibles, function ($a, $b) {
            if ($a === 'sin') return 1;
            if ($b === 'sin') return -1;
            return (int) $a - (int) $b;
        });

        $conTutorias = count(array_filter($materias, function ($m) { return $m['tutorias'] > 0; }));
      ?>

      <!-- ====================================================================
           1. BANNER
           ==================================================================== -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white"
           style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 65%, #1e3a8a 100%);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div>
            <span class="badge bg-primary text-white rounded-pill px-3 py-1 small fw-semibold mb-2 d-inline-block">
              <i class="bi bi-journal-bookmark-fill me-1"></i> Catálogo Académico
            </span>
            <h1 class="h3 fw-bold mb-1 text-white" style="text-shadow: none;">Gestión de Materias y Carreras</h1>
            <p class="text-white text-opacity-75 small mb-0">
              Materias y carreras registradas en StudyMatch, con la cantidad de tutorías publicadas sobre cada una.
            </p>
          </div>
          <button type="button"
                  class="btn btn-outline-light rounded-pill btn-sm px-3 fw-semibold shadow-sm d-inline-flex align-items-center gap-2"
                  data-bs-toggle="modal" data-bs-target="#modalResumenCarreras">
            <i class="bi bi-mortarboard"></i><span>Ver carreras</span>
          </button>
        </div>
      </div>

      <?php if ($errorMaterias): ?>
        <div class="alert alert-warning rounded-4 d-flex align-items-center gap-2" role="alert">
          <i class="bi bi-exclamation-triangle-fill"></i>
          No se pudo cargar el catálogo. Intentá nuevamente más tarde.
        </div>
      <?php else: ?>

      <!-- ====================================================================
           2. RESUMEN (datos reales)
           ==================================================================== -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
            <div class="card-body d-flex align-items-center gap-3">
              <span class="stat-icon stat-icon--blue"><i class="bi bi-mortarboard-fill"></i></span>
              <div>
                <div class="stat-value"><?= (int) $cantCarreras ?></div>
                <div class="stat-label">Carreras</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
            <div class="card-body d-flex align-items-center gap-3">
              <span class="stat-icon stat-icon--violet"><i class="bi bi-book-fill"></i></span>
              <div>
                <div class="stat-value"><?= (int) $cantTotal ?></div>
                <div class="stat-label">Materias</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
            <div class="card-body d-flex align-items-center gap-3">
              <span class="stat-icon stat-icon--green"><i class="bi bi-patch-check-fill"></i></span>
              <div>
                <div class="stat-value"><?= (int) $conTutorias ?></div>
                <div class="stat-label">Con tutorías publicadas</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
            <div class="card-body d-flex align-items-center gap-3">
              <span class="stat-icon stat-icon--amber"><i class="bi bi-hourglass-split"></i></span>
              <div>
                <div class="stat-value"><?= (int) ($cantTotal - $conTutorias) ?></div>
                <div class="stat-label">Sin tutorías todavía</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ====================================================================
           3. CATÁLOGO DE MATERIAS
           ==================================================================== -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <div class="p-4 border-bottom">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
              <h5 class="fw-bold mb-0">Catálogo de materias</h5>
              <span class="text-secondary small">Listado completo del plan académico cargado en el sistema</span>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-primary rounded-pill btn-sm px-3 fw-semibold d-inline-flex align-items-center gap-2" onclick="abrirModalCarrera(0)">
                <i class="bi bi-mortarboard"></i><span>Nueva carrera</span>
              </button>
              <button type="button" class="btn btn-primary rounded-pill btn-sm px-3 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm" onclick="abrirModalMateria(0)" <?= empty($carreras) ? 'disabled title="Primero creá una carrera"' : '' ?>>
                <i class="bi bi-plus-lg"></i><span>Nueva materia</span>
              </button>
            </div>
          </div>

          <div class="row g-2 align-items-center">
            <div class="col-12 col-lg">
              <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
                <input type="text" id="materiaSearchInput" class="form-control rounded-pill ps-5 py-2 search-input-wide shadow-none"
                       style="font-size: 0.9rem;" placeholder="Buscar por materia o carrera...">
              </div>
            </div>
            <div class="col-6 col-lg-auto">
              <select id="filtroCarrera" class="form-select rounded-pill filtro-select shadow-none" aria-label="Filtrar por carrera">
                <option value="">Todas las carreras</option>
                <?php foreach ($carreras as $car): ?>
                  <option value="<?= $e($car['codigo']) ?>"><?= $e($car['codigo']) ?> · <?= $e($car['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-lg-auto">
              <select id="filtroAnio" class="form-select rounded-pill filtro-select shadow-none" aria-label="Filtrar por año">
                <option value="">Todos los años</option>
                <?php foreach (array_keys($aniosDisponibles) as $clave): ?>
                  <option value="<?= $e($clave) ?>"><?= $clave === 'sin' ? 'Sin año cargado' : $e($clave) . '° año' ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-materias align-middle mb-0" id="tablaMateriasAdmin">
            <thead>
              <tr>
                <th scope="col">Materia</th>
                <th scope="col">Carrera</th>
                <th scope="col" style="width: 130px;">Año</th>
                <th scope="col" class="text-end" style="width: 160px;">Tutorías</th>
                <th scope="col" class="text-end" style="width: 190px;">Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaMateriasBody">
              <?php foreach ($materias as $mat): ?>
                <tr class="materia-row"
                    data-carrera="<?= $e($mat['carrera']) ?>"
                    data-anio="<?= $mat['anio'] === null ? 'sin' : (int) $mat['anio'] ?>"
                    id="row-materia-<?= (int) $mat['id_materia'] ?>">
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <span class="materia-icon"><i class="bi bi-journal-text"></i></span>
                      <span class="fw-semibold"><?= $e($mat['nombre']) ?></span>
                    </div>
                  </td>
                  <td>
                    <span class="badge rounded-pill badge-carrera" title="<?= $e($mat['carrera_nombre']) ?>">
                      <?= $e($mat['carrera']) ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($mat['anio'] === null): ?>
                      <span class="text-secondary small">Sin año</span>
                    <?php else: ?>
                      <span class="small fw-semibold"><?= (int) $mat['anio'] ?>° año</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <?php if ($mat['tutorias'] > 0): ?>
                      <span class="badge rounded-pill badge-tutorias">
                        <i class="bi bi-person-check-fill me-1"></i><?= (int) $mat['tutorias'] ?> <?= $mat['tutorias'] === 1 ? 'tutoría' : 'tutorías' ?>
                      </span>
                    <?php else: ?>
                      <span class="badge rounded-pill badge-tutorias badge-tutorias--vacio">Sin tutorías</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <div class="d-inline-flex align-items-center gap-2">
                      <button type="button" class="btn btn-sm btn-action-pill btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-1"
                              onclick="abrirModalMateria(<?= (int) $mat['id_materia'] ?>)" title="Editar materia">
                        <i class="bi bi-pencil"></i><span>Editar</span>
                      </button>
                      <?php if ($mat['tutorias'] > 0): ?>
                        <span class="d-inline-block" tabindex="0" title="No se puede eliminar: tiene tutorías publicadas">
                          <button type="button" class="btn btn-sm btn-action-pill btn-outline-danger d-inline-flex align-items-center justify-content-center gap-1" disabled>
                            <i class="bi bi-trash"></i><span>Eliminar</span>
                          </button>
                        </span>
                      <?php else: ?>
                        <button type="button" class="btn btn-sm btn-action-pill btn-outline-danger d-inline-flex align-items-center justify-content-center gap-1"
                                onclick="confirmarEliminarMateria(<?= (int) $mat['id_materia'] ?>)" title="Eliminar materia">
                          <i class="bi bi-trash"></i><span>Eliminar</span>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div id="sinResultados" class="text-center text-secondary py-5 d-none">
            <i class="bi bi-search fs-3 d-block mb-2"></i>
            <?= empty($materias) ? 'Todavía no hay materias cargadas.' : 'Ninguna materia coincide con los filtros.' ?>
          </div>
        </div>

        <div class="card-footer border-top px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="text-secondary small" id="paginacionInfoTexto"></div>
          <nav aria-label="Navegación del catálogo">
            <ul class="pagination pagination-sm mb-0" id="paginacionControles"></ul>
          </nav>
        </div>

      </div>

      <?php endif; ?>

    </main>
  </div>

  <!-- ==========================================================================
       MODAL: CARRERAS (datos reales)
       ========================================================================== -->
  <div class="modal fade" id="modalResumenCarreras" tabindex="-1" aria-labelledby="modalResumenCarrerasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">

        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3"><i class="bi bi-mortarboard fs-5"></i></div>
            <div>
              <h5 class="modal-title fw-bold" id="modalResumenCarrerasLabel">Carreras</h5>
              <p class="text-secondary small mb-0">Programas académicos cargados en StudyMatch</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body p-4">
          <?php if (empty($carreras)): ?>
            <p class="text-secondary mb-0">Todavía no hay carreras cargadas.</p>
          <?php else: ?>
            <div class="list-group list-group-flush rounded-3 border">
              <?php foreach ($carreras as $car): ?>
                <div class="list-group-item d-flex align-items-center justify-content-between p-3 gap-3">
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge rounded-pill badge-carrera fw-bold"><?= $e($car['codigo']) ?></span>
                    <div>
                      <h6 class="fw-semibold mb-0 small"><?= $e($car['nombre']) ?></h6>
                      <span class="text-secondary" style="font-size: 0.74rem;">
                        <?= (int) $car['materias'] ?> <?= $car['materias'] === 1 ? 'materia' : 'materias' ?>
                      </span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <?php if ($car['activa']): ?>
                      <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill">Activa</span>
                    <?php else: ?>
                      <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle rounded-pill">Inactiva</span>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-action-pill" onclick="abrirModalCarrera(<?= (int) $car['id_carrera'] ?>)" title="Editar carrera">
                      <i class="bi bi-pencil"></i>
                    </button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="modal-footer border-top px-4 py-3 rounded-bottom-4">
          <button type="button" class="btn btn-outline-primary rounded-pill px-3 me-auto" onclick="abrirModalCarrera(0)">
            <i class="bi bi-plus-lg me-1"></i>Nueva carrera
          </button>
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
        </div>

      </div>
    </div>
  </div>

  <!-- ==========================================================================
       MODAL: ALTA / EDICIÓN DE MATERIA
       ========================================================================== -->
  <div class="modal fade" id="modalMateria" tabindex="-1" aria-labelledby="modalMateriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content rounded-4 border-0 shadow-lg" id="formMateria" onsubmit="guardarMateria(event)" novalidate>
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div>
            <h5 class="modal-title fw-bold" id="modalMateriaLabel">Nueva materia</h5>
            <p class="text-secondary small mb-0">Se muestra a alumnos y tutores al publicar o buscar tutorías</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" name="id_materia" id="materiaId">
          <div class="alert alert-danger small py-2 d-none" id="materiaError" role="alert"></div>

          <div class="mb-3">
            <label for="materiaNombre" class="form-label small fw-semibold text-secondary">Nombre <span class="text-danger">*</span></label>
            <input type="text" class="form-control rounded-3" id="materiaNombre" name="nombre" maxlength="255" required placeholder="Ej. Programación III">
          </div>
          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-8">
              <label for="materiaCarrera" class="form-label small fw-semibold text-secondary">Carrera <span class="text-danger">*</span></label>
              <select class="form-select rounded-3" id="materiaCarrera" name="id_carrera" required>
                <?php foreach ($carreras as $car): ?>
                  <option value="<?= (int) $car['id_carrera'] ?>"><?= $e($car['codigo']) ?> · <?= $e($car['nombre']) ?><?= $car['activa'] ? '' : ' (inactiva)' ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-sm-4">
              <label for="materiaAnio" class="form-label small fw-semibold text-secondary">Año del plan</label>
              <select class="form-select rounded-3" id="materiaAnio" name="anio_carrera">
                <option value="">Sin año</option>
                <?php for ($a = 1; $a <= 6; $a++): ?>
                  <option value="<?= $a ?>"><?= $a ?>° año</option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div>
            <label for="materiaDescripcion" class="form-label small fw-semibold text-secondary">Descripción <span class="text-secondary fw-normal">(opcional)</span></label>
            <textarea class="form-control rounded-3" id="materiaDescripcion" name="descripcion" rows="2" maxlength="1000"></textarea>
          </div>
        </div>
        <div class="modal-footer border-top px-4 py-3 rounded-bottom-4">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" id="btnGuardarMateria">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ==========================================================================
       MODAL: ALTA / EDICIÓN DE CARRERA
       ========================================================================== -->
  <div class="modal fade" id="modalCarrera" tabindex="-1" aria-labelledby="modalCarreraLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content rounded-4 border-0 shadow-lg" id="formCarrera" onsubmit="guardarCarrera(event)" novalidate>
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div>
            <h5 class="modal-title fw-bold" id="modalCarreraLabel">Nueva carrera</h5>
            <p class="text-secondary small mb-0">Las carreras inactivas no se ofrecen al publicar tutorías</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" name="id_carrera" id="carreraId">
          <div class="alert alert-danger small py-2 d-none" id="carreraError" role="alert"></div>

          <div class="mb-3">
            <label for="carreraNombre" class="form-label small fw-semibold text-secondary">Nombre <span class="text-danger">*</span></label>
            <input type="text" class="form-control rounded-3" id="carreraNombre" name="nombre" maxlength="150" required placeholder="Ej. Ingeniería en Sistemas de Información">
          </div>
          <div class="mb-3">
            <label for="carreraCodigo" class="form-label small fw-semibold text-secondary">Sigla <span class="text-danger">*</span></label>
            <input type="text" class="form-control rounded-3 text-uppercase" id="carreraCodigo" name="codigo" maxlength="20" required placeholder="Ej. ISI">
          </div>
          <div class="form-check form-switch" id="carreraActivaWrap">
            <input class="form-check-input" type="checkbox" role="switch" id="carreraActiva" checked>
            <label class="form-check-label small" for="carreraActiva">Carrera activa</label>
          </div>
        </div>
        <div class="modal-footer border-top px-4 py-3 rounded-bottom-4">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" id="btnGuardarCarrera">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ==========================================================================
       MODAL: CONFIRMAR ELIMINACIÓN DE MATERIA
       ========================================================================== -->
  <div class="modal fade" id="modalEliminarMateria" tabindex="-1" aria-labelledby="modalEliminarMateriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-danger-subtle text-danger p-2 rounded-3"><i class="bi bi-trash fs-5"></i></div>
            <h5 class="modal-title fw-bold" id="modalEliminarMateriaLabel">Eliminar materia</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body p-4">
          <p class="mb-2">¿Eliminar <strong id="eliminarMateriaNombre"></strong> del catálogo?</p>
          <p class="text-secondary small mb-0">Esta acción no se puede deshacer. Solo se pueden eliminar materias sin tutorías publicadas.</p>
          <div class="alert alert-danger small py-2 mt-3 mb-0 d-none" id="eliminarError" role="alert"></div>
        </div>
        <div class="modal-footer border-top px-4 py-3 rounded-bottom-4">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger rounded-pill px-4 fw-semibold" id="btnConfirmarEliminar" onclick="eliminarMateria()">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- TOAST -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="toastAdminMaterias" class="toast align-items-center text-bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill text-success fs-5"></i>
          <span id="toastAdminMateriasMsg"></span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // ==========================================
    // 1. Colapso / expansión del sidebar
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
    // 2. Búsqueda, filtros y paginación del catálogo
    // ==========================================
    const ITEMS_POR_PAGINA = 10;
    let paginaActual = 1;

    const searchInput = document.getElementById('materiaSearchInput');
    const filtroCarrera = document.getElementById('filtroCarrera');
    const filtroAnio = document.getElementById('filtroAnio');
    const filas = Array.from(document.querySelectorAll('#tablaMateriasBody .materia-row'));

    function actualizarCatalogo() {
      if (!searchInput) return;
      const query = searchInput.value.toLowerCase().trim();

      const coincidentes = filas.filter(row => {
        const texto = row.textContent.toLowerCase();
        return (!query || texto.includes(query))
          && (!filtroCarrera.value || row.dataset.carrera === filtroCarrera.value)
          && (!filtroAnio.value || row.dataset.anio === filtroAnio.value);
      });

      const total = coincidentes.length;
      const totalPaginas = Math.max(1, Math.ceil(total / ITEMS_POR_PAGINA));
      paginaActual = Math.min(Math.max(1, paginaActual), totalPaginas);

      const inicio = (paginaActual - 1) * ITEMS_POR_PAGINA;
      const visibles = new Set(coincidentes.slice(inicio, inicio + ITEMS_POR_PAGINA));
      filas.forEach(row => { row.style.display = visibles.has(row) ? '' : 'none'; });

      document.getElementById('sinResultados').classList.toggle('d-none', total > 0);
      document.getElementById('paginacionInfoTexto').innerHTML = total === 0
        ? ''
        : `Mostrando <strong>${inicio + 1} - ${Math.min(inicio + ITEMS_POR_PAGINA, total)}</strong> de <strong>${total}</strong> materias`;

      dibujarPaginacion(totalPaginas);
    }

    function dibujarPaginacion(totalPaginas) {
      const ul = document.getElementById('paginacionControles');
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
        b.addEventListener('click', () => { paginaActual = pagina; actualizarCatalogo(); });
        li.appendChild(b);
        ul.appendChild(li);
      };

      item('Anterior', paginaActual - 1, { deshabilitado: paginaActual <= 1, clase: 'rounded-start-pill px-3' });
      for (let p = 1; p <= totalPaginas; p++) item(String(p), p, { activo: p === paginaActual });
      item('Siguiente', paginaActual + 1, { deshabilitado: paginaActual >= totalPaginas, clase: 'rounded-end-pill px-3' });
    }

    [searchInput, filtroCarrera, filtroAnio].forEach(el => {
      if (el) el.addEventListener(el === searchInput ? 'input' : 'change', () => { paginaActual = 1; actualizarCatalogo(); });
    });

    // ==========================================
    // 3. Alta, edición y baja (datos reales; la página se recarga al terminar)
    // ==========================================
    const MATERIAS = <?= json_encode(array_column($materias, null, 'id_materia'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const CARRERAS = <?= json_encode(array_column($carreras, null, 'id_carrera'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const CLAVE_MENSAJE = 'study-match-admin-materias-msg';

    function mostrarError(id, texto) {
      const el = document.getElementById(id);
      el.textContent = texto || '';
      el.classList.toggle('d-none', !texto);
    }

    function abrirModal(id) {
      bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show();
    }

    // Envía los datos y, si salió bien, recarga la página para mostrar el catálogo actualizado
    function enviar(ruta, datos, boton, idError) {
      mostrarError(idError, '');
      boton.disabled = true;

      return fetch('index.php?r=' + ruta, { method: 'POST', body: datos })
        .then(res => res.json())
        .then(data => {
          if (data.ok) {
            sessionStorage.setItem(CLAVE_MENSAJE, data.mensaje);
            window.location.reload();
          } else {
            mostrarError(idError, data.error || 'No se pudo completar la acción.');
            boton.disabled = false;
          }
        })
        .catch(() => {
          mostrarError(idError, 'Error de conexión con el servidor.');
          boton.disabled = false;
        });
    }

    // --- Materia ---
    function abrirModalMateria(id) {
      const mat = id ? MATERIAS[id] : null;
      document.getElementById('modalMateriaLabel').textContent = mat ? 'Editar materia' : 'Nueva materia';
      document.getElementById('materiaId').value = mat ? mat.id_materia : '';
      document.getElementById('materiaNombre').value = mat ? mat.nombre : '';
      document.getElementById('materiaAnio').value = mat && mat.anio !== null ? mat.anio : '';
      document.getElementById('materiaDescripcion').value = mat ? mat.descripcion : '';

      const selCarrera = document.getElementById('materiaCarrera');
      if (mat) {
        selCarrera.value = mat.id_carrera;
      } else if (filtroCarrera && filtroCarrera.value) {
        // Si hay una carrera filtrada, la propone como carrera de la materia nueva
        const propuesta = Object.values(CARRERAS).find(c => c.codigo === filtroCarrera.value);
        if (propuesta) selCarrera.value = propuesta.id_carrera;
      }

      mostrarError('materiaError', '');
      document.getElementById('btnGuardarMateria').disabled = false;
      abrirModal('modalMateria');
    }

    function guardarMateria(e) {
      e.preventDefault();
      enviar('admin-materia-guardar', new FormData(e.target), document.getElementById('btnGuardarMateria'), 'materiaError');
    }

    // --- Carrera ---
    function abrirModalCarrera(id) {
      const car = id ? CARRERAS[id] : null;
      document.getElementById('modalCarreraLabel').textContent = car ? 'Editar carrera' : 'Nueva carrera';
      document.getElementById('carreraId').value = car ? car.id_carrera : '';
      document.getElementById('carreraNombre').value = car ? car.nombre : '';
      document.getElementById('carreraCodigo').value = car ? car.codigo : '';
      document.getElementById('carreraActiva').checked = car ? car.activa : true;
      document.getElementById('carreraActivaWrap').classList.toggle('d-none', !car); // una carrera nueva nace activa

      mostrarError('carreraError', '');
      document.getElementById('btnGuardarCarrera').disabled = false;

      // Si se abre desde el modal de carreras, ese se cierra primero
      const listado = bootstrap.Modal.getInstance(document.getElementById('modalResumenCarreras'));
      if (listado) listado.hide();
      abrirModal('modalCarrera');
    }

    function guardarCarrera(e) {
      e.preventDefault();
      const datos = new FormData(e.target);
      datos.append('activa', document.getElementById('carreraActiva').checked ? '1' : '0');
      enviar('admin-carrera-guardar', datos, document.getElementById('btnGuardarCarrera'), 'carreraError');
    }

    // --- Eliminar materia ---
    let idMateriaAEliminar = null;

    function confirmarEliminarMateria(id) {
      idMateriaAEliminar = id;
      document.getElementById('eliminarMateriaNombre').textContent = MATERIAS[id].nombre;
      mostrarError('eliminarError', '');
      document.getElementById('btnConfirmarEliminar').disabled = false;
      abrirModal('modalEliminarMateria');
    }

    function eliminarMateria() {
      if (!idMateriaAEliminar) return;
      const datos = new FormData();
      datos.append('id_materia', idMateriaAEliminar);
      enviar('admin-materia-eliminar', datos, document.getElementById('btnConfirmarEliminar'), 'eliminarError');
    }

    // ==========================================
    // 4. Toast y modo claro / oscuro
    // ==========================================
    function mostrarToast(mensaje) {
      const toastEl = document.getElementById('toastAdminMaterias');
      document.getElementById('toastAdminMateriasMsg').textContent = mensaje;
      bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3000 }).show();
    }

    function aplicarTema(tema) {
      document.documentElement.setAttribute('data-bs-theme', tema);
      localStorage.setItem('study-match-theme', tema);
      const icon = document.getElementById('themeIcon');
      if (icon) icon.className = tema === 'dark' ? 'bi bi-sun-fill text-warning' : 'bi bi-moon-stars text-secondary';
    }

    function toggleTemaModo() {
      const nuevoTema = (document.documentElement.getAttribute('data-bs-theme') || 'light') === 'dark' ? 'light' : 'dark';
      aplicarTema(nuevoTema);
      mostrarToast('Modo ' + (nuevoTema === 'dark' ? 'oscuro' : 'claro') + ' activado');
    }

    document.addEventListener('DOMContentLoaded', () => {
      aplicarTema(localStorage.getItem('study-match-theme') || 'light');
      actualizarCatalogo();

      // Mensaje de la acción que originó la recarga (alta, edición o baja)
      const pendiente = sessionStorage.getItem(CLAVE_MENSAJE);
      if (pendiente) {
        sessionStorage.removeItem(CLAVE_MENSAJE);
        mostrarToast(pendiente);
      }
    });
  </script>
</body>
</html>
