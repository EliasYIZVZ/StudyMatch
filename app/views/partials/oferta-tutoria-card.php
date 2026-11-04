<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/**
 * Card de una oferta de tutoría, en tres bloques: tutor, oferta y solicitud.
 * Espera la variable $oferta (fila de OfertaTutoria::listarParaAlumno).
 */
$anios = [
    '1ro' => '1° año', '2do' => '2° año', '3ro' => '3° año',
    '4to' => '4° año', '5to' => '5° año', 'graduado' => 'Graduado/a',
];
$perfilTutor = [];
if (!empty($oferta['tutor_carrera'])) {
    $perfilTutor[] = $oferta['tutor_carrera'];
}
if (!empty($oferta['tutor_anio']) && isset($anios[$oferta['tutor_anio']])) {
    $perfilTutor[] = $anios[$oferta['tutor_anio']];
}
$tutoriasDadas = (int) $oferta['tutorias_dadas'];

// Precio: "Gratuita" o monto por clase (sin decimales si son ",00")
$precio = (float) $oferta['precio'];
$esGratuita = $precio <= 0;
$montoTexto = '$ ' . number_format($precio, fmod($precio, 1) == 0 ? 0 : 2, ',', '.');
$medios = $esGratuita ? [] : Vista::mediosPago($oferta['medios_pago']);

// Texto en el que busca el buscador del dashboard (public/js/buscador-cards.js)
$textoBusqueda = mb_strtolower(implode(' ', [
    $oferta['tutor'], $oferta['materia'], $oferta['descripcion'], $oferta['tutor_carrera'],
    Vista::modalidad($oferta['modalidad']), $oferta['disponibilidad'], $esGratuita ? 'gratuita gratis' : '',
]), 'UTF-8');
?>
<div class="col buscador-item<?= isset($posicion, $ofertasVisibles) && $posicion >= $ofertasVisibles ? ' d-none' : '' ?>"
     data-materias="<?= (int) $oferta['id_materia'] ?>"
     data-orden="<?= isset($posicion) ? (int) $posicion : 0 ?>"
     data-busqueda="<?= Vista::e($textoBusqueda) ?>">
  <article class="public-room-card oferta-card h-100 d-flex flex-column">

    <!-- 1. Tutor -->
    <header class="oferta-card-tutor d-flex align-items-center gap-3">
      <?= Vista::avatar($oferta['tutor'], $oferta['tutor_foto'], 'avatar-circle avatar-oferta bg-primary-subtle text-primary') ?>
      <div class="min-w-0 flex-grow-1">
        <h3 class="h6 fw-bold text-dark mb-1 text-truncate"><?= Vista::e($oferta['tutor']) ?></h3>
        <?php if (!empty($perfilTutor)): ?>
          <p class="small text-secondary mb-1 text-truncate"><?= Vista::e(implode(' • ', $perfilTutor)) ?></p>
        <?php endif; ?>
        <p class="small text-secondary mb-0">
          <i class="bi bi-mortarboard-fill text-primary me-1"></i>
          <?php if ($tutoriasDadas > 0): ?>
            <?= $tutoriasDadas ?> <?= $tutoriasDadas === 1 ? 'tutoría dada' : 'tutorías dadas' ?>
          <?php else: ?>
            Tutor/a nuevo/a
          <?php endif; ?>
        </p>
      </div>
    </header>

    <!-- 2. Oferta -->
    <section class="oferta-card-oferta flex-grow-1 d-flex flex-column">
      <span class="oferta-card-label">Tutoría</span>
      <h4 class="h5 fw-bold text-dark mb-2"><?= Vista::e($oferta['materia']) ?></h4>
      <p class="text-secondary small lh-base mb-3 oferta-descripcion"><?= Vista::e($oferta['descripcion']) ?></p>

      <dl class="oferta-card-datos mb-0 mt-auto">
        <div>
          <dt class="oferta-card-label">Modalidad</dt>
          <dd class="mb-0 small text-dark">
            <i class="bi <?= Vista::e(Vista::iconoModalidad($oferta['modalidad'])) ?> text-primary me-1"></i><?= Vista::e(Vista::modalidad($oferta['modalidad'])) ?>
          </dd>
        </div>
        <div>
          <dt class="oferta-card-label">Disponibilidad</dt>
          <dd class="mb-0 small text-dark"><?= Vista::e($oferta['disponibilidad']) ?></dd>
        </div>
        <div>
          <dt class="oferta-card-label">Precio por clase</dt>
          <dd class="mb-0">
            <?php if ($esGratuita): ?>
              <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-semibold"><i class="bi bi-gift me-1"></i>Gratuita</span>
            <?php else: ?>
              <span class="fw-bold text-dark"><?= Vista::e($montoTexto) ?></span>
              <?php if (!empty($medios)): ?>
                <span class="d-block small text-secondary"><?= Vista::e(implode(' • ', $medios)) ?></span>
              <?php endif; ?>
            <?php endif; ?>
          </dd>
        </div>
      </dl>
    </section>

    <!-- 3. Solicitud del alumno sobre esta oferta -->
    <footer class="oferta-card-solicitud d-flex align-items-center justify-content-between gap-3 flex-wrap">
      <?php if (!empty($oferta['mi_solicitud_id'])): ?>
        <?php [$etiquetaEstado, $claseEstado] = Vista::estadoSolicitud($oferta['mi_solicitud_estado']); ?>
        <div class="d-flex flex-column gap-1">
          <span class="badge rounded-pill align-self-start <?= Vista::e($claseEstado) ?>">Solicitud <?= Vista::e(mb_strtolower($etiquetaEstado, 'UTF-8')) ?></span>
          <div class="d-flex align-items-center gap-3">
            <form method="POST" action="index.php?r=cancelar-solicitud">
              <input type="hidden" name="id_solicitud" value="<?= (int) $oferta['mi_solicitud_id'] ?>">
              <button type="submit" class="btn btn-link btn-sm text-danger text-decoration-none p-0">Cancelar solicitud</button>
            </form>
            <button type="button" 
                    class="btn btn-link text-muted p-0 text-decoration-none small" 
                    style="font-size: 0.72rem;"
                    onclick="abrirModalReporte('usuario', <?= (int) $oferta['id_tutor'] ?>, '<?= Vista::e(addslashes($oferta['tutor'])) ?>')">
              <i class="bi bi-flag"></i> Reportar
            </button>
          </div>
        </div>
        <?php if ($oferta['mi_solicitud_estado'] === 'aceptada'): ?>
          <a href="index.php?r=tutoria&amp;id=<?= (int) $oferta['mi_solicitud_id'] ?>" class="btn btn-success rounded-pill fw-bold px-4 d-inline-flex align-items-center gap-2 oferta-card-accion">
            <i class="bi bi-chat-dots"></i> Abrir chat
          </a>
        <?php else: ?>
          <span class="small text-secondary oferta-card-accion text-sm-end">Esperando respuesta del tutor</span>
        <?php endif; ?>
      <?php else: ?>
        <div class="d-flex flex-column">
          <span class="small text-secondary">¿Te sirve esta tutoría?</span>
          <button type="button" 
                  class="btn btn-link text-muted p-0 text-decoration-none text-start small mt-0.5" 
                  style="font-size: 0.72rem;"
                  onclick="abrirModalReporte('usuario', <?= (int) $oferta['id_tutor'] ?>, '<?= Vista::e(addslashes($oferta['tutor'])) ?>')">
            <i class="bi bi-flag"></i> Reportar publicación
          </button>
        </div>
        <button type="button" class="btn btn-discord rounded-pill fw-bold px-4 d-inline-flex align-items-center justify-content-center gap-2 oferta-card-accion"
                data-bs-toggle="modal" data-bs-target="#modalSolicitarTutoria"
                data-id-oferta="<?= (int) $oferta['id_tutoria'] ?>"
                data-materia="<?= Vista::e($oferta['materia']) ?>"
                data-tutor="<?= Vista::e($oferta['tutor']) ?>"
                data-descripcion="<?= Vista::e($oferta['descripcion']) ?>">
          <i class="bi bi-send"></i> Solicitar tutoría
        </button>
      <?php endif; ?>
    </footer>
  </article>
</div>
