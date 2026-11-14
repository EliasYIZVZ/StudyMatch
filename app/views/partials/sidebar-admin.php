<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/**
 * Menú lateral del panel de administración (único para todas las vistas de admin).
 * Espera $sidebarAdminActivo: 'resumen' | 'usuarios' | 'materias' | 'salas' | 'reportes'.
 * Usa $menuAdmin (contadores reales: lo agrega AdminController::renderAdmin),
 * $adminNombre y $adminRol.
 */
$activoAdmin = function ($item) use ($sidebarAdminActivo) {
    return isset($sidebarAdminActivo) && $sidebarAdminActivo === $item ? ' active' : '';
};
$menuAdmin = (isset($menuAdmin) && is_array($menuAdmin) ? $menuAdmin : [])
    + ['usuarios' => 0, 'materias' => 0, 'salas' => 0, 'reportes' => 0];
?>
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

        <a href="index.php?r=admin" class="sidebar-link<?= $activoAdmin('resumen') ?>" title="Inicio / Resumen">
          <i class="bi bi-speedometer2 fs-5"></i>
          <span class="sidebar-text">Inicio / Resumen</span>
        </a>

        <a href="index.php?r=admin-usuarios" class="sidebar-link<?= $activoAdmin('usuarios') ?>" title="Gestión de Usuarios">
          <i class="bi bi-people fs-5"></i>
          <span class="sidebar-text">Gestión de Usuarios</span>
          <span class="badge bg-primary-subtle text-primary ms-auto sidebar-text" style="font-size: 0.68rem;" id="badgeSidebarUsuarios"><?= (int) $menuAdmin['usuarios'] ?></span>
        </a>

        <a href="index.php?r=admin-materias" class="sidebar-link<?= $activoAdmin('materias') ?>" title="Gestión de Materias y Carreras">
          <i class="bi bi-journal-bookmark fs-5"></i>
          <span class="sidebar-text">Gestión de Materias</span>
          <span class="badge bg-primary-subtle text-primary ms-auto sidebar-text" style="font-size: 0.68rem;" id="badgeSidebarMaterias"><?= (int) $menuAdmin['materias'] ?></span>
        </a>

        <a href="index.php?r=admin-salas" class="sidebar-link<?= $activoAdmin('salas') ?>" title="Salas Públicas">
          <i class="bi bi-broadcast fs-5 text-danger"></i>
          <span class="sidebar-text">Salas Públicas</span>
          <span class="badge bg-danger text-white rounded-pill ms-auto sidebar-text" style="font-size: 0.65rem;"><span id="badgeSidebarSalas"><?= (int) $menuAdmin['salas'] ?></span> en vivo</span>
        </a>

        <a href="index.php?r=admin-reportes" class="sidebar-link<?= $activoAdmin('reportes') ?>" title="Reportes y Moderación">
          <i class="bi bi-exclamation-triangle fs-5 text-warning"></i>
          <span class="sidebar-text">Reportes</span>
          <span class="badge bg-warning text-dark rounded-pill ms-auto sidebar-text fw-bold" style="font-size: 0.65rem;" id="badgeSidebarReportes"><?= (int) $menuAdmin['reportes'] ?></span>
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
