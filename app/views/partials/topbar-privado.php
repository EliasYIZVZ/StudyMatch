<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php /* Barra superior simple del área privada: menú lateral, tema y perfil. */ ?>
    <header class="topbar-header px-4 py-3 sticky-top">
      <div class="d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
          <button id="btnToggleSidebar" class="btn btn-light border text-secondary p-2 rounded-3" title="Expandir/Contraer menú lateral">
            <i class="bi bi-layout-sidebar fs-5"></i>
          </button>
        </div>

        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-outline-brand btn-sm theme-toggle-btn d-inline-flex align-items-center justify-content-center rounded-pill" type="button" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
            <i class="theme-icon bi bi-sun-fill text-warning"></i>
          </button>

          <?php require APP_PATH . '/views/partials/perfil-alumno-menu.php'; ?>
        </div>
      </div>
    </header>
