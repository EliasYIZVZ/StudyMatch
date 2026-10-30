<?php
/**
 * ==========================================================================
 * STUDY MATCH • Gestión de Usuarios (admin/gestion-usuarios.php)
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
 * Tu compañero de backend podrá conectar esta vista con la base de datos real:
 * 
 * require_once __DIR__ . '/../app/config/Database.php';
 * $db = Database::getInstance()->getConnection();
 * 
 * $sql = "SELECT id_usuario, nombre_usuario, correo_usuario, fecha_registro, 
 *                estado_usuario, carrera_usuario, anio_cursada_usuario, 
 *                es_tutor, tutor_verificado, doc_titulo
 *         FROM usuarios 
 *         ORDER BY fecha_registro DESC 
 *         LIMIT 5";
 * $stmt = $db->query($sql);
 * $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
 * ========================================================================== */

// Datos simulados reducidos a 5 registros de ejemplo
$usuarios = [
    [
        'id_usuario'       => 110,
        'nombre'           => 'Lucas Benítez',
        'correo'           => 'lbenitez@frh.utn.edu.ar',
        'carrera'          => 'TUP (Programación)',
        'anio'             => '2do año',
        'legajo'           => '51294',
        'es_alumno'        => true,
        'es_tutor'         => false,
        'tutor_verificado' => false,
        'doc_titulo'       => null,
        'estado'           => 'activo',
        'fecha_registro'   => '29/10/2026 10:15',
        'avatar_bg'        => 'bg-primary-subtle text-primary'
    ],
    [
        'id_usuario'       => 109,
        'nombre'           => 'Julieta Abbiati',
        'correo'           => 'jabbiati@frh.utn.edu.ar',
        'carrera'          => 'TUP (Programación)',
        'anio'             => '3er año',
        'legajo'           => '49812',
        'es_alumno'        => true,
        'es_tutor'         => true,
        'tutor_verificado' => true,
        'doc_titulo'       => 'titulo_tecnico_abbiati.jpg',
        'estado'           => 'activo',
        'fecha_registro'   => '28/10/2026 18:40',
        'avatar_bg'        => 'bg-success-subtle text-success'
    ],
    [
        'id_usuario'       => 108,
        'nombre'           => 'Matías Fuentes',
        'correo'           => 'mfuentes@frh.utn.edu.ar',
        'carrera'          => 'Ing. en Sistemas',
        'anio'             => '4to año',
        'legajo'           => '48991',
        'es_alumno'        => true,
        'es_tutor'         => true,
        'tutor_verificado' => true,
        'doc_titulo'       => 'analitico_titulacion_fuentes.pdf',
        'estado'           => 'activo',
        'fecha_registro'   => '28/10/2026 14:22',
        'avatar_bg'        => 'bg-success-subtle text-success'
    ],
    [
        'id_usuario'       => 105,
        'nombre'           => 'Valentina Rojas',
        'correo'           => 'vrojas@frh.utn.edu.ar',
        'carrera'          => 'Ing. en Sistemas',
        'anio'             => '4to año',
        'legajo'           => '47820',
        'es_alumno'        => true,
        'es_tutor'         => true,
        'tutor_verificado' => false, // Tutor sin verificar título aún
        'doc_titulo'       => null,
        'estado'           => 'activo',
        'fecha_registro'   => '26/10/2026 11:15',
        'avatar_bg'        => 'bg-warning-subtle text-warning-emphasis'
    ],
    [
        'id_usuario'       => 103,
        'nombre'           => 'Facundo Morales',
        'correo'           => 'fmorales@frh.utn.edu.ar',
        'carrera'          => 'Ing. Ferroviaria',
        'anio'             => '2do año',
        'legajo'           => '50312',
        'es_alumno'        => true,
        'es_tutor'         => false,
        'tutor_verificado' => false,
        'doc_titulo'       => null,
        'estado'           => 'suspendido',
        'fecha_registro'   => '24/10/2026 22:10',
        'avatar_bg'        => 'bg-danger-subtle text-danger'
    ]
];

// Contadores rápidos para los botones de filtrado
$cantTotal   = count($usuarios);
$cantTutores = count(array_filter($usuarios, fn($u) => $u['es_tutor']));
$cantAlumnos = $cantTotal;
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Study Match - Gestión de Usuarios</title>

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

  <!-- Estilos específicos de Gestión de Usuarios -->
  <link rel="stylesheet" href="../css/admin-usuarios.css?v=20261029_v3">
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

        <!-- 1. Inicio / Resumen (Métricas) -->
        <a href="dashboard.php" class="sidebar-link" title="Inicio / Resumen">
          <i class="bi bi-speedometer2 fs-5"></i>
          <span class="sidebar-text">Inicio / Resumen</span>
        </a>

        <!-- 2. Gestión de Usuarios (ACTIVO) -->
        <a href="gestion-usuarios.php" class="sidebar-link active" title="Gestión de Usuarios">
          <i class="bi bi-people fs-5"></i>
          <span class="sidebar-text">Gestión de Usuarios</span>
          <span class="badge bg-primary-subtle text-primary ms-auto sidebar-text" style="font-size: 0.68rem;">1,280</span>
        </a>

        <!-- 3. Gestión de Materias -->
        <a href="gestion-materias.php" class="sidebar-link" title="Gestión de Materias">
          <i class="bi bi-journal-bookmark fs-5"></i>
          <span class="sidebar-text">Gestión de Materias</span>
          <span class="badge bg-secondary-subtle text-light ms-auto sidebar-text" style="font-size: 0.68rem;">42</span>
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
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Usuarios</li>
              </ol>
            </nav>
            <h1 class="h6 fw-bold mb-0 text-dark">Gestión de Usuarios</h1>
          </div>
        </div>

        <!-- Acciones: Alternador de Tema + Perfil Desplegable del Administrador -->
        <div class="d-flex align-items-center gap-2">
          
          <!-- Alternador de Tema Claro / Oscuro -->
          <button id="btnToggleTheme" class="btn btn-light border text-secondary p-2 rounded-3" title="Alternar Modo Claro / Oscuro" onclick="toggleTemaModo()">
            <i class="bi bi-moon-stars" id="themeIcon"></i>
          </button>

          <!-- Perfil Desplegable -->
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

      <!-- BANNER SUPERIOR CONTEXTUAL -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-white" 
           style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 65%, #1e3a8a 100%);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div>
            <span class="badge bg-primary text-white rounded-pill px-3 py-1 small fw-semibold mb-2 d-inline-block">
              <i class="bi bi-people-fill me-1"></i> Padrón de Usuarios
            </span>
            <h1 class="h3 fw-bold mb-1 text-white">
              Gestión de Alumnos y Tutores
            </h1>
            <p class="text-white text-opacity-75 small mb-0">
              Auditoría, validación de certificados de tutoría y control de cuentas activas en la UTN.
            </p>
          </div>
          <div>
            <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-3.5 py-2 small border border-white border-opacity-15">
              <i class="bi bi-database me-1"></i> <strong>1,280</strong> Registros totales
            </span>
          </div>
        </div>
      </div>

      <!-- CONTENEDOR PRINCIPAL DE TABLA Y FILTROS -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        
        <!-- BARRA SUPERIOR DE ACCIONES: BUSCADOR Y FILTROS RÁPIDOS -->
        <div class="p-3.5 px-4 border-bottom">
          <div class="row g-3 align-items-center justify-content-between">
            
            <!-- Buscador Rápido -->
            <div class="col-12 col-md-5 col-lg-4">
              <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
                <input type="text" 
                       id="userSearchInput" 
                       class="form-control rounded-pill ps-5 py-2 search-input-wide shadow-none" 
                       style="font-size: 0.88rem;" 
                       placeholder="Buscar por nombre, legajo o correo...">
              </div>
            </div>

            <!-- Filtros Rápidos por Rol -->
            <div class="col-12 col-md-7 col-lg-8 d-flex justify-content-md-end flex-wrap gap-2">
              <div class="btn-group btn-group-sm rounded-pill p-1 bg-light border" role="group" aria-label="Filtro de roles">
                <button type="button" class="btn btn-sm btn-white active rounded-pill fw-semibold px-3 shadow-sm filter-btn-role" onclick="filtrarUsuarios('todos', this)">
                  Todos (<?= $cantTotal; ?>)
                </button>
                <button type="button" class="btn btn-sm text-secondary rounded-pill fw-semibold px-2.5 filter-btn-role" onclick="filtrarUsuarios('alumno', this)">
                  <i class="bi bi-mortarboard me-1"></i>Alumnos (<?= $cantAlumnos; ?>)
                </button>
                <button type="button" class="btn btn-sm text-secondary rounded-pill fw-semibold px-2.5 filter-btn-role" onclick="filtrarUsuarios('tutor', this)">
                  <i class="bi bi-patch-check me-1 text-primary"></i>Tutores (<?= $cantTutores; ?>)
                </button>
              </div>
            </div>

          </div>
        </div>

        <!-- TABLA DE USUARIOS RESPONSIVA (DISEÑO LIMPIO: SIN COLUMNAS REDUNDANTES DE ROL) -->
        <div class="table-responsive">
          <table class="table table-users-admin align-middle mb-0" id="tablaUsuariosAdmin">
            <thead>
              <tr>
                <th scope="col" style="min-width: 250px;">Estudiante / Usuario</th>
                <th scope="col" style="min-width: 180px;">Carrera y Nivel</th>
                <th scope="col" style="width: 130px;">Estado</th>
                <th scope="col" class="text-end" style="width: 180px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              
              <!-- Iteración dinámica PHP (Mock representativo de 5 usuarios) -->
              <?php foreach ($usuarios as $usr): ?>
                <?php 
                  $esTutor = $usr['es_tutor'];
                  $rolType = $esTutor ? 'tutor' : 'alumno';
                ?>
                <tr id="user-row-<?= $usr['id_usuario']; ?>" data-role-type="<?= $rolType; ?>">
                  
                  <!-- 1. Columna: Datos del Usuario (Avatar, Nombre y Correo) -->
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <div class="avatar-user-badge <?= $usr['avatar_bg']; ?> fw-bold" style="width: 38px; height: 38px; font-size: 0.82rem;">
                        <?php 
                          $partes = explode(' ', $usr['nombre']);
                          echo count($partes) >= 2 
                            ? strtoupper(substr($partes[0], 0, 1) . substr($partes[1], 0, 1))
                            : strtoupper(substr($usr['nombre'], 0, 2));
                        ?>
                      </div>
                      <div class="d-flex flex-column lh-sm">
                        <span class="fw-bold text-dark user-name-slot"><?= htmlspecialchars($usr['nombre']); ?></span>
                        <span class="text-secondary small text-truncate" style="max-width: 220px; font-size: 0.78rem;"><?= htmlspecialchars($usr['correo']); ?></span>
                      </div>
                    </div>
                  </td>

                  <!-- 2. Columna: Carrera y Año -->
                  <td>
                    <span class="fw-semibold text-dark small d-block"><?= htmlspecialchars($usr['carrera']); ?></span>
                    <span class="text-muted small" style="font-size: 0.74rem;"><?= htmlspecialchars($usr['anio']); ?></span>
                  </td>

                  <!-- 3. Columna: Estado de la Cuenta (Activo / Suspendido) -->
                  <td>
                    <?php if ($usr['estado'] === 'activo'): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small user-status-badge">
                        <i class="bi bi-check-circle-fill me-1"></i>Activo
                      </span>
                    <?php else: ?>
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 small user-status-badge">
                        <i class="bi bi-x-circle-fill me-1"></i>Suspendido
                      </span>
                    <?php endif; ?>
                  </td>

                  <!-- 4. Columna: Botones de Acción [Ver Ficha] y [Suspender/Reactivar] -->
                  <td class="text-end">
                    <div class="d-inline-flex align-items-center gap-1.5">
                      
                      <!-- Botón Ver Ficha (Modal con rol completo y acreditaciones) -->
                      <button type="button" 
                              class="btn btn-sm btn-primary rounded-pill btn-action-user d-inline-flex align-items-center gap-1 shadow-sm px-3"
                              onclick="abrirModalDetalles(<?= htmlspecialchars(json_encode($usr)); ?>)"
                              title="Ver ficha completa y rol del usuario">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Ver Ficha</span>
                      </button>

                      <!-- Botón Suspender / Reactivar según estado actual -->
                      <?php if ($usr['estado'] === 'activo'): ?>
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger btn-action-user btn-action-suspend d-inline-flex align-items-center gap-1"
                                onclick="confirmarSuspension(<?= $usr['id_usuario']; ?>, '<?= htmlspecialchars($usr['nombre']); ?>', 'suspender')"
                                title="Suspender cuenta por infracción">
                          <i class="bi bi-slash-circle"></i>
                          <span class="d-none d-md-inline">Suspender</span>
                        </button>
                      <?php else: ?>
                        <button type="button" 
                                class="btn btn-sm btn-outline-success btn-action-user d-inline-flex align-items-center gap-1"
                                onclick="confirmarSuspension(<?= $usr['id_usuario']; ?>, '<?= htmlspecialchars($usr['nombre']); ?>', 'reactivar')"
                                title="Reactivar acceso del usuario">
                          <i class="bi bi-check2-circle"></i>
                          <span class="d-none d-md-inline">Reactivar</span>
                        </button>
                      <?php endif; ?>

                    </div>
                  </td>

                </tr>
              <?php endforeach; ?>

            </tbody>
          </table>
        </div>

        <!-- PAGINACIÓN Y RESUMEN INFERIOR -->
        <div class="card-footer border-top px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="text-secondary small">
            Mostrando <strong>1 - 5</strong> de <strong><?= $cantTotal; ?></strong> usuarios cargados
          </div>
          
          <nav aria-label="Navegación de páginas">
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item disabled"><a class="page-link rounded-start-pill px-3" href="#">Anterior</a></li>
              <li class="page-item active"><a class="page-link" href="#">1</a></li>
              <li class="page-item disabled"><a class="page-link rounded-end-pill px-3" href="#">Siguiente</a></li>
            </ul>
          </nav>
        </div>

      </div>

    </main>
  </div>

  <!-- ==========================================================================
       MODAL 1: FICHA DE DETALLES DEL USUARIO
       ========================================================================== -->
  <div class="modal fade" id="modalDetallesUsuario" tabindex="-1" aria-labelledby="modalDetallesUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
              <i class="bi bi-person-lines-fill fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark" id="modalDetallesUsuarioLabel">Perfil Académico</h5>
              <span class="text-secondary small" id="modalIdDisplay">#ID</span>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Tarjeta de Identidad -->
          <div class="text-center pb-3 border-bottom mb-3">
            <div id="modalAvatarDisplay" class="avatar-user-badge mx-auto mb-2 fw-bold" style="width: 58px; height: 58px; font-size: 1.25rem;">
              --
            </div>
            <h5 class="fw-bold text-dark mb-0" id="modalNombreDisplay">Cargando...</h5>
            <span class="text-secondary small" id="modalCorreoDisplay">...</span>
            <div class="mt-2" id="modalRolesContainer">
              <!-- Badges de Alumno / Tutor inyectados dinámicamente -->
            </div>
          </div>

          <!-- Campos de Información -->
          <div class="row g-2 small">
            <div class="col-6">
              <span class="text-muted d-block" style="font-size: 0.72rem;">Legajo Académico:</span>
              <strong class="text-dark" id="modalLegajoDisplay">...</strong>
            </div>
            <div class="col-6">
              <span class="text-muted d-block" style="font-size: 0.72rem;">Estado de Cuenta:</span>
              <span id="modalEstadoBadge">...</span>
            </div>
            <div class="col-12 mt-2">
              <span class="text-muted d-block" style="font-size: 0.72rem;">Carrera y Nivel:</span>
              <strong class="text-dark" id="modalCarreraDisplay">...</strong> (<span id="modalAnioDisplay">...</span>)
            </div>
            <div class="col-12 mt-2">
              <span class="text-muted d-block" style="font-size: 0.72rem;">Certificación de Tutor:</span>
              <div id="modalDocTituloDisplay" class="mt-1">
                <!-- Estado del certificado -->
              </div>
            </div>
            <div class="col-12 mt-2">
              <span class="text-muted d-block" style="font-size: 0.72rem;">Fecha de Registro:</span>
              <span class="text-secondary" id="modalFechaDisplay">...</span>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
          <a href="#" id="modalBtnContactar" class="btn btn-outline-primary rounded-pill px-3 btn-sm">
            <i class="bi bi-envelope me-1"></i> Enviar Correo
          </a>
          <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">
            Cerrar
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- ==========================================================================
       MODAL 2: CONFIRMAR SUSPENSIÓN / REACTIVACIÓN
       ========================================================================== -->
  <div class="modal fade" id="modalSuspenderUsuario" tabindex="-1" aria-labelledby="modalSuspenderUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-danger-subtle text-danger p-2 rounded-3">
              <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark" id="modalSuspenderUsuarioLabel">Moderar Usuario</h5>
              <p class="text-secondary small mb-0">Control de acceso y medidas disciplinarias</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body p-4">
          <p class="mb-3 text-secondary">
            ¿Estás seguro de que deseas <strong id="suspendAccionNombre" class="text-dark">suspender</strong> a 
            <strong class="text-dark" id="suspendUserNameDisplay">...</strong>?
          </p>

          <div id="campoMotivoSuspension" class="mb-3">
            <label for="motivoSuspensionInput" class="form-label small fw-semibold text-secondary">
              Motivo o justificación de la medida:
            </label>
            <select class="form-select rounded-3 mb-2" id="motivoSuspensionInput">
              <option value="Conducta inapropiada en salas">Conducta inapropiada en salas comunitarias</option>
              <option value="Spam o difusión no autorizada">Spam o difusión no autorizada de enlaces</option>
              <option value="Inasistencias reiteradas">Inasistencias reiteradas a tutorías pactadas</option>
              <option value="Otra infracción">Otra infracción a las normas de convivencia</option>
            </select>
          </div>
        </div>

        <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
            Cancelar
          </button>
          <button type="button" class="btn btn-danger rounded-pill px-4 fw-semibold" id="btnEjecutarSuspension" onclick="ejecutarAccionSuspension()">
            Confirmar Suspensión
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- TOAST DE NOTIFICACIÓN DE ACCIONES -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="toastAdminUsers" class="toast align-items-center text-bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill text-success fs-5"></i>
          <span id="toastAdminUsersMsg">Acción procesada correctamente.</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       JAVASCRIPT: COMPORTAMIENTOS, BÚSQUEDA Y MODALES
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
    // 3. Buscador en Tiempo Real por Nombre o Correo
    // ==========================================
    const searchInput = document.getElementById('userSearchInput');
    const tableRows = document.querySelectorAll('#tablaUsuariosAdmin tbody tr');
    let filtroActual = 'todos';

    function filtrarTabla() {
      const query = (searchInput ? searchInput.value : '').toLowerCase().trim();

      tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const role = row.getAttribute('data-role-type');

        const coincideTexto = !query || text.includes(query);
        const coincideRol = (filtroActual === 'todos') || (role === filtroActual);

        if (coincideTexto && coincideRol) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    if (searchInput) searchInput.addEventListener('input', filtrarTabla);

    // ==========================================
    // 4. Filtro Rápido por Rol (Todos, Alumnos, Tutores)
    // ==========================================
    function filtrarUsuarios(tipo, btn) {
      filtroActual = tipo;

      document.querySelectorAll('.filter-btn-role').forEach(b => {
        b.classList.remove('btn-white', 'active', 'shadow-sm');
        b.classList.add('text-secondary');
      });
      btn.classList.remove('text-secondary');
      btn.classList.add('btn-white', 'active', 'shadow-sm');

      filtrarTabla();
    }

    // ==========================================
    // 5. Modal "Ver Detalles"
    // ==========================================
    function abrirModalDetalles(userData) {
      document.getElementById('modalIdDisplay').textContent = '#' + userData.id_usuario;
      document.getElementById('modalNombreDisplay').textContent = userData.nombre;
      document.getElementById('modalCorreoDisplay').textContent = userData.correo;
      document.getElementById('modalLegajoDisplay').textContent = userData.legajo;
      document.getElementById('modalCarreraDisplay').textContent = userData.carrera;
      document.getElementById('modalAnioDisplay').textContent = userData.anio;
      document.getElementById('modalFechaDisplay').textContent = userData.fecha_registro;
      document.getElementById('modalBtnContactar').href = 'mailto:' + userData.correo;

      const avatarBox = document.getElementById('modalAvatarDisplay');
      avatarBox.className = 'avatar-user-badge mx-auto mb-2 fw-bold ' + userData.avatar_bg;
      const partes = userData.nombre.split(' ');
      avatarBox.textContent = partes.length >= 2 
        ? (partes[0][0] + partes[1][0]).toUpperCase() 
        : userData.nombre.substring(0, 2).toUpperCase();

      const rolesBox = document.getElementById('modalRolesContainer');
      let rolesHtml = '';
      if (userData.es_tutor) {
        if (userData.tutor_verificado) {
          rolesHtml = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold small shadow-sm"><i class="bi bi-patch-check-fill me-1"></i>Tutor Acreditado (Título Verificado)</span>';
        } else {
          rolesHtml = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1.5 fw-semibold small"><i class="bi bi-clock-history me-1"></i>Tutor Postulante (Pendiente de Título)</span>';
        }
      } else {
        rolesHtml = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-semibold small"><i class="bi bi-mortarboard me-1"></i>Alumno Regular</span>';
      }
      rolesBox.innerHTML = rolesHtml;

      const docBox = document.getElementById('modalDocTituloDisplay');
      if (userData.es_tutor && userData.tutor_verificado && userData.doc_titulo) {
        docBox.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i>Foto de título aprobada: <code>${userData.doc_titulo}</code></span>`;
      } else if (userData.es_tutor && !userData.tutor_verificado) {
        docBox.innerHTML = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-clock me-1"></i>Sin foto de título adjunta (No verificado)</span>`;
      } else {
        docBox.innerHTML = `<span class="text-muted">No solicitada (Perfil Alumno)</span>`;
      }

      const estadoBadge = document.getElementById('modalEstadoBadge');
      if (userData.estado === 'activo') {
        estadoBadge.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle">Activo</span>';
      } else {
        estadoBadge.innerHTML = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Suspendido</span>';
      }

      const modalEl = document.getElementById('modalDetallesUsuario');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }

    // ==========================================
    // 6. Moderación: Suspender / Reactivar Usuario
    // ==========================================
    let usuarioModerarId = null;
    let usuarioModerarAccion = null;

    function confirmarSuspension(id, nombre, accion) {
      usuarioModerarId = id;
      usuarioModerarAccion = accion;

      document.getElementById('suspendUserNameDisplay').textContent = nombre;
      const accionText = accion === 'suspender' ? 'suspender' : 'reactivar';
      document.getElementById('suspendAccionNombre').textContent = accionText;

      const btnEjecutar = document.getElementById('btnEjecutarSuspension');
      const campoMotivo = document.getElementById('campoMotivoSuspension');

      if (accion === 'suspender') {
        btnEjecutar.className = 'btn btn-danger rounded-pill px-4 fw-semibold';
        btnEjecutar.textContent = 'Confirmar Suspensión';
        if (campoMotivo) campoMotivo.style.display = 'block';
      } else {
        btnEjecutar.className = 'btn btn-success rounded-pill px-4 fw-semibold';
        btnEjecutar.textContent = 'Confirmar Reactivación';
        if (campoMotivo) campoMotivo.style.display = 'none';
      }

      const modalEl = document.getElementById('modalSuspenderUsuario');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }

    function ejecutarAccionSuspension() {
      if (!usuarioModerarId) return;

      const row = document.getElementById(`user-row-${usuarioModerarId}`);
      if (row) {
        const badge = row.querySelector('.user-status-badge');

        if (usuarioModerarAccion === 'suspender') {
          if (badge) {
            badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 small user-status-badge';
            badge.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i>Suspendido';
          }
          mostrarToast("Usuario suspendido correctamente.");
        } else {
          if (badge) {
            badge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small user-status-badge';
            badge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Activo';
          }
          mostrarToast("Usuario reactivado correctamente.");
        }
      }

      const modalEl = document.getElementById('modalSuspenderUsuario');
      const modalInst = bootstrap.Modal.getInstance(modalEl);
      if (modalInst) modalInst.hide();
    }

    function mostrarToast(mensaje) {
      const toastEl = document.getElementById('toastAdminUsers');
      const toastMsg = document.getElementById('toastAdminUsersMsg');
      if (toastEl && toastMsg) {
        toastMsg.textContent = mensaje;
        const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3500 });
        toast.show();
      }
    }

    // Inicialización al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
      const temaGuardado = localStorage.getItem('study-match-theme') || 'light';
      aplicarTema(temaGuardado);
    });
  </script>
</body>
</html>