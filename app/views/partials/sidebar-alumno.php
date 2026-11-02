<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/**
 * Menú lateral del modo alumno.
 * Espera $sidebarActivo: 'inicio' | 'buscar' | 'tutorias' (marca "Mis tutorías"). Usa $contadores (lo agrega Controller::render).
 */
$activo = function ($item) use ($sidebarActivo) {
    return isset($sidebarActivo) && $sidebarActivo === $item ? ' active' : '';
};
?>
  <aside id="mainSidebar" class="sidebar d-flex flex-column justify-content-between p-3">
    <div>
      <!-- Header con Logo Study Match y Botón de Colapso -->
      <div class="d-flex align-items-center justify-content-between mb-4 px-2 logo-container">
        <a href="index.php?r=dashboard" class="d-flex align-items-center gap-2 text-decoration-none overflow-hidden">
          <svg class="logo-svg flex-shrink-0" width="36" height="36" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M28 54 V72 C28 80 72 80 72 72 V54 L50 65 Z" fill="#172554"/>
            <polygon points="50,20 10,42 50,62" fill="#1e3a8a"/>
            <polygon points="50,20 90,42 50,62" fill="#1677c8"/>
            <line x1="78" y1="48" x2="78" y2="70" stroke="#1677c8" stroke-width="3.5" stroke-linecap="round"/>
            <circle cx="78" cy="68" r="3.5" fill="#1677c8"/>
            <polygon points="78,68 73,82 83,82" fill="#1677c8"/>
            <path d="M38 41 L48 51 L76 23 L60 34 L48 44 L42 37 Z" fill="#ffffff" stroke="#ffffff" stroke-linejoin="round" stroke-width="2"/>
          </svg>
          <span class="fw-bold fs-5 text-dark logo-text">Study <span style="color: var(--brand-blue);">Match</span></span>
        </a>

        <!-- Botón para contraer sidebar -->
        <button id="btnCollapseSidebar" class="btn btn-sm btn-light border-0 text-secondary p-1 rounded-2 sidebar-hide-collapsed" title="Contraer menú lateral">
          <i class="bi bi-chevron-left"></i>
        </button>
      </div>

      <!-- Menú de Navegación: MODO ALUMNO (Acento Azul) -->
      <nav class="d-flex flex-column gap-1">
        <div class="px-3 pb-1 text-uppercase small text-muted fw-bold sidebar-text" style="font-size: 0.68rem; letter-spacing: 0.08em;">
          Menú Alumno
        </div>
        <a href="index.php?r=dashboard" class="sidebar-link<?= $activo('inicio') ?>" title="Inicio / Novedades">
          <i class="bi bi-house-door"></i>
          <span class="sidebar-text">Inicio</span>
        </a>
        <a href="index.php?r=buscar-tutor" class="sidebar-link<?= $activo('buscar') ?>" title="Buscar Tutor">
          <i class="bi bi-search"></i>
          <span class="sidebar-text">Buscar Tutor</span>
        </a>
        <a href="index.php?r=mis-tutorias&amp;rol=alumno" class="sidebar-link<?= $activo('tutorias') ?>" title="Mis tutorías">
          <i class="bi bi-mortarboard"></i>
          <span class="sidebar-text">Mis tutorías</span>
          <?php if (!empty($contadores['mensajes'])): ?>
            <span class="badge rounded-pill ms-auto sidebar-text bg-danger" style="font-size: 0.65rem;" title="Mensajes sin leer"><?= (int) $contadores['mensajes'] ?> <?= (int) $contadores['mensajes'] === 1 ? 'nuevo' : 'nuevos' ?></span>
          <?php endif; ?>
        </a>
      </nav>
    </div>

    <!-- Usuario abajo en el Sidebar: Tarjeta de Perfil Interactivo -->
    <div class="border-top pt-3 mt-auto">
      <div class="user-sidebar-card p-2 d-flex align-items-center justify-content-between mb-2" 
           data-bs-toggle="modal" data-bs-target="#modalEditarPerfil" title="Haz clic para editar tu perfil">
        <div class="d-flex align-items-center gap-2 overflow-hidden">
          <div class="avatar-user-display userAvatarSlot">
            AL
          </div>
          <div class="sidebar-text text-truncate lh-sm">
            <span class="d-block fw-bold text-dark small text-truncate userNameSlot">Cargando...</span>
            <span class="text-secondary userCareerSlot" style="font-size: 0.72rem;">Sistemas • 3° año</span>
          </div>
        </div>
        <i class="bi bi-pencil-square text-secondary sidebar-text fs-6 me-1" title="Editar"></i>
      </div>

      <a href="index.php?r=logout" class="sidebar-link text-danger p-2" title="Cerrar sesión">
        <i class="bi bi-box-arrow-left"></i>
        <span class="sidebar-text">Cerrar sesión</span>
      </a>
    </div>
  </aside>
