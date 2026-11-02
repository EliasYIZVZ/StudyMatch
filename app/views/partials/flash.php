<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/* Mensaje de una sola vez generado por Controller::flash() */
if (!empty($_SESSION['flash']) && is_array($_SESSION['flash'])):
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $flashExito = isset($flash['tipo']) && $flash['tipo'] === 'exito';
?>
<div class="alert <?= $flashExito ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show d-flex align-items-center gap-2 rounded-4 shadow-sm mb-4" role="alert">
  <i class="bi <?= $flashExito ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> flex-shrink-0"></i>
  <div><?= htmlspecialchars(isset($flash['mensaje']) ? (string) $flash['mensaje'] : '', ENT_QUOTES, 'UTF-8') ?></div>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>
