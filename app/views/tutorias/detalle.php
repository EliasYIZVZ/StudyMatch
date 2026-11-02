<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/**
 * Detalle de una tutoría con su chat.
 * Espera: $tutoria, $rol, $mensajes, $idUsuario, $chatHabilitado, $chatVisible, $maxMensaje.
 */
$t = $tutoria;
$tituloPagina = 'Tutoría de ' . $t['materia'];
$volver = 'tutoria';
$otro = $rol === 'tutor' ? $t['alumno'] : $t['tutor'];
list($estadoTexto, $estadoClase) = Vista::estadoSolicitud($t['estado']);

$modalidades = ['Virtual' => 'Virtual', 'Presencial en UTN' => 'Presencial en UTN', 'Hibrido' => 'Híbrido'];
$nombresMedios = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'mercado_pago' => 'Mercado Pago'];
$medios = [];
foreach (explode(',', (string) $t['medios_pago']) as $medio) {
    if (isset($nombresMedios[$medio])) {
        $medios[] = $nombresMedios[$medio];
    }
}
$precio = (float) $t['precio'] > 0
    ? '$ ' . number_format((float) $t['precio'], 2, ',', '.') . ' por clase'
    : 'Gratuita';

$ultimoMensaje = end($mensajes);
$ultimoId = $ultimoMensaje ? (int) $ultimoMensaje['id_mensaje'] : 0;
$scriptsExtra = ['public/js/chat-tutoria.js'];
?>
<?php require APP_PATH . '/views/partials/privado-head.php'; ?>
<body class="dashboard-body" data-role="<?= $rol ?>">

  <?php $sidebarActivo = 'tutorias'; require APP_PATH . '/views/partials/sidebar-' . $rol . '.php'; ?>

  <div class="dashboard-main-wrapper">
    <?php require APP_PATH . '/views/partials/topbar-privado.php'; ?>

    <main class="p-3 p-md-4 flex-grow-1 view-fade">
      <?php require APP_PATH . '/views/partials/flash.php'; ?>

      <a href="index.php?r=mis-tutorias&amp;rol=<?= $rol ?>" class="small text-decoration-none d-inline-flex align-items-center gap-1 mb-3">
        <i class="bi bi-arrow-left"></i> Volver a Mis tutorías
      </a>

      <div class="row g-4">
        <!-- Datos de la tutoría -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1"><?= Vista::e($t['materia']) ?></span>
              <span class="badge rounded-pill <?= $estadoClase ?>"><?= Vista::e($estadoTexto) ?></span>
            </div>
            <h1 class="h5 fw-bold text-dark mb-3"><?= $rol === 'tutor' ? 'Alumno' : 'Tutor' ?>: <?= Vista::e($otro) ?></h1>

            <ul class="list-unstyled small text-secondary d-flex flex-column gap-2 mb-3">
              <li class="d-flex gap-2"><i class="bi bi-laptop"></i><span><?= Vista::e(isset($modalidades[$t['modalidad']]) ? $modalidades[$t['modalidad']] : $t['modalidad']) ?></span></li>
              <li class="d-flex gap-2"><i class="bi bi-clock"></i><span><?= Vista::e($t['disponibilidad']) ?></span></li>
              <li class="d-flex gap-2"><i class="bi bi-cash-coin"></i><span><?= Vista::e($precio) ?><?= !empty($medios) ? ' • ' . Vista::e(implode(', ', $medios)) : '' ?></span></li>
              <li class="d-flex gap-2"><i class="bi bi-calendar-event"></i><span>Solicitada el <?= Vista::e(Vista::fecha($t['fecha'])) ?></span></li>
            </ul>

            <div class="border-top pt-3 mb-3">
              <span class="small fw-semibold text-dark d-block mb-1">Mensaje de la solicitud</span>
              <?php if ($t['mensaje'] !== null && $t['mensaje'] !== ''): ?>
                <p class="small text-secondary mb-0 chat-texto">"<?= Vista::enlazar($t['mensaje']) ?>"</p>
              <?php else: ?>
                <p class="small text-secondary fst-italic mb-0">Sin mensaje.</p>
              <?php endif; ?>
            </div>

            <div class="mt-auto d-flex flex-wrap gap-2">
              <?php require APP_PATH . '/views/partials/tutoria-acciones.php'; ?>
            </div>
          </div>
        </div>

        <!-- Chat -->
        <div class="col-lg-8">
          <section id="chat" class="card border-0 shadow-sm rounded-4 bg-white h-100 d-flex flex-column chat-card"
                   data-id-solicitud="<?= (int) $t['id_solicitud'] ?>"
                   data-ultimo-id="<?= $ultimoId ?>"
                   data-habilitado="<?= $chatHabilitado ? '1' : '0' ?>">
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
              <h2 class="h6 fw-bold text-dark mb-0"><i class="bi bi-chat-dots me-2"></i>Chat de la tutoría</h2>
              <?php if (!$chatHabilitado && $chatVisible): ?>
                <span class="badge bg-secondary-subtle text-secondary border rounded-pill"><i class="bi bi-lock me-1"></i>Solo lectura</span>
              <?php endif; ?>
            </div>

            <?php if ($chatVisible): ?>
              <div id="chatMensajes" class="chat-mensajes flex-grow-1 p-3 p-md-4" aria-live="polite">
                <?php if (empty($mensajes)): ?>
                  <p id="chatVacio" class="text-center text-secondary small my-auto">Todavía no hay mensajes. ¡Escribí para coordinar el primer encuentro!</p>
                <?php endif; ?>
                <?php foreach ($mensajes as $m): ?>
                  <div class="chat-msg<?= (int) $m['id_emisor'] === $idUsuario ? ' chat-msg-propio' : '' ?>" data-id="<?= (int) $m['id_mensaje'] ?>">
                    <div class="chat-burbuja chat-texto"><?= Vista::enlazar($m['contenido']) ?></div>
                    <div class="chat-meta"><?= Vista::e(Vista::fecha($m['fecha'], 'd/m H:i')) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="flex-grow-1 d-flex align-items-center justify-content-center p-4 text-center">
                <div>
                  <i class="bi bi-chat-square-text fs-1 text-secondary d-block mb-2"></i>
                  <p class="text-secondary small mb-0">
                    <?php if ($t['estado'] === 'pendiente'): ?>
                      El chat se habilita cuando el tutor acepte la solicitud.
                    <?php elseif ($t['estado'] === 'rechazada'): ?>
                      La solicitud fue rechazada: no hay chat disponible.
                    <?php else: ?>
                      La solicitud fue cancelada antes de iniciar el chat.
                    <?php endif; ?>
                  </p>
                </div>
              </div>
            <?php endif; ?>

            <div class="border-top px-3 px-md-4 py-3">
              <?php if ($chatHabilitado): ?>
                <form id="chatForm" method="POST" action="index.php?r=enviar-mensaje" class="d-flex align-items-start gap-2">
                  <input type="hidden" name="id_solicitud" value="<?= (int) $t['id_solicitud'] ?>">
                  <div class="flex-grow-1">
                    <label for="chatTexto" class="visually-hidden">Mensaje</label>
                    <textarea id="chatTexto" name="contenido" class="form-control rounded-4" rows="2" maxlength="<?= (int) $maxMensaje ?>" required
                              placeholder="Escribí un mensaje o pegá un link. Enter envía, Shift+Enter hace un salto de línea."></textarea>
                    <div class="d-flex justify-content-between small mt-1">
                      <span id="chatError" class="text-danger" role="alert"></span>
                      <span class="text-secondary"><span id="chatContador">0</span> / <?= (int) $maxMensaje ?></span>
                    </div>
                  </div>
                  <button type="submit" id="chatEnviar" class="btn btn-primary rounded-pill px-3 py-2" title="Enviar">
                    <i class="bi bi-send-fill"></i><span class="visually-hidden">Enviar</span>
                  </button>
                </form>
              <?php elseif ($t['estado'] === 'finalizada'): ?>
                <p class="small text-secondary mb-0 text-center"><i class="bi bi-lock me-1"></i>La tutoría finalizó: el chat quedó en modo solo lectura.</p>
              <?php elseif ($t['estado'] === 'cancelada' && $chatVisible): ?>
                <p class="small text-secondary mb-0 text-center"><i class="bi bi-lock me-1"></i>La tutoría fue cancelada: el chat quedó en modo solo lectura.</p>
              <?php elseif ($t['estado'] === 'pendiente'): ?>
                <p class="small text-secondary mb-0 text-center"><i class="bi bi-hourglass-split me-1"></i>Esperando la respuesta del tutor.</p>
              <?php else: ?>
                <p class="small text-secondary mb-0 text-center"><i class="bi bi-lock me-1"></i>Chat no disponible.</p>
              <?php endif; ?>
            </div>
          </section>
        </div>
      </div>
    </main>
  </div>

  <?php require APP_PATH . '/views/partials/privado-scripts.php'; ?>
</body>
</html>
