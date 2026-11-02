<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php /* Modales de perfil y scripts comunes del área privada. $scriptsExtra: scripts adicionales. */ ?>
  <?php require APP_PATH . '/views/partials/perfil-alumno-modales.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="public/js/perfil.js"></script>
  <script src="public/js/sidebar.js"></script>
  <script src="public/js/main.js"></script>
  <?php foreach (isset($scriptsExtra) ? $scriptsExtra : [] as $script): ?>
  <script src="<?= Vista::e($script) ?>"></script>
  <?php endforeach; ?>
  <script>
    // Opción "Cambiar a Modo Tutor" del menú de perfil
    function irAModoTutor() {
      window.location.href = 'index.php?r=dashboard-tutor';
    }
  </script>
