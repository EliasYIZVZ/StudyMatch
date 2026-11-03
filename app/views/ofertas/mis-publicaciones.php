<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/**
 * Mis publicaciones: ofertas de tutoría del usuario (rol tutor).
 * Espera: $ofertas, $materias, $errorOfertas, $formulario, $maxDisponibilidad, $maxDescripcion.
 */
$tituloPagina = 'Mis publicaciones';
$scriptsExtra = ['public/js/mis-publicaciones.js'];

// Datos de cada oferta para completar el modal de edición
$datosModal = function (array $oferta) {
    return Vista::e(json_encode([
        'id_oferta' => (int) $oferta['id_tutoria'],
        'id_materia' => (int) $oferta['id_materia'],
        'modalidad' => $oferta['modalidad'],
        'disponibilidad' => $oferta['disponibilidad'],
        'descripcion' => $oferta['descripcion'],
        'precio' => (float) $oferta['precio'] > 0 ? number_format((float) $oferta['precio'], 2, ',', '') : '0',
        'medios_pago' => array_values(array_filter(explode(',', (string) $oferta['medios_pago']))),
    ], JSON_UNESCAPED_UNICODE));
};
?>
<?php require APP_PATH . '/views/partials/privado-head.php'; ?>
<body class="dashboard-body" data-role="tutor">

  <?php $sidebarActivo = 'publicaciones'; require APP_PATH . '/views/partials/sidebar-tutor.php'; ?>

  <div class="dashboard-main-wrapper">
    <?php require APP_PATH . '/views/partials/topbar-privado.php'; ?>

    <main class="p-3 p-md-4 flex-grow-1 view-fade">
      <?php require APP_PATH . '/views/partials/flash.php'; ?>

      <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
        <div>
          <h1 class="page-title fw-bold text-dark mb-1">Mis publicaciones</h1>
          <p class="text-secondary small mb-0">Publicá las materias en las que das tutorías y gestioná su disponibilidad.</p>
        </div>
        <?php if (!$errorOfertas && !empty($materias)): ?>
          <button type="button" class="btn btn-role-action rounded-pill px-4 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalOferta">
            <i class="bi bi-plus-lg me-1"></i> Publicar nueva tutoría
          </button>
        <?php endif; ?>
      </div>

      <?php if ($errorOfertas): ?>
        <div class="public-room-card p-4 p-md-5 text-center">
          <i class="bi bi-exclamation-triangle fs-1 text-warning d-block mb-2"></i>
          <p class="text-secondary mb-0">No se pudieron cargar tus publicaciones. Intentá nuevamente más tarde.</p>
        </div>
      <?php elseif (empty($ofertas)): ?>
        <div class="public-room-card p-4 p-md-5 text-center">
          <i class="bi bi-journal-plus fs-1 text-secondary d-block mb-2"></i>
          <p class="text-secondary mb-0">Todavía no publicaste ninguna tutoría.</p>
          <?php if (empty($materias)): ?>
            <p class="text-secondary small mt-2 mb-0">Aún no hay materias cargadas en el sistema.</p>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($ofertas as $oferta): ?>
            <?php
              $activa = $oferta['estado'] === 'activa';
              $pendientes = (int) $oferta['pendientes'];
              $medios = Vista::mediosPago($oferta['medios_pago']);
            ?>
            <div class="tutoria-item p-3 p-md-4 rounded-4 border bg-white d-flex flex-wrap justify-content-between align-items-start gap-3<?= $activa ? '' : ' oferta-pausada' ?>">
              <div class="flex-grow-1" style="min-width: 240px; max-width: 760px;">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h2 class="h6 fw-bold text-dark mb-0"><?= Vista::e($oferta['materia']) ?></h2>
                  <?php if ($activa): ?>
                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">Activa</span>
                  <?php else: ?>
                    <span class="badge rounded-pill bg-secondary-subtle text-secondary border" style="font-size: 0.7rem;">Pausada</span>
                  <?php endif; ?>
                  <?php if ($pendientes > 0): ?>
                    <a href="index.php?r=mis-tutorias&amp;rol=tutor&amp;estado=pendientes" class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle text-decoration-none" style="font-size: 0.7rem;">
                      <?= $pendientes ?> solicitud<?= $pendientes === 1 ? '' : 'es' ?> pendiente<?= $pendientes === 1 ? '' : 's' ?>
                    </a>
                  <?php endif; ?>
                </div>

                <p class="text-secondary small mt-2 mb-2 lh-base oferta-descripcion-lista"><?= Vista::e($oferta['descripcion']) ?></p>

                <ul class="list-unstyled small text-secondary d-flex flex-wrap gap-3 mb-1">
                  <li class="d-flex align-items-center gap-1"><i class="bi <?= Vista::e(Vista::iconoModalidad($oferta['modalidad'])) ?>"></i><?= Vista::e(Vista::modalidad($oferta['modalidad'])) ?></li>
                  <li class="d-flex align-items-center gap-1"><i class="bi bi-clock"></i><?= Vista::e($oferta['disponibilidad']) ?></li>
                  <li class="d-flex align-items-center gap-1"><i class="bi bi-cash-coin"></i><?= Vista::e(Vista::precio($oferta['precio'])) ?><?= !empty($medios) ? ' • ' . Vista::e(implode(', ', $medios)) : '' ?></li>
                </ul>
                <span class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-calendar-event me-1"></i>Publicada el <?= Vista::e(Vista::fecha($oferta['fecha_publicacion'])) ?></span>
              </div>

              <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                        data-bs-toggle="modal" data-bs-target="#modalOferta" data-oferta="<?= $datosModal($oferta) ?>">
                  <i class="bi bi-pencil me-1"></i>Editar
                </button>
                <form method="POST" action="index.php?r=estado-oferta">
                  <input type="hidden" name="id_oferta" value="<?= (int) $oferta['id_tutoria'] ?>">
                  <?php if ($activa): ?>
                    <button type="submit" name="accion" value="pausar" class="btn btn-sm btn-outline-warning rounded-pill px-3"><i class="bi bi-pause-fill me-1"></i>Pausar</button>
                  <?php else: ?>
                    <button type="submit" name="accion" value="activar" class="btn btn-sm btn-success rounded-pill px-3"><i class="bi bi-play-fill me-1"></i>Activar</button>
                  <?php endif; ?>
                </form>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                        data-bs-toggle="modal" data-bs-target="#modalEliminarOferta"
                        data-id-oferta="<?= (int) $oferta['id_tutoria'] ?>"
                        data-materia="<?= Vista::e($oferta['materia']) ?>"
                        data-con-solicitudes="<?= (int) $oferta['total_solicitudes'] > 0 ? '1' : '0' ?>">
                  <i class="bi bi-trash me-1"></i>Eliminar
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>

  <!-- MODAL: publicar / editar oferta -->
  <div class="modal fade" id="modalOferta" tabindex="-1" aria-labelledby="modalOfertaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <form method="POST" action="index.php?r=guardar-oferta" id="formOferta">
          <div class="modal-header border-bottom px-4 pt-4 pb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="bg-success-subtle text-success p-2 rounded-3"><i class="bi bi-journal-plus fs-5"></i></div>
              <div>
                <h5 class="modal-title fw-bold text-dark" id="modalOfertaLabel">Publicar nueva tutoría</h5>
                <p class="text-secondary small mb-0">Los alumnos verán esta información en su dashboard.</p>
              </div>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>

          <div class="modal-body p-4">
            <input type="hidden" name="id_oferta" id="ofertaId" value="">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="ofertaMateria" class="form-label small fw-semibold text-dark">Materia</label>
                <select class="form-select" id="ofertaMateria" name="id_materia" required>
                  <option value="">Elegí una materia...</option>
                  <?php foreach ($materias as $materia): ?>
                    <option value="<?= (int) $materia['id_materia'] ?>">
                      <?= Vista::e($materia['nombre']) ?><?= $materia['anio_carrera'] ? ' (' . (int) $materia['anio_carrera'] . '° año)' : '' ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label for="ofertaModalidad" class="form-label small fw-semibold text-dark">Modalidad</label>
                <select class="form-select" id="ofertaModalidad" name="modalidad" required>
                  <?php foreach (OfertaController::MODALIDADES as $modalidad): ?>
                    <option value="<?= Vista::e($modalidad) ?>"><?= Vista::e(Vista::modalidad($modalidad)) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label for="ofertaDisponibilidad" class="form-label small fw-semibold text-dark">Disponibilidad</label>
                <input type="text" class="form-control" id="ofertaDisponibilidad" name="disponibilidad" required maxlength="<?= (int) $maxDisponibilidad ?>"
                       placeholder="Ej. Lunes y miércoles de 18:00 a 20:00">
              </div>
              <div class="col-12">
                <label for="ofertaDescripcion" class="form-label small fw-semibold text-dark">Descripción</label>
                <textarea class="form-control" id="ofertaDescripcion" name="descripcion" rows="4" required maxlength="<?= (int) $maxDescripcion ?>"
                          placeholder="Qué temas cubrís, cómo trabajás, para qué instancia (parcial, final, TP)..."></textarea>
                <div class="text-end text-secondary small mt-1"><span id="ofertaDescripcionContador">0</span> / <?= (int) $maxDescripcion ?></div>
              </div>
              <div class="col-md-5">
                <label for="ofertaPrecio" class="form-label small fw-semibold text-dark">Precio por clase (ARS)</label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input type="text" inputmode="decimal" class="form-control" id="ofertaPrecio" name="precio" value="0" placeholder="0">
                </div>
                <div class="form-text">Dejá 0 para una tutoría gratuita.</div>
              </div>
              <div class="col-md-7">
                <span class="form-label small fw-semibold text-dark d-block">Medios de pago</span>
                <div class="d-flex flex-wrap gap-3 pt-1" id="ofertaMedios">
                  <?php foreach (OfertaController::MEDIOS_PAGO as $medio): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="medios_pago[]" value="<?= Vista::e($medio) ?>" id="medio_<?= Vista::e($medio) ?>">
                      <label class="form-check-label small" for="medio_<?= Vista::e($medio) ?>"><?= Vista::e(implode('', Vista::mediosPago($medio))) ?></label>
                    </div>
                  <?php endforeach; ?>
                </div>
                <div class="form-text" id="ofertaMediosAyuda">Solo si la tutoría tiene precio.</div>
              </div>
            </div>
          </div>

          <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success px-4 rounded-pill fw-semibold shadow-sm" id="btnGuardarOferta">
              <i class="bi bi-check2 me-1"></i><span id="btnGuardarOfertaTexto">Publicar</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL: confirmar eliminación -->
  <div class="modal fade" id="modalEliminarOferta" tabindex="-1" aria-labelledby="modalEliminarOfertaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <form method="POST" action="index.php?r=estado-oferta">
          <input type="hidden" name="id_oferta" id="eliminarOfertaId" value="">
          <input type="hidden" name="accion" value="eliminar">
          <div class="modal-body p-4 text-center">
            <i class="bi bi-trash text-danger fs-1 d-block mb-2"></i>
            <h5 class="fw-bold text-dark mb-2" id="modalEliminarOfertaLabel">¿Eliminar la publicación de <span id="eliminarOfertaMateria"></span>?</h5>
            <p class="text-secondary small mb-0" id="eliminarOfertaTexto"></p>
          </div>
          <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4 justify-content-center">
            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger px-4 rounded-pill fw-semibold" id="btnConfirmarEliminar">Eliminar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php if ($formulario !== null): ?>
    <!-- Datos del último envío con error: el modal se reabre con lo que se había escrito -->
    <script type="application/json" id="ofertaFormularioPrevio"><?= json_encode(array_merge(
        ['id_oferta' => $formulario['id_oferta']],
        $formulario['datos']
    ), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
  <?php endif; ?>

  <?php require APP_PATH . '/views/partials/privado-scripts.php'; ?>
</body>
</html>
