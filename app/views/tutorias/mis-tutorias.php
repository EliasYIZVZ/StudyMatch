<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/**
 * Mis tutorías: vista compartida entre los roles alumno y tutor.
 * Espera: $rol, $filtro, $cantidades, $tutorias, $noLeidosAlumno, $noLeidosTutor, $errorTutorias.
 */
$tituloPagina = 'Mis tutorías';
$volver = 'mis-tutorias-' . $rol;
$filtros = [
    'todas'      => ['Todas', 'bi-list-ul'],
    'pendientes' => ['Pendientes', 'bi-hourglass-split'],
    'en-curso'   => ['En curso', 'bi-chat-dots'],
    'historial'  => ['Historial', 'bi-archive'],
];
$sufijoFiltro = $filtro !== 'todas' ? '&amp;estado=' . Vista::e($filtro) : '';
?>
<?php require APP_PATH . '/views/partials/privado-head.php'; ?>
<body class="dashboard-body" data-role="<?= $rol ?>">

  <?php $sidebarActivo = ($rol === 'tutor' && $filtro === 'pendientes') ? 'solicitudes' : 'tutorias'; require APP_PATH . '/views/partials/sidebar-' . $rol . '.php'; ?>

  <div class="dashboard-main-wrapper">
    <?php require APP_PATH . '/views/partials/topbar-privado.php'; ?>

    <main class="p-3 p-md-4 flex-grow-1 view-fade">
      <?php require APP_PATH . '/views/partials/flash.php'; ?>

      <div class="mb-4">
        <h1 class="page-title fw-bold text-dark mb-1">Mis tutorías</h1>
        <p class="text-secondary small mb-0">Seguí tus solicitudes, coordiná los encuentros por chat y revisá tu historial.</p>
      </div>

      <!-- Pestañas por rol -->
      <ul class="nav nav-pills gap-2 mb-4">
        <li class="nav-item">
          <a class="nav-link rounded-pill d-flex align-items-center gap-2<?= $rol === 'alumno' ? ' active' : '' ?>" href="index.php?r=mis-tutorias&amp;rol=alumno<?= $sufijoFiltro ?>">
            <i class="bi bi-mortarboard"></i> Como alumno
            <?php if ($noLeidosAlumno > 0): ?><span class="badge rounded-pill bg-danger"><?= (int) $noLeidosAlumno ?></span><?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link rounded-pill d-flex align-items-center gap-2<?= $rol === 'tutor' ? ' active' : '' ?>" href="index.php?r=mis-tutorias&amp;rol=tutor<?= $sufijoFiltro ?>">
            <i class="bi bi-person-video3"></i> Como tutor
            <?php if ($noLeidosTutor > 0): ?><span class="badge rounded-pill bg-danger"><?= (int) $noLeidosTutor ?></span><?php endif; ?>
          </a>
        </li>
      </ul>

      <!-- Filtros por estado -->
      <div class="d-flex flex-wrap gap-2 mb-4" role="group" aria-label="Filtrar por estado">
        <?php foreach ($filtros as $clave => $datosFiltro): ?>
          <a href="index.php?r=mis-tutorias&amp;rol=<?= $rol ?><?= $clave !== 'todas' ? '&amp;estado=' . $clave : '' ?>"
             class="btn btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1 filtro-estado<?= $filtro === $clave ? ' active' : '' ?>"
             <?= $filtro === $clave ? 'aria-current="page"' : '' ?>>
            <i class="bi <?= $datosFiltro[1] ?>"></i> <?= $datosFiltro[0] ?>
            <span class="badge rounded-pill <?= $filtro === $clave ? 'bg-light text-dark' : 'bg-secondary-subtle text-secondary' ?>"><?= (int) $cantidades[$clave] ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($errorTutorias): ?>
        <div class="public-room-card p-4 p-md-5 text-center">
          <i class="bi bi-exclamation-triangle fs-1 text-warning d-block mb-2"></i>
          <p class="text-secondary mb-0">No se pudieron cargar tus tutorías. Intentá nuevamente más tarde.</p>
        </div>
      <?php elseif (empty($tutorias) && $cantidades['todas'] > 0): ?>
        <div class="public-room-card p-4 p-md-5 text-center">
          <i class="bi bi-funnel fs-1 text-secondary d-block mb-2"></i>
          <p class="text-secondary mb-3">
            <?php if ($filtro === 'pendientes'): ?>
              <?= $rol === 'tutor' ? 'No tenés solicitudes pendientes de respuesta. ¡Estás al día!' : 'No tenés solicitudes esperando respuesta.' ?>
            <?php elseif ($filtro === 'en-curso'): ?>
              No tenés tutorías en curso.
            <?php else: ?>
              Todavía no hay tutorías en tu historial.
            <?php endif; ?>
          </p>
          <a href="index.php?r=mis-tutorias&amp;rol=<?= $rol ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-4">Ver todas</a>
        </div>
      <?php elseif (empty($tutorias)): ?>
        <div class="public-room-card p-4 p-md-5 text-center">
          <i class="bi bi-calendar-x fs-1 text-secondary d-block mb-2"></i>
          <?php if ($rol === 'alumno'): ?>
            <p class="text-secondary mb-3">Todavía no solicitaste ninguna tutoría.</p>
            <a href="index.php?r=dashboard#salas-publicas" class="btn btn-primary btn-sm rounded-pill px-4">Buscar una tutoría</a>
          <?php else: ?>
            <p class="text-secondary mb-3">Todavía no recibiste solicitudes en tus ofertas.</p>
            <a href="index.php?r=dashboard-tutor" class="btn btn-success btn-sm rounded-pill px-4">Ir al panel de tutor</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($tutorias as $t): ?>
            <?php
              $otro = $rol === 'tutor' ? $t['alumno'] : $t['tutor'];
              list($estadoTexto, $estadoClase) = Vista::estadoSolicitud($t['estado']);
              $noLeidos = (int) $t['no_leidos'];
            ?>
            <div class="tutoria-item p-3 rounded-4 border bg-white d-flex flex-wrap align-items-center justify-content-between gap-3">
              <a href="index.php?r=tutoria&amp;id=<?= (int) $t['id_solicitud'] ?>" class="d-flex align-items-start gap-3 text-decoration-none flex-grow-1">
                <?= Vista::avatar($otro, $rol === 'tutor' ? $t['alumno_foto'] : $t['tutor_foto']) ?>
                <div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h2 class="h6 fw-bold text-dark mb-0"><?= Vista::e($t['materia']) ?></h2>
                    <span class="badge rounded-pill <?= $estadoClase ?>" style="font-size: 0.7rem;"><?= Vista::e($estadoTexto) ?></span>
                    <?php if ($noLeidos > 0): ?>
                      <span class="badge rounded-pill bg-danger" style="font-size: 0.7rem;"><?= $noLeidos ?> mensaje<?= $noLeidos === 1 ? '' : 's' ?> nuevo<?= $noLeidos === 1 ? '' : 's' ?></span>
                    <?php endif; ?>
                  </div>
                  <span class="text-secondary small d-block mt-1"><?= $rol === 'tutor' ? 'Alumno' : 'Tutor' ?>: <?= Vista::e($otro) ?></span>
                  <span class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-clock me-1"></i>Solicitada el <?= Vista::e(Vista::fecha($t['fecha'])) ?></span>
                </div>
              </a>
              <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                <?php require APP_PATH . '/views/partials/tutoria-acciones.php'; ?>
                <a href="index.php?r=tutoria&amp;id=<?= (int) $t['id_solicitud'] ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                  <?php if ($t['estado'] === 'aceptada'): ?><i class="bi bi-chat-dots me-1"></i>Abrir chat<?php else: ?>Ver detalle<?php endif; ?>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>

  <?php require APP_PATH . '/views/partials/privado-scripts.php'; ?>
</body>
</html>
