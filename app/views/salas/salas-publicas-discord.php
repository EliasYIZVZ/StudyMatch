<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/**
 * Salas públicas de Discord: vista compartida entre los roles alumno y tutor.
 * Un único listado con buscador: las salas de la comunidad y las propias
 * (estas se filtran con "Mis salas").
 * Espera: $rol, $salas, $idUsuario, $errorSalas, $formulario, $puedePublicar.
 */
$tituloPagina = 'Salas públicas de Discord';
$scriptsExtra = ['public/js/buscador-cards.js', 'public/js/salas-publicas.js?v=20261113'];
?>
<?php require APP_PATH . '/views/partials/privado-head.php'; ?>
<body class="dashboard-body" data-role="<?= $rol ?>">

  <?php $sidebarActivo = 'salas'; require APP_PATH . '/views/partials/sidebar-' . $rol . '.php'; ?>

  <div class="dashboard-main-wrapper">
    <?php require APP_PATH . '/views/partials/topbar-privado.php'; ?>

    <main class="p-3 p-md-4 flex-grow-1 view-fade">
      <?php require APP_PATH . '/views/partials/flash.php'; ?>

      <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
        <div>
          <h1 class="page-title fw-bold text-dark mb-1">Salas públicas de Discord</h1>
          <p class="text-secondary small mb-0">Sumate a las salas de estudio de otros alumnos de la UTN o publicá la tuya.</p>
        </div>
        <?php if ($puedePublicar): ?>
          <button type="button" class="btn btn-role-action rounded-pill px-4 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSala">
            <i class="bi bi-plus-lg me-1"></i> Publicar sala
          </button>
        <?php endif; ?>
      </div>

      <?php if (!$errorSalas && !$puedePublicar): ?>
        <p class="text-secondary small mb-3"><i class="bi bi-info-circle me-1"></i>Llegaste al máximo de <?= (int) SalaController::MAX_SALAS_POR_USUARIO ?> salas publicadas. Eliminá una para publicar otra.</p>
      <?php endif; ?>

      <?php
      $buscador = [
          'grid' => 'gridResultadosSalas',
          'pagina' => 0,
          'placeholder' => 'Sala, tema, materia o creador…',
          'materias' => ['propia' => 'Mis salas'],
          'todas' => 'Todas las salas',
          'filtro' => 'Filtrar salas',
          'total' => count($salas),
          'unidad' => 'salas',
      ];
      require APP_PATH . '/views/partials/buscador-compacto.php';
      ?>

      <!-- ======================================================================
           SALAS DE LA COMUNIDAD (más las propias, que se filtran con "Mis salas")
           ====================================================================== -->
      <?php if ($errorSalas): ?>
        <div class="public-room-card p-4 p-md-5 text-center">
          <i class="bi bi-exclamation-triangle fs-1 text-warning d-block mb-2"></i>
          <p class="text-secondary mb-0">No se pudieron cargar las salas. Intentá nuevamente más tarde.</p>
        </div>
      <?php elseif (empty($salas)): ?>
        <div class="public-room-card p-4 p-md-5 text-center">
          <i class="bi bi-discord fs-1 text-secondary d-block mb-2"></i>
          <p class="text-secondary mb-0">Todavía no hay salas publicadas. ¡Publicá la primera!</p>
        </div>
      <?php else: ?>
        <section aria-label="Salas de la comunidad">
          <h2 class="h5 fw-bold text-dark mb-3">Salas de la comunidad</h2>
          <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="gridResultadosSalas">
            <?php foreach ($salas as $posicion => $sala): ?>
              <?php
                $propia = (int) $sala['id_creador'] === (int) $idUsuario;
                $activa = $sala['estado'] === 'activa';
                $busqueda = $sala['titulo'] . ' ' . (string) $sala['descripcion'] . ' ' . $sala['creador_nombre'] . ($propia ? ' mi sala mis salas' : '');
              ?>
              <div class="col buscador-item"
                   data-busqueda="<?= Vista::e($busqueda) ?>"
                   data-materias="<?= $propia ? 'propia' : 'comunidad' ?>"
                   data-orden="<?= (int) $posicion ?>">
                <article class="public-room-card h-100 d-flex flex-column p-4<?= $activa ? '' : ' oferta-pausada' ?>">
                  <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1">
                      <i class="bi bi-discord"></i> Sala de Discord
                    </span>
                    <?php if ($propia): ?>
                      <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle small">Mi sala</span>
                    <?php endif; ?>
                    <?php if (!$activa): ?>
                      <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle small">Suspendida</span>
                    <?php endif; ?>
                  </div>

                  <h3 class="h6 fw-bold text-dark mb-2"><?= Vista::e($sala['titulo']) ?></h3>
                  <p class="text-secondary small lh-base mb-3 flex-grow-1"><?= Vista::e($sala['descripcion'] ?: 'Espacio de estudio libre para consultas.') ?></p>

                  <?php if (!$activa): ?>
                    <p class="small text-danger mb-3">
                      <i class="bi bi-shield-exclamation me-1"></i>Suspendida por moderación<?= $sala['motivo_suspension'] ? ': ' . Vista::e($sala['motivo_suspension']) : '.' ?>
                    </p>
                  <?php endif; ?>

                  <div class="d-flex align-items-center justify-content-between gap-2 pt-3 border-top mt-auto">
                    <div class="d-flex flex-column min-w-0">
                      <small class="text-muted text-truncate"><i class="bi bi-person me-1"></i><?= $propia ? 'Vos' : Vista::e($sala['creador_nombre']) ?></small>
                      <?php if ($propia && $activa): ?>
                        <button type="button" class="btn btn-link text-danger p-0 text-decoration-none text-start small" style="font-size: 0.72rem;"
                                data-bs-toggle="modal" data-bs-target="#modalEliminarSala"
                                data-id-sala="<?= (int) $sala['id_sala'] ?>" data-titulo="<?= Vista::e($sala['titulo']) ?>">
                          <i class="bi bi-trash"></i> Eliminar sala
                        </button>
                      <?php elseif (!$propia): ?>
                        <button type="button" class="btn btn-link text-muted p-0 text-decoration-none text-start small btn-reportar-sala" style="font-size: 0.72rem;"
                                data-id-sala="<?= (int) $sala['id_sala'] ?>" data-titulo="<?= Vista::e($sala['titulo']) ?>">
                          <i class="bi bi-flag"></i> Reportar sala
                        </button>
                      <?php endif; ?>
                    </div>
                    <?php if ($activa): ?>
                      <a href="<?= Vista::e($sala['enlace_discord']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-discord btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-2 shadow-sm flex-shrink-0">
                        <i class="bi bi-discord"></i>
                        <span><?= $propia ? 'Abrir' : 'Unirse a Discord' ?></span>
                      </a>
                    <?php endif; ?>
                  </div>
                </article>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <!-- Estado vacío: ningún filtro coincide -->
      <div id="buscadorSinResultados" class="text-center py-5 d-none">
        <div class="avatar-circle mx-auto mb-3 bg-light text-secondary fs-3" style="width: 64px; height: 64px;">
          <i class="bi bi-search"></i>
        </div>
        <h5 class="fw-bold text-dark">No se encontraron salas con esos criterios</h5>
        <p class="text-secondary small mb-3">Probá con otra palabra o elegí "Todas las salas".</p>
        <button type="button" class="btn btn-outline-primary rounded-pill px-4 btn-sm" id="buscadorRestablecer">
          Restablecer filtros
        </button>
      </div>
    </main>
  </div>

  <!-- MODAL: publicar sala -->
  <div class="modal fade" id="modalSala" tabindex="-1" aria-labelledby="modalSalaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <form method="POST" action="index.php?r=guardar-sala" id="formSala">
          <input type="hidden" name="rol" value="<?= $rol ?>">
          <div class="modal-header border-bottom px-4 pt-4 pb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="bg-primary-subtle text-primary p-2 rounded-3"><i class="bi bi-discord fs-5"></i></div>
              <div>
                <h5 class="modal-title fw-bold text-dark" id="modalSalaLabel">Publicar sala de Discord</h5>
                <p class="text-secondary small mb-0">La van a ver todos los usuarios de Study Match.</p>
              </div>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>

          <div class="modal-body p-4">
            <div class="mb-3">
              <label for="salaTitulo" class="form-label small fw-semibold text-dark">Título</label>
              <input type="text" class="form-control" id="salaTitulo" name="titulo" required maxlength="<?= (int) SalaController::MAX_TITULO ?>"
                     placeholder="Ej. Repaso de Programación II para el parcial">
            </div>
            <div class="mb-3">
              <label for="salaEnlace" class="form-label small fw-semibold text-dark">Enlace de invitación</label>
              <input type="url" class="form-control" id="salaEnlace" name="enlace_discord" required maxlength="<?= (int) SalaController::MAX_ENLACE ?>"
                     placeholder="https://discord.gg/abc123">
              <div class="form-text">Solo enlaces de Discord: <code>https://discord.gg/…</code> o <code>https://discord.com/invite/…</code></div>
            </div>
            <div>
              <label for="salaDescripcion" class="form-label small fw-semibold text-dark">Descripción <span class="text-secondary fw-normal">(opcional)</span></label>
              <textarea class="form-control" id="salaDescripcion" name="descripcion" rows="3" maxlength="<?= (int) SalaController::MAX_DESCRIPCION ?>"
                        placeholder="Qué materia o temas se tratan, días y horarios..."></textarea>
              <div class="text-end text-secondary small mt-1"><span id="salaDescripcionContador">0</span> / <?= (int) SalaController::MAX_DESCRIPCION ?></div>
            </div>
          </div>

          <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-role-action px-4 rounded-pill fw-semibold shadow-sm">
              <i class="bi bi-check2 me-1"></i>Publicar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL: confirmar eliminación -->
  <div class="modal fade" id="modalEliminarSala" tabindex="-1" aria-labelledby="modalEliminarSalaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <form method="POST" action="index.php?r=eliminar-sala">
          <input type="hidden" name="rol" value="<?= $rol ?>">
          <input type="hidden" name="id_sala" id="eliminarSalaId" value="">
          <div class="modal-body p-4 text-center">
            <i class="bi bi-trash text-danger fs-1 d-block mb-2"></i>
            <h5 class="fw-bold text-dark mb-2" id="modalEliminarSalaLabel">¿Eliminar la sala <span id="eliminarSalaTitulo"></span>?</h5>
            <p class="text-secondary small mb-0">Deja de mostrarse a la comunidad. Esta acción no se puede deshacer.</p>
          </div>
          <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4 justify-content-center">
            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger px-4 rounded-pill fw-semibold">Eliminar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php if ($formulario !== null): ?>
    <!-- Datos del último envío con error: el modal se reabre con lo que se había escrito -->
    <script type="application/json" id="salaFormularioPrevio"><?= json_encode($formulario, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
  <?php endif; ?>

  <?php require APP_PATH . '/views/partials/modal-reportar.php'; ?>
  <?php require APP_PATH . '/views/partials/privado-scripts.php'; ?>
</body>
</html>
