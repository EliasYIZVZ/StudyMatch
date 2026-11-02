<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/**
 * Lista de solicitudes pendientes del tutor.
 * Espera: $solicitudesPendientes (array) y $errorSolicitudes (bool).
 */
$e = function ($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};
$iniciales = function ($nombre) {
    $partes = preg_split('/\s+/u', trim((string) $nombre), -1, PREG_SPLIT_NO_EMPTY);
    $letras = '';
    foreach (array_slice($partes, 0, 2) as $parte) {
        $letras .= mb_substr($parte, 0, 1, 'UTF-8');
    }
    return mb_strtoupper($letras !== '' ? $letras : '?', 'UTF-8');
};
?>
<?php if ($errorSolicitudes): ?>
  <div class="text-center py-4 text-muted bg-light rounded-3 border">
    <i class="bi bi-exclamation-triangle text-warning fs-1 d-block mb-2"></i>
    <p class="small text-secondary mb-0">No se pudieron cargar las solicitudes. Intentá nuevamente más tarde.</p>
  </div>
<?php elseif (empty($solicitudesPendientes)): ?>
  <div class="text-center py-4 text-muted bg-light rounded-3 border border-dashed">
    <i class="bi bi-check2-circle text-success fs-1 d-block mb-2"></i>
    <h6 class="fw-bold text-dark mb-1">¡Estás al día!</h6>
    <p class="small text-secondary mb-0">No tenés solicitudes de alumnos pendientes de respuesta.</p>
  </div>
<?php else: ?>
  <?php foreach ($solicitudesPendientes as $solicitud): ?>
    <div class="p-3 rounded-3 border bg-light-subtle d-flex align-items-start justify-content-between flex-wrap gap-3 item-solicitud">
      <div class="d-flex align-items-start gap-3">
        <?= Vista::avatar($solicitud['alumno'], $solicitud['alumno_foto']) ?>
        <div>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <h6 class="fw-bold text-dark mb-0"><?= $e($solicitud['alumno']) ?></h6>
            <span class="badge bg-primary-subtle text-primary" style="font-size: 0.7rem;"><?= $e($solicitud['materia']) ?></span>
          </div>
          <p class="text-secondary small mb-1 mt-1">
            <?php if ($solicitud['mensaje'] !== null && $solicitud['mensaje'] !== ''): ?>
              "<?= nl2br($e($solicitud['mensaje'])) ?>"
            <?php else: ?>
              <em>Sin mensaje.</em>
            <?php endif; ?>
          </p>
          <span class="text-muted" style="font-size: 0.72rem;">
            <i class="bi bi-clock me-1"></i><?= $e(date('d/m/Y H:i', strtotime($solicitud['fecha']))) ?> • UTN FRH
          </span>
        </div>
      </div>
      <form method="POST" action="index.php?r=responder-solicitud" class="d-flex align-items-center gap-2 ms-auto">
        <input type="hidden" name="id_solicitud" value="<?= (int) $solicitud['id_solicitud'] ?>">
        <button type="submit" name="accion" value="rechazar" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Rechazar</button>
        <button type="submit" name="accion" value="aceptar" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold shadow-sm">
          <i class="bi bi-check-lg me-1"></i> Aceptar tutoría
        </button>
      </form>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
