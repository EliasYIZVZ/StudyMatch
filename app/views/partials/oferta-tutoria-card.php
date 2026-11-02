<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/**
 * Card de una oferta de tutoría.
 * Espera la variable $oferta con: id_tutoria, materia, tutor, descripcion, modalidad,
 * disponibilidad, precio, medios_pago, mi_solicitud_id y mi_solicitud_estado.
 */
$e = function ($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};

$modalidades = [
    'Virtual'           => ['etiqueta' => 'Virtual',           'icono' => 'bi-laptop'],
    'Presencial en UTN' => ['etiqueta' => 'Presencial en UTN', 'icono' => 'bi-building'],
    'Hibrido'           => ['etiqueta' => 'Híbrido',           'icono' => 'bi-arrow-left-right'],
];
$modalidad = isset($modalidades[$oferta['modalidad']])
    ? $modalidades[$oferta['modalidad']]
    : ['etiqueta' => $oferta['modalidad'], 'icono' => 'bi-geo-alt'];

// Precio: "Gratuita" o monto por clase (sin decimales si son ",00")
$precio = (float) $oferta['precio'];
$esGratuita = $precio <= 0;
$montoTexto = '$ ' . number_format($precio, fmod($precio, 1) == 0 ? 0 : 2, ',', '.');
$medios = $esGratuita ? [] : Vista::mediosPago($oferta['medios_pago']);
?>
<div class="col">
  <article class="public-room-card oferta-card p-4 h-100 d-flex flex-column justify-content-between">
    <div>
      <div class="mb-3">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small fw-semibold text-wrap text-start">
          <?= $e($oferta['materia']) ?>
        </span>
      </div>

      <div class="d-flex align-items-center gap-2 mb-2">
        <?= Vista::avatar($oferta['tutor'], isset($oferta['tutor_foto']) ? $oferta['tutor_foto'] : null, 'avatar-circle avatar-mini bg-primary-subtle text-primary') ?>
        <h3 class="h6 fw-bold text-dark mb-0"><?= $e($oferta['tutor']) ?></h3>
      </div>
      <p class="text-secondary small mb-3 lh-base oferta-descripcion">
        <?= $e($oferta['descripcion']) ?>
      </p>
    </div>

    <div>
      <ul class="list-unstyled small text-secondary mb-3 d-flex flex-column gap-1">
        <li class="d-flex align-items-start gap-2">
          <i class="bi <?= $e($modalidad['icono']) ?>"></i>
          <span><?= $e($modalidad['etiqueta']) ?></span>
        </li>
        <li class="d-flex align-items-start gap-2">
          <i class="bi bi-clock"></i>
          <span><?= $e($oferta['disponibilidad']) ?></span>
        </li>
        <?php if (!empty($medios)): ?>
          <li class="d-flex align-items-start gap-2" title="Medios de pago">
            <i class="bi bi-wallet2"></i>
            <span><?= $e(implode(' • ', $medios)) ?></span>
          </li>
        <?php endif; ?>
      </ul>

      <!-- Precio por clase -->
      <div class="d-flex align-items-center justify-content-between gap-2 border-top pt-3 mb-3 oferta-precio">
        <span class="text-secondary small text-nowrap" title="Precio por clase">Por clase</span>
        <?php if ($esGratuita): ?>
          <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">
            <i class="bi bi-gift me-1"></i>Gratuita
          </span>
        <?php else: ?>
          <span class="fs-6 fw-bold text-dark text-nowrap"><?= $e($montoTexto) ?></span>
        <?php endif; ?>
      </div>

      <?php if (!empty($oferta['mi_solicitud_id'])): ?>
        <?php if ($oferta['mi_solicitud_estado'] === 'aceptada'): ?>
          <a href="index.php?r=tutoria&amp;id=<?= (int) $oferta['mi_solicitud_id'] ?>" class="btn btn-success w-100 rounded-pill fw-bold py-2 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-chat-dots"></i> Tutoría aceptada: abrir chat
          </a>
        <?php else: ?>
          <button type="button" class="btn btn-outline-secondary w-100 rounded-pill fw-bold py-2 d-flex align-items-center justify-content-center gap-2" disabled>
            <i class="bi bi-hourglass-split"></i> Solicitud pendiente
          </button>
        <?php endif; ?>
        <form method="POST" action="index.php?r=cancelar-solicitud" class="text-center mt-2">
          <input type="hidden" name="id_solicitud" value="<?= (int) $oferta['mi_solicitud_id'] ?>">
          <button type="submit" class="btn btn-link btn-sm text-danger text-decoration-none p-0">Cancelar solicitud</button>
        </form>
      <?php else: ?>
        <button type="button" class="btn btn-discord w-100 rounded-pill fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalSolicitarTutoria"
                data-id-oferta="<?= (int) $oferta['id_tutoria'] ?>"
                data-materia="<?= $e($oferta['materia']) ?>"
                data-tutor="<?= $e($oferta['tutor']) ?>">
          <i class="bi bi-send"></i> Solicitar tutoría
        </button>
      <?php endif; ?>
    </div>
  </article>
</div>
