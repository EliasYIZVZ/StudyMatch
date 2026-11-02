<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/**
 * Botones de acción de una tutoría según su estado y el rol del usuario.
 * Espera: $t (tutoría), $rol ('alumno' | 'tutor'),
 *         $volver ('tutoria' | 'mis-tutorias-alumno' | 'mis-tutorias-tutor'),
 *         $filtro (opcional: filtro activo de Mis tutorías, para volver a la misma vista).
 */
$idSol = (int) $t['id_solicitud'];
$campoFiltro = isset($filtro) && $filtro !== 'todas'
    ? '<input type="hidden" name="filtro" value="' . Vista::e($filtro) . '">'
    : '';
?>
<?php if ($rol === 'tutor' && $t['estado'] === 'pendiente'): ?>
  <form method="POST" action="index.php?r=responder-solicitud" class="d-flex align-items-center gap-2">
    <input type="hidden" name="id_solicitud" value="<?= $idSol ?>">
    <input type="hidden" name="volver" value="<?= Vista::e($volver) ?>">
    <?= $campoFiltro ?>
    <button type="submit" name="accion" value="rechazar" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Rechazar</button>
    <button type="submit" name="accion" value="aceptar" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold">
      <i class="bi bi-check-lg me-1"></i>Aceptar
    </button>
  </form>
<?php elseif ($rol === 'tutor' && $t['estado'] === 'aceptada'): ?>
  <form method="POST" action="index.php?r=responder-solicitud">
    <input type="hidden" name="id_solicitud" value="<?= $idSol ?>">
    <input type="hidden" name="volver" value="<?= Vista::e($volver) ?>">
    <?= $campoFiltro ?>
    <button type="submit" name="accion" value="finalizar" class="btn btn-sm btn-outline-primary rounded-pill px-3">
      <i class="bi bi-flag me-1"></i>Finalizar tutoría
    </button>
  </form>
<?php elseif ($rol === 'alumno' && in_array($t['estado'], ['pendiente', 'aceptada'], true)): ?>
  <form method="POST" action="index.php?r=cancelar-solicitud">
    <input type="hidden" name="id_solicitud" value="<?= $idSol ?>">
    <input type="hidden" name="volver" value="<?= Vista::e($volver) ?>">
    <?= $campoFiltro ?>
    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Cancelar</button>
  </form>
<?php endif; ?>
