<?php
/**
 * ==========================================================================
 * STUDY MATCH • Gestión de Materias y Carreras (admin/gestion-materias.php)
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
 * Tu compañero de backend podrá conectar esta vista con la base de datos:
 * 
 * require_once __DIR__ . '/../app/config/Database.php';
 * $db = Database::getInstance()->getConnection();
 * 
 * 1. Carreras registradas:
 *    $carreras = $db->query("SELECT c.*, COUNT(m.id_materia) as materias_count 
 *                            FROM carreras c 
 *                            LEFT JOIN materias m ON c.codigo = m.carrera 
 *                            GROUP BY c.id_carrera ORDER BY c.nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
 * 
 * 2. Catálogo de materias:
 *    $materias = $db->query("SELECT id_materia, codigo, nombre, carrera, tipo 
 *                            FROM materias ORDER BY codigo ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
 * ========================================================================== */

// Catálogo de carreras registradas en la facultad
$carreras = [
    [
        'id_carrera'      => 1,
        'codigo'          => 'TUP',
        'nombre'          => 'Tecnicatura Universitaria en Programación',
        'color'           => 'primary',
        'icono'           => 'bi-code-slash',
        'materias_count'  => 3
    ],
    [
        'id_carrera'      => 2,
        'codigo'          => 'TGE',
        'nombre'          => 'Tecnicatura en Gestión Empresarial',
        'color'           => 'info',
        'icono'           => 'bi-briefcase-fill',
        'materias_count'  => 1
    ],
    [
        'id_carrera'      => 3,
        'codigo'          => 'ISI',
        'nombre'          => 'Ingeniería en Sistemas de Información',
        'color'           => 'success',
        'icono'           => 'bi-cpu-fill',
        'materias_count'  => 0
    ],
    [
        'id_carrera'      => 4,
        'codigo'          => 'LAR',
        'nombre'          => 'Licenciatura en Administración Rural',
        'color'           => 'warning',
        'icono'           => 'bi-tree-fill',
        'materias_count'  => 0
    ]
];

// Datos simulados del catálogo general de materias (5 ejemplos representativos)
$materias = [
    [
        'id_materia' => 1,
        'codigo'     => 'TUP-01',
        'nombre'     => 'Programación I (C++)',
        'carrera'    => 'TUP',
        'tipo'       => 'carrera'
    ],
    [
        'id_materia' => 2,
        'codigo'     => 'TUP-02',
        'nombre'     => 'Sistemas de Procesamiento de Datos',
        'carrera'    => 'TUP',
        'tipo'       => 'carrera'
    ],
    [
        'id_materia' => 3,
        'codigo'     => 'GEN-01',
        'nombre'     => 'Inglés Técnico I',
        'carrera'    => 'General',
        'tipo'       => 'libre'
    ],
    [
        'id_materia' => 4,
        'codigo'     => 'TGE-01',
        'nombre'     => 'Principios de Administración y Organización',
        'carrera'    => 'TGE',
        'tipo'       => 'carrera'
    ],
    [
        'id_materia' => 5,
        'codigo'     => 'TUP-03',
        'nombre'     => 'Bases de Datos Relacionales (SQL)',
        'carrera'    => 'TUP',
        'tipo'       => 'carrera'
    ]
];

// Contadores rápidos para métricas y filtros
$cantTotal    = count($materias);
$cantCarreras = count($carreras);
$cantLibres   = count(array_filter($materias, fn($m) => $m['carrera'] === 'General' || $m['tipo'] === 'libre'));
$cantTUP      = count(array_filter($materias, fn($m) => $m['carrera'] === 'TUP'));
$cantTGE      = count(array_filter($materias, fn($m) => $m['carrera'] === 'TGE'));
$cantISI      = count(array_filter($materias, fn($m) => $m['carrera'] === 'ISI'));
?>
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
  <link rel="stylesheet" href="../css/dashboard.css?v=20261029_admin_clean">

  <!-- Estilos globales de Administración (Fondo img_admin.png desenfocado, sidebar claro gris azulado / oscuro grafito) -->
  <link rel="stylesheet" href="../css/admin-global.css?v=20261029_v3">

  <!-- Estilos específicos y optimizados de Gestión de Materias -->
  <link rel="stylesheet" href="../css/admin-materias.css?v=20261029_v3">
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

        <!-- 3. Gestión de Materias (ACTIVO) -->
        <a href="gestion-materias.php" class="sidebar-link active" title="Gestión de Materias y Carreras">
          <i class="bi bi-journal-bookmark fs-5"></i>
          <span class="sidebar-text">Gestión de Materias</span>
          <span class="badge bg-primary-subtle text-primary ms-auto sidebar-text" style="font-size: 0.68rem;"><?= $cantTotal; ?></span>
        </a>

        <!-- 4. Salas Públicas -->
        <a href="salas.php" class="sidebar-link" title="Salas Públicas">
          <i class="bi bi-broadcast fs-5 text-danger"></i>
          <span class="sidebar-text">Salas Públicas</span>
          <span class="badge bg-danger text-white rounded-pill ms-auto sidebar-text" style="font-size: 0.65rem;">6 en vivo</span>
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
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Materias y Carreras</li>
              </ol>
            </nav>
            <h1 class="h6 fw-bold mb-0 text-dark">Gestión de Materias y Carreras</h1>
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
           1. BANNER DE GESTIÓN DE MATERIAS Y CARRERAS
           ==================================================================== -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white" 
           style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 65%, #1e3a8a 100%);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div>
            <span class="badge bg-primary text-white rounded-pill px-3 py-1 small fw-semibold mb-2 d-inline-block">
              <i class="bi bi-journal-bookmark-fill me-1"></i> Catálogo Académico
            </span>
            <h1 class="h3 fw-bold mb-1 text-white">
              Gestión de Materias y Carreras
            </h1>
            <p class="text-white text-opacity-75 small mb-0">
              Registrá nuevas carreras y materias para el apoyo académico de la UTN.
            </p>
          </div>

          <!-- Badges de métricas y acceso al resumen flotante -->
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-3 py-2 small border border-white border-opacity-15">
              <i class="bi bi-mortarboard me-1"></i> <strong><?= $cantCarreras; ?></strong> Carreras
            </span>
            <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-3 py-2 small border border-white border-opacity-15">
              <i class="bi bi-book me-1"></i> <strong><?= $cantTotal; ?></strong> Materias
            </span>

            <!-- Botón flotante para ver resumen de carreras (máximo 5 informaciones) -->
            <button type="button" 
                    class="btn btn-outline-light rounded-pill btn-sm px-3 fw-semibold shadow-sm ms-lg-2 d-inline-flex align-items-center gap-1.5"
                    data-bs-toggle="modal" 
                    data-bs-target="#modalResumenCarreras"
                    title="Ver resumen rápido del catálogo">
              <i class="bi bi-eye"></i>
              <span>Ver Carreras</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ====================================================================
           2. TARJETA HORIZONTAL SIMPLIFICADA: REGISTRO DE MATERIA Y CARRERA
           ==================================================================== -->
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        
        <!-- Pestañas horizontales -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
          <ul class="nav nav-pills nav-pills-horizontal gap-2" id="pillsRegistro" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active d-inline-flex align-items-center gap-1.5" 
                      id="tab-materia-btn" 
                      data-bs-toggle="pill" 
                      data-bs-target="#tab-materia-pane" 
                      type="button" 
                      role="tab" 
                      aria-controls="tab-materia-pane" 
                      aria-selected="true">
                <i class="bi bi-journal-plus"></i>
                <span>Registrar Materia</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link d-inline-flex align-items-center gap-1.5" 
                      id="tab-carrera-btn" 
                      data-bs-toggle="pill" 
                      data-bs-target="#tab-carrera-pane" 
                      type="button" 
                      role="tab" 
                      aria-controls="tab-carrera-pane" 
                      aria-selected="false">
                <i class="bi bi-mortarboard"></i>
                <span>Registrar Carrera</span>
              </button>
            </li>
          </ul>

          <span class="text-secondary small d-none d-md-inline" style="font-size: 0.76rem;">
            <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Carga ágil al catálogo académico
          </span>
        </div>

        <div class="tab-content" id="pillsRegistroContent">
          
          <!-- PESTAÑA A: REGISTRAR MATERIA (HORIZONTAL) -->
          <div class="tab-pane fade show active" id="tab-materia-pane" role="tabpanel" aria-labelledby="tab-materia-btn">
            <form id="formCargaMateria" onsubmit="simularGuardadoMateria(event)">
              <div class="row g-3 align-items-end">
                
                <!-- 1. Input: Nombre de la Materia -->
                <div class="col-12 col-md-5 col-lg-4">
                  <label for="nombreMateria" class="form-label small fw-semibold text-secondary mb-1">
                    Nombre de la Materia <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0">
                      <i class="bi bi-book"></i>
                    </span>
                    <input type="text" 
                           name="nombre_materia" 
                           id="nombreMateria" 
                           class="form-control border-start-0 ps-1" 
                           placeholder="Ej. Paradigmas de Programación" 
                           required>
                  </div>
                </div>

                <!-- 2. Input: Código interno -->
                <div class="col-12 col-md-3 col-lg-2">
                  <label for="codigoMateria" class="form-label small fw-semibold text-secondary mb-1">
                    Código <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0">
                      <i class="bi bi-upc-scan"></i>
                    </span>
                    <input type="text" 
                           name="codigo_materia" 
                           id="codigoMateria" 
                           class="form-control border-start-0 ps-1" 
                           placeholder="Ej. TUP-05" 
                           style="text-transform: uppercase;" 
                           required>
                  </div>
                </div>

                <!-- 3. Selector: Carrera con atajo para agregar carrera -->
                <div class="col-12 col-md-4 col-lg-4">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="carreraMateria" class="form-label small fw-semibold text-secondary mb-0">
                      Asociar a Carrera
                    </label>
                    <button type="button" 
                            class="btn btn-link btn-sm p-0 text-decoration-none text-primary fw-semibold" 
                            style="font-size: 0.74rem;" 
                            onclick="irARegistrarCarrera()"
                            title="Ir a registrar una nueva carrera">
                      <i class="bi bi-plus-circle"></i> + Agregar Carrera
                    </button>
                  </div>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0">
                      <i class="bi bi-mortarboard"></i>
                    </span>
                    <select name="carrera_materia" id="carreraMateria" class="form-select border-start-0 ps-1">
                      <option value="" selected>General / Libre en catálogo</option>
                      <?php foreach ($carreras as $car): ?>
                        <option value="<?= htmlspecialchars($car['codigo']); ?>">
                          <?= htmlspecialchars($car['nombre']); ?> (<?= htmlspecialchars($car['codigo']); ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <!-- 4. Botón Guardar Materia -->
                <div class="col-12 col-lg-2">
                  <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                    <i class="bi bi-plus-lg"></i>
                    <span>Guardar</span>
                  </button>
                </div>

              </div>
            </form>
          </div>

          <!-- PESTAÑA B: REGISTRAR CARRERA (HORIZONTAL - SIN FACULTAD/SEDE) -->
          <div class="tab-pane fade" id="tab-carrera-pane" role="tabpanel" aria-labelledby="tab-carrera-btn">
            <form id="formCargaCarrera" onsubmit="crearNuevaCarreraHorizontal(event)">
              <div class="row g-3 align-items-end">
                
                <!-- 1. Input: Nombre de la Carrera -->
                <div class="col-12 col-md-7 col-lg-7">
                  <label for="nuevaCarreraNombre" class="form-label small fw-semibold text-secondary mb-1">
                    Nombre de la Carrera <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0">
                      <i class="bi bi-mortarboard-fill"></i>
                    </span>
                    <input type="text" 
                           id="nuevaCarreraNombre" 
                           class="form-control border-start-0 ps-1" 
                           placeholder="Ej. Tecnicatura Universitaria en Telecomunicaciones" 
                           required>
                  </div>
                </div>

                <!-- 2. Input: Sigla o Código -->
                <div class="col-12 col-md-3 col-lg-3">
                  <label for="nuevaCarreraCodigo" class="form-label small fw-semibold text-secondary mb-1">
                    Sigla / Código <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0">
                      <i class="bi bi-tag"></i>
                    </span>
                    <input type="text" 
                           id="nuevaCarreraCodigo" 
                           class="form-control border-start-0 ps-1" 
                           placeholder="Ej. TUT" 
                           style="text-transform: uppercase;" 
                           required>
                  </div>
                </div>

                <!-- 3. Botón Guardar Carrera -->
                <div class="col-12 col-md-2 col-lg-2">
                  <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm text-white">
                    <i class="bi bi-check-lg"></i>
                    <span>Crear Carrera</span>
                  </button>
                </div>

              </div>
            </form>
          </div>

        </div>

      </div>

      <!-- ====================================================================
           3. CATÁLOGO GENERAL DE MATERIAS (LIMPIO, MAX 5 POR VISTA, PAGINACIÓN)
           ==================================================================== -->
      <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        
        <!-- Cabecera de la tabla con Buscador y Filtros Rápidos -->
        <div class="p-4 border-bottom">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
              <h5 class="fw-bold text-dark mb-0">Catálogo General de Materias</h5>
              <span class="text-secondary small">Listado de contenidos del plan académico de StudyMatch</span>
            </div>

            <!-- Filtros rápidos -->
            <div class="btn-group btn-group-sm rounded-pill p-1 bg-light border flex-wrap" role="group" aria-label="Filtro por carrera">
              <button type="button" class="btn btn-sm btn-white active rounded-pill fw-semibold px-3 shadow-sm filter-btn-carrera" onclick="filtrarMaterias('todas', this)">
                Todas <span class="badge bg-secondary-subtle text-dark ms-1"><?= $cantTotal; ?></span>
              </button>
              <button type="button" class="btn btn-sm text-secondary rounded-pill fw-semibold px-2.5 filter-btn-carrera" onclick="filtrarMaterias('TUP', this)">
                TUP (<?= $cantTUP; ?>)
              </button>
              <button type="button" class="btn btn-sm text-secondary rounded-pill fw-semibold px-2.5 filter-btn-carrera" onclick="filtrarMaterias('TGE', this)">
                Gestión (<?= $cantTGE; ?>)
              </button>
              <button type="button" class="btn btn-sm text-secondary rounded-pill fw-semibold px-2.5 filter-btn-carrera" onclick="filtrarMaterias('ISI', this)">
                ISI (<?= $cantISI; ?>)
              </button>
              <button type="button" class="btn btn-sm text-secondary rounded-pill fw-semibold px-2.5 filter-btn-carrera" onclick="filtrarMaterias('libre', this)">
                Libres (<?= $cantLibres; ?>)
              </button>
            </div>
          </div>

          <!-- Input de búsqueda amplia -->
          <div class="position-relative w-100">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3.5 text-secondary"></i>
            <input type="text" 
                   id="materiaSearchInput" 
                   class="form-control rounded-pill ps-5 py-2 bg-light border shadow-none" 
                   style="font-size: 0.9rem;" 
                   placeholder="Buscar por código de materia, nombre o carrera (ej. Programación, TUP, Inglés)...">
          </div>
        </div>

        <!-- Tabla de materias limpia (Máximo 5 elementos por vista) -->
        <div class="table-responsive">
          <table class="table table-materias align-middle mb-0" id="tablaMateriasAdmin">
            <thead>
              <tr>
                <th scope="col" style="width: 120px;">Código</th>
                <th scope="col">Nombre de la Materia</th>
                <th scope="col">Carrera</th>
                <th scope="col" class="text-end" style="width: 220px;">Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaMateriasBody">
              
              <!-- Filas del catálogo -->
              <?php foreach ($materias as $index => $mat): ?>
                <?php
                  $colorBadge = match ($mat['carrera']) {
                      'TUP' => 'primary',
                      'TGE' => 'info',
                      'ISI' => 'success',
                      'LAR' => 'warning',
                      default => 'secondary'
                  };
                ?>
                <tr data-carrera="<?= htmlspecialchars($mat['carrera']); ?>" 
                    data-tipo="<?= $mat['tipo']; ?>" 
                    id="row-materia-<?= $mat['id_materia']; ?>"
                    class="materia-row">
                  
                  <!-- Columna: Código Interno -->
                  <td>
                    <span class="materia-code-badge bg-light text-dark border">
                      <?= htmlspecialchars($mat['codigo']); ?>
                    </span>
                  </td>

                  <!-- Columna: Nombre de la Materia -->
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <i class="bi bi-journal-text text-primary"></i>
                      <span class="fw-bold text-dark materia-nombre-slot">
                        <?= htmlspecialchars($mat['nombre']); ?>
                      </span>
                    </div>
                  </td>

                  <!-- Columna: Carrera (Badge limpio único) -->
                  <td class="materia-carrera-slot">
                    <?php if ($mat['carrera'] === 'General' || $mat['tipo'] === 'libre'): ?>
                      <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                        General
                      </span>
                    <?php else: ?>
                      <span class="badge bg-<?= $colorBadge; ?>-subtle text-<?= $colorBadge; ?> border border-<?= $colorBadge; ?>-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                        <?= htmlspecialchars($mat['carrera']); ?>
                      </span>
                    <?php endif; ?>
                  </td>

                  <!-- Columna: Acciones [Editar] y [Eliminar] -->
                  <td class="text-end">
                    <div class="d-inline-flex align-items-center gap-1.5">
                      
                      <!-- Botón [Editar] -->
                      <button type="button" 
                              class="btn btn-sm btn-light border text-secondary btn-action-pill d-inline-flex align-items-center gap-1 shadow-sm"
                              onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($mat)); ?>)"
                              title="Editar materia">
                        <i class="bi bi-pencil"></i>
                        <span>Editar</span>
                      </button>

                      <!-- Botón [Eliminar] -->
                      <button type="button" 
                              class="btn btn-sm btn-outline-danger btn-action-pill d-inline-flex align-items-center gap-1"
                              onclick="confirmarEliminarMateria(<?= $mat['id_materia']; ?>, '<?= addslashes($mat['nombre']); ?>')"
                              title="Eliminar materia">
                        <i class="bi bi-trash"></i>
                        <span class="d-none d-md-inline">Eliminar</span>
                      </button>

                    </div>
                  </td>

                </tr>
              <?php endforeach; ?>

            </tbody>
          </table>
        </div>

        <!-- Footer con paginación resumida y correcta (5 elementos por página) -->
        <div class="card-footer bg-white border-top px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="text-secondary small" id="paginacionInfoTexto">
            Mostrando <strong>1 - 5</strong> de <strong><?= $cantTotal; ?></strong> materias registradas
          </div>
          
          <nav aria-label="Navegación del catálogo">
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

      </div>

    </main>
  </div>

  <!-- ==========================================================================
       VENTANA FLOTANTE (MODAL): RESUMEN DE CARRERAS Y MATERIAS
       (Máximo 5 informaciones resumidas para consulta rápida)
       ========================================================================== -->
  <div class="modal fade" id="modalResumenCarreras" tabindex="-1" aria-labelledby="modalResumenCarrerasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
              <i class="bi bi-mortarboard fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark" id="modalResumenCarrerasLabel">Resumen de Carreras</h5>
              <p class="text-secondary small mb-0">Programas académicos activos en StudyMatch</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body p-4">
          <div class="list-group list-group-flush rounded-3 border">
            
            <!-- 1. TUP -->
            <div class="list-group-item d-flex align-items-center justify-content-between p-3">
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border rounded-pill px-2.5 py-1 fw-bold">TUP</span>
                <div>
                  <h6 class="fw-semibold text-dark mb-0 small">Tecnicatura Universitaria en Programación</h6>
                  <span class="text-muted" style="font-size: 0.72rem;">Pregrado • 5 materias</span>
                </div>
              </div>
              <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">Activa</span>
            </div>

            <!-- 2. TGE -->
            <div class="list-group-item d-flex align-items-center justify-content-between p-3">
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-info-subtle text-info-emphasis border rounded-pill px-2.5 py-1 fw-bold">TGE</span>
                <div>
                  <h6 class="fw-semibold text-dark mb-0 small">Tecnicatura en Gestión Empresarial</h6>
                  <span class="text-muted" style="font-size: 0.72rem;">Pregrado • 4 materias</span>
                </div>
              </div>
              <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">Activa</span>
            </div>

            <!-- 3. ISI -->
            <div class="list-group-item d-flex align-items-center justify-content-between p-3">
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success border rounded-pill px-2.5 py-1 fw-bold">ISI</span>
                <div>
                  <h6 class="fw-semibold text-dark mb-0 small">Ingeniería en Sistemas de Información</h6>
                  <span class="text-muted" style="font-size: 0.72rem;">Grado • 3 materias</span>
                </div>
              </div>
              <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">Activa</span>
            </div>

            <!-- 4. LAR -->
            <div class="list-group-item d-flex align-items-center justify-content-between p-3">
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill px-2.5 py-1 fw-bold">LAR</span>
                <div>
                  <h6 class="fw-semibold text-dark mb-0 small">Licenciatura en Administración Rural</h6>
                  <span class="text-muted" style="font-size: 0.72rem;">Grado • 1 materia</span>
                </div>
              </div>
              <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">Activa</span>
            </div>

            <!-- 5. Libres / Generales -->
            <div class="list-group-item d-flex align-items-center justify-content-between p-3 bg-light bg-opacity-50">
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1 fw-bold">LIBRE</span>
                <div>
                  <h6 class="fw-semibold text-dark mb-0 small">Materias Libres en Catálogo</h6>
                  <span class="text-muted" style="font-size: 0.72rem;">Transversales • 2 materias</span>
                </div>
              </div>
              <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">General</span>
            </div>

          </div>
        </div>

        <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
        </div>

      </div>
    </div>
  </div>

  <!-- ==========================================================================
       MODAL: EDITAR MATERIA
       ========================================================================== -->
  <div class="modal fade" id="modalEditarMateria" tabindex="-1" aria-labelledby="modalEditarMateriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
              <i class="bi bi-pencil-square fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark" id="modalEditarMateriaLabel">Editar Materia</h5>
              <p class="text-secondary small mb-0">Modificá los datos del catálogo académico</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <form id="formEditarMateriaModal" onsubmit="guardarEdicionMateria(event)">
          <div class="modal-body p-4">
            <input type="hidden" id="editMateriaId">

            <div class="mb-3">
              <label for="editNombreMateria" class="form-label small fw-semibold text-secondary">Nombre de la Materia</label>
              <input type="text" class="form-control rounded-3" id="editNombreMateria" required>
            </div>

            <div class="mb-3">
              <label for="editCodigoMateria" class="form-label small fw-semibold text-secondary">Código interno</label>
              <input type="text" class="form-control rounded-3" id="editCodigoMateria" style="text-transform: uppercase;" required>
            </div>

            <div class="mb-3">
              <label for="editCarreraMateria" class="form-label small fw-semibold text-secondary">Carrera Asociada</label>
              <select class="form-select rounded-3" id="editCarreraMateria">
                <option value="General">General / Libre en catálogo</option>
                <?php foreach ($carreras as $car): ?>
                  <option value="<?= htmlspecialchars($car['codigo']); ?>">
                    <?= htmlspecialchars($car['nombre']); ?> (<?= htmlspecialchars($car['codigo']); ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Guardar Cambios</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- ==========================================================================
       MODAL: CONFIRMAR ELIMINAR MATERIA
       ========================================================================== -->
  <div class="modal fade" id="modalEliminarMateria" tabindex="-1" aria-labelledby="modalEliminarMateriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-danger-subtle text-danger p-2 rounded-3">
              <i class="bi bi-trash fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark" id="modalEliminarMateriaLabel">Eliminar Materia</h5>
              <p class="text-secondary small mb-0">Esta acción removerá la materia del catálogo</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body p-4">
          <p class="text-secondary mb-2">
            ¿Estás seguro de que deseas eliminar la materia <strong class="text-dark" id="eliminarMateriaNombreDisplay">Programación I</strong>?
          </p>
          <div class="alert alert-warning small mb-0 rounded-3">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Esta acción quitará la materia de la vista de alumnos y tutores.
          </div>
        </div>

        <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger rounded-pill px-4 fw-semibold" onclick="ejecutarEliminarMateria()">
            Confirmar Eliminación
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- TOAST DE NOTIFICACIÓN DE ACCIONES -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="toastAdminMaterias" class="toast align-items-center text-bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill text-success fs-5"></i>
          <span id="toastAdminMateriasMsg">Acción procesada correctamente.</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       JAVASCRIPT: COMPORTAMIENTOS, BÚSQUEDA Y PAGINACIÓN DE 5 ELEMENTOS
       ========================================================================== -->
  <!-- Bootstrap 5.3.3 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // ==========================================
    // 1. Colapso / Expansión del Sidebar
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
    // 2. Control de Paginación (Máximo 5 por vista)
    // ==========================================
    const ITEMS_POR_PAGINA = 5;
    let paginaActual = 1;
    let filasFiltradas = [];

    function actualizarPaginacion() {
      const todasLasFilas = Array.from(document.querySelectorAll('#tablaMateriasBody .materia-row'));
      const query = (document.getElementById('materiaSearchInput')?.value || '').toLowerCase().trim();

      // Filtrar filas según búsqueda y categoría seleccionada
      filasFiltradas = todasLasFilas.filter(row => {
        const text = row.textContent.toLowerCase();
        const carrera = row.getAttribute('data-carrera') || '';
        const tipo = row.getAttribute('data-tipo') || '';

        const coincideTexto = !query || text.includes(query);
        let coincideCarrera = false;

        if (filtroCarreraActual === 'todas') {
          coincideCarrera = true;
        } else if (filtroCarreraActual === 'libre') {
          coincideCarrera = (tipo === 'libre' || carrera === 'General');
        } else {
          coincideCarrera = (carrera === filtroCarreraActual);
        }

        return coincideTexto && coincideCarrera;
      });

      const totalFiltradas = filasFiltradas.length;
      const totalPaginas = Math.ceil(totalFiltradas / ITEMS_POR_PAGINA) || 1;

      // Asegurar que la página actual esté en rango
      if (paginaActual > totalPaginas) paginaActual = totalPaginas;
      if (paginaActual < 1) paginaActual = 1;

      // Ocultar todas las filas primero
      todasLasFilas.forEach(row => row.style.display = 'none');

      // Mostrar solo las del rango de la página actual
      const inicio = (paginaActual - 1) * ITEMS_POR_PAGINA;
      const fin = inicio + ITEMS_POR_PAGINA;
      const visibles = filasFiltradas.slice(inicio, fin);
      visibles.forEach(row => row.style.display = '');

      // Actualizar texto de información
      const infoTexto = document.getElementById('paginacionInfoTexto');
      if (infoTexto) {
        if (totalFiltradas === 0) {
          infoTexto.innerHTML = 'No se encontraron materias que coincidan con la búsqueda.';
        } else {
          const primerNum = inicio + 1;
          const ultimoNum = Math.min(fin, totalFiltradas);
          infoTexto.innerHTML = `Mostrando <strong>${primerNum} - ${ultimoNum}</strong> de <strong>${totalFiltradas}</strong> materias registradas`;
        }
      }

      // Actualizar botones de paginación
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

    function irAPagina(num) {
      paginaActual = num;
      actualizarPaginacion();
    }

    function cambiarPagina(accion) {
      if (accion === 'prev') {
        irAPagina(paginaActual - 1);
      } else if (accion === 'next') {
        irAPagina(paginaActual + 1);
      }
    }

    // ==========================================
    // 3. Buscador en Tiempo Real
    // ==========================================
    const searchInput = document.getElementById('materiaSearchInput');
    let filtroCarreraActual = 'todas';

    if (searchInput) {
      searchInput.addEventListener('input', () => {
        paginaActual = 1;
        actualizarPaginacion();
      });
    }

    // ==========================================
    // 4. Filtro Rápido por Carrera
    // ==========================================
    function filtrarMaterias(codigoCarrera, btn) {
      filtroCarreraActual = codigoCarrera;
      paginaActual = 1;

      document.querySelectorAll('.filter-btn-carrera').forEach(b => {
        b.classList.remove('btn-white', 'active', 'shadow-sm');
        b.classList.add('text-secondary');
      });
      btn.classList.remove('text-secondary');
      btn.classList.add('btn-white', 'active', 'shadow-sm');

      actualizarPaginacion();
    }

    // ==========================================
    // 5. Toast de Feedback (Reutiliza instancia)
    // ==========================================
    function mostrarToast(mensaje) {
      const toastEl = document.getElementById('toastAdminMaterias');
      const toastMsg = document.getElementById('toastAdminMateriasMsg');
      if (toastEl && toastMsg) {
        toastMsg.textContent = mensaje;
        const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3000 });
        toast.show();
      }
    }

    // ==========================================
    // 6. Transición entre pestañas horizontales (Ágil y optimizada)
    // ==========================================
    function irARegistrarCarrera() {
      const tabCarreraBtn = document.getElementById('tab-carrera-btn');
      if (tabCarreraBtn) {
        const tab = bootstrap.Tab.getOrCreateInstance(tabCarreraBtn);
        tab.show();
      }
    }

    function irARegistrarMateria() {
      const tabMateriaBtn = document.getElementById('tab-materia-btn');
      if (tabMateriaBtn) {
        const tab = bootstrap.Tab.getOrCreateInstance(tabMateriaBtn);
        tab.show();
      }
    }

    // ==========================================
    // 7. Guardar Carrera en la Tarjeta Horizontal (Sin Sede)
    // ==========================================
    function crearNuevaCarreraHorizontal(e) {
      e.preventDefault();
      const nombre = document.getElementById('nuevaCarreraNombre').value.trim();
      const codigo = document.getElementById('nuevaCarreraCodigo').value.trim().toUpperCase();

      if (!nombre || !codigo) return;

      // 1. Agregar al select del formulario de materias
      const selMateria = document.getElementById('carreraMateria');
      if (selMateria) {
        const opt = document.createElement('option');
        opt.value = codigo;
        opt.textContent = `${nombre} (${codigo})`;
        selMateria.appendChild(opt);
        selMateria.value = codigo; // Seleccionarla automáticamente
      }

      // 2. Agregar al select del modal de edición
      const selEdit = document.getElementById('editCarreraMateria');
      if (selEdit) {
        const optEdit = document.createElement('option');
        optEdit.value = codigo;
        optEdit.textContent = `${nombre} (${codigo})`;
        selEdit.appendChild(optEdit);
      }

      // 3. Limpiar formulario de carrera
      document.getElementById('formCargaCarrera').reset();

      // 4. Cambiar inmediatamente a la pestaña de Registrar Materia
      irARegistrarMateria();

      mostrarToast(`¡Carrera "${nombre}" (${codigo}) creada y seleccionada!`);
    }

    // ==========================================
    // 8. Simulación de Guardado de Materia
    // ==========================================
    function simularGuardadoMateria(e) {
      e.preventDefault();
      const nombre = document.getElementById('nombreMateria').value.trim();
      const codigo = document.getElementById('codigoMateria').value.trim().toUpperCase();
      const selectCarrera = document.getElementById('carreraMateria');
      const carreraCodigo = selectCarrera.value || 'General';
      const esLibre = (carreraCodigo === 'General' || carreraCodigo === '');

      const randomId = Math.floor(Math.random() * 9000) + 1000;
      const tbody = document.getElementById('tablaMateriasBody');

      if (tbody) {
        const tr = document.createElement('tr');
        tr.id = `row-materia-${randomId}`;
        tr.className = 'materia-row';
        tr.setAttribute('data-carrera', carreraCodigo);
        tr.setAttribute('data-tipo', esLibre ? 'libre' : 'carrera');

        // Badge limpio y único
        let badgeHtml = '';
        if (esLibre) {
          badgeHtml = `
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
              General
            </span>
          `;
        } else {
          badgeHtml = `
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
              ${carreraCodigo}
            </span>
          `;
        }

        const matObj = {
          id_materia: randomId,
          codigo: codigo,
          nombre: nombre,
          carrera: carreraCodigo,
          tipo: esLibre ? 'libre' : 'carrera'
        };

        tr.innerHTML = `
          <td>
            <span class="materia-code-badge bg-light text-dark border">
              ${codigo}
            </span>
          </td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-journal-text text-primary"></i>
              <span class="fw-bold text-dark materia-nombre-slot">${nombre}</span>
            </div>
          </td>
          <td class="materia-carrera-slot">
            ${badgeHtml}
          </td>
          <td class="text-end">
            <div class="d-inline-flex align-items-center gap-1.5">
              <button type="button" class="btn btn-sm btn-light border text-secondary btn-action-pill d-inline-flex align-items-center gap-1 shadow-sm" onclick='abrirModalEditar(${JSON.stringify(matObj)})'>
                <i class="bi bi-pencil"></i>
                <span>Editar</span>
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger btn-action-pill d-inline-flex align-items-center gap-1" onclick="confirmarEliminarMateria(${randomId}, '${nombre.replace(/'/g, "\\'")}')">
                <i class="bi bi-trash"></i>
                <span class="d-none d-md-inline">Eliminar</span>
              </button>
            </div>
          </td>
        `;

        tbody.insertBefore(tr, tbody.firstChild);
      }

      // Reiniciar a página 1 y refrescar vista de 5 elementos
      paginaActual = 1;
      actualizarPaginacion();

      mostrarToast(`¡Materia "${nombre}" (${codigo}) guardada exitosamente!`);
      document.getElementById('formCargaMateria').reset();
    }

    // ==========================================
    // 9. Modal [Editar] (Reutiliza instancia)
    // ==========================================
    function abrirModalEditar(mat) {
      document.getElementById('editMateriaId').value = mat.id_materia;
      document.getElementById('editNombreMateria').value = mat.nombre;
      document.getElementById('editCodigoMateria').value = mat.codigo;
      
      const selEdit = document.getElementById('editCarreraMateria');
      if (selEdit) {
        selEdit.value = mat.carrera || 'General';
      }

      const modalEl = document.getElementById('modalEditarMateria');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }

    function guardarEdicionMateria(e) {
      e.preventDefault();
      const id = document.getElementById('editMateriaId').value;
      const nombre = document.getElementById('editNombreMateria').value;
      const nuevaCarrera = document.getElementById('editCarreraMateria').value;

      const row = document.getElementById(`row-materia-${id}`);
      if (row) {
        const nombreSlot = row.querySelector('.materia-nombre-slot');
        if (nombreSlot) nombreSlot.textContent = nombre;

        const carreraSlot = row.querySelector('.materia-carrera-slot');
        row.setAttribute('data-carrera', nuevaCarrera);
        row.setAttribute('data-tipo', (nuevaCarrera === 'General' ? 'libre' : 'carrera'));

        if (carreraSlot) {
          if (nuevaCarrera === 'General') {
            carreraSlot.innerHTML = `
              <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                General
              </span>
            `;
          } else {
            carreraSlot.innerHTML = `
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                ${nuevaCarrera}
              </span>
            `;
          }
        }
      }

      const modalEl = document.getElementById('modalEditarMateria');
      const modalInst = bootstrap.Modal.getInstance(modalEl);
      if (modalInst) modalInst.hide();

      actualizarPaginacion();
      mostrarToast(`Materia actualizada correctamente.`);
    }

    // ==========================================
    // 10. Modal [Eliminar] (Reutiliza instancia)
    // ==========================================
    let idMateriaAEliminar = null;

    function confirmarEliminarMateria(id, nombre) {
      idMateriaAEliminar = id;
      document.getElementById('eliminarMateriaNombreDisplay').textContent = nombre;

      const modalEl = document.getElementById('modalEliminarMateria');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }

    function ejecutarEliminarMateria() {
      if (!idMateriaAEliminar) return;

      const row = document.getElementById(`row-materia-${idMateriaAEliminar}`);
      if (row) {
        row.style.transition = 'all 0.2s ease';
        row.style.opacity = '0';
        row.style.transform = 'scale(0.96)';
        setTimeout(() => {
          row.remove();
          actualizarPaginacion();
        }, 200);
      }

      const modalEl = document.getElementById('modalEliminarMateria');
      const modalInst = bootstrap.Modal.getInstance(modalEl);
      if (modalInst) modalInst.hide();

      mostrarToast(`Materia eliminada del catálogo.`);
    }

    // ==========================================
    // 11. Modo Claro / Modo Oscuro
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

    // Inicializar paginación y tema al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
      const temaGuardado = localStorage.getItem('study-match-theme') || 'light';
      aplicarTema(temaGuardado);
      actualizarPaginacion();
    });
  </script>
</body>
</html>
